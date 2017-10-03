jQuery(document).ready(function($) {
	$(".js-wsb-add-to-cart").on('click', function(e) {
		e.preventDefault();

		var product_id   = $(this).val(),
				variation_id = $('input[name="variation_id"]').val(),
				quantity     = $('input[name="quantity"]').val(),
				data         = 'action=wsb_add_to_cart_ajax&product_id=' + product_id + '&quantity=' + quantity;

		if (variation_id != '') {
				data = 'action=wsb_add_to_cart_ajax&product_id=' + product_id + '&variation_id=' + variation_id + '&quantity=' + quantity;
		}

		if ( $(this).hasClass('wsb-added-to-cart') ) {
			$('.wsb-modal').addClass('is-visible');
		} else {
			$.ajax ({
				url: woocommerce_simple_buy_now.ajax_url,
				type:'POST',
				data:data,
				success:function(results) {
				  $('.wsb-modal-content').html(results.data.checkout);
				  $('.wsb-modal').addClass('is-visible');
				  $('.js-wsb-add-to-cart').addClass('wsb-added-to-cart');
				}
			});
		}
	});

	$('.wsb-modal-toggle').on('click', function(e) {
	  e.preventDefault();
	  $('.wsb-modal').removeClass('is-visible');
	});
});
