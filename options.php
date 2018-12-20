<?php

function qp_get_stored_setup () {
    $qp_setup = get_option('qp_setup');
    if(!is_array($qp_setup)) $qp_setup = array();
    $default = array(
        'current' => 'default',
        'alternative' => 'default',
        'sandbox' => false,
        'encryption' => false
    );
    $qp_setup = array_merge($default, $qp_setup);
    return $qp_setup;
}

function qp_get_stored_curr () {
    $qp_curr = get_option('qp_curr');
    if(!is_array($qp_curr)) $qp_curr = array();
    $default =  array('default' => 'USD');
    $qp_curr = array_merge($default, $qp_curr);
    return $qp_curr;
}

function qp_get_stored_msg () {
    $messageoptions = get_option('qp_messageoptions');
    if(!is_array($messageoptions)) $messageoptions = array();
    $default = array(
        'messageqty' => 'fifty',
        'messageorder' => 'newest'
    );
    $messageoptions = array_merge($default, $messageoptions);
    return $messageoptions;
}

function qp_get_stored_options($id) {
    $qp = get_option('qp_options'.$id);
    if(!is_array($qp)) $qp = array();
    $default = array(
        'sort' => 'reference,amount,quantity,stock,options,postage,processing,coupon,additionalinfo,address,slider,email,message,datepicker,terms,captcha,totals',
        'title' => 'Payment Form',
        'blurb' => 'Enter the payment details and submit',
        'inputreference' => 'Payment reference',
        'inputamount' => 'Amount to pay',
        'comboboxword' => 'Other',
        'comboboxlabel' => 'Enter Amount',
        'quantitylabel' => 'Quantity',
        'quantity' => '1',
        'stocklabel' => 'Item Number',
        'use_stock' => false,
        'optionlabel' => 'Options',
        'optionvalues' => 'Large,Medium,Small',
        'use_options' => false,
        'use_slider' => false,
        'sliderlabel' => 'Amount to pay',
        'min' => '0',
        'max' => '100',
        'initial' => '50',
        'step' => '10',
        'output-values' => 'checked',
        'messagelabel' => 'Message',
        'shortcodereference' => 'Payment for: ',
        'shortcodeamount' => 'Amount: ',
        'paypal-location' => 'imagebelow',
        'captcha' => false,
        'mathscaption' => 'Spambot blocker question',
        'submitcaption' => 'Make Payment',
        'resetcaption' => 'Reset Form',
        'use_reset' => false,
        'use_process' => false,
        'processblurb' => 'A processing fee will be added before payment',
        'processref' => 'Processing Fee',
        'processtype' => 'processpercent',
        'processpercent' => '5',
        'processfixed' => '2',
        'use_postage' => false,
        'postageblurb' => 'Post and Packing will be added before payment',
        'postageref' => 'Post and Packing',
        'postagetype' => 'postagefixed',
        'postagepercent' => '5',
        'postagefixed' => '5',
        'use_coupon' => false,
        'use_blurb' => false,
        'use_email' => false,
        'extrablurb' => 'Make sure you complete the next field',
        'couponblurb' => 'Enter coupon code',
        'couponref' => 'Coupon Applied',
        'couponbutton' => 'Apply Coupon',
        'termsblurb' => 'I agree to the Terms and Conditions',
        'termsurl' => home_url(),
        'termspage' => 'checked',
        'quantitymaxblurb' => 'maximum of 10',
        'currencies' => 'USD,GBP,EUR',
        'use_address' => false,
        'addressblurb' => 'Enter your details below',
        'use_datepicker' => false,
        'datepickerlabel' => 'Select date',
        'use_totals' => false,
        'totalsblurb' => 'Total:',
        'emailblurb' => 'Your email address',
        'couponapplied' => '',
        'inline_amount' => '',
        'selector' => 'radio',
        'refselector' => 'refradio',
        'amtselector' => 'amtradio',
        'optionselector' => 'optionsradio'
    );
    $qp = array_merge($default, $qp);
    return $qp;
}
    
function qp_get_send_defaults() {
    $defaults = array(
        'cancelurl' => false,
        'thanksurl' => false,
        'errortitle' => 'Oops, got a problem here',
        'errorblurb' => 'Please check the payment details',
        'validating' => 'Validating payment information...',
        'waiting' => 'Waiting for merchant...',
        'technicalerrorblurb' => 'There is a technical issue, contact the site owner',
        'failuretitle' => 'Order Failure',
        'failureblurb' => 'The payment has not been completed.',
        'failureanchor' => 'Try Again',
        'pendingtitle' => 'Payment Pending',
        'pendingblurb' => 'The payment has been processed, but confimration is currently pending. Refresh this page for real-time changes to this order.',
        'pendinganchor' => 'Refresh the Page',
        'confirmationtitle' => 'Order Confirmation',
        'confirmationblurb' => 'The transaction has been completed successfully.',
        'confirmationreference' => 'Payment Reference:',
        'confirmationamount' => 'Amount Paid:',
        'confirmationanchor' => 'Continue Shopping',
    );
    return $defaults;
}

function qp_get_stored_send($id) {
    $send = get_option('qp_send'.$id);
    if(!is_array($send)) $send = array();
    $send = array_merge(qp_get_send_defaults(), $send);
    return $send;
}

function qp_get_stored_style() {
    $style = get_option('qp_style');
    if(!is_array($style)) $style = array();
    $default = array(
        'font-family' => 'arial, sans-serif',
        'font-size' => '1em',
        'font-colour' => '#465069',
        'header-type' => 'h2',
        'header-size' => '1.6em',
        'header-colour' => '#465069',
        'text-font-family' => 'arial, sans-serif',
        'text-font-size' => '1em',
        'text-font-colour' => '#465069',
        'width' => 280,
        'form-border' => '1px solid #415063',
        'widthtype' => 'pixel',
        'border' => 'plain',
        'input-border' => '1px solid #415063',
        'required-border' => '1px solid #00C618',
        'error-colour' => '#FF0000',
        'bordercolour' => '#415063',
        'background' => 'theme',
        'backgroundhex' => '#FFF',
        'backgroundimage' => false,
        'corners' => 'square',
        'line_margin' => 'margin: 2px 0 3px 0;padding: 6px;',
        'para_margin' => 'margin: 20px 0 3px 0;padding: 0',
        'submit-colour' => '#FFF',
        'submit-background' => '#343838',
        'submit-hover-background' => '#888888',
        'submit-button' => false,
        'submit-border' => '1px solid #415063',
        'submitwidth' => 'submitpercent',
        'submitposition' => 'submitleft',
        'coupon-colour' => '#FFF',
        'coupon-background' => '#1f8416',
        'slider-thickness' => '2',
        'slider-background' => '#CCC',
        'slider-revealed' => '#00ff00',
        'handle-background' => 'white',
        'handle-border' => '#CCC',
        'handle-corners' => 50,
        'output-size' => '1.2em',
        'output-colour' => '#465069',
        'styles' => 'plugin'
    );
    $style = array_merge($default, $style);
    return $style;
}

function qp_get_stored_coupon ($id) {
    $coupon = get_option('qp_coupon'.$id);
    if(!is_array($coupon)) $coupon = array();
    $default = qp_get_default_coupon();
    $coupon = array_merge($default, $coupon);
    return $coupon;
}

function qp_get_default_coupon () {
    for ($i=1; $i<=10; $i++) {
        $coupon['couponget'] = 'Coupon Code:';
        $coupon['coupontype'.$i] = 'percent'.$i;
        $coupon['couponpercent'.$i] = '10';
        $coupon['couponfixed'.$i] = '5';
    }
    $coupon['couponget'] = 'Coupon Code:';
    $coupon['couponnumber'] = '10';
    $coupon['duplicate'] = '';
    $coupon['couponerror'] = 'Invalid Code';
    $coupon['couponexpired'] = 'Coupon Expired';
    return $coupon;
}

function qp_get_stored_address($id) {
    $address = get_option('qp_address'.$id);
    if(!is_array($address)) $address = array();
    $default = array(
        'firstname' => 'First Name',
        'lastname' => 'Last Name',
        'email' => 'Your Email Address',
        'address1' => 'Address Line 1',
        'address2' => 'Address Line 2',
        'city' => 'City',
        'state' => 'State',
        'zip' => 'ZIP Code',
        'country' => 'Country',
        'night_phone_b' => 'Phone Number'
    );
    $address = array_merge($default, $address);
    return $address;
}

function qp_get_stored_autoresponder($id) {
    $auto = get_option('qp_autoresponder'.$id);
    if(!is_array($auto)) $auto = array();
    $default = array(
        'use_autoresponder' => false,
        'subject' => 'Thank you for your payment.',
        'message' => 'Once payment has been confirmed we will process your order and be in contact soon.',
        'paymentdetails' => 'checked',
        'fromname' => false,
        'fromemail' => false,
        'reference' => 'Reference',
        'amount' => 'Amount',
    );
    $auto = array_merge($default, $auto);
    return $auto;
}

function qp_get_paypal_api () {
    $payment = get_option('qp_paypal_api');
    if(!is_array($payment)) $payment = array();
    $default = array(
		'use_paypal' => false,
        'paypal_email' => false,
        'paypal_submit' => 'Pay with PayPal',
        'merchantid' => false,
        'api_username' => false,
        'api_password' => false,
        'api_key' => false,
        'sandbox' => false
    );
    $payment = array_merge($default, $payment);
    return $payment;
}

function qp_get_stripe_api () {
    $payment = get_option('qp_stripe_api');
    if(!is_array($payment)) $payment = array();
    $default = array(
        'use_stripe' => false,
        'stripe_submit' => 'Pay with Stripe',
        'secret_key' => false,
        'publishable_key' => false,
        'stripeimage' => false
    );
    $payment = array_merge($default, $payment);
    return $payment;
}

function qp_get_worldpay_api () {
    $payment = get_option('qp_worldpay_api');
    if(!is_array($payment)) $payment = array();
    $default = array(
        'use_worldpay' => false,
        'worldpay_submit' => 'Pay with Worldpay',
        'client_key' => false,
        'service_key' => false,
    );
    $payment = array_merge($default, $payment);
    return $payment;
}


function qp_get_amazon_api() {
    $payment = get_option('qp_amazon_api');
    if(!is_array($payment)) $payment = array();
    $default = array(
        'use_amazon' => 'checked', // checked or not
        'amazon_submit' => 'Pay with Amazon',
        'sellerID' => '', // SellerID
        'accessKey' => '', // MWS Access Key
        'secretKey' => '', // MWS Secret Key
        'clientID' => '', // Login With Amazon Client ID
        'mode' => 'PRODUCTION' // PRODUCTION or SANDBOX
    );
    $payment = array_merge($default, $payment);
    return $payment;
}