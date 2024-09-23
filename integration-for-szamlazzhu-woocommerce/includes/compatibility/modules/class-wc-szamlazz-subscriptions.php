<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//WooCommerce Subscriptions Compatibility
class WC_Szamlazz_Woo_Subscriptions_Compatibility {

	public static function init() {

		//Create settings
		add_filter( 'wc_szamlazz_settings_automations', array( __CLASS__, 'add_settings') );

		//Add actions
		add_filter( 'wcs_renewal_order_created', array( __CLASS__, 'renewal_order_created' ), 10, 2 );
		add_action( 'woocommerce_renewal_order_payment_complete', array( __CLASS__, 'renewal_order_payment_completed' ) );
		add_filter( 'wcs_renewal_order_meta', array( __CLASS__, 'order_meta' ) );

	}

	public static function add_settings($settings) {
		$settings_custom = array(
			array(
				'title' => __( 'WooCommerce Subscriptions', 'wc-szamlazz' ),
				'type' => 'title',
				'description' => __( 'Settings related to WooCommerce Subscriptions.', 'wc-szamlazz' ),
			),
			array(
				'title'    => __( 'Generate proform or deposit on renewal order', 'wc-szamlazz' ),
				'type'     => 'checkbox',
				'default' => 'no',
				'id' => 'compat_subscriptions_proform',
				'desc_tip' => __( 'If checked, a proform invoice will be created when a new renewal order is created(based on the payment method settings in the automatization section).', 'wc-szamlazz' ),
			),
			array(
				'title'    => __( 'Generate invoice on payment complete', 'wc-szamlazz' ),
				'type'     => 'checkbox',
				'default' => 'no',
				'id' => 'compat_subscriptions_invoice',
				'desc_tip' => __( 'If checked, and invoice will be created automatically when the order is set to paid by WooCommerce Subscriptions.', 'wc-szamlazz' ),
			),
			array(
				'type' => 'sectionend',
			)
		);

		return array_merge($settings, $settings_custom);
	}


	public static function renewal_order_created( $renewal_order, $subscription ) {
		if(WC_Szamlazz()->get_option('compat_subscriptions_proform', 'no') == 'yes') {
			WC_Szamlazz_Automations::on_order_processing($renewal_order);
		}
		return $renewal_order;
	}

	public static function renewal_order_payment_completed( $order_id ) {
		if(WC_Szamlazz()->get_option('compat_subscriptions_invoice', 'no') == 'yes') {
			WC_Szamlazz_Automations::on_order_complete($order_id);
		}
	}

	public static function order_meta($order_meta) {
		foreach ( $order_meta as $key => $value ) {
			$order_meta_query = array('_wc_szamlazz_own', '_wc_szamlazz_invoice', '_wc_szamlazz_proform', '_wc_szamlazz_proform_pdf', '_wc_szamlazz_invoice_pdf', '_wc_szamlazz_invoice_manual', '_wc_szamlazz_delivery', '_wc_szamlazz_delivery_pdf', '_wc_szamlazz_delivery_manual', '_wc_szamlazz_deposit', '_wc_szamlazz_deposit_pdf', '_wc_szamlazz_deposit_manual', '_wc_szamlazz_completed', '_wc_szamlazz_account_id');
			if ( in_array($value['meta_key'],$order_meta_query) ) {
				unset( $order_meta[$key] );
				return $order_meta;
			}
		}
		return $order_meta;
	}

}

WC_Szamlazz_Woo_Subscriptions_Compatibility::init();