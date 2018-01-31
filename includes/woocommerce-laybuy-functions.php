<?php

if( !defined( 'ABSPATH' ) ) exit;

/**
 * Get the return url (thank you page).
 * Note: this is bad to be honest but Laybuy at this moment don't provide a cancel url.
 *
 * @param WC_Order $order
 * @return string
 */
function woocommerce_laybuy_get_return_url( $order = null ) {
    if ( $order ) {
        $return_url = $order->get_checkout_order_received_url();
    } else {
        $return_url = wc_get_endpoint_url( 'order-received', '', wc_get_page_permalink( 'checkout' ) );
    }

    if ( is_ssl() || get_option( 'woocommerce_force_ssl_checkout' ) == 'yes' ) {
        $return_url = str_replace( 'http:', 'https:', $return_url );
    }

    return apply_filters( 'woocommerce_get_return_url', $return_url, $order );
}

function woocommerce_laybuy_get_settings() {
    return get_option( 'woocommerce_laybuy_settings', true );
}

function woocommerce_laybuy_is_sandbox_enabled() {
    $settings = woocommerce_laybuy_get_settings();

    return !isset( $settings['environment'] ) || 'sandbox' == $settings['environment'];
}