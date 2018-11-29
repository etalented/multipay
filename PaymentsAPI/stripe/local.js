qp_stripe_handler = false;

jQuery('document').ready(function() {

	$ = jQuery;
	
	$('.stripe-payment input[type=submit]').click(function(event) {
		
		event.preventDefault();

		/*
			Collect Important Data
		*/
		form = $(event.target).closest('form'), k = form.closest('div');

		qp_show_validating(k);

		qp_do_validation(form,'stripe',function(data) {
			
			qp_dont_cancel = false;
			qp_stripe_handler = StripeCheckout.configure({
				'key':data.data.key,
				'image': data.data.image,
				'locale': 'auto',
				'token': function(token) {
					/*
						Redirect user to same page
						
						Include in redirect the token for the card and user email
					*/

					params = {'module':'stripe','token':token.id,'qp_key':data.qp_key,'form':form.find('input[name=form_id]').val()};
					
					qp_redirect(params);
					
					qp_dont_cancel = true;
				},
				'closed': function() {
					if (!qp_dont_cancel) qp_redirect({'module':'stripe','force':'failure'});
					
					qp_close_modal();
				},
				'opened': function() {
					qp_close_modal();
				}
			});
			
			var options = {
				name: data.data.name,
				description: data.data.description,
				amount: data.data.amount * 100,
				currency: data.currency
			};
			
			if (data.data.email.length > 0) options.email = data.data.email;
			
			qp_stripe_handler.open(options);
			$('.qp_payment_modal_button input').click(function() {
				qp_close_modal();
			});
		},function(e) {
			qp_show_main(k);
		});
	});
});