jQuery(document).on('click', '.woocommerce-laybuy-notice .notice-dismiss', function () {

  jQuery.ajax({
    url: '/wp/wp-admin/admin-ajax.php',
    type: 'post',
    data: {
      action: 'woocommerce_laybuy_dismiss_ssl_notice'
    },
    success: function (response) {
       //
    }

  });

});