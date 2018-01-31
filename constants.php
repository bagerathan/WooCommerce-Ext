<?php
if( !defined( 'ABSPATH' ) ) exit;

define( 'PRODUCTION_API_ENDPOINT', 'https://api.laybuy.com/' );
define( 'SANDBOX_API_ENDPOINT', 'https://sandbox-api.laybuy.com/' );
define( 'PRODUCTION_PAY_API_ENDPOINT', 'https://payment.laybuy.com/' );
define( 'SANDBOX_PAY_API_ENDPOINT', 'https://sandbox-payment.laybuy.com/' );
define( 'CREATE_ORDER_SUFFIX', 'order/create' );
define( 'CANCEL_ORDER_SUFFIX', 'order/cancel' );
define( 'CONFIRM_ORDER_SUFFIX', 'order/confirm' );
define( 'REFUND_ORDER_SUFFIX', 'order/refund' );
define( 'WOOCOMMERCE_LAYBUY_SLUG', 'woocommerce-laybuy');