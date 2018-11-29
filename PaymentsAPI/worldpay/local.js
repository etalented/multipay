qp_worldpay_handler = false;

jQuery('document').ready(function() {

	$ = jQuery;
	
	$('.worldpay-payment input[type=submit]').click(function(event) {
		
		event.preventDefault();

		/*
			Collect Important Data
		*/
		form = $(event.target).closest('form'), k = form.closest('div');

		qp_show_validating(k);

		qp_do_validation(form,'worldpay',function(data) {

			/*
				Validation success
			*/
			$('body').append($("<div id='qp_wp_modal'></div><form id='qp_wp_temp_form'><div id='mySection'><button>Testing</button></div><button onclick='Worldpay.submitTemplateForm()' id='_iframe_price' style='display: none;'>Pay "+data.currency_sign.b+data.data.amount+"</button><a id='qp_wp_modal_close'>x</a></form>"));

			Worldpay.useTemplateForm({
				'clientKey':data.data.key,
				'form':'qp_wp_temp_form',
				'saveButton':false,
				'paymentSection':'mySection',
				'display':'inline',
				'callback': function(obj) {
					if (obj && obj.token) {
						
						qp_redirect({
							'module':'worldpay',
							'token':obj.token,
							'qp_key':data.qp_key,
							'form':form.find('input[name=form_id]').val()
						});

					}
				}
			});
			
			$('#qp_wp_modal, #qp_wp_modal_close').click(function() {
				$('#qp_wp_modal, #qp_wp_temp_form').hide();
			});
			
			/*
				Create setTimeout loop until Worldpay form loads
			*/
			$_iframe_invisible = function() {
				var visible = jQuery('#_iframe_holder').is(':visible');
				if (!visible) {
					// hide price
					$('#_iframe_price').hide();
					qp_redirect({'module':'worldpay','force':'failure'});
				} else {
					window.setTimeout($_iframe_invisible, 50);
				}
			}
			$_iframe_visible = function() {
				if (Worldpay.templateFormVisible) {
					$('#_iframe_price').fadeIn();
					$('.qp_payment_modal').fadeOut();
					window.setTimeout($_iframe_invisible, 50);
				} else {
					window.setTimeout($_iframe_visible, 50);
				}
			}
			window.setTimeout($_iframe_visible, 50);

			Worldpay.getTemplateToken();
			
		},function(e) {
			qp_show_main(k);
		});
	});
});