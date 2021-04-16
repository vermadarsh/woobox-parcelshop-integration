jQuery(document).ready(function ($) {
  'use strict';
  
  var woopi_public_js_functions = {
    
    init: function() {
    
    },
  
    woopiLoadPickupPoints: function (customer_shipping_postcode, customer_shipping_country) {
      $('.woopi-pickup-ajax-loader').show();
      var data = {
        'action': 'woopi_fetch_pickuppoints_html',
        'security': WOOPI_Public_JS_Obj.woopi_nonce,
        'customer_shipping_postcode': customer_shipping_postcode,
        'customer_shipping_country': customer_shipping_country,
      };
      $.ajax({
        dataType: 'JSON',
        url: WOOPI_Public_JS_Obj.woopi_ajax_url,
        type: 'POST',
        data: data,
        success: function (response) {
          if ('woopi-pickpoints-html-fetched' === response.data.message) {
            $('.woopi-pickup-ajax-loader').hide();
            $('#woopi-pickup-locations').html(response.data.html); // phpcs: ignore
            markers = response.data.coordinates;
          }
        },
      });
    },
  };
  woopi_public_js_functions.init();
  
  var woopi_public_js = {
    
    init: function () {
      this.woopiLoadPickupPointsOnLoad();
      $(document).on('change', '#billing_country', this.woopiLoadPickupPointsOnBillingCountryChange);
      $(document).on('keyup', '#billing_postcode', this.woopiLoadPickupPointsOnBillingPostcodeChange);
    },
  
    woopiLoadPickupPointsOnLoad: function () {
      if ('yes' === WOOPI_Public_JS_Obj.is_checkout_page) {
        var customer_shipping_postcode = WOOPI_Public_JS_Obj.customer_shipping_postcode;
				var customer_shipping_country = WOOPI_Public_JS_Obj.customer_shipping_country;
        if ('' !== customer_shipping_postcode && '' !== customer_shipping_country) {
          woopi_public_js_functions.woopiLoadPickupPoints(customer_shipping_postcode, customer_shipping_country);
        }
      }
    },
  
    woopiLoadPickupPointsOnBillingCountryChange: function () {
      if ('yes' === WOOPI_Public_JS_Obj.is_checkout_page) {
        var customer_shipping_postcode = $('#billing_postcode').val();
        var customer_shipping_country = $('#billing_country').val();
        if ('' !== customer_shipping_postcode && '' !== customer_shipping_country) {
          $('#woopi-pickup-locations').html('');
          woopi_public_js_functions.woopiLoadPickupPoints(customer_shipping_postcode, customer_shipping_country);
        }
      }
    },
  
    woopiLoadPickupPointsOnBillingPostcodeChange: function () {
      if ('yes' === WOOPI_Public_JS_Obj.is_checkout_page) {
        var customer_shipping_postcode = $('#billing_postcode').val();
        var customer_shipping_country = $('#billing_country').val();
        if ('' !== customer_shipping_postcode && '' !== customer_shipping_country) {
          $('#woopi-pickup-locations').html('');
          woopi_public_js_functions.woopiLoadPickupPoints(customer_shipping_postcode, customer_shipping_country);
        }
      }
    },
  };
  woopi_public_js.init();
  
});
