<?php
/*
Plugin Name:  Busha Pay
Plugin URI:   'http://www.brusha.com/'
Description:  Accept cryptocurrencies through Busha Pay such as Bitcoin, Ethereum, Litecoin and Bitcoin Cash on your WooCommerce store.
Version:      1.0.0
Author:       Busha Pay
Author URI:   https://busha.co/
License:     GPLv3+

WC requires at least: 3.0.9
WC tested up to: 3.6.3

Busha Pay Payment Gateway for WooCommerce is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Busha Pay Payment Gateway for WooCommerce is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Busha Pay Payment Gateway for WooCommerce. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
*/
function busha_init_gateway() {

	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		require_once 'class-wc-gateway-busha.php';
		add_action( 'init', 'busha_wc_register_blockchain_status' );
		add_filter( 'woocommerce_valid_order_statuses_for_payment', 'busha_wc_status_valid_for_payment', 10, 2 );
		add_action( 'busha_check_orders', 'busha_wc_check_orders' );
		add_filter( 'woocommerce_payment_gateways', 'busha_wc_add_busha_class' );
		add_filter( 'wc_order_statuses', 'busha_wc_add_status' );
		add_action( 'woocommerce_admin_order_data_after_order_details', 'busha_order_meta_general' );
		add_action( 'woocommerce_order_details_after_order_table', 'busha_order_meta_general' );
		add_filter( 'woocommerce_email_order_meta_fields', 'busha_custom_woocommerce_email_order_meta_fields', 10, 3 );
		add_action('woocommerce_thankyou', 'busha_check_payment_status', 10, 1);
		add_filter( 'cron_schedules', 'busha_add_cron_interval' );
	}
}
add_action( 'plugins_loaded', 'busha_init_gateway' );

/*
* Add custom schedule event
*/
function busha_add_cron_interval($schedules)
{
	$schedules['thirty_mim'] = array(
        'interval' => 1800,
        'display'  => esc_html__( 'Every Thirty Minute' ),
    );
 
    return $schedules;
}

// Setup cron job.

function busha_activation() {
	if ( ! wp_next_scheduled( 'busha_check_orders' ) ) {
		wp_schedule_event( time(), 'thirty_mim', 'busha_check_orders' );
	}
}
register_activation_hook( __FILE__, 'busha_activation' );

function busha_deactivation() {
	wp_clear_scheduled_hook( 'busha_check_orders' );
}
register_deactivation_hook( __FILE__, 'busha_deactivation' );


// WooCommerce

function busha_wc_add_busha_class( $methods ) {
	$methods[] = 'WC_Gateway_Busha';
	return $methods;
}

function busha_wc_check_orders() {
	$gateway = WC()->payment_gateways()->payment_gateways()['busha'];
	return $gateway->check_orders();
}


function busha_check_payment_status($order_id) {
	busha_wc_check_orders();
}

/**
 * Register new status with ID "wc-blockchainpending" and label "Blockchain Pending"
 */
function busha_wc_register_blockchain_status() {
	register_post_status( 'wc-blockchainpending', array(
		'label'                     => __( 'Blockchain Pending', 'busha' ),
		'public'                    => true,
		'show_in_admin_status_list' => true,
		/* translators: WooCommerce order count in blockchain pending. */
		'label_count'               => _n_noop( 'Blockchain pending <span class="count">(%s)</span>', 'Blockchain pending <span class="count">(%s)</span>' ),
	) );
}

/**
 * Register wc-blockchainpending status as valid for payment.
 */
function busha_wc_status_valid_for_payment( $statuses, $order ) {
	$statuses[] = 'wc-blockchainpending';
	return $statuses;
}

/**
 * Add registered status to list of WC Order statuses
 * @param array $wc_statuses_arr Array of all order statuses on the website.
 */
function busha_wc_add_status( $wc_statuses_arr ) {
	$new_statuses_arr = array();

	// Add new order status after payment pending.
	foreach ( $wc_statuses_arr as $id => $label ) {
		$new_statuses_arr[ $id ] = $label;

		if ( 'wc-pending' === $id ) {  // after "Payment Pending" status.
			$new_statuses_arr['wc-blockchainpending'] = __( 'Blockchain Pending', 'busha' );
		}
	}

	return $new_statuses_arr;
}


/**
 * Add order Busha meta after General and before Billing
 *
 * @see: https://rudrastyh.com/woocommerce/customize-order-details.html
 *
 * @param WC_Order $order WC order instance
 */
function busha_order_meta_general( $order )
{
    if ($order->get_payment_method() == 'busha') {
        ?>

        <br class="clear"/>
        <h3>Busha Pay Data</h3>
        <div class="">
            <p>Busha Pay Reference # <?php echo esc_html($order->get_meta('_busha_charge_id')); ?></p>
        </div>

        <?php
    }
}


/**
 * Add Busha meta to WC emails
 *
 * @see https://docs.woocommerce.com/document/add-a-custom-field-in-an-order-to-the-emails/
 *
 * @param array    $fields indexed list of existing additional fields.
 * @param bool     $sent_to_admin If should sent to admin.
 * @param WC_Order $order WC order instance
 *
 */
function busha_custom_woocommerce_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
    if ($order->get_payment_method() == 'busha') {
        $fields['busha_pay_reference'] = array(
            'label' => __( 'Busha Pay Reference #' ),
            'value' => $order->get_meta( '_busha_charge_id' ),
        );
    }

    return $fields;
}
