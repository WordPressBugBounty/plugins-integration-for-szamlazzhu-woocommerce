<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Szamlazz_Xml_Generator', false ) ) :

	class WC_Szamlazz_Xml_Generator {

		private $agent_body = array();
		private $agent_header = array();

		//Send the XML to Számlázz.hu
		public function generate($xml, $orderId, $field) {

			//Log request in debug mode
			WC_Szamlazz()->log_debug_messages($xml, $field.'-'.$orderId);

			//Send the XML as a file upload, built in memory, so we don't need a temporary file
			$boundary = wp_generate_password(24, false);
			$body  = "--{$boundary}\r\n";
			$body .= "Content-Disposition: form-data; name=\"{$field}\"; filename=\"{$orderId}.xml\"\r\n";
			$body .= "Content-Type: text/xml\r\n\r\n";
			$body .= $xml."\r\n";
			$body .= "--{$boundary}--\r\n";

			//Session cookie for this account, so Számlázz.hu doesn't need to log in again on every request
			$session_option = $this->get_session_option_name($xml);

			$request = wp_remote_post('https://www.szamlazz.hu/szamla/', array(
				'timeout' => 30,
				'redirection' => 0,
				'sslverify' => apply_filters('wc_szamlazz_ssl_verify', true),
				'headers' => array('Content-Type' => 'multipart/form-data; boundary='.$boundary),
				'cookies' => $this->get_session_cookies($session_option),
				'body' => $body,
			));

			$http_error = '';
			$header_array = array();
			$agent_body = '';

			if(is_wp_error($request)) {
				$http_error = $request->get_error_message();
			} else {
				$this->save_session_cookies($session_option, wp_remote_retrieve_cookies($request));
				$agent_body = wp_remote_retrieve_body($request);

				//Keep the headers as "name: value" lines, like the raw response, other functions parse these
				$header_array[] = 'HTTP/1.1 '.wp_remote_retrieve_response_code($request).' '.wp_remote_retrieve_response_message($request);
				foreach (wp_remote_retrieve_headers($request) as $name => $values) {
					foreach ((array)$values as $value) {
						$header_array[] = $name.': '.$value;
					}
				}
			}

			// Save data locally
			$this->agent_header = $header_array;
			$this->agent_body = $agent_body;

			// ezt majd true-ra állítjuk ha volt hiba
			$volt_hiba = false;

			// ebben lesznek a hiba információk, plusz a bodyban
			$agent_error = '';
			$agent_error_code = '';

			// menjünk végig a header sorokon, ami "szlahu"-val kezdődik az érdekes nekünk és írjuk ki
			foreach ($header_array as $val) {
				if (substr($val, 0, strlen('szlahu')) === 'szlahu') {
					// megvizsgáljuk, hogy volt-e hiba
					if (substr($val, 0, strlen('szlahu_error:')) === 'szlahu_error:') {
						// sajnos volt
						$volt_hiba = true;
						$agent_error = substr($val, strlen('szlahu_error:'));
					}
					if (substr($val, 0, strlen('szlahu_error_code:')) === 'szlahu_error_code:') {
						// sajnos volt
						$volt_hiba = true;
						$agent_error_code = substr($val, strlen('szlahu_error_code:'));
					}
				}
			}

			// ha volt http hiba dobunk egy kivételt
			$response = array();
			$response['header_array'] = $header_array;
			$response['error'] = false;
			$response['messages'] = array();
			$response['agent_body'] = $agent_body;
			if ( $http_error != "" ) {
				$response['error'] = true;
				$response['http_error'] = $http_error;
				$response['messages'][] = sprintf(__('Http error: %s', 'wc-szamlazz'), $http_error);

				//Log error messages
				WC_Szamlazz()->log_error_messages($response, $field.'-'.$orderId);

				return $response;
			}

			if ($volt_hiba) {
				$response['error'] = true;
				$response['agent_error'] = $agent_error;
				$response['agent_error_code'] = $agent_error_code;

				// ha a számla nem készült el kiírjuk amit lehet
				$response['messages'][] = sprintf(__('Agent error code: %s', 'wc-szamlazz'), $agent_error_code);
				$response['messages'][] = sprintf(__('Agent error message: %s', 'wc-szamlazz'), urldecode($agent_error));
				//$response['messages'][] = 'Agent válasz: '.urldecode($agent_body);

				//Log error messages
				WC_Szamlazz()->log_error_messages($response, $field.'-'.$orderId);

				// dobunk egy kivételt
				return $response;
			}

			return $response;
		}

		//One session per agent key. The option name uses a hash, so the key itself is not stored again
		private function get_session_option_name($xml) {
			$agent_key = '';
			$xml_file = @simplexml_load_string($xml); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			if($xml_file) {
				if($xml_file->beallitasok && $xml_file->beallitasok->szamlaagentkulcs) {
					$agent_key = (string)$xml_file->beallitasok->szamlaagentkulcs;
				} elseif($xml_file->szamlaagentkulcs) {
					$agent_key = (string)$xml_file->szamlaagentkulcs;
				}
			}
			return '_wc_szamlazz_session_'.substr(md5($agent_key), 0, 12);
		}

		private function get_session_cookies($option_name) {
			$cookies = array();
			$stored = get_option($option_name, array());
			if(is_array($stored)) {
				foreach ($stored as $name => $cookie) {
					if(empty($cookie['expires']) || $cookie['expires'] > time()) {
						$cookies[$name] = $cookie['value'];
					}
				}
			}
			return $cookies;
		}

		private function save_session_cookies($option_name, $response_cookies) {
			if(empty($response_cookies)) return;

			$stored = get_option($option_name, array());
			$stored = is_array($stored) ? $stored : array();
			foreach ($response_cookies as $cookie) {
				$stored[$cookie->name] = array('value' => $cookie->value, 'expires' => $cookie->expires);
			}
			update_option($option_name, $stored, false);
		}

		//Cookies used to be stored in files, remove those once
		public static function remove_legacy_cookie_files() {
			if(get_option('_wc_szamlazz_legacy_cookies_removed')) return;
			global $wpdb;

			$files = glob(WC_Szamlazz_Document_Storage::get_base_dir().'szamlazz_cookie_*.txt');
			foreach ((array)$files as $file) {
				@unlink($file); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			}
			$wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '\\_wc\\_szamlazz\\_cookie\\_name%'");
			wp_cache_delete('alloptions', 'options');

			update_option('_wc_szamlazz_legacy_cookies_removed', true);
		}

		public function save_pdf_file($type, $orderId, $content = false, $invoice_name = false) {
			$pdf_file_path = WC_Szamlazz()->get_pdf_file_path($type, $orderId, $invoice_name);

			//Make sure the main folder is protected
			WC_Szamlazz_Document_Storage::protect();

			//If the month folder doesn't exists, create it with an empty index.html file
			$file = array(
				'base' 		=> $pdf_file_path['file_dir'],
				'file' 		=> 'index.html',
				'content' 	=> ''
			);

			if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
				if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
					fwrite( $file_handle, $file['content'] );
					fclose( $file_handle );
				}
			}

			//Save the actual PDF file
			if($content) {
				file_put_contents($pdf_file_path['path'], $content);
			} else {
				file_put_contents($pdf_file_path['path'], $this->agent_body);
			}

			return $pdf_file_path['name'];
		}

		public function get_invoice_id($headers) {
			$szlahu_szamlaszam = '';
			foreach ($headers as $val) {
				if (substr($val, 0, strlen('szlahu_szamlaszam')) === 'szlahu_szamlaszam') {
					$szlahu_szamlaszam = substr($val, strlen('szlahu_szamlaszam:'));
					break;
				}
			}
			return preg_replace("/\s+/", "", $szlahu_szamlaszam); //remove whitespace characters, sometimes there is one for some reason
		}

	}

	add_action( 'admin_init', array( 'WC_Szamlazz_Xml_Generator', 'remove_legacy_cookie_files' ) );

endif;

if ( ! class_exists( 'WCSzamlazzSimpleXMLElement', false ) ) :
	class WCSzamlazzSimpleXMLElement extends SimpleXMLElement {
		public function appendXML($append) {
			if ($append) {
				// Create new DOMElements from the two SimpleXMLElements
				$domdict = dom_import_simplexml($this);
				$domcat = dom_import_simplexml($append);

				// Import the <cat> into the dictionary document
				$domcat = $domdict->ownerDocument->importNode($domcat, TRUE);

				// Append the <cat> to <c> in the dictionary
				$domdict->appendChild($domcat);
			}
		}

		public function prependXML($prepend) {
			if ($prepend) {
				// Create new DOMElements from the two SimpleXMLElements
				$domdict = dom_import_simplexml($this);
				$domcat = dom_import_simplexml($prepend);

				// Import the element into the dictionary document
				$domcat = $domdict->ownerDocument->importNode($domcat, TRUE);

				// Prepend the element (insert before the first child)
				if ($domdict->firstChild) {
					$domdict->insertBefore($domcat, $domdict->firstChild);
				} else {
					$domdict->appendChild($domcat);
				}
			}
		}
	}
endif;
