<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Busha Class.
 */
class WC_Gateway_Busha extends WC_Payment_Gateway {

	/** @var bool Whether or not logging is enabled */
	public static $log_enabled = false;

	/** @var WC_Logger Logger instance */
	public static $log = false;

	/**
	 * Constructor for the gateway.
	 */
	public function __construct() {
		$this->id                 = 'busha';
		$this->has_fields         = false;
		$this->order_button_text  = __( 'Pay with Busha', 'busha' );
		$this->method_title       = __( 'Busha', 'busha' );
		$this->method_description = '<p>' .
			__( 'A payment gateway that sends your customers to Busha Pay to pay with cryptocurrency.', 'busha' )
			. '</p><p>' .
			sprintf(
				__( 'If you do not currently have a Busha Pay account, you can set one up here: %s', 'busha' ),
				'<a target="_blank" href="https://pay.busha.co">https://pay.busha.co</a>'
			);

		$this->testmode = 'yes' === $this->get_option( 'testmode' );
		$this->api_key = $this->testmode ? $this->get_option( 'test_api_key' ) : $this->get_option( 'api_key' );
		$this->webhook_url = $this->testmode ? $this->get_option( 'test_webhook_secret' ) : $this->get_option( 'webhook_secret' );


		$this->timeout = ( new WC_DateTime() )->sub( new DateInterval( 'P3D' ) );

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		$this->debug       = 'yes' === $this->get_option( 'debug', 'no' );

		self::$log_enabled = $this->debug;

		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, '_custom_query_var' ), 10, 2 );
		add_action( 'woocommerce_api_wc_gateway_busha', array( $this, 'handle_webhook' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'busha_admin_scripts' ) );
	}
	
	/*
	* Include script file for admin section
	*/
	public function busha_admin_scripts()
	{
		if ( 'woocommerce_page_wc-settings' !== get_current_screen()->id ) {
			return;
		}
		wp_enqueue_script( 'woocommerce_busha_admin', plugins_url( 'assets/js/admin-busha.js', __FILE__ ), array(), 1.2, true );
	}

	/**
	 * Logging method.
	 *
	 * @param string $message Log message.
	 * @param string $level   Optional. Default 'info'.
	 *     emergency|alert|critical|error|warning|notice|info|debug
	 */
	public static function log( $message, $level = 'info' ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, array( 'source' => 'busha' ) );
		}
	}

	/**
	 * Get gateway icon.
	 * @return string
	 */
	public function get_icon() {
		if ( $this->get_option( 'show_icons' ) === 'no' ) {
			return '';
		}

		$image_path = plugin_dir_path( __FILE__ ) . 'assets/images';
		$icon_html  = '';
		$methods    = get_option( 'busha_payment_methods', array( 'bitcoin',  'ethereum' ) );

		// Load icon for each available payment method.
		foreach ( $methods as $m ) {
			$path = realpath( $image_path . '/' . $m . '.png' );
			if ( $path && dirname( $path ) === $image_path && is_file( $path ) ) {
				$url        = WC_HTTPS::force_https_url( plugins_url( '/assets/images/' . $m . '.png', __FILE__ ) );
				$icon_html .= '<img width="26" src="' . esc_attr( $url ) . '" alt="' . esc_attr__( $m, 'busha' ) . '" />';
			}
		}

		return apply_filters( 'woocommerce_gateway_icon', $icon_html, $this->id );
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'        => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Busha Pay Payment', 'busha' ),
				'default' => 'yes',
			),
			'title'          => array(
				'title'       => __( 'Title', 'woocommerce' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Busha Pay (Bitcoin and other cryptocurrencies)', 'busha' ),
				'desc_tip'    => true,
			),
			'description'    => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
				'default'     => __( 'Pay with Bitcoin or other cryptocurrencies powered by Busha Pay.', 'busha' ),
			),
			'testmode' => array(
				'title'       => __( 'Test mode', 'busha' ),
				'label'       => __( 'Enable Test Mode' ),
				'type'        => 'checkbox',
				'description' => __('Place the payment gateway in test mode using test API keys.'),
				'default'     => 'yes',
				'desc_tip'    => true,
			),
			'test_api_key' => array(
				'title'       => __('Staging API Key'),
				'type'        => 'text',
				'description' => sprintf(
					
					__(
						'You can manage your API keys within the Busha Settings page, available here: %s',
						'busha'
					),
					esc_url( 'https://dashboard.pay.busha.co/dashboard/settings' )
				),
			),
			'test_webhook_secret' => array(
				'title'       => __('Staging Webhook Shared Secret'),
				'type'        => 'text',
				'description' =>
				__( 'Using webhooks allows busha to send payment confirmation messages to the website. To fill this out:', 'busha' )

				. '<br /><br />' .
				__( '1. In your Busha Pay Pay settings page, scroll to the \'Webhook subscriptions\' section', 'busha' )

				. '<br />' .
				sprintf( __( '2. Click \'Add an endpoint\' and paste the following URL: %s', 'busha' ), add_query_arg( 'wc-api', 'WC_Gateway_Busha', home_url( '/', 'https' ) ) )

				. '<br />' .

				// translators: Step 4 of the instructions for 'webhook shared secrets' on settings page.
				__( '3. Click "Show shared secret" and paste into the box above.', 'busha' ),
			),
			
			'api_key'        => array(
				'title'       => __( 'API Key', 'busha' ),
				'type'        => 'text',
				'default'     => '',
				'description' => sprintf(
					
					__(
						'You can manage your API keys within the Busha Settings page, available here: %s',
						'busha'
					),
					esc_url( 'https://dashboard.pay.busha.co/dashboard/settings' )
				),
			),
			'webhook_secret' => array(
				'title'       => __( 'Webhook Shared Secret', 'busha' ),
				'type'        => 'text',
				'description' =>
				__( 'Using webhooks allows busha to send payment confirmation messages to the website. To fill this out:', 'busha' )

				. '<br /><br />' .

				// translators: Step 1 of the instructions for 'webhook shared secrets' on settings page.
				__( '1. In your Busha Pay Pay settings page, scroll to the \'Webhook subscriptions\' section', 'busha' )

				. '<br />' .

				// translators: Step 2 of the instructions for 'webhook shared secrets' on settings page. Includes webhook URL.
				sprintf( __( '2. Click \'Add an endpoint\' and paste the following URL: %s', 'busha' ), add_query_arg( 'wc-api', 'WC_Gateway_Busha', home_url( '/', 'https' ) ) )

				. '<br />' .

				// translators: Step 4 of the instructions for 'webhook shared secrets' on settings page.
				__( '3. Click "Show shared secret" and paste into the box above.', 'busha' ),

			),
			'show_icons'     => array(
				'title'       => __( 'Show icons', 'busha' ),
				'type'        => 'checkbox',
				'label'       => __( 'Display currency icons on checkout page.', 'busha' ),
				'default'     => 'yes',
			),
			'debug'          => array(
				'title'       => __( 'Debug log', 'woocommerce' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce' ),
				'default'     => 'no',
				// translators: Description for 'Debug log' section of settings page.
				'description' => sprintf( __( 'Log busha API events inside %s', 'busha' ), '<code>' . WC_Log_Handler_File::get_log_file_path( 'busha' ) . '</code>' ),
			),
		);
	}

	/**
	 * Process the payment and return the result.
	 * @param  int $order_id
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		// Create description for charge based on order's products. Ex: 1 x Product1, 2 x Product2
		try {
			$order_items = array_map( function( $item ) {
				return $item['quantity'] . ' x ' . $item['name'];
			}, $order->get_items() );

			$description = mb_substr( implode( ', ', $order_items ), 0, 200 );
		} catch ( Exception $e ) {
			$description = null;
		}

		$this->init_api();

		// Create a new charge.
		$metadata = array(
			'order_id'  => $order->get_id(),
			'order_key' => $order->get_order_key(),
            		'source' => 'woocommerce'
		);
		$result   = Busha_API_Handler::create_charge(
			$order->get_total(), get_woocommerce_currency(), $metadata,
			$this->get_return_url( $order ), null, $description,
			$this->get_cancel_url( $order )
		);

		if ( ! $result[0] ) {
			return array( 'result' => 'fail' );
		}

		$charge = $result[1]['data'];

		$order->update_meta_data( '_busha_charge_id', $charge['code'] );
		$order->save();

		return array(
			'result'   => 'success',
			'redirect' => $charge['hosted_url'],
		);
	}

	/**
	 * Get the cancel url.
	 *
	 * @param WC_Order $order Order object.
	 * @return string
	 */
	public function get_cancel_url( $order ) {
		$return_url = $order->get_cancel_order_url();

		if ( is_ssl() || get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' ) {
			$return_url = str_replace( 'http:', 'https:', $return_url );
		}

		return apply_filters( 'woocommerce_get_cancel_url', $return_url, $order );
	}

	/**
	 * Check payment statuses on orders and update order statuses.
	 */
	public function check_orders() {
		$this->init_api();

		// Check the status of non-archived Busha Pay orders.
		$orders = wc_get_orders( array( 'busha_archived' => false, 'status'   => array( 'wc-pending' ) ) );
		foreach ( $orders as $order ) {
			$charge_id = $order->get_meta( '_busha_charge_id' );

			usleep( 300000 );  // Ensure we don't hit the rate limit.
			$result = Busha_API_Handler::send_request( 'charges/' . $charge_id );

			if ( ! $result[0] ) {
				self::log( 'Failed to fetch order updates for: ' . $order->get_id() );
				continue;
			}

			$timeline = $result[1]['data']['timeline'];
			self::log( 'Timeline: ' . print_r( $timeline, true ) );
			$this->_update_order_status( $order, $timeline );
		}
	}


	/**
	 * Handle requests sent to webhook.
	 */
	public function handle_webhook() {
	    $payload = file_get_contents( 'php://input' );
	    		
		if ( ! empty( $payload ) && $this->validate_webhook( $payload ) ) {
			$data       = json_decode( $payload, true );
			$event_data = $data['event']['data'];

			self::log( 'Webhook received event: ' . print_r( $data, true ) );

			if ( ! isset( $event_data['metadata']['order_id'] ) ) {
				exit;
			}

			$order_id = $event_data['metadata']['order_id'];
			$this->_update_order_status( wc_get_order( $order_id ), $event_data['timeline'] );
			exit; 
		}

		wp_die( 'Busha Pay Webhook Request Failure', 'Busha Pay Webhook', array( 'response' => 500 ) );
	}

	/**
	 * Check Busha Pay webhook request is valid.
	 * @param  string $payload
	 */
	public function validate_webhook( $payload ) {
		self::log( 'Checking Webhook response is valid' );

		if ( ! isset( $_SERVER['HTTP_X_BP_WEBHOOK_SIGNATURE'] ) ) {
			return false;
		}

		$sig    = $_SERVER['HTTP_X_BP_WEBHOOK_SIGNATURE'];
		$secret = $this->get_option( 'webhook_secret' );

		$sig2 = hash_hmac( 'sha256', $payload, $secret );

		if ( $sig === $sig2 ) {
			return true;
		}

		return false;
	}

	/**
	 * Init the API class and set the API key etc.
	 */
	protected function init_api() {
		include_once dirname( __FILE__ ) . '/includes/class-busha-api-handler.php';

		Busha_API_Handler::$log     = get_class( $this ) . '::log';
		Busha_API_Handler::$api_key = 'yes' === $this->get_option( 'testmode' ) ? $this->get_option( 'test_api_key' ) : $this->get_option( 'api_key' );
		Busha_API_Handler::$api_url = 'yes' === $this->get_option( 'testmode' ) ? 'https://api.staging.pay.busha.co/' : 'https://api.pay.busha.co/';
		self::log( 'End url: ' . Busha_API_Handler::$api_url );
	}

	/**
	 * Update the status of an order from a given timeline.
	 * @param  WC_Order $order
	 * @param  array    $timeline
	 */
	public function _update_order_status( $order, $timeline ) {
		$prev_status = $order->get_meta( '_busha_status' );

		$last_update = end( $timeline );
		$status      = $last_update['status'];
		if ( $status !== $prev_status ) {
			$order->update_meta_data( '_busha_status', $status );

			if ( 'EXPIRED' === $status && 'pending' == $order->get_status() ) {
				$order->update_status( 'cancelled', __( 'Busha Pay payment expired.', 'busha' ) );
			} elseif ( 'CANCELED' === $status ) {
				$order->update_status( 'cancelled', __( 'Busha Pay payment cancelled.', 'busha' ) );
			} elseif ( 'UNRESOLVED' === $status ) {
			    	if ($last_update['context'] === 'OVERPAID') {
                    			$order->update_status( 'processing', __( 'Busha Pay payment was successfully processed.', 'busha' ) );
                    			$order->payment_complete();
                		} else {
                    			// translators: Busha Pay error status for "unresolved" payment. Includes error status.
                    			$order->update_status( 'failed', sprintf( __( 'Busha Pay payment unresolved, reason: %s.', 'busha' ), $last_update['context'] ) );
                		}
			} elseif ( 'PENDING' === $status ) {
				$order->update_status( 'blockchainpending', __( 'Busha Pay payment detected, but awaiting blockchain confirmation.', 'busha' ) );
			} elseif ( 'RESOLVED' === $status ) {
				// We don't know the resolution, so don't change order status.
				$order->add_order_note( __( 'Busha Pay payment marked as resolved.', 'busha' ) );
            		} elseif ( 'COMPLETED' === $status ) {
                		$order->update_status( 'processing', __( 'Busha Pay payment was successfully processed.', 'busha' ) );
                		$order->payment_complete();
            		}
		}

		// Archive if in a resolved state and idle more than timeout.
		if ( in_array( $status, array( 'EXPIRED', 'COMPLETED', 'RESOLVED' ), true ) &&
			$order->get_date_modified() < $this->timeout ) {
			self::log( 'Archiving order: ' . $order->get_order_number() );
			$order->update_meta_data( '_busha_archived', true );
		}
	}

	/**
	 * Handle a custom 'busha_archived' query var to get orders
	 * payed through Busha Pay with the '_busha_archived' meta.
	 * @param array $query - Args for WP_Query.
	 * @param array $query_vars - Query vars from WC_Order_Query.
	 * @return array modified $query
	 */
	public function _custom_query_var( $query, $query_vars ) {
		if ( array_key_exists( 'busha_archived', $query_vars ) ) {
			$query['meta_query'][] = array(
				'key'     => '_busha_archived',
				'compare' => $query_vars['busha_archived'] ? 'EXISTS' : 'NOT EXISTS',
			);
			// Limit only to orders payed through Busha Pay.
			$query['meta_query'][] = array(
				'key'     => '_busha_charge_id',
				'compare' => 'EXISTS',
			);
		}

		return $query;
	}
}
