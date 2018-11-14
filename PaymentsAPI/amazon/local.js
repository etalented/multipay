jQuery('document').ready(function() {

	$ = jQuery;
	
	/*
		Set up proceed button
	*/
	$('.qp_payment_modal_button input').click(function(event) {
		
		if (qp_pending_payment.qp_key != '' && qp_pending_payment.method == 'amazon') {
			
			k = qp_pending_payment.k, data = qp_pending_payment.data;
			form = k.find('form');
			
			var button = form.find('#amazon_'+form.attr('id')).find('img');
			
			button.click();
			
		}
		
	});
	
	$('.amazon-payment input[type=submit]').click(function(event) {
		
		event.preventDefault();

		/*
			Collect Important Data
		*/
		form = $(event.target).closest('form'), k = form.closest('div');
		
		if (!form.find('#amazon_'+form.attr('id')).size()) {
			form.append($('<div style="display:none;" id="amazon_'+form.attr('id')+'"></div>'));
		}
		
		qp_do_validation(form,'amazon',function(data) { 
			OffAmazonPayments.Button("amazon_"+form.attr('id'), data.data.payload.sellerId, {
				type: "hostedPayment",
				hostedParametersProvider: function(done) {
					done(data.data.payload);
				},
				onError: function(errorCode) {
					console.log(errorCode.getErrorCode() + " " + errorCode.getErrorMessage());
				}
			});
		},function(e) { qp_show_main(k); });
	});
});