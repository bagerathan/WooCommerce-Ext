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
        // /store/?gateway_id=woocommerce-laybuy&order=wc_order_5ad82b288815b&order_id=3361&status=SUCCESS&token=8i6OSrTRcFgFC73dHPMklCVSdbbUHy1n3LayUaIk
        if( isset( $_GET['gateway_id'] ) && WOOCOMMERCE_LAYBUY_SLUG == $_GET['gateway_id'] && isset( $_GET['order'] ) && isset( $_GET['order_id'] ) && isset( $_GET['token']
            ) ) {
            $order_id = absint($_GET['order_id']);
            $order = wc_get_order($order_id );
            $status = strtolower( $_GET['status'] );

            if( !$order ) {
                wc_add_notice( 'Invalid order.' );
                $redirect = WC()->cart->get_cart_url();
                wp_redirect( html_entity_decode( $redirect ) );
                exit;
            }
            // save the received token from laybuy
            update_post_meta($order_id, '_laybuy_token', $_GET['token'] );

            if( empty($status) || 'cancelled' === $status  ) {
                $redirect = $order->get_cancel_order_url();
                
            } else if( 'success' === $status ) {
                
                // do the laybuy conformation here
                // so if the thankyou page doesn't happen we have a correct order in place
                $confimed = self::confirmOrder($order_id);
                
                
                if(is_wp_error($confimed)){
                    
                    wc_add_notice($confimed->get_error_message());
                    $redirect = add_query_arg([
                                                  'order_id'      => $order_id,
                                                  'order'         => $order->get_order_key(),
                                                  'decline_order' => 'true'
                                              ], $order->get_cancel_endpoint());
                    
                }
                else {
                    $laybuy_id  = $confimed;
                    // $order->wc_reduce_stock();
                    $order->payment_complete($laybuy_id);  // this was missing!
                    //wc_reduce_stock_levels($order); hooked into complete
                    WC()->cart->empty_cart();
                    $redirect = woocommerce_laybuy_get_return_url($order);
                }
                
                
            } else if( 'declined' === $status ) {

                $order_id = absint( $_GET['order_id'] );
                $order_key = $_GET['order'];
                $user_can_decline  = current_user_can( 'cancel_order', $order_id );
                $order_can_cancel = $order->has_status( array( 'pending', 'failed' ) );

                if ( $user_can_decline && $order->get_id() === $order_id && $order->get_order_key() === $order_key ) {
                    wc_add_notice( 'Your order is declined.' );
                } else {
                    wc_add_notice( __( 'Invalid Order.', 'woocommerce' ), 'error' );
                }

                $redirect = add_query_arg( array(
                    'order_id' => $order_id,
                    'order' => $order->get_order_key(),
                    'decline_order' => 'true'
                ), $order->get_cancel_endpoint() );
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
            
            $response = json_decode($request['body']);
            
            if( is_wp_error( $request ) ) {
                $order->add_order_note( 'The Laybuy system failed to process your request to cancel the order.. msg: ' . $response->error );
            }

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
    
    public static function confirmOrder($order_id) {
    
        $settings = woocommerce_laybuy_get_settings();
        
        if (woocommerce_laybuy_is_sandbox_enabled()) {
            $endpoint = SANDBOX_API_ENDPOINT;
        }
        else {
            $endpoint = PRODUCTION_API_ENDPOINT;
        }
      
        $endpoint .= CONFIRM_ORDER_SUFFIX;
        
        if (!get_post_meta($order_id, '_laybuy_token', TRUE)) {
            return [
                'result' => FALSE,
                'error'  => 'Token not found error'
            ];
        }
        
        $request_data = [
            'token' => get_post_meta($order_id, '_laybuy_token', TRUE)
        ];
        
        $request = wp_remote_post($endpoint, [
                                               'headers' => [
                                                   'Authorization' => 'Basic ' . base64_encode($settings['merchant_id'] . ':' . $settings['api_key'])
                                               ],
                                               'body'    => json_encode($request_data)
                                           ]);
        
        $response = json_decode($request['body']);
        
        if ('error' === strtolower($response->result)) {
            $error = new \WP_Error('Order could not be confirmed');
            return $error;
        }
        else {
            update_post_meta($order_id, '_laybuy_order_id', $response->orderId);
            return  $response->orderId;
        }
        
       
    }
    
}

Woocommerce_Laybuy_Form_Handler::init();