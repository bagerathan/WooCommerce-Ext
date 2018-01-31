<?php
// see cconstants.php

if( !defined( 'ABSPATH' ) ) exit;

class Woocommerce_Laybuy_Form_Handler {

    public static function init() {
        add_action( 'wp_loaded', array( __CLASS__, 'handle_return_url' ), 20 );

        /**
        Disabled until further notice.
        =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
        add_action( 'woocommerce_order_status_cancelled', array( __CLASS__, 'handle_order_cancelled' ) );
        add_action( 'woocommerce_order_status_refunded', array( __CLASS__, 'handle_order_refunded' ) );
        =-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-
        */
    }

    public static function handle_return_url() {

        if( isset( $_GET['gateway_id'] ) && WOOCOMMERCE_LAYBUY_SLUG == $_GET['gateway_id'] && isset( $_GET['order'] ) && isset( $_GET['order_id'] ) && isset( $_GET['status'] ) && isset( $_GET['token']
            ) ) {

            $order = wc_get_order( absint( $_GET['order_id'] ) );
            $status = strtolower( $_GET['status'] );

            // save the received token from laybuy
            update_post_meta( $_GET['order_id'], '_laybuy_token', $_GET['token'] );

            if( 'cancelled' == $status ) {
                $redirect = $order->get_cancel_order_url();
            } else if( 'success' == $status ) {
                WC()->cart->empty_cart();
                $redirect = woocommerce_laybuy_get_return_url( $order );
            } else {

            }

            wp_redirect( html_entity_decode( $redirect ) );
            exit;
        }
    }

    public static function handle_order_cancelled( $order_id ) {
        /**
         * We need to use the token, we need to save it
         */
        $order = wc_get_order( $order_id );
        if( 'laybuy' == $order->get_payment_method() && !empty( get_post_meta( $order_id, '_laybuy_token', true ) ) ) {
            $token = get_post_meta( $order_id, '_laybuy_token', true );

            $settings = woocommerce_laybuy_get_settings();
            if( woocommerce_laybuy_is_sandbox_enabled() ) {
                $endpoint = SANDBOX_API_ENDPOINT . CANCEL_ORDER_SUFFIX;
            } else {
                $endpoint = PRODUCTION_API_ENDPOINT . CANCEL_ORDER_SUFFIX;
            }

            $endpoint .= '/' . $token;

            $request = wp_remote_get( $endpoint,
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode( $settings['merchant_id'] . ':' . $settings['api_key'] )
                    )
                )
            );

            if( is_wp_error( $request ) ) {
                $order->add_order_note( 'The Laybuy system failed to process your request to cancel the order.. msg: ' . $response->error );
            }

            $response = json_decode( $request['body'] );

            if( 'success' == strtolower( $response->result ) ) {
                $order->add_order_note( 'The Laybuy system has processed your request to cancel the order..' );
            } else if( 'error' == strtolower( $response->result ) ) {
                $order->add_order_note( 'The Laybuy system failed to process your request to cancel the order.. msg: ' . $response->error );
            }
        }
    }

    public static function handle_order_refunded( $order_id ) {
        $order = wc_get_order( $order_id );

        if( 'laybuy' == $order->get_payment_method() && !empty( get_post_meta( $order_id, '_laybuy_order_id', true ) ) ) {

            $settings = woocommerce_laybuy_get_settings();
            if( woocommerce_laybuy_is_sandbox_enabled() ) {
                $endpoint = SANDBOX_API_ENDPOINT . REFUND_ORDER_SUFFIX;
            } else {
                $endpoint = PRODUCTION_API_ENDPOINT . REFUND_ORDER_SUFFIX;
            }

            $order_id = get_post_meta( $order_id, '_laybuy_order_id', true );

            $request_data = array(
                'orderId' => $order_id,
                'amount'  => $order->get_total()
            );

            $request = wp_remote_post( $endpoint,
                array(
                    'headers' => array(
                        'Authorization' => 'Basic ' . base64_encode( $settings['merchant_id'] . ':' . $settings['api_key'] )
                    ),
                    'body' => json_encode( $request_data )
                )
            );

            $response = json_decode( $request['body'] );

            if( 'error' == strtolower( $response->result ) ) {
                 $order->add_order_note( 'The Laybuy system failed to process your request to refund the order. Error: ' . $response->error );
            } else if( 'success' == strtolower( $response->result ) ) {
                $order->add_order_note( 'The Laybuy system has processed your request to refund the order.' );
            }
        }
    }

}

Woocommerce_Laybuy_Form_Handler::init();