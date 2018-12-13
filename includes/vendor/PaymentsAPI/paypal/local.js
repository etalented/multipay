jQuery(document).ready(function() {
	buttons = [], $ = jQuery;
	$('.paypal-payment input[type=submit]').each(function(index) {
		
		var btn = $('.qp_payment_modal_button input');

		buttons.push({'button':this,'click':function(event) {
			event.preventDefault();
			
			/*
				Open modal
			*/
			
			/*
				Collect Important Data
			*/
			form = jQuery(event.target).closest('form'), k = form.closest('div'), x = paypal.checkout;

			//qp_show_validating(k);

			qp_do_validation(form,'paypal',function(data) {
				
				qp_close_modal();
				paypal.checkout.initXO();
				paypal.checkout.startFlow(data.data.token);
				
					
			},function(e) {
				
				qp_show_working(k);
				
				qp_show_main(k);
				
				paypal.checkout.closeFlow();
					
			});
				
		}});
	});
	
	if (buttons.length) {
		paypal.checkout.setup(qp_ic.id,{
			environment:qp_ic.environment.toLowerCase(),
			buttons:buttons
		});
	}
});