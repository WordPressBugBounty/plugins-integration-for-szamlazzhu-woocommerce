<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Szamlazz_Invoice_Preview', false ) ) :

	class WC_Szamlazz_Invoice_Preview {

		//Constructor
		public static function init() {

			// Not using Jetpack\Constants here as it can run before 'plugin_loaded' is done.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX
				|| defined( 'DOING_CRON' ) && DOING_CRON
				|| ! is_admin() ) {
				return;
			}

			//Load template based on get parameter
			add_action( 'admin_init', array( __CLASS__, 'load_preview_template') );

		}

		public static function load_preview_template() {
			// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Read-only preview page, nothing is saved. Access is limited to users who can edit orders.
			if(!isset( $_GET['wc_szamlazz_preview'] )) {
				return;
			}

			//Check for permissions before doing anything
			if ( ! current_user_can( 'edit_shop_orders' ) ) {
				wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wc-szamlazz' ) );
			}

			//Get order info
			$order_id = absint( wp_unslash( $_GET['wc_szamlazz_preview'] ) );
			$options = array('preview' => true);

			if(isset($_GET['deadline']) && isset($_GET['completed'])) {
				$options['deadline'] = ( isset( $_GET['deadline'] ) ? intval( $_GET['deadline'] ) : 0 );
				$options['completed'] = ( isset( $_GET['completed'] ) ? sanitize_text_field( wp_unslash( $_GET['completed'] ) ) : '' );
			}

			if(isset($_GET['note']) && !empty($_GET['note'])) {
				$options['note'] = ( isset( $_GET['note'] ) ? sanitize_textarea_field( wp_unslash( $_GET['note'] ) ) : '' );
			}

			if(isset($_GET['account'])) {
				$options['account'] = ( isset( $_GET['account'] ) ? sanitize_text_field( wp_unslash( $_GET['account'] ) ) : '' );
			}

			//Get invoice XML as json
			$invoice_xml = WC_Szamlazz()->generate_invoice($order_id, 'invoice', $options);
			$invoice = false;

			if(isset($invoice_xml['xml'])) {
				$xml = simplexml_load_string($invoice_xml['xml']);
				$json = json_encode($xml);
				$invoice = json_decode($json,TRUE);
				if(is_array($invoice)) {
					$invoice = self::normalize_empty_nodes($invoice);
				}
			}

			//If order and invoice found, show template
			if($invoice_xml) {
				include( dirname( __FILE__ ) . '/views/html-invoice-preview.php' );
				exit();
			} else {
				return;
			}
			// phpcs:enable WordPress.Security.NonceVerification.Recommended
		}

		//Empty XML elements are decoded as empty arrays, turn them into empty strings so they can be printed safely
		public static function normalize_empty_nodes( $data ) {
			foreach ( $data as $key => $value ) {
				if ( is_array( $value ) ) {
					$data[ $key ] = empty( $value ) ? '' : self::normalize_empty_nodes( $value );
				}
			}
			return $data;
		}

	}

	WC_Szamlazz_Invoice_Preview::init();

endif;
