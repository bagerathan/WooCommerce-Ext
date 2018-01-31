<?php
/*
Plugin Name: Woocommerce Laybuy
Plugin URI: https://www.laybuy.com/
Description:  Payment gateway extension for laybuy.com
Version: 1.0
Author: Carl Bowden, Larry Watene
Author URI: carl@16hands.co.nz
Text Domain: woocommerce_laybuy
License: GPL2

Woocommerce Laybuy is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Woocommerce Laybuy is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Woocommerce Laybuy. If not, see https://www.laybuy.com/.
*/


if (!defined('ABSPATH')) {
    exit;
}

/**
 * Exit when woocommerce is not active.
 */
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

require_once 'constants.php';

add_action('plugins_loaded', 'woocommerce_laybuy_gateway_init');

function woocommerce_laybuy_gateway_init() {
    require_once 'includes/woocommerce-laybuy-functions.php';
    require_once 'includes/woocommerce-laybuy-form-handler.php';
    require_once 'includes/Woocommerce_Laybuy_Logger.php';
    require_once 'woocommerce-laybuy-gateway.php';
    
    // Add the Gateway to WooCommerce
    add_filter('woocommerce_payment_gateways', 'woocommerce_laybuy_add_gateway');
    
    function woocommerce_laybuy_add_gateway($gateways) {
        $gateways[] = 'Woocommerce_Laybuy_Gateway';
        return $gateways;
    }
}

add_action('wp_ajax_woocommerce_laybuy_dismiss_ssl_notice', 'woocommerce_laybuy_notice_dismissed_ssl_update'); // the bit after the wp_ajax_ is the ajax action

function woocommerce_laybuy_notice_dismissed_ssl_update() {
    set_option('woocommerce-laybuy-notice-dismissed-ssl', 1);
}


add_action('admin_notices', 'woocommerce_laybuy_recommend_ssl_notice');

function woocommerce_laybuy_recommend_ssl_notice() {
    
    //if( !is_ssl() ) {
    if (!is_ssl() && empty(get_option('woocommerce-laybuy-notice-dismissed-ssl')) ) {
        ?>
        <div class="error notice woocommerce-laybuy-notice is-dismissible">
            <p><?php _e('Enabling SSL is highly recommended when using the Laybuy payment option.', 'woocommerce_laybuy'); ?></p>
            <button type="button" class="notice-dismiss">
                <span class="screen-reader-text">Hide</span>
            </button>
        </div>
        <?php
    }
    
}

add_action('wp_enqueue_scripts', 'woocommerce_laybuy_plugin_assets');

function woocommerce_laybuy_plugin_assets() {
    wp_enqueue_script('woocommerce-laybuy-notice-update', plugins_url('/js/notice-update.js', __FILE__), ['jquery'], '1.0', TRUE);
}

/**
 * Add automated updates class
 */
//require_once('wp-updates-plugin.php');
//new WPUpdatesPluginUpdater_1709( 'http://wp-updates.com/api/2/plugin', plugin_basename(__FILE__));
