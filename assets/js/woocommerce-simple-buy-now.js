jQuery(document).ready(function($) {
  $('.js-wsb-add-to-cart').on('click', function(e) {
    e.preventDefault();
    var $el = $(this);

    var data = $('form.cart').serializeArray().reduce(function(obj, item) {
    	obj[item.name] = item.value;
    	return obj;
	}, {});

	if ( ( typeof data['add-to-cart'] != "undefined" ) && ( data['add-to-cart'] !== null ) ) {
		delete( data['add-to-cart'] );
	}

	data['wsb-buy-now'] = $el.val();
	data['action'] = 'wsb_add_to_cart_ajax';

    $.ajax({
      url: woocommerce_simple_buy_now.ajax_url,
      type: 'POST',
      data: data,
      beforeSend: function() {
        $el.addClass('wsb-loading');
      },
      success: function(results) {
        $el.removeClass('wsb-loading');

        var element = results.data.element,
            template = results.data.template,
            redirect = results.data.redirect,
            checkout_url = results.data.checkout_url,
            method = results.data.method;

        if ('append' == method) {
          $(element).append(template);
        } else if ('prepend' == method) {
          $(element).prepend(template);
        } else {
          $(element).html(template);
        }

        if (redirect) {
          // Redirect to the checkout page.
          $(location).attr('href', checkout_url);
        } else {
          // Re-call checkout js.
          woocommerce_checkout_js();
          country_select();
          address_i18n();

          $(document.body).trigger('wsb_checkout_template_added');
          $('.wsb-modal').addClass('is-visible');
        }
      },
    });
  });

  $('.wsb-modal-toggle').on('click', function(e) {
    e.preventDefault();
    $('.wsb-modal').removeClass('is-visible');
  });

  // Handler variation products.
  $('.variations_form').on('hide_variation', function(e) {
    $(this).find('.js-wsb-add-to-cart').addClass('disabled').attr('disabled', 'disabled');
  });

  $('.variations_form').on('show_variation', function(e, variation, purchasable) {
    e.preventDefault();
    if (purchasable) {
      $(this).find('.js-wsb-add-to-cart').removeClass('disabled').removeAttr('disabled');
    }
  });

  function woocommerce_checkout_js() {
    // wc_checkout_params is required to continue, ensure the object exists
    if (typeof wc_checkout_params === 'undefined') {
      return false;
    }

    $.blockUI.defaults.overlayCSS.cursor = 'default';

    var wc_checkout_form = {
      updateTimer: false,
      dirtyInput: false,
      selectedPaymentMethod: false,
      xhr: false,
      $order_review: $('#order_review'),
      $checkout_form: $('form.checkout'),
      init: function() {
        $(document.body).bind('update_checkout', this.update_checkout);
        $(document.body).bind('init_checkout', this.init_checkout);

        // Payment methods
        this.$checkout_form.on('click', 'input[name="payment_method"]', this.payment_method_selected);

        if ($(document.body).hasClass('woocommerce-order-pay')) {
          this.$order_review.on('click', 'input[name="payment_method"]', this.payment_method_selected);
        }

        // Prevent HTML5 validation which can conflict.
        this.$checkout_form.attr('novalidate', 'novalidate');

        // Form submission
        this.$checkout_form.on('submit', this.submit);

        // Inline validation
        this.$checkout_form.on('input validate change', '.input-text, select, input:checkbox', this.validate_field);

        // Manual trigger
        this.$checkout_form.on('update', this.trigger_update_checkout);

        // Inputs/selects which update totals
        this.$checkout_form.on('change',
            'select.shipping_method, input[name^="shipping_method"], #ship-to-different-address input, .update_totals_on_change select, .update_totals_on_change input[type="radio"], .update_totals_on_change input[type="checkbox"]',
            this.trigger_update_checkout);
        this.$checkout_form.on('change', '.address-field select', this.input_changed);
        this.$checkout_form.on('change', '.address-field input.input-text, .update_totals_on_change input.input-text',
            this.maybe_input_changed);
        this.$checkout_form.on('keydown', '.address-field input.input-text, .update_totals_on_change input.input-text',
            this.queue_update_checkout);

        // Address fields
        this.$checkout_form.on('change', '#ship-to-different-address input', this.ship_to_different_address);

        // Trigger events
        this.$checkout_form.find('#ship-to-different-address input').change();
        this.init_payment_methods();

        // Update on page load
        if (wc_checkout_params.is_checkout === '1') {
          $(document.body).trigger('init_checkout');
        }
        if (wc_checkout_params.option_guest_checkout === 'yes') {
          $('input#createaccount').change(this.toggle_create_account).change();
        }
      },
      init_payment_methods: function() {
        var $payment_methods = $('.woocommerce-checkout').find('input[name="payment_method"]');

        // If there is one method, we can hide the radio input
        if (1 === $payment_methods.length) {
          $payment_methods.eq(0).hide();
        }

        // If there was a previously selected method, check that one.
        if (wc_checkout_form.selectedPaymentMethod) {
          $('#' + wc_checkout_form.selectedPaymentMethod).prop('checked', true);
        }

        // If there are none selected, select the first.
        if (0 === $payment_methods.filter(':checked').length) {
          $payment_methods.eq(0).prop('checked', true);
        }

        // Trigger click event for selected method
        $payment_methods.filter(':checked').eq(0).trigger('click');
      },
      get_payment_method: function() {
        return wc_checkout_form.$checkout_form.find('input[name="payment_method"]:checked').val();
      },
      payment_method_selected: function() {
        if ($('.payment_methods input.input-radio').length > 1) {
          var target_payment_box = $('div.payment_box.' + $(this).attr('ID'));

          if ($(this).is(':checked') && !target_payment_box.is(':visible')) {
            $('div.payment_box').filter(':visible').slideUp(250);

            if ($(this).is(':checked')) {
              $('div.payment_box.' + $(this).attr('ID')).slideDown(250);
            }
          }
        } else {
          $('div.payment_box').show();
        }

        if ($(this).data('order_button_text')) {
          $('#place_order').text($(this).data('order_button_text'));
        } else {
          $('#place_order').text($('#place_order').data('value'));
        }

        var selectedPaymentMethod = $('.woocommerce-checkout input[name="payment_method"]:checked').attr('id');

        if (selectedPaymentMethod !== wc_checkout_form.selectedPaymentMethod) {
          $(document.body).trigger('payment_method_selected');
        }

        wc_checkout_form.selectedPaymentMethod = selectedPaymentMethod;
      },
      toggle_create_account: function() {
        $('div.create-account').hide();

        if ($(this).is(':checked')) {
          // Ensure password is not pre-populated.
          $('#account_password').val('').change();
          $('div.create-account').slideDown();
        }
      },
      init_checkout: function() {
        $('#billing_country, #shipping_country, .country_to_state').change();
        $(document.body).trigger('update_checkout');
      },
      maybe_input_changed: function(e) {
        if (wc_checkout_form.dirtyInput) {
          wc_checkout_form.input_changed(e);
        }
      },
      input_changed: function(e) {
        wc_checkout_form.dirtyInput = e.target;
        wc_checkout_form.maybe_update_checkout();
      },
      queue_update_checkout: function(e) {
        var code = e.keyCode || e.which || 0;

        if (code === 9) {
          return true;
        }

        wc_checkout_form.dirtyInput = this;
        wc_checkout_form.reset_update_checkout_timer();
        wc_checkout_form.updateTimer = setTimeout(wc_checkout_form.maybe_update_checkout, '1000');
      },
      trigger_update_checkout: function() {
        wc_checkout_form.reset_update_checkout_timer();
        wc_checkout_form.dirtyInput = false;
        $(document.body).trigger('update_checkout');
      },
      maybe_update_checkout: function() {
        var update_totals = true;

        if ($(wc_checkout_form.dirtyInput).length) {
          var $required_inputs = $(wc_checkout_form.dirtyInput).closest('div').find('.address-field.validate-required');

          if ($required_inputs.length) {
            $required_inputs.each(function() {
              if ($(this).find('input.input-text').val() === '') {
                update_totals = false;
              }
            });
          }
        }
        if (update_totals) {
          wc_checkout_form.trigger_update_checkout();
        }
      },
      ship_to_different_address: function() {
        $('div.shipping_address').hide();
        if ($(this).is(':checked')) {
          $('div.shipping_address').slideDown();
        }
      },
      reset_update_checkout_timer: function() {
        clearTimeout(wc_checkout_form.updateTimer);
      },
      is_valid_json: function(raw_json) {
        try {
          var json = $.parseJSON(raw_json);

          return (json && 'object' === typeof json);
        } catch (e) {
          return false;
        }
      },
      validate_field: function(e) {
        var $this = $(this),
            $parent = $this.closest('.form-row'),
            validated = true,
            validate_required = $parent.is('.validate-required'),
            validate_email = $parent.is('.validate-email'),
            event_type = e.type;

        if ('input' === event_type) {
          $parent.removeClass(
              'woocommerce-invalid woocommerce-invalid-required-field woocommerce-invalid-email woocommerce-validated');
        }

        if ('validate' === event_type || 'change' === event_type) {

          if (validate_required) {
            if ('checkbox' === $this.attr('type') && !$this.is(':checked')) {
              $parent.removeClass('woocommerce-validated').
                  addClass('woocommerce-invalid woocommerce-invalid-required-field');
              validated = false;
            } else if ($this.val() === '') {
              $parent.removeClass('woocommerce-validated').
                  addClass('woocommerce-invalid woocommerce-invalid-required-field');
              validated = false;
            }
          }

          if (validate_email) {
            if ($this.val()) {
              /* https://stackoverflow.com/questions/2855865/jquery-validate-e-mail-address-regex */
              var pattern = new RegExp(
                  /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i);

              if (!pattern.test($this.val())) {
                $parent.removeClass('woocommerce-validated').addClass('woocommerce-invalid woocommerce-invalid-email');
                validated = false;
              }
            }
          }

          if (validated) {
            $parent.removeClass('woocommerce-invalid woocommerce-invalid-required-field woocommerce-invalid-email').
                addClass('woocommerce-validated');
          }
        }
      },
      update_checkout: function(event, args) {
        // Small timeout to prevent multiple requests when several fields update at the same time
        wc_checkout_form.reset_update_checkout_timer();
        wc_checkout_form.updateTimer = setTimeout(wc_checkout_form.update_checkout_action, '5', args);
      },
      update_checkout_action: function(args) {
        if (wc_checkout_form.xhr) {
          wc_checkout_form.xhr.abort();
        }

        if ($('form.checkout').length === 0) {
          return;
        }

        args = typeof args !== 'undefined' ? args : {
          update_shipping_method: true,
        };

        var country = $('#billing_country').val(),
            state = $('#billing_state').val(),
            postcode = $('input#billing_postcode').val(),
            city = $('#billing_city').val(),
            address = $('input#billing_address_1').val(),
            address_2 = $('input#billing_address_2').val(),
            s_country = country,
            s_state = state,
            s_postcode = postcode,
            s_city = city,
            s_address = address,
            s_address_2 = address_2,
            $required_inputs = $(wc_checkout_form.$checkout_form).find('.address-field.validate-required:visible'),
            has_full_address = true;

        if ($required_inputs.length) {
          $required_inputs.each(function() {
            if ($(this).find(':input').val() === '') {
              has_full_address = false;
            }
          });
        }

        if ($('#ship-to-different-address').find('input').is(':checked')) {
          s_country = $('#shipping_country').val();
          s_state = $('#shipping_state').val();
          s_postcode = $('input#shipping_postcode').val();
          s_city = $('#shipping_city').val();
          s_address = $('input#shipping_address_1').val();
          s_address_2 = $('input#shipping_address_2').val();
        }

        var data = {
          security: wc_checkout_params.update_order_review_nonce,
          payment_method: wc_checkout_form.get_payment_method(),
          country: country,
          state: state,
          postcode: postcode,
          city: city,
          address: address,
          address_2: address_2,
          s_country: s_country,
          s_state: s_state,
          s_postcode: s_postcode,
          s_city: s_city,
          s_address: s_address,
          s_address_2: s_address_2,
          has_full_address: has_full_address,
          post_data: $('form.checkout').serialize(),
        };

        if (false !== args.update_shipping_method) {
          var shipping_methods = {};

          $('select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]').
              each(function() {
                shipping_methods[$(this).data('index')] = $(this).val();
              });

          data.shipping_method = shipping_methods;
        }

        $('.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table').block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6,
          },
        });

        wc_checkout_form.xhr = $.ajax({
          type: 'POST',
          url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'update_order_review'),
          data: data,
          success: function(data) {

            // Reload the page if requested
            if (true === data.reload) {
              window.location.reload();
              return;
            }

            // Remove any notices added previously
            $('.woocommerce-NoticeGroup-updateOrderReview').remove();

            var termsCheckBoxChecked = $('#terms').prop('checked');

            // Save payment details to a temporary object
            var paymentDetails = {};
            $('.payment_box input').each(function() {
              var ID = $(this).attr('id');

              if (ID) {
                if ($.inArray($(this).attr('type'), ['checkbox', 'radio']) !== -1) {
                  paymentDetails[ID] = $(this).prop('checked');
                } else {
                  paymentDetails[ID] = $(this).val();
                }
              }
            });

            // Always update the fragments
            if (data && data.fragments) {
              $.each(data.fragments, function(key, value) {
                $(key).replaceWith(value);
                $(key).unblock();
              });
            }

            // Recheck the terms and conditions box, if needed
            if (termsCheckBoxChecked) {
              $('#terms').prop('checked', true);
            }

            // Fill in the payment details if possible without overwriting data if set.
            if (!$.isEmptyObject(paymentDetails)) {
              $('.payment_box input').each(function() {
                var ID = $(this).attr('id');

                if (ID) {
                  if ($.inArray($(this).attr('type'), ['checkbox', 'radio']) !== -1) {
                    $(this).prop('checked', paymentDetails[ID]).change();
                  } else if (0 === $(this).val().length) {
                    $(this).val(paymentDetails[ID]).change();
                  }
                }
              });
            }

            // Check for error
            if ('failure' === data.result) {

              var $form = $('form.checkout');

              // Remove notices from all sources
              $('.woocommerce-error, .woocommerce-message').remove();

              // Add new errors returned by this event
              if (data.messages) {
                $form.prepend(
                    '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-updateOrderReview">' + data.messages +
                    '</div>');
              } else {
                $form.prepend(data);
              }

              // Lose focus for all fields
              $form.find('.input-text, select, input:checkbox').trigger('validate').blur();

              wc_checkout_form.scroll_to_notices();
            }

            // Re-init methods
            wc_checkout_form.init_payment_methods();

            // Fire updated_checkout event.
            $(document.body).trigger('updated_checkout', [data]);
          },

        });
      },
      submit: function() {
        wc_checkout_form.reset_update_checkout_timer();
        var $form = $(this);

        if ($form.is('.processing')) {
          return false;
        }

        // Trigger a handler to let gateways manipulate the checkout if needed
        if ($form.triggerHandler('checkout_place_order') !== false &&
            $form.triggerHandler('checkout_place_order_' + wc_checkout_form.get_payment_method()) !== false) {

          $form.addClass('processing');

          var form_data = $form.data();

          if (1 !== form_data['blockUI.isBlocked']) {
            $form.block({
              message: null,
              overlayCSS: {
                background: '#fff',
                opacity: 0.6,
              },
            });
          }

          // ajaxSetup is global, but we use it to ensure JSON is valid once returned.
          $.ajaxSetup({
            dataFilter: function(raw_response, dataType) {
              // We only want to work with JSON
              if ('json' !== dataType) {
                return raw_response;
              }

              if (wc_checkout_form.is_valid_json(raw_response)) {
                return raw_response;
              } else {
                // Attempt to fix the malformed JSON
                var maybe_valid_json = raw_response.match(/{"result.*}/);

                if (null === maybe_valid_json) {
                  console.log('Unable to fix malformed JSON');
                } else if (wc_checkout_form.is_valid_json(maybe_valid_json[0])) {
                  console.log('Fixed malformed JSON. Original:');
                  console.log(raw_response);
                  raw_response = maybe_valid_json[0];
                } else {
                  console.log('Unable to fix malformed JSON');
                }
              }

              return raw_response;
            },
          });

          $.ajax({
            type: 'POST',
            url: wc_checkout_params.checkout_url,
            data: $form.serialize(),
            dataType: 'json',
            success: function(result) {
              try {
                if ('success' === result.result) {
                  if (-1 === result.redirect.indexOf('https://') || -1 === result.redirect.indexOf('http://')) {
                    window.location = result.redirect;
                  } else {
                    window.location = decodeURI(result.redirect);
                  }
                } else if ('failure' === result.result) {
                  throw 'Result failure';
                } else {
                  throw 'Invalid response';
                }
              } catch (err) {
                // Reload page
                if (true === result.reload) {
                  window.location.reload();
                  return;
                }

                // Trigger update in case we need a fresh nonce
                if (true === result.refresh) {
                  $(document.body).trigger('update_checkout');
                }

                // Add new errors
                if (result.messages) {
                  wc_checkout_form.submit_error(result.messages);
                } else {
                  wc_checkout_form.submit_error(
                      '<div class="woocommerce-error">' + wc_checkout_params.i18n_checkout_error + '</div>');
                }
              }
            },
            error: function(jqXHR, textStatus, errorThrown) {
              wc_checkout_form.submit_error('<div class="woocommerce-error">' + errorThrown + '</div>');
            },
          });
        }

        return false;
      },
      submit_error: function(error_message) {
        $('.woocommerce-NoticeGroup-checkout, .woocommerce-error, .woocommerce-message').remove();
        wc_checkout_form.$checkout_form.prepend(
            '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout">' + error_message + '</div>');
        wc_checkout_form.$checkout_form.removeClass('processing').unblock();
        wc_checkout_form.$checkout_form.find('.input-text, select, input:checkbox').trigger('validate').blur();
        wc_checkout_form.scroll_to_notices();
        $(document.body).trigger('checkout_error');
      },
      scroll_to_notices: function() {
        var scrollElement = $('.woocommerce-NoticeGroup-updateOrderReview, .woocommerce-NoticeGroup-checkout'),
            isSmoothScrollSupported = 'scrollBehavior' in document.documentElement.style;

        if (!scrollElement.length) {
          scrollElement = $('.form.checkout');
        }

        if (scrollElement.length) {
          if (isSmoothScrollSupported) {
            scrollElement[0].scrollIntoView({
              behavior: 'smooth',
            });
          } else {
            $('html, body').animate({
              scrollTop: (scrollElement.offset().top - 100),
            }, 1000);
          }
        }
      },
    };

    var wc_checkout_coupons = {
      init: function() {
        // $(document.body).on('click', 'a.showcoupon', this.show_coupon_form);
        $(document.body).on('click', '.woocommerce-remove-coupon', this.remove_coupon);
        $('form.checkout_coupon').hide().submit(this.submit);
      },
      show_coupon_form: function() {
        $('.checkout_coupon').slideToggle(400, function() {
          $('.checkout_coupon').find(':input:eq(0)').focus();
        });
        return false;
      },
      submit: function() {
        var $form = $(this);

        if ($form.is('.processing')) {
          return false;
        }

        $form.addClass('processing').block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6,
          },
        });

        var data = {
          security: wc_checkout_params.apply_coupon_nonce,
          coupon_code: $form.find('input[name="coupon_code"]').val(),
        };

        $.ajax({
          type: 'POST',
          url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
          data: data,
          success: function(code) {
            $('.woocommerce-error, .woocommerce-message').remove();
            $form.removeClass('processing').unblock();

            if (code) {
              $form.before(code);
              $form.slideUp();

              $(document.body).trigger('update_checkout', {update_shipping_method: false});
            }
          },
          dataType: 'html',
        });

        return false;
      },
      remove_coupon: function(e) {
        e.preventDefault();

        var container = $(this).parents('.woocommerce-checkout-review-order'),
            coupon = $(this).data('coupon');

        container.addClass('processing').block({
          message: null,
          overlayCSS: {
            background: '#fff',
            opacity: 0.6,
          },
        });

        var data = {
          security: wc_checkout_params.remove_coupon_nonce,
          coupon: coupon,
        };

        $.ajax({
          type: 'POST',
          url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'remove_coupon'),
          data: data,
          success: function(code) {
            $('.woocommerce-error, .woocommerce-message').remove();
            container.removeClass('processing').unblock();

            if (code) {
              $('form.woocommerce-checkout').before(code);

              $(document.body).trigger('update_checkout', {update_shipping_method: false});

              // Remove coupon code from coupon field
              $('form.checkout_coupon').find('input[name="coupon_code"]').val('');
            }
          },
          error: function(jqXHR) {
            if (wc_checkout_params.debug_mode) {
              /* jshint devel: true */
              console.log(jqXHR.responseText);
            }
          },
          dataType: 'html',
        });
      },
    };

    var wc_checkout_login_form = {
      init: function() {
        $(document.body).on('click', 'a.showlogin', this.show_login_form);
      },
      show_login_form: function() {
        $('form.login, form.woocommerce-form--login').slideToggle();
        return false;
      },
    };

    var wc_terms_toggle = {
      init: function() {
        $(document.body).on('click', 'a.woocommerce-terms-and-conditions-link', this.toggle_terms);
      },

      toggle_terms: function() {
        if ($('.woocommerce-terms-and-conditions').length) {
          $('.woocommerce-terms-and-conditions').slideToggle();
          return false;
        }
      },
    };

    wc_checkout_form.init();
    wc_checkout_coupons.init();
    wc_checkout_login_form.init();
    wc_terms_toggle.init();
  }

  function country_select() {
	  // wc_country_select_params is required to continue, ensure the object exists
	  if ( typeof wc_country_select_params === 'undefined' ) {
		  return false;
	  }

	  function getEnhancedSelectFormatString() {
		  return {
			  'language': {
				  errorLoading: function() {
					  // Workaround for https://github.com/select2/select2/issues/4355 instead of i18n_ajax_error.
					  return wc_country_select_params.i18n_searching;
				  },
				  inputTooLong: function( args ) {
					  var overChars = args.input.length - args.maximum;

					  if ( 1 === overChars ) {
						  return wc_country_select_params.i18n_input_too_long_1;
					  }

					  return wc_country_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
				  },
				  inputTooShort: function( args ) {
					  var remainingChars = args.minimum - args.input.length;

					  if ( 1 === remainingChars ) {
						  return wc_country_select_params.i18n_input_too_short_1;
					  }

					  return wc_country_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
				  },
				  loadingMore: function() {
					  return wc_country_select_params.i18n_load_more;
				  },
				  maximumSelected: function( args ) {
					  if ( args.maximum === 1 ) {
						  return wc_country_select_params.i18n_selection_too_long_1;
					  }

					  return wc_country_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
				  },
				  noResults: function() {
					  return wc_country_select_params.i18n_no_matches;
				  },
				  searching: function() {
					  return wc_country_select_params.i18n_searching;
				  }
			  }
		  };
	  }

	  // Select2 Enhancement if it exists
	  if ( $().selectWoo ) {
		  var wc_country_select_select2 = function() {
			  $( 'select.country_select:visible, select.state_select:visible' ).each( function() {
				  var select2_args = $.extend({
					  placeholderOption: 'first',
					  width: '100%'
				  }, getEnhancedSelectFormatString() );

				  $( this ).selectWoo( select2_args );
				  // Maintain focus after select https://github.com/select2/select2/issues/4384
				  $( this ).on( 'select2:select', function() {
					  $( this ).focus();
				  } );
			  });
		  };

		  wc_country_select_select2();

		  $( document.body ).bind( 'country_to_state_changed', function() {
			  wc_country_select_select2();
		  });
	  }

	  /* State/Country select boxes */
	  var states_json = wc_country_select_params.countries.replace( /&quot;/g, '"' ),
		  states = $.parseJSON( states_json );

	  $( document.body ).on( 'change', 'select.country_to_state, input.country_to_state', function() {
		  // Grab wrapping element to target only stateboxes in same 'group'
		  var $wrapper    = $( this ).closest('.woocommerce-billing-fields, .woocommerce-shipping-fields, .woocommerce-shipping-calculator');

		  if ( ! $wrapper.length ) {
			  $wrapper = $( this ).closest('.form-row').parent();
		  }

		  var country     = $( this ).val(),
			  $statebox   = $wrapper.find( '#billing_state, #shipping_state, #calc_shipping_state' ),
			  $parent     = $statebox.closest( 'p.form-row' ),
			  input_name  = $statebox.attr( 'name' ),
			  input_id    = $statebox.attr( 'id' ),
			  value       = $statebox.val(),
			  placeholder = $statebox.attr( 'placeholder' ) || $statebox.attr( 'data-placeholder' ) || '';

		  if ( states[ country ] ) {
			  if ( $.isEmptyObject( states[ country ] ) ) {

				  $statebox.closest( 'p.form-row' ).hide().find( '.select2-container' ).remove();
				  $statebox.replaceWith( '<input type="hidden" class="hidden" name="' + input_name + '" id="' + input_id + '" value="" placeholder="' + placeholder + '" />' );

				  $( document.body ).trigger( 'country_to_state_changed', [ country, $wrapper ] );

			  } else {

				  var options = '',
					  state = states[ country ];

				  for( var index in state ) {
					  if ( state.hasOwnProperty( index ) ) {
						  options = options + '<option value="' + index + '">' + state[ index ] + '</option>';
					  }
				  }

				  $statebox.closest( 'p.form-row' ).show();

				  if ( $statebox.is( 'input' ) ) {
					  // Change for select
					  $statebox.replaceWith( '<select name="' + input_name + '" id="' + input_id + '" class="state_select" data-placeholder="' + placeholder + '"></select>' );
					  $statebox = $wrapper.find( '#billing_state, #shipping_state, #calc_shipping_state' );
				  }

				  $statebox.html( '<option value="">' + wc_country_select_params.i18n_select_state_text + '</option>' + options );
				  $statebox.val( value ).change();

				  $( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

			  }
		  } else {
			  if ( $statebox.is( 'select' ) ) {

				  $parent.show().find( '.select2-container' ).remove();
				  $statebox.replaceWith( '<input type="text" class="input-text" name="' + input_name + '" id="' + input_id + '" placeholder="' + placeholder + '" />' );

				  $( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

			  } else if ( $statebox.is( 'input[type="hidden"]' ) ) {

				  $parent.show().find( '.select2-container' ).remove();
				  $statebox.replaceWith( '<input type="text" class="input-text" name="' + input_name + '" id="' + input_id + '" placeholder="' + placeholder + '" />' );

				  $( document.body ).trigger( 'country_to_state_changed', [country, $wrapper ] );

			  }
		  }

		  $( document.body ).trigger( 'country_to_state_changing', [country, $wrapper ] );

	  });
  }

  function address_i18n() {
	  // wc_address_i18n_params is required to continue, ensure the object exists
	  if ( typeof wc_address_i18n_params === 'undefined' ) {
		  return false;
	  }

	  var locale_json = wc_address_i18n_params.locale.replace( /&quot;/g, '"' ), locale = $.parseJSON( locale_json );

	  function field_is_required( field, is_required ) {
		  if ( is_required ) {
			  field.find( 'label .optional' ).remove();
			  field.addClass( 'validate-required' );

			  if ( field.find( 'label .required' ).length === 0 ) {
				  field.find( 'label' ).append( '&nbsp;<abbr class="required" title="' + wc_address_i18n_params.i18n_required_text + '">*</abbr>' );
			  }
		  } else {
			  field.find( 'label .required' ).remove();
			  field.removeClass( 'validate-required' );

			  if ( field.find( 'label .optional' ).length === 0 ) {
				  field.find( 'label' ).append( '&nbsp;<span class="optional">(' + wc_address_i18n_params.i18n_optional_text + ')</span>' );
			  }
		  }
	  }

	  // Handle locale
	  $( document.body ).bind( 'country_to_state_changing', function( event, country, wrapper ) {
		  var thisform = wrapper, thislocale;

		  if ( typeof locale[ country ] !== 'undefined' ) {
			  thislocale = locale[ country ];
		  } else {
			  thislocale = locale['default'];
		  }

		  var $postcodefield = thisform.find( '#billing_postcode_field, #shipping_postcode_field' ),
			  $cityfield     = thisform.find( '#billing_city_field, #shipping_city_field' ),
			  $statefield    = thisform.find( '#billing_state_field, #shipping_state_field' );

		  if ( ! $postcodefield.attr( 'data-o_class' ) ) {
			  $postcodefield.attr( 'data-o_class', $postcodefield.attr( 'class' ) );
			  $cityfield.attr( 'data-o_class', $cityfield.attr( 'class' ) );
			  $statefield.attr( 'data-o_class', $statefield.attr( 'class' ) );
		  }

		  var locale_fields = $.parseJSON( wc_address_i18n_params.locale_fields );

		  $.each( locale_fields, function( key, value ) {

			  var field       = thisform.find( value ),
				  fieldLocale = $.extend( true, {}, locale['default'][ key ], thislocale[ key ] );

			  // Labels.
			  if ( typeof fieldLocale.label !== 'undefined' ) {
				  field.find( 'label' ).html( fieldLocale.label );
			  }

			  // Placeholders.
			  if ( typeof fieldLocale.placeholder !== 'undefined' ) {
				  field.find( 'input' ).attr( 'placeholder', fieldLocale.placeholder );
				  field.find( '.select2-selection__placeholder' ).text( fieldLocale.placeholder );
			  }

			  // Use the i18n label as a placeholder if there is no label element and no i18n placeholder.
			  if ( typeof fieldLocale.placeholder === 'undefined' && typeof fieldLocale.label !== 'undefined' && ! field.find( 'label' ).length ) {
				  field.find( 'input' ).attr( 'placeholder', fieldLocale.label );
				  field.find( '.select2-selection__placeholder' ).text( fieldLocale.label );
			  }

			  // Required.
			  if ( typeof fieldLocale.required !== 'undefined' ) {
				  field_is_required( field, fieldLocale.required );
			  } else {
				  field_is_required( field, false );
			  }

			  // Priority.
			  if ( typeof fieldLocale.priority !== 'undefined' ) {
				  field.data( 'priority', fieldLocale.priority );
			  }

			  // Hidden fields.
			  if ( 'state' !== key ) {
				  if ( typeof fieldLocale.hidden !== 'undefined' && true === fieldLocale.hidden ) {
					  field.hide().find( 'input' ).val( '' );
				  } else {
					  field.show();
				  }
			  }
		  });

		  var fieldsets = $('.woocommerce-billing-fields__field-wrapper, .woocommerce-shipping-fields__field-wrapper, .woocommerce-address-fields__field-wrapper, .woocommerce-additional-fields__field-wrapper .woocommerce-account-fields');

		  fieldsets.each( function( index, fieldset ) {
			  var rows    = $( fieldset ).find( '.form-row' );
			  var wrapper = rows.first().parent();

			  // Before sorting, ensure all fields have a priority for bW compatibility.
			  var last_priority = 0;

			  rows.each( function() {
				  if ( ! $( this ).data( 'priority' ) ) {
					  $( this ).data( 'priority', last_priority + 1 );
				  }
				  last_priority = $( this ).data( 'priority' );
			  } );

			  // Sort the fields.
			  rows.sort( function( a, b ) {
				  var asort = $( a ).data( 'priority' ),
					  bsort = $( b ).data( 'priority' );

				  if ( asort > bsort ) {
					  return 1;
				  }
				  if ( asort < bsort ) {
					  return -1;
				  }
				  return 0;
			  });

			  rows.detach().appendTo( wrapper );
		  } );
	  });
  }
});
