<?php

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

//Include the dependencies needed to instantiate the block.
add_action('woocommerce_blocks_loaded', function() {
    require_once __DIR__ . '/vat-number-block-integration.php';
	add_action(
		'woocommerce_blocks_checkout_block_registration',
		function( $integration_registry ) {
			$integration_registry->register( new WC_Szamlazz_VAT_Number_Block_Integration() );
		}
	);
	
	//Extends the cart schema to include the vat number values
	if(function_exists('woocommerce_store_api_register_endpoint_data')) {
		require_once __DIR__ . '/vat-number-block-endpoints.php';
		WC_Szamlazz_VAT_Number_Block_Extend_Store_Endpoint::init();
	}
	
	//Save order meta
	add_action('woocommerce_store_api_checkout_update_order_from_request', function( \WC_Order $order, \WP_REST_Request $request ) {
		$request_data = $request['extensions']['wc-szamlazz-vat-number'];
		if($request_data['customer_type'] == 'company') {

			//If company name is missing, ask for it
			if(!$request['billing_address']['company']) {
				throw new Exception(apply_filters('wc_szamlazz_company_validation_required_message', esc_html__( 'If you enter a VAT number, the company name field is required.', 'wc-szamlazz')));
			}

			//Check if VAT number is EU, in that case we need to validate the billing country too
			$vat_number = $request_data['billing_vat_number'];
			if(preg_match('/^[A-Z]{2}/', $vat_number)) {
				$country_code = substr($vat_number, 0, 2);
				$billing_country = $request['billing_address']['country'];
				$billing_country = WC_Szamlazz_Vat_Number_Field::get_vat_number_prefix($billing_country);
				if($country_code != $billing_country) {
					throw new Exception(apply_filters('wc_szamlazz_eu_vat_number_validation_country_mismatch_message', esc_html__( 'The VAT number is from another country, please select that country in the billing address.', 'wc-szamlazz')));
				}
			}
			
			//Validate it if we don't have data, this will also return the data to save
			$vat_number_data = WC()->session->get( 'vat-number-data' );
			if(!isset($vat_number_data['valid']) || !isset($vat_number_data['name'])) {
				if(preg_match('/^[A-Z]{2}/', $vat_number)) {
					$vat_number_data = WC_Szamlazz_Vat_Number_Field::get_eu_vat_number_data($vat_number);
				} else {
					$vat_number_data = WC_Szamlazz_Vat_Number_Field::get_vat_number_data($vat_number);
				}
			}

			//Maybe validate it again just in case?
			if(!$vat_number_data['valid']) {
				throw new Exception(apply_filters('wc_szamlazz_vat_number_validation_failed_message', esc_html__( 'The VAT number is invalid.', 'wc-szamlazz')));
			}

			//Save data
			$order->update_meta_data( '_billing_wc_szamlazz_adoszam', $vat_number );
			$order->update_meta_data( '_wc_szamlazz_adoszam_data', $vat_number_data );
			$order->save();

			//Update customer meta too
			if(is_user_logged_in()) {
				$customer_id = get_current_user_id();
				update_user_meta( $customer_id, 'wc_szamlazz_adoszam', $vat_number );
			}

		}

		//If a company name was entered, but not a vat number, throw error
		if($request_data['customer_type'] == 'individual' && $request['billing_address']['company']) {
			$eu_countries = WC()->countries->get_european_union_countries();
			$check_eu_vat = (WC_Szamlazz()->get_option('vat_number_eu', 'no') == 'yes');
			$billing_country = $request['billing_address']['country'] ?? '';
			if($billing_country == 'HU' || ($check_eu_vat && in_array($billing_country, $eu_countries))) {
				throw new Exception(apply_filters('wc_szamlazz_tax_validation_required_message', esc_html__( 'If you enter a company name, the VAT number field is required.', 'wc-szamlazz')));
			}
		}
	}, 10, 2);

	//Keep the VAT exemption in sync on every cart calculation during Store API (block)
	//requests. The classic checkout hooks (update_order_review, checkout_process) never run
	//here, and the Store API update callback only runs when the VAT field value changes, so
	//both the EU VAT number and the "company outside the EU" exemptions are handled here.
	add_action('woocommerce_before_calculate_totals', function( $cart ) {

		//Only for the block/Store API flow, so the classic checkout logic is untouched.
		if( ! ( function_exists('WC') && WC()->is_rest_api_request() ) ) {
			return;
		}

		$eu_vat_exempt = ( WC_Szamlazz()->get_option('eu_vat_exempt', 'yes') == 'yes' );
		$abroad_exempt = ( WC_Szamlazz()->get_option('vat_exempt_abroad', 'no') == 'yes' );
		if( ! $eu_vat_exempt && ! $abroad_exempt ) {
			return;
		}

		if( ! WC()->customer || ! WC()->session ) {
			return;
		}

		$is_exempt = false;

		//EU VAT number exemption
		if( $eu_vat_exempt ) {

			//Resolve the VAT number: prefer the value stored during checkout, fall back to saved user meta.
			$vat_number_data = WC()->session->get( 'vat-number-data' );
			$vat_number    = ( is_array($vat_number_data) && ! empty($vat_number_data['vat_number']) ) ? $vat_number_data['vat_number'] : '';
			$customer_type = ( is_array($vat_number_data) && isset($vat_number_data['customer_type']) ) ? $vat_number_data['customer_type'] : '';

			if( $vat_number === '' && is_user_logged_in() ) {
				$saved = get_user_meta( get_current_user_id(), 'wc_szamlazz_adoszam', true );
				if( $saved ) {
					$vat_number    = $saved;
					$customer_type = 'company';
				}
			}

			//Exempt only company purchases with a non-Hungarian EU VAT number (two-letter prefix).
			if( $customer_type === 'company' && preg_match('/^[A-Z]{2}/', strtoupper($vat_number)) ) {
				$country_code = strtoupper( substr($vat_number, 0, 2) );
				if( $country_code !== 'HU' ) {
					$is_exempt = true;
				}
			}

			$is_exempt = apply_filters( 'wc_szamlazz_should_set_eu_vat_exempt', $is_exempt, array( 'wc_szamlazz_adoszam' => $vat_number ) );
		}

		//Company outside the EU buying only virtual products (block version of vat_exempt_abroad).
		//The Store API saves the billing address on the customer before totals are calculated.
		if( ! $is_exempt && $abroad_exempt ) {
			$billing_company = WC()->customer->get_billing_company();
			$billing_country = WC()->customer->get_billing_country();
			$eu_countries    = WC()->countries->get_european_union_countries('eu_vat');

			if(
				! empty($billing_company) &&
				! empty($billing_country) &&
				! in_array($billing_country, $eu_countries) &&
				! $cart->needs_shipping()
			) {
				$is_exempt = true;
			}
		}

		if( WC()->customer->get_is_vat_exempt() !== (bool) $is_exempt ) {
			WC()->customer->set_is_vat_exempt( (bool) $is_exempt );
		}
	}, 5);

});