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

function wwoocommerce_laybuy_get_product_price( $product ) {
    if ( 'excl' === WC()->cart->tax_display_cart ) {
        $product_price = wc_get_price_excluding_tax( $product );
    } else {
        $product_price = wc_get_price_including_tax( $product );
    }
    return $product_price;
}

function woocommerce_laybuy_get_cart_shipping_total() {

    if ( isset( WC()->cart->shipping_total ) ) {
        if ( WC()->cart->shipping_total > 0 ) {

            // Display varies depending on settings
            if ( 'excl' === WC()->cart->tax_display_cart ) {
                $return = WC()->cart->shipping_total;
                return $return;

            } else {

                $return = WC()->cart->shipping_total + WC()->cart->shipping_tax_total;
                return $return;
            }
        } else {
            return 0;
        }
    }
    return 0;
}

function woocommerce_laybuy_get_coupon_amount( $coupon ) {
    if ( is_string( $coupon ) ) {
        $coupon = new WC_Coupon( $coupon );
    }

    return ( WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax ) ) ? WC()->cart->get_coupon_discount_amount( $coupon->get_code(), WC()->cart->display_cart_ex_tax ) : 0;
}

// Add price breakdown to Products

// add_filter( 'woocommerce_get_price_html', 'w2mlaybuy_add_price_breakdown_for_text_only', 10, 2 );

function woocommerce_laybuy_product_page_action($function){
    $settings = woocommerce_laybuy_get_settings();
    $action = $settings['price_breakdown_option_product_page_position'];
    add_action($action, $function, 11);
}

woocommerce_laybuy_product_page_action('woocommerce_laybuy_add_price_breakdown_for_text_only');

function woocommerce_laybuy_add_price_breakdown_for_text_only() {
    global $product;
    $settings = woocommerce_laybuy_get_settings();

    if( !isset( $settings['price_breakdown_option_product_page'] ) || 'text_only' !== $settings['price_breakdown_option_product_page'] ) {

        return;
    }

	if( is_product() ) {
		$payment_breakdown = woocommerce_laybuy_calculation( $product->get_price() );
		if(is_composite_product()){
            		$payment_breakdown = woocommerce_laybuy_calculation( $product->get_composite_price());
        	}
        $minimum_today = wc_price( $payment_breakdown['minimum_today'] );
        $weekly_payment = wc_price( $payment_breakdown['weekly_payment'] );
       /* markup = '<link href="https://fonts.googleapis.com/css?family=Montserrat:300,700&text=.abeilmnoprstuwy1234567890$P" rel="stylesheet">';
        $markup .= '<div style="font-family: Montserrat, sans-serif; color: black; font-weight: 300; font-size: 14px;">';
        $markup .= 'Receive your order now, pay over <br><span style="font-weight: 700;">6 weeks</span> interest free!<br>Selecting Laybuy will re-direct you to a secure checkout facility. ';
        $markup .= '<a style="font-weight: 300; color:black; text-decoration: underline;" target="_blank" href="https://laybuy.com"> Learn More</a>';
        */
        
        $laybuyicon = WOOCOMMERCE_LAYBUY_PLUGIN_PATH . 'images/laybuy_logo_small.svg';
		$html_breakdown = sprintf('%1$s%2$s',
			      '<link href="https://fonts.googleapis.com/css?family=Montserrat:300,700&text=.abefilkmnoprstuwy1234567890$PNZD" rel="stylesheet">',
                '<p style="font-family: Montserrat, sans-serif; color: black; font-weight: 300; font-size: 12px;">'.
                   "or 6 weekly interest free payments of <strong>NZD{$weekly_payment}</strong> with ".
                 '<a href="https://www.laybuy.com" target="_blank">'.
                 '<img style="float:none; display:inline-block; vertical-align:middle;padding-left: 2px; padding-bottom:3px; width:80px" src="' .
                $laybuyicon . '"></a></p>');
        // '<img src="' . $laybuyicon . '"> LAY<strong style="font-family: Montserrat, sans-serif; color: black; font-weight: 700; font-size: 14px;">BUY</strong> - <a href="https://www.laybuy
        //.com/what-is-laybuy" target="_blank">Whats this?</a></p>'
		
		echo $html_breakdown;
	}

}


woocommerce_laybuy_product_page_action('woocommerce_laybuy_add_price_breakdown_for_text_and_table');

function woocommerce_laybuy_add_price_breakdown_for_text_and_table() {
    global $product;
    $settings = woocommerce_laybuy_get_settings();

    if( !isset( $settings['price_breakdown_option_product_page'] ) || 'text_and_table' !== $settings['price_breakdown_option_product_page'] ) {
        return;
    }

    if( is_product() ) {

        $payment_breakdown = woocommerce_laybuy_calculation( $product->get_price() );
	if(is_composite_product()){
            $payment_breakdown = woocommerce_laybuy_calculation( $product->get_composite_price());
        }
        $minimum_today = wc_price( $payment_breakdown['minimum_today'] );
        $weekly_payment = wc_price( $payment_breakdown['weekly_payment'] );
    
    
        $laybuyicon = WOOCOMMERCE_LAYBUY_PLUGIN_PATH . 'images/laybuy_logo_small.svg';
        $html_breakdown = sprintf('%1$s%2$s%3$s',
                                  '<link href="https://fonts.googleapis.com/css?family=Montserrat:300,700&text=.abefilkmnoprstuwy1234567890$PNZDP" rel="stylesheet">' ,
                                 '<p style="font-family: Montserrat, sans-serif; color: black; font-weight: 300; font-size: 14px;">',
                                  "or 6 interest free payments from <strong style='font-weight: 700;'>NZD{$weekly_payment}</strong> with ",
                                  '<a href="https://www.laybuy.com" target="_blank"><img style="float:none; display:inline; vertical-align:middle;padding-left: 4px" src="' . $laybuyicon . '"></a></p>'
        );

        date_default_timezone_set( wp_get_timezone_string() );

        ob_start();
        ?>
        <table class="w2m-pricetable-products">
            <thead>
                <th>Payment Date</th>
                <th>Amount</th>
            </thead>
            <tbody>
                <tr><td>Minimum Today</td><td><?php echo $minimum_today; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+1 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+2 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+3 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+4 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+5 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
            </tbody>
        </table>

        <?php
        $html_breakdown .= ob_get_clean();

        echo $html_breakdown;
    }

    return;
}

// Add price breakdown to Checkout
add_filter( 'laybuy_modify_payment_description', 'woocommerce_laybuy_modify_payment_description_for_text_only', 10, 2 );

function woocommerce_laybuy_modify_payment_description_for_text_only( $description, $total ) {

    $settings = woocommerce_laybuy_get_settings();

    if( !isset( $settings['price_breakdown_option_checkout_page'] ) || 'text_only' !== $settings['price_breakdown_option_checkout_page'] ) {

        return $description;
    }

    $payment_breakdown = woocommerce_laybuy_calculation( $total );
    // $minimum_today = wc_price( $payment_breakdown['minimum_today'] );
    $weekly_payment = wc_price( $payment_breakdown['weekly_payment'] );
   
    if ('sandbox' == $settings['environment']) {
        
        //<img style="font-weight: 700; display: inline-block;" src="' . WOOCOMMERCE_LAYBUY_PLUGIN_PATH . 'images/laybuy_logo_transparent.png" alt="">
        $description_prefix = 'SANDBOX Enabled: ';
        
    }
    else {
        $description_prefix = '';
    }
    
    $laybuyicon = WOOCOMMERCE_LAYBUY_PLUGIN_PATH . 'images/laybuy_logo_small.svg';
    $html_breakdown = sprintf('%1$s%2$s%3$s', $description_prefix
                                  . '<link href="https://fonts.googleapis.com/css?family=Montserrat:300,700&text=.abefilkmnoprstuwy1234567890$PNZD" rel="stylesheet">'
                                  . '<p style="font-family: Montserrat, sans-serif; color: black; font-weight: 300; font-size: 14px;">',
                              'Receive your order now, pay with <br><span style="font-weight: 700;">6 weekly</span> payments of <strong>'.$weekly_payment.'</strong> interest free! &nbsp; Selecting Laybuy will
    re-direct you to a secure checkout facility. ', '</p>'
	);

    return $description . $html_breakdown;
}

add_filter( 'laybuy_modify_payment_description', 'woocommerce_laybuy_modify_payment_description_for_text_and_table', 10, 2 );
function woocommerce_laybuy_modify_payment_description_for_text_and_table( $description, $total ) {
    $settings = woocommerce_laybuy_get_settings();

    if( !isset( $settings['price_breakdown_option_checkout_page'] ) || 'text_and_table' !== $settings['price_breakdown_option_checkout_page'] ) {

        return $description;
    }


    $payment_breakdown = woocommerce_laybuy_calculation( $total );
    $minimum_today = wc_price( $payment_breakdown['minimum_today'] );
    $weekly_payment = wc_price( $payment_breakdown['weekly_payment'] );
    
    
    $laybuyicon = WOOCOMMERCE_LAYBUY_PLUGIN_PATH . 'images/laybuy_logo_small.svg';
    $html_breakdown = sprintf('%1$s%2$s%3$s',
                              '<link href="https://fonts.googleapis.com/css?family=Montserrat:300,700&text=.abefilkmnoprstuwy1234567890$PNZD" rel="stylesheet">' . '<p style="font-family: Montserrat, sans-serif; color: black; font-weight: 300; font-size: 14px;">',
                              "or 6 interest free payments from <strong>{$weekly_payment}</strong> with ",
                              '<a href="https://www.laybuy.com" target="_blank"><img src="' . $laybuyicon . '"></a></p>'
    );

    date_default_timezone_set( wp_get_timezone_string() );
    ob_start();
    ?>
    <table class="w2m-pricetable-checkout">
        <thead>
            <th>Payment Date</th>
            <th>Amount</th>
        </thead>
        <tbody>
                <tr><td>Minimum Today</td><td><?php echo $minimum_today; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+1 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+2 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+3 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+4 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
                <tr><td><?php echo date("d F Y", strtotime("+5 week")); ?></td><td><?php echo $weekly_payment; ?></td></tr>
            </tbody>
    </table>

    <?php
    $html_breakdown .= ob_get_clean();

    return $description . $html_breakdown;
}

function woocommerce_laybuy_calculation($dividend, $divisor = 6) {

    // multiplying it to 100 makes is having a better precision.
    // stripe use this
    $dividend = $dividend * 100;

    if( 0 < ($dividend % $divisor) ) {
        // get weeklys
        $weekly_payment = intval( $dividend / $divisor );

        // get minimum payment
        $minimum_today = intval( $dividend - ($weekly_payment * 5) );

        $calculation = array(
            'minimum_today' => $minimum_today * 0.01,
            'weekly_payment' => $weekly_payment * 0.01
        );
    } else {
        $even_weekly_payment = $dividend / $divisor;

        $calculation = array(
            'minimum_today' => $even_weekly_payment * 0.01,
            'weekly_payment' => $even_weekly_payment * 0.01
        );

    }

    return $calculation;
}


if( !function_exists('wp_get_timezone_string') ) {
    /**
     * Returns the timezone string for a site, even if it's set to a UTC offset
     *
     * Adapted from http://www.php.net/manual/en/function.timezone-name-from-abbr.php#89155
     *
     * @return string valid PHP timezone string
     */
    function wp_get_timezone_string() {

        // if site timezone string exists, return it
        if ( $timezone = get_option( 'timezone_string' ) )
            return $timezone;

        // get UTC offset, if it isn't set then return UTC
        if ( 0 === ( $utc_offset = get_option( 'gmt_offset', 0 ) ) )
            return 'UTC';

        // adjust UTC offset from hours to seconds
        $utc_offset *= 3600;

        // attempt to guess the timezone string from the UTC offset
        if ( $timezone = timezone_name_from_abbr( '', $utc_offset, 0 ) ) {
            return $timezone;
        }

        // last try, guess timezone string manually
        $is_dst = date( 'I' );

        foreach ( timezone_abbreviations_list() as $abbr ) {
            foreach ( $abbr as $city ) {
                if ( $city['dst'] == $is_dst && $city['offset'] == $utc_offset )
                    return $city['timezone_id'];
            }
        }

        // fallback to UTC
        return 'UTC';
    }

}
