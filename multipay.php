<?php
/*
Plugin Name: MultiPay
Plugin URI: https://wordpress.org/plugins/multipay/
Description: Just want to take payments online? You don't need WooCommerce! With MultiPay you can take payments online quickly via PayPal, Stripe and WorldPay.
Version: 1.5
Author: etalented
Author URI: https://etalented.co.uk/
Text-domain: multipay
*/

defined( 'ABSPATH' ) || exit;

final class MultiPay {
    
	public $version = '1.5';
    
	protected static $_instance = null;
    
	public $payments_api = null;
    
	public $form = null;
    
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
    
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}
    
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin':
				return is_admin();
			case 'ajax':
				return defined( 'DOING_AJAX' );
			case 'cron':
				return defined( 'DOING_CRON' );
			case 'frontend':
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' ) && ! defined( 'REST_REQUEST' );
		}
	}
    
	private function define_constants() {
		$this->define( 'MP_ABSPATH', dirname( __FILE__ ) . '/' );
		$this->define( 'MP_VERSION', $this->version );
	}
    
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
    
	public function includes() {
        include_once MP_ABSPATH . 'includes/class-mp-form.php';
        include_once MP_ABSPATH . 'includes/class-mp-form-widget.php';
        
        // Payments API: base
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/PaymentBase.class.php';
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/PaymentsAPI.class.php';
        
        // Payments API: PayPal
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/paypal/PaypalPayments.class.php';
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/paypal/PaypalAPI.class.php';
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/paypal/PaypalAPI_Order.class.php';
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/paypal/PaypalAPI_Item.class.php';
        
        // Payments API: Stripe
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/stripe/init.php';
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/stripe/StripePayments.class.php';
        
        // Payments API: WorldPay
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/worldpay/init.php';
        include_once MP_ABSPATH . 'includes/vendor/PaymentsAPI/worldpay/WorldpayPayments.class.php';
        
        if ( $this->is_request( 'admin' ) ) {
            include_once MP_ABSPATH . 'includes/class-mp-admin.php';
        }
    }
    
	private function init_hooks() {
		register_shutdown_function( array( $this, 'log_errors' ) );
        
        if ( $this->is_request( 'frontend' ) ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
            add_action( 'wp_head', array( $this, 'head' ) );
            add_action( 'init', array( $this, 'init' ) );
        }

        add_action( 'wp_footer', array( $this, 'footer_style' ), 100 );
        add_action( 'template_redirect', array( $this, 'upgrade_ipn' ) );
	}
    
	public function log_errors() {
		$error = error_get_last();
	}
    
    public function init() {
		if ( $this->is_request( 'frontend' ) ) {
            $this->form = new MP_Form();
        }
        
        $modules = [];
        $modules['worldpay'] = new WorldpayPayments();
        $modules['paypal'] = new PaypalPayments();
        $modules['stripe'] = new StripePayments();
        $this->payments_api = new PaymentsAPI($modules);
        
        /*
            Check if paypal is enabled
        */
        $paypalapi = $this->get_paypal_api();
        if ($paypalapi['use_paypal'] == 'checked') $this->payments_api->load('paypal',$paypalapi);

        /*
            Check if stripe is enabled
        */
        $stripeapi = $this->get_stripe_api();
        if ($stripeapi['use_stripe'] == 'checked') $this->payments_api->load('stripe',$stripeapi);

        /*
            Check if wordpay is enabled
        */
        $worldpayapi = $this->get_worldpay_api();
        if ($worldpayapi['use_worldpay'] == 'checked') $this->payments_api->load('worldpay',$worldpayapi);
    }
    
    public function scripts() {
        // Load payment module assets
        foreach ( $this->payments_api->assets() as $asset ) {
            switch ( $asset['type'] ) {
                case 'css':
                    wp_register_style( $asset['name'], $asset['url'] );
                break;
                case 'script':
                    wp_register_script( $asset['name'], $asset['url'] );
                break;
            }
        }

        wp_register_script( 'multipay', $this->get_asset_url( 'assets/js/multipay.js' ), array( 'jquery', 'jquery-effects-core', 'jquery-ui-datepicker' ), false, true );

        wp_register_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
        wp_register_style( 'multipay-style', $this->get_asset_url( 'assets/css/multipay.css' ), array( 'jquery-ui-style' ) );
    }

    public function head() {
        $this->payments_api->onHead();
    }
    
    public function footer_style() {
        $data = '<style type="text/css" media="screen">'."\r\n".$this->generate_css()."\r\n".'</style>';
        echo $data;
    }
    
    private function generate_css() {
        $code=$corners=$input=$background=$paragraph=$submit='';
        $style = $this->get_stored_style();

            $font = "font-family: ".$style['text-font-family']."; font-size: ".$style['text-font-size'].";color: ".$style['text-font-colour'].";line-height:100%;";
            $inputfont = "font-family: ".$style['font-family']."; font-size: ".$style['font-size']."; color: ".$style['font-colour'].";";
            $selectfont = "font-family: ".$style['font-family']."; font-size: inherit; color: ".$style['font-colour'].";";
            $submitfont = "font-family: ".$style['font-family'];
            if ($style['header-size'] || $style['header-colour']) $header = ".qp-style h2 {font-size: ".$style['header-size']."; color: ".$style['header-colour'].";}";

        $input = ".qp-style input[type=text], .qp-style textarea {border: ".$style['input-border'].";".$inputfont.";height:auto;line-height:normal; ".$style['line_margin'].";}";
        $input .= ".qp-style select {border: ".$style['input-border'].";".$selectfont.";height:auto;line-height:normal;}";
         $input .= ".qp-style .qpcontainer input + label, .qp-style .qpcontainer textarea + label {".$inputfont."}";
        $required = ".qp-style input[type=text].required, .qp-style textarea.required {border: ".$style['required-border'].";}";
        $paragraph = ".qp-style p {margin:4px 0 4px 0;padding:0;".$font.";}";
        if ($style['submitwidth'] == 'submitpercent') $submitwidth = 'width:100%;';
        if ($style['submitwidth'] == 'submitrandom') $submitwidth = 'width:auto;';
        if ($style['submitwidth'] == 'submitpixel') $submitwidth = 'width:'.$style['submitwidthset'].';';

        if ($style['submitposition'] == 'submitleft') $submitposition = 'text-align:left;'; else $submitposition = 'text-align:right;';
        if ($style['submitposition'] == 'submitmiddle') $submitposition = 'margin:0 auto;text-align:center;';

        $submitbutton = ".qp-style p.submit {".$submitposition."}
    .qp-style input#submit {".$submitwidth."color:".$style['submit-colour'].";background:".$style['submit-background'].";border:".$style['submit-border'].";".$submitfont.";font-size: inherit;text-align:center;} .qp_payment_modal_button {color:".$style['submit-colour'].";background:".$style['submit-background']."!important;border:".$style['submit-border'].";".$submitfont.";font-size: inherit;text-align:center;}";

        $submithover = ".qp-style input#submit:hover, .qp_payment_modal_content .qp_payment_modal_button:hover {background:".$style['submit-hover-background'].";}";

        $couponbutton = ".qp-style #couponsubmit, .qp-style #couponsubmit:hover{".$submitwidth."color:".$style['coupon-colour'].";background:".$style['coupon-background'].";border:".$style['submit-border'].";".$submitfont.";font-size: inherit;margin: 3px 0px 7px;padding: 6px;text-align:center;}";
        if ($style['border']<>'none') $border =".qp-style #".$style['border'].", .qp_payment_modal_content {border:".$style['form-border'].";}";
        if ($style['background'] == 'white') {$bg = "background-color:#FFF";$background = ".qp-style div, .qp_payment_modal_content {background-color:#FFF;}";}
        if ($style['background'] == 'color') {$background = ".qp-style div, .qp_payment_modal_content {background-color:".$style['backgroundhex'].";}";$bg = "background-color:".$style['backgroundhex'].";";}
        if ($style['backgroundimage']) $background = ".qp-style #".$style['border']." {background-image: url('".$style['backgroundimage']."');background-size: cover;background-position:center;}";
        $formwidth = preg_split('#(?<=\d)(?=[a-z%])#i', $style['width']);
        if (!isset($formwidth[1])) $formwidth[1] = 'px';
        if ($style['widthtype'] == 'pixel') $width = $formwidth[0].$formwidth[1];
        else $width = '100%';
        if ($style['corners'] == 'round') $corner = '5px'; else $corner = '0';
        $corners = ".qp-style input[type=text], .qp-style textarea, .qp-style select, .qp-style #submit {border-radius:".$corner.";}";
        if ($style['corners'] == 'theme') $corners = '';

        $handle = ( (int) $style['slider-thickness']  ) + 1;
        $slider = '.qp-style div.rangeslider, .qp-style div.rangeslider__fill {height: '.$style['slider-thickness'].'em;background: '.$style['slider-background'].';}
    .qp-style div.rangeslider__fill {background: '.$style['slider-revealed'].';}
    .qp-style div.rangeslider__handle {background: '.$style['handle-background'].';border: 1px solid '.$style['handle-border'].';width: '.$handle.'em;height: '.$handle.'em;position: absolute;top: -0.5em;-webkit-border-radius:'.$style['handle-colours'].'%;-moz-border-radius:'.$style['handle-corners'].'%;-ms-border-radius:'.$style['handle-corners'].'%;-o-border-radius:'.$style['handle-corners'].'%;border-radius:'.$style['handle-corners'].'%;}
    .qp-style div.qp-slideroutput{font-size:'.$style['output-size'].';color:'.$style['output-colour'].';}';

        $code .= ".qp-style {width:".$width.";max-width:100%; }".$border.$corners.$header.$paragraph.$input.$required.$background.$submitbutton.$submithover.$couponbutton.$slider;
        $code  .= '.qp-style input#qptotal {font-weight:bold;font-size:inherit;padding: 0;margin-left:3px;border:none;'.$bg.'}';
        if ($style['use_custom'] == 'checked') $code .= $style['custom'];

        return $code;   
    }
    
	public static function get_asset_url( $path ) {
		return plugins_url( $path, __FILE__ );
	}
    
    public function get_paypal_api() {
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

    public function get_stripe_api() {
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

    public function get_worldpay_api() {
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
    
    public function get_stored_style() {
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

    public function get_stored_curr() {
        $qp_curr = get_option('qp_curr');
        if(!is_array($qp_curr)) $qp_curr = array();
        $default =  array('default' => 'USD');
        $qp_curr = array_merge($default, $qp_curr);
        return $qp_curr;
    }
    
    public function get_stored_options($id) {
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
    
    public function get_send_defaults() {
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
    
    public function get_stored_send($id) {
        $send = get_option('qp_send'.$id);
        if(!is_array($send)) $send = array();
        $send = array_merge($this->get_send_defaults(), $send);
        return $send;
    }

    public function get_stored_address($id) {
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

    public function get_stored_coupon ($id) {
        $coupon = get_option('qp_coupon'.$id);
        if(!is_array($coupon)) $coupon = array();
        $default = $this->get_default_coupon();
        $coupon = array_merge($default, $coupon);
        return $coupon;
    }
    
    public function get_default_coupon () {
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

    public function get_stored_setup () {
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
    
    public function get_current_page_url() {
        $pageURL = 'http';
        if (!isset($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = '';
        if (!empty($_SERVER["HTTPS"])) {
            $pageURL .= "s";
        }
        $pageURL .= "://";
        if (($_SERVER["SERVER_PORT"] != "80") && ($_SERVER['SERVER_PORT'] != '443'))
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        else 
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        return $pageURL;
    }

    public function get_stored_autoresponder($id) {
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

    public function get_stored_msg () {
        $messageoptions = get_option('qp_messageoptions');
        if(!is_array($messageoptions)) $messageoptions = array();
        $default = array(
            'messageqty' => 'fifty',
            'messageorder' => 'newest'
        );
        $messageoptions = array_merge($default, $messageoptions);
        return $messageoptions;
    }

    public function format_amount($currency,$qp,$amount) {
        $curr = ($currency == '' ? 'USD' : $currency);
        $decimal = array('HKD','JPY','MYR','TWD');$d='2';
        foreach ($decimal as $item) {
            if ($item == $curr) {$d = '';break;}
        }
        if (!$d) {
            $check = preg_replace ( '/[^.0-9]/', '', $amount);
            $check = intval($check);
        } else {
            $check = preg_replace ( '/[^.0-9]/', '', $amount);
            $check = number_format($check, $d,'.','');
        }
        return $check;
    }
    
    public function currency($form) {
        $currency = $this->get_stored_curr();
        $c = array();
        $c['a'] = $c['b'] = '';
        $before = array(
            'USD'=>'&#x24;',
            'CDN'=>'&#x24;',
            'EUR'=>'&euro;',
            'GBP'=>'&pound;',
            'JPY'=>'&yen;',
            'AUD'=>'&#x24;',
            'BRL'=>'R&#x24;',
            'HKD'=>'&#x24;',
            'ILS'=>'&#x20aa;',
            'MXN'=>'&#x24;',
            'NZD'=>'&#x24;',
            'PHP'=>'&#8369;',
            'SGD'=>'&#x24;',
            'TWD'=>'NT&#x24;',
            'TRY'=>'&pound;');
        $after = array(
            'CZK'=>'K&#269;',
            'DKK'=>'Kr',
            'HUF'=>'Ft',
            'MYR'=>'RM',
            'NOK'=>'kr',
            'PLN'=>'z&#322',
            'RUB'=>'&#1056;&#1091;&#1073;',
            'SEK'=>'kr',
            'CHF'=>'CHF',
            'THB'=>'&#3647;');
        foreach($before as $item=>$key) {if ($item == $currency[$form]) $c['b'] = $key;}
        foreach($after as $item=>$key) {if ($item == $currency[$form]) $c['a'] = $key;}
        return $c;
    }
    
    public function messagetable($form,$email) {
        $qp_setup = $this->get_stored_setup();
        $options = $this->get_stored_options ($form);
        $message = get_option('qp_messages'.$form);
        $coupon = $this->get_stored_coupon($form);
        $messageoptions = $this->get_stored_msg();
        $address = $this->get_stored_address($form);
        $c = $this->currency ($form);
        $showthismany = '9999';
        $dashboard = $table_content = $table = $padding = $arr = '';
        $count = 0;
        if ($messageoptions['messageqty'] == 'fifty') $showthismany = '50';
        if ($messageoptions['messageqty'] == 'hundred') $showthismany = '100';
        $$messageoptions['messageqty'] = "checked";
        $$messageoptions['messageorder'] = "checked";
        if(!is_array($message)) $message = array();
        $title = $form;

        if ($options['fixedamount'] && strrpos($options['inputamount'],',')) {
            $options['inputamount'] = 'Amount';
        }
        if ($options['fixedreference'] && strrpos($options['inputreference'],';')) {
            $options['inputreference'] = 'Reference';
        }


        if (!$email) $dashboard = '<div class="wrap"><div id="qp-widget">';
        else $padding = 'cellpadding="5"';

        $table .= '<table cellspacing="0" '.$padding.'><tr>';
        if (!$email) $table .= '<th></th>';

        $table .= '<th style="text-align:left">Date Sent</th>';
        foreach (explode( ',',$options['sort']) as $name) {
            $title='';
            switch ( $name ) {
                case 'reference': $table .= '<th style="text-align:left">'.$options['inputreference'].'</th>';break;
                case 'quantity': $table .= '<th style="text-align:left">'.$options['quantitylabel'].'</th>';break;
                case 'amount': $table .= '<th style="text-align:left">'.$options['inputamount'].'</th>';break;
                case 'stock': if ($options['use_stock']) $table .= '<th style="text-align:left">'.$options['stocklabel'].'</th>';break;
                case 'options': if ($options['use_options']) $table .= '<th style="text-align:left">'.$options['optionlabel'].'</th>';break;
                case 'coupon': if ($options['use_coupon']) $table .= '<th style="text-align:left">'.$options['couponblurb'].'</th>';break;
                case 'email': if ($options['use_email']) $table .= '<th style="text-align:left">'.$options['emailblurb'].'</th>';break;
                case 'message': if ($options['use_message']) $table .= '<th style="text-align:left:max-width:20%;">'.$options['messagelabel'].'</th>';break;
                case 'datepicker': if ($options['use_datepicker']) $table .= '<th style="text-align:left:max-width:20%;">'.$options['datepickerlabel'].'</th>';break;
            }
        }
        if ($messageoptions['showaddress']) {
            $arr = array('firstname','lastname','email','address1','address2','city','state','zip','country','night_phone_b');
            foreach ($arr as $item) $table .= '<th style="text-align:left">'.$address[$item].'</th>';
        }
        $table .= '<th>'.__('Payment','multipay').'</th>
        <th>'.__('Merchant','multipay').'</th>
        <th>'.__('Transaction ID','multipay').'</th>
        </tr>';
        if ($messageoptions['messageorder'] == 'newest') {
            $i=count($message) - 1;
            foreach(array_reverse( $message ) as $value) {
                if ($count < $showthismany ) {
                    $table_content .= $this->messagecontent ($form,$value,$options,$c,$messageoptions,$address,$arr,$i,$email);
                    $count = $count+1;
                    $i--;
                }
            }
        } else {
            $i=0;
            foreach($message as $value) {
                if ($count < $showthismany ) {
                    $table_content .= $this->messagecontent ($form,$value,$options,$c,$messageoptions,$address,$arr,$i,$email);
                    $count = $count+1;
                    $i++;
                }
            }
        }

        $table .= $table_content.'</table>';

        if ($table_content) $dashboard .= $table;
        else $dashboard .= '<p>'.__('No payments found','multipay').'</p>';

        for ($i=1; $i<=$coupon['couponnumber']; $i++) {
            if ($coupon['qty'.$i] > 0) $coups .= '<p>'.$coupon['code'.$i].' - '.$coupon['qty'.$i].'</p>';
        }
        if($coups) $dashboard.= '<h2>'.__('Coupons remaining','multipay').'</h2>'.$coups;

        return $dashboard;
    }

    private function messagecontent($form,$value,$options,$c,$messageoptions,$address,$arr,$i,$email) {
        $qp_setup = $this->get_stored_setup();
        $content = '<tr>';
        if (!$email) $content .= '<td><input type="checkbox" name="'.$i.'" value="checked" /></td>';
        $content .= '<td>'.strip_tags($value['sentdate']).'</td>';
        foreach (explode( ',',$options['sort']) as $name) {
            $title='';
            $amount = preg_replace ( '/[^.,0-9]/', '', $value['amount']);                 
            switch ( $name ) {
                case 'reference': $content .= '<td>'.$value['reference'].'</td>';break;
                case 'amount': $content .= '<td>'.$c['b'].$amount.$c['a'].'</td>';break;
                case 'quantity': $content .= '<td>'.$value['quantity'].'</td>';break;
                case 'stock': if ($options['use_stock']) {
                    if ($options['stocklabel'] == $value['stocklabel']) $value['stocklabel']='';
                    $content .= '<td>'.$value['stocklabel'].'</td>';}break;
                case 'options': if ($options['use_options']) {
                    if ($options['optionlabel'] == $value['optionlabel']) $value['option1']='';
                    $content .= '<td>'.$value['option1'].'</td>';}break;
                case 'coupon': if ($options['use_coupon']) {
                    if ($options['couponblurb'] == $value['couponblurb']) $value['couponblurb']='';
                    $content .= '<td>'.$value['couponblurb'].'</td>';}break;
                case 'email': if ($options['use_email']) {
                    if ($options['emailblurb'] == $value['email']) $value['email']='';
                    $content .= '<td>'.$value['email'].'</td>';}break;
                case 'message': if ($options['use_message']) {
                    if ($options['messagelabel'] == $value['yourmessage']) $value['yourmessage']='';
                    $content .= '<td>'.$value['yourmessage'].'</td>';}break;
                case 'datepicker': if ($options['use_datepicker']) {
                    if ($options['datepickerlabel'] == $value['datepickerlabel']) $value['datepickerlabel']='';
                    $content .= '<td>'.$value['datepickerlabel'].'</td>';}break;
            }
        }
        if ($messageoptions['showaddress']) {
            $arr = array('firstname','lastname','email','address1','address2','city','state','zip','country','night_phone_b');
            foreach ($arr as $item) {
                if ($value[$item] == $address[$item]) $value[$item] = '';
                $content .= '<td>'.$value[$item].'</td>';
            }
        }
        $content .= ($value['custom'] == "Paid" ? '<td>Complete</td>' : '<td>Pending</td>');
        $content .= '<td>'.$value['module'].'</td>';
        $content .= '<td>'.$value['tid'].'</td>';
        $content .= '</tr>';
        return $content;	
    }

    public static function admin_notice($message) {
        if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
    }

    public function upgrade_ipn() {
        $qppkey = get_option('qpp_key');
        if ( (!isset( $_REQUEST['paypal_ipn_result']) && !$_POST['custom']) || $qppkey['authorised'])
            return;
        define("DEBUG", 0);
        define("LOG_FILE", "./ipn.log");
        $raw_post_data = file_get_contents('php://input');
        $raw_post_array = explode('&', $raw_post_data);
        $myPost = array();
        foreach ($raw_post_array as $keyval) {
            $keyval = explode ('=', $keyval);
            if (count($keyval) == 2)
                $myPost[$keyval[0]] = urldecode($keyval[1]);
        }
        $req = 'cmd=_notify-validate';
        if(function_exists('get_magic_quotes_gpc')) {
            $get_magic_quotes_exists = true;
        }
        foreach ($myPost as $key => $value) {
            if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode(stripslashes($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        $ch = curl_init("https://www.paypal.com/cgi-bin/webscr");
        if ($ch == FALSE) {
            return FALSE;
        }

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

        if(DEBUG == true) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

        $res = curl_exec($ch);
        if (curl_errno($ch) != 0) // cURL error
        {
            if(DEBUG == true) {	
                error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
            }
            curl_close($ch);
            // exit;
        } else {
            if(DEBUG == true) {
                error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
                error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
            }
            curl_close($ch);
        }

        $tokens = explode("\r\n\r\n", trim($res));
        $res = trim(end($tokens));

        if (strcmp ($res, "VERIFIED") == 0 && $qppkey['key'] == $_POST['custom']) {
            $qppkey['authorised'] = 'true';
            update_option('qpp_key',$qppkey);
            $email = get_option('admin_email');
            $qp_setup = qp_get_stored_setup();
            $email  = bloginfo('adminemail');
            $headers = "From: Etalented Plugins <plugins@etalented.co.uk>\r\n"
    . "MIME-Version: 1.0\r\n"
    . "Content-Type: text/html; charset=\"utf-8\"\r\n";	
            $message = '<html><p>Thank for upgrading. Your authorisation key is:</p><p>'.$qppkey['key'].'</p></html>';
            wp_mail($email,'MultiPay Plugin Authorisation Key',$message,$headers);
        }
        exit();
    }

}

function MP() {
    return MultiPay::instance();
}

MP();