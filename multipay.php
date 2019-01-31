<?php
/*
Plugin Name: MultiPay
Plugin URI: https://wordpress.org/plugins/multipay/
Description: MultiPay is a free eCommerce plugin that allows you to sell anything, simply
Version: 1.4.4
Author: etalented
Author URI: https://etalented.co.uk/
Text-domain: multipay
*/

init();

function init() {

     include_once dirname( __FILE__ ) . '/options.php';
    // Frontend
    if ( !is_admin() || (defined('DOING_AJAX') && DOING_AJAX) ) {

        function qp_shutdown() {
            $error = error_get_last();
        }

        register_shutdown_function('qp_shutdown');

        add_shortcode( 'multipay', 'qp_loop' );
        add_shortcode( 'qpreport', 'qp_report' );

        add_action( 'wp_enqueue_scripts', 'multipay_scripts' );
        
        add_action( 'wp_head', 'qp_head' );
        add_action( 'wp_footer', 'qp_footer_style', 100 );
        add_action( 'init', 'qp_bootstrap' );
        add_action( 'wp_ajax_qp_validate_form', 'qp_validate_form_callback');
        add_action( 'wp_ajax_nopriv_qp_validate_form', 'qp_validate_form_callback');
        add_action( 'wp_ajax_qp_process_payment', 'qp_process_payment');
        add_action( 'wp_ajax_nopriv_qp_process_express_checkout_payment', 'qp_process_express_checkout_payment');

        $qp_end_loop = false;
        $PaymentsAPI = false;
        
    // Admin
    } else {
        add_action( 'admin_menu', 'multipay_admin_menu', 9 );
        add_action( 'admin_enqueue_scripts', 'multipay_admin_scripts' );
        
        include_once dirname( __FILE__ ) . '/settings.php';
        include_once dirname( __FILE__ ) . '/messages.php';
    }
}

function multipay_scripts() {
	global $PaymentsAPI;

	// Load payment module assets
	foreach ( $PaymentsAPI->assets() as $asset ) {
		switch ( $asset['type'] ) {
			case 'css':
				wp_register_style( $asset['name'], $asset['url'] );
			break;
			case 'script':
				wp_register_script( $asset['name'], $asset['url'] );
			break;
		}
	}
    
	wp_register_script( 'multipay', plugins_url( 'multipay.js', __FILE__ ), array( 'jquery', 'jquery-effects-core', 'jquery-ui-datepicker' ), false, true );
    
    wp_register_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
    wp_register_style( 'multipay-style', plugins_url( 'multipay.css', __FILE__ ), array( 'jquery-ui-style' ) );
}

function multipay_admin_scripts() {
    wp_register_script( 'multipay-media', plugins_url( 'media.js', __FILE__ ), array( 'jquery', 'wp-color-picker', 'jquery-ui-sortable', 'jquery-ui-datepicker' ), false, true );
    
    wp_register_style( 'jquery-ui-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
    wp_register_style( 'multipay-admin-style', plugins_url( 'settings.css', __FILE__ ), array( 'jquery-ui-style', 'wp-color-picker' ) );
}

function multipay_admin_menu() {
    add_menu_page( 'MultiPay', 'MultiPay', 'manage_options', 'multipay-messages', null, 'dashicons-cart' );
    add_submenu_page( 'multipay-messages', __( 'Transactions', 'multipay' ),  __( 'Transactions', 'multipay' ), 'manage_options', 'multipay-messages', 'messages_page' );
    add_submenu_page( 'multipay-messages', __( 'Settings', 'multipay' ),  __( 'Settings', 'multipay' ), 'manage_options', 'multipay-settings', 'settings_page' );
}

function settings_page() {
    wp_enqueue_style( 'multipay-admin-style' );
    wp_enqueue_script( 'multipay-media' );
    wp_enqueue_media();
    
    MultiPay_Settings::output();
}

function messages_page() {
    wp_enqueue_style( 'multipay-admin-style' );
    
    MultiPay_Messages::output();
}

function qp_bootstrap() {
	global $PaymentsAPI;
	
	require_once( plugin_dir_path( __FILE__ ) . '/PaymentsAPI/bootstrap.php' );
	/*
		Check if paypal is enabled
	*/
	$paypalapi = qp_get_paypal_api();
	if ($paypalapi['use_paypal'] == 'checked') $PaymentsAPI->load('paypal',$paypalapi);
	
	/*
		Check if stripe is enabled
	*/
	$stripeapi = qp_get_stripe_api();
	if ($stripeapi['use_stripe'] == 'checked') $PaymentsAPI->load('stripe',$stripeapi);
	
	/*
		Check if wordpay is enabled
	*/
	$worldpayapi = qp_get_worldpay_api();
	if ($worldpayapi['use_worldpay'] == 'checked') $PaymentsAPI->load('worldpay',$worldpayapi);
	
	/*
		Check if Amazon is enabled
	*/
	$amazonapi = qp_get_amazon_api();
	if ($amazonapi['use_amazon'] == 'checked') $PaymentsAPI->load('amazon',$amazonapi);
}

function qp_display_scripts() {
	global $qp_shortcode_exists;

	if ($qp_shortcode_exists)
		wp_print_scripts('qp_script');
}

function qp_validate_form_callback($degrade = false) {
	
	global $PaymentsAPI;
	
	if (isset($_POST['form_id'])) {
		$formerrors = array();
		$form = $_POST['form_id'];
		$style = qp_get_stored_style($form);
		$currency = qp_get_stored_curr();
		$current_currency = $currency[$_POST['form_id']];
		$qp = qp_get_stored_options($form);
		$send = qp_get_stored_send($form);
		
		$json = (object) array(
			'success' => false,
			'errors' => array(),
			'display' => $send['errortitle'],
			'blurb' => $send['errorblurb'],
			'error_color' => $style['error-colour']
		);
		if (!qp_verify_form($_POST, $formerrors, $_POST['form_id'])) {
			/* Format Form Errors */
			foreach ($formerrors as $k => $v) {
				if ($k == 'captcha') $k = 'maths';
				if ($k == 'use_stock') $k = 'stock';
				if ($k == 'use_terms') $k = 'termschecked';
				if ($k == 'use_message') $k = 'yourmessage';
				$json->errors[] = (object) array(
					'name' => $k,
					'error' => $v
				);
			}
			
		} else {
			
			$json->success = true;
			
			$v = array();

			$form = $amount = $id = '';
				
			qp_formulate_v($_POST['sc'],$v,$form,$amount,$id);
			
			$qp_key = md5(time() . ' ' . mt_rand());
			
			qp_process_form($v,$form,$_REQUEST['module'], $qp_key);
			
			$PaymentsAPI->collect($form,$amount,$v,$qp_key);
			
			$json->module = $_REQUEST['module'];
			
			$PaymentsAPI->onValidation($_REQUEST['module'],$json);
		}
	} else {
		// error
	}
	echo json_encode($json);
	wp_die();
}

function qp_formulate_v($atts,&$v, &$form = 'default', &$amount = '', &$id = '', &$stock = '', &$labels = '') {
	extract(shortcode_atts(array( 'form' =>'default','amount' => '' , 'id' => '','stock' => '', 'labels' => ''), $atts));
    $qp = qp_get_stored_options($form);
    $address = qp_get_stored_address($form);
    $coupon = qp_get_stored_coupon ($form);
    global $_REQUEST;
	
	// Make sure this form is the form which is being submitted
    
	if (isset($_REQUEST['form_id']) && $_REQUEST['form_id'] == $form) {
		if(isset($_REQUEST["reference"])) {$id = $_REQUEST["reference"];}
		if(isset($_REQUEST["amount"])) {
			$amount = $_REQUEST["amount"];
		}
        if(isset($_REQUEST["item"])) {$qp['stocklabel'] = $_REQUEST["item"];}
		if(isset($_REQUEST["form"])) {$form = $_REQUEST["form"];}
	}
	
    $arr = array('email','firstname','lastname','address1','address2','city','state','zip','country','night_phone_b');
    foreach($arr as $item) $v[$item] = $address[$item];
    $v['quantity'] = 1;
    $v['option1'] = '';
    $v['stock'] = $qp['stocklabel'];
	$v['otheramount'] = $qp['comboboxlabel'];
	
    $v['couponblurb'] = $qp['couponblurb'];
    $v['yourmessage'] = $qp['messagelabel'];
    $v['datepicker'] = $qp['datepickerlabel'];
	
	$v['srt'] = $qp['srt'];
    $v['combine'] = $v['couponapplied'] = $v['couponget'] =$v['maths'] = $v['explodepay'] =  $v['explode'] = $v['recurring'] = $v['termschecked'] = '';
    
    if (!$address['email'] || !$qp['use_address']) {
        $v['email'] = $qp['emailblurb'];
    }
    
    if ($qp['refselector'] != 'refnone' && (strrpos($qp['inputreference'],';') || strrpos($id,';'))) {
        $v['combine'] = 'initial';
    }
    
    if (!$labels) {
        $shortcodeamount = $qp['shortcodeamount'].' ';
	}
    
    if ($id) {
        $v['setref'] = 'checked';
        if (strrpos($id,',') ) {
            $v['reference'] = $id;
            if (!$v['combine']) $v['explode'] = 'checked';
        } else {
            $v['reference'] = $id;
        }
    } else {
        $v['reference'] = $qp['inputreference'];
        $v['setref'] = '';
    }
    
    if ($qp['fixedreference'] && !$id) {
        if (strrpos($qp['inputreference'],',')) {
            $v['reference'] = $qp['inputreference'];
            if (!$v['combine']) $v['explode'] = 'checked';
            $v['setref'] = 'checked';
        } else {
			$v['reference'] = $qp['inputreference'];
			$v['setref'] = 'checked';
        }
    }
    
    if ($amount) {
        $v['setpay'] = 'checked';
        if (strrpos($amount,',')) {
            $v['amount'] = $amount;
            $v['explodepay'] = 'checked';
            $v['fixedamount'] = $amount;
        } else {
            $v['amount'] = $shortcodeamount.$amount;
            $v['fixedamount'] = $amount;
        }
    } else {
        $v['amount'] = $qp['inputamount'];
        $v['setpay'] = '';
    }
    
    if ($qp['fixedamount'] && !$amount) {
        if (strrpos($qp['inputamount'],',')) {
            $v['amount'] = $qp['inputamount'];
            $v['explodepay'] = 'checked';
            $v['setpay'] = 'checked';
            $a = explode(",",$qp['inputamount']);
            $v['fixedamount'] = $a[0];
        } else {
            $v['amount'] = $shortcodeamount.$qp['inputamount'];
            $v['fixedamount'] = $qp['inputamount'];
            $v['setpay'] = 'checked';
        }
    }
	
	$d = qp_sanitize($_POST);

    if (isset($d['action']) || isset($d['qpapply'])) {
		
        if (isset($d['reference'])) $id = $d['reference'];
        if (isset($d['amount'])) $amount = $d['amount'];

		
        // check for combobox option
        if (isset($d['otheramount']) && isset($d['use_other_amount'])) {
			if (strtolower($d['use_other_amount']) == 'true') $d['amount'] = $d['otheramount'];
		} 
        if ($qp['use_options']) {
            if ($qp['optionselector'] == 'optionscheckbox') {
                $checks ='';
                $arr = explode(",",$qp['optionvalues']);
                foreach ($arr as $key) if ($d['option1_' . str_replace(' ','',$key)]) $checks .= $key . ', ';
                $v['option1'] = rtrim( $checks , ', ' );
            } else {
                $v['option1'] = $d['option1'];
            }
        }

        $arr = array(
            'reference',
            'amount',
            'stock',
            'quantity',
            'options',
            'couponblurb',
            'maths',
            'thesum',
            'answer',
            'termschecked',
            'yourmessage',
            'datepicker',
            'email',
            'firstname',
            'lastname',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'country',
            'night_phone_b',
            'combine',
			'srt'
        );
		
        foreach($arr as $item) {
			if (isset($d[$item])) $v[$item] = $d[$item];
		}
    }
    
    if (isset($d['qpapply'])) {
        if ($v['combine']) {
            $arr = explode('&',$v['reference']);
            $v['reference'] = $arr[0];
            $v['amount'] = $arr[1];
        }
        $check = qp_format_amount($currency[$form],$qp,$v['amount']);
        $coupon = qp_get_stored_coupon($form);
        $c = qp_currency($form);
        for ($i=1; $i<=$coupon['couponnumber']; $i++) {
            if ($coupon['expired'.$i]) $v['couponerror'] = $coupon['couponexpired'];
            if ($v['couponblurb'] == $coupon['code'.$i]) {
                if ($coupon['coupontype'.$i] == 'percent'.$i) $check = $check - ($check * $coupon['couponpercent'.$i]/100);
                if ($coupon['coupontype'.$i] == 'fixed'.$i) $check = $check - $coupon['couponfixed'.$i];
                if ($check > 0) {
                    $check = number_format($check, 2,'.','');
                    $v['couponapplied'] = 'checked';
                    $v['setpay'] = 'checked';
                    $v['amount'] = $shortcodeamount.$c['b'].$check.$c['a'];
                    $v['fixedamount'] = $check;
                    $v['explodepay'] = $v['combine'] ='';
                } else {
                   $v['couponblurb'] = $qp['couponblurb'];
                }
            }
        }
        if (!$v['couponapplied'] && !$v['couponerror']) $v['couponerror'] = $coupon['couponerror'];
    }
	
	$amount = $v['amount'];
}

function qp_display_success($form, $tid, $data) {
	$style = qp_get_stored_style($form);
	$send = qp_get_stored_send($form);
    $c = qp_currency($form);
	$post = $_POST['sc']['post'];
    
    $custom = $_REQUEST['qp_key'];
    $arr = explode(",",$qpp_setup['alternative']);
	foreach ($arr as $item) {
		$message = get_option('qp_messages'.$form);
		$count = count($message);
		for($i = 0; $i <= $count; $i++) {
			if ($message[$i]['custom'] == $custom && $message[$i]['reference']) {
				$message[$i]['custom'] = 'Paid';
                $message[$i]['tid'] =  $tid;
				$auto = qp_get_stored_autoresponder($form);
				$send = qp_get_stored_send($form);
				qp_check_coupon($message[$i]['coupon'],$item);
				$values = array(
					'reference' => $message[$i]['reference'],
					'quantity' => $message[$i]['quantity'],
					'amount' => $message[$i]['amount'],
					'stock' => $message[$i]['stock'],
					'option1' => $message[$i]['option1'],
					'email' => $message[$i]['email'],
					'firstname' => $message[$i]['firstname'],
					'lastname' => $message[$i]['lastname'],
                    'tid' => $message[$i]['tid'],
				);
				qp_send_confirmation($message[$i],$form);
			}
		}
		update_option('qp_messages'.$form,$message);
	}
    
	$url = get_permalink($post);

	$display = '';
	if (strlen($send['thanksurl'])) {
		$display .= "<script type='text/javascript'>";
		$display .= "window.location.href = '{$send['thanksurl']}';";
		$display .= "</script>";
	}

	$display .= <<<Form
	<div class="qp-style qp-complete {$form}"><div id="{$style['border']}">
		<form id="frmPayment{$f}" name="frmPayment{$f}" method="post" action="">
			<h2>{$send['confirmationtitle']}</h2>
            <p class="qp-blurb">{$send['confirmationblurb']}</p>
			<p class="qp-blurb">Transaction ID: {$tid}</p>
			<p><a href="{$url}">{$send['confirmationanchor']}</a></p>
		</form>
	</div></div>
Form;
	echo $display;
}

function qp_display_pending($form, $data) {
	$f = $form;
	$style = qp_get_stored_style($f);
	$send = qp_get_stored_send($f);
	$post = $_POST['sc']['post'];
	$url = get_permalink($post);

	$display = <<<Form
	<div class="qp-style qp-complete {$form}"><div id="{$style['border']}">
		<form id="frmPayment{$f}" name="frmPayment{$f}" method="post" action="">
			<h2>{$send['pendingtitle']}</h2>
			<p class="qp-blurb">{$send['pendingblurb']}</p>
			<p><a href="{$url}?token={$token}&PayerID={$payerid}">{$send['pendinganchor']}</a></p>
		</form>
	</div></div>
Form;
	echo $display;
}

function qp_display_failure($form, $data) {

	$f = $form;
	$style = qp_get_stored_style($f);
	$send = qp_get_stored_send($f);
	$post = $_POST['sc']['post'];
	$url = get_permalink($post);
	
	$display = '';
	if (strlen($send['cancelurl'])) {
		$display .= "<script type='text/javascript'>";
		$display .= "window.location.href = '{$send['cancelurl']}';";
		$display .= "</script>";
	}
	$display .= <<<Form
	<div class="qp-style qp-complete {$form}"><div id="{$style['border']}">
		<form id="frmPayment{$f}" name="frmPayment{$f}" method="post" action="">
		<h2>{$send['failuretitle']}</h2>
Form;
	if ($result && isset($result['L_LONGMESSAGE0'])) {
		$display .= '<p class="qp-blurb">'.$result['L_LONGMESSAGE0'].'</p><br />';
	}
	
	$display .= <<<Form
			<p class="qp-blurb">{$send['failureblurb']}</p>
			<p><a href="{$url}">{$send['failureanchor']}</a></p>
		</form>
	</div></div>
Form;
	echo $display;
}

function qp_loop($atts) {
	
	/*
		Let the rest of wordpress know that there is a shortcode that we're looking for!
	*/
	global $qp_shortcode_exists, $qp_end_loop, $PaymentsAPI;
	
	/*
		Loop through all of the assets we're using
	*/
		
    wp_enqueue_script( 'multipay' );
	wp_enqueue_style( 'multipay-style' );
	
	foreach ($PaymentsAPI->assets() as $asset) {
		switch ($asset['type']) {
			case 'css':
				wp_enqueue_style($asset['name']);
			break;
			case 'script':
				wp_enqueue_script($asset['name']);
			break;
		}
		
	}
	if ($qp_end_loop) return;
	
	
	$qp_shortcode_exists = true;
	
	$v = array();
    $form = 'default';
	$amount = $id = '';
	qp_formulate_v($atts,$v,$form,$amount,$id);

    ob_start();
	if ($PaymentsAPI->processing) {
		
		$qp_end_loop = true;
		
		/*
			If we're here, a payment has been captured by a module and requires further processing
		*/
		
		$messages = get_option('qp_messages'.$_REQUEST['form']);
		$message = false;
		
		for ($i = 0; $i < count($messages); $i++) {
			if ($messages[$i]['custom'] == $_REQUEST['qp_key']) {
				$message = $messages[$i];
				break;
			}
		}

		// Trigger processing
		$PaymentsAPI->onProcessing($message);
		
		if ($PaymentsAPI->complete) {
			
			switch ($PaymentsAPI->status) {
				
				case 'success':
					qp_display_success($form, $PaymentsAPI->tid, $PaymentsAPI->payment_details);
				break;
				
				case 'pending':
					qp_display_pending($form, $PaymentsAPI->payment_details);
				break;
				
				case 'failure':
					qp_display_failure($form, $PaymentsAPI->payment_details);
				break;
				
			}
			
		}
		
	} else {
		$v = array();
		qp_formulate_v($atts,$v,$form,$amount,$id);
		if (isset($_POST['qpsubmit'.$form]) || isset($_POST['qpsubmit'.$form.'_x'])) {
			$formerrors = array();
			if (!qp_verify_form($v,$formerrors,$form)) {
				qp_display_form($v,$formerrors,$form,$atts);
			} else {
				// do nothing
			}
		} else {
			$digit1 = mt_rand(1,10);
			$digit2 = mt_rand(1,10);
			if( $digit2 >= $digit1 ) {
				$v['thesum'] = "$digit1 + $digit2";
				$v['answer'] = $digit1 + $digit2;
			} else {
				$v['thesum'] = "$digit1 - $digit2";
				$v['answer'] = $digit1 - $digit2;
			}
			qp_display_form($v,null,$form,$atts);
		}
	}
    $output_string=ob_get_contents();
    ob_end_clean();
    return $output_string;
}

function qp_display_form($values, $errors, $form, $attr = '') {
	if (!$attr) $attr = array();
    global $_GET, $PaymentsAPI;
    if(isset($_GET["form"]) && !$form) {
        $id = $_GET["form"];
    }
    if(isset($_GET["reference"])) {
        $values['reference'] = $_GET["reference"];
        $values['setref'] = true;
    }
     if(isset($_GET["amount"])) {
         $values['amount'] = $_GET["amount"];
         $values['setpay'] = true;
    }
    if(isset($_GET["coupon"])) {
        $values['couponblurb'] = $_GET["coupon"];$values['couponget']=$coupon['couponget'];
    }
    
    $qp = qp_get_stored_options($form);
    $coupon = qp_get_stored_coupon($form);
    $send = qp_get_stored_send($form);
    $style = qp_get_stored_style($form);
    $currency = qp_get_stored_curr();
    $address = qp_get_stored_address($form);
	
    $curr = ($currency[$form] == '' ? 'USD' : $currency[$form]);
    $check = (double) preg_replace ( '/[^.0-9]/', '', $values['amount']);
    $decimal = array('HKD','JPY','MYR','TWD');$d='2';
    foreach ($decimal as $item) if ($item == $currency[$form]) $d ='0';
    $values['producttotal'] = $values['quantity'] * $check;

    if ($qp['use_slider']) $values['amount'] = $qp['initial'];
    $c = qp_currency ($form);
    $p = (double) qp_postage($qp,$values['producttotal'],'1');
    $h = (double) qp_handling($qp,$values['producttotal'],'1');
    $t = $form;
    $values['producttotal'] = $values['producttotal'] + $p + $h;
    $values['producttotal'] = number_format($values['producttotal'], $d,'.','');
    
	$content = "<script type='text/javascript'>ajaxurl = '".admin_url('admin-ajax.php')."';</script>";
	
	if (!empty($qp['title'])) $qp['title'] = '<h2 id="qp_reload" class="qp-header">' . $qp['title'] . '</h2>';
	if (!empty($qp['blurb'])) $qp['blurb'] = '<p class="qp-blurb">' . $qp['blurb'] . '</p>';
    
    $content .= '<div class="qp-style '.$form.'"><div id="'.$style['border'].'">';
    $content .= '<form id="frmPayment'.$t.'" name="frmPayment'.$t.'" method="post" action="">';
	
    if (!empty($errors) && count($errors) > 0) {
        $content .= "<script type='text/javascript' language='javascript'>document.querySelector('#qp_reload').scrollIntoView();</script>";
		"<h2 class='qp-header' id='qp_reload' style='color:".$style['error-colour'].";'>" . $send['errortitle'] . "</h2>        
        <p class='qp-blurb' style='color:".$style['error-colour'].";'>" . $send['errorblurb'] . "</p>";
        $arr = array(
            'amount',
            'reference',
            'quantity',
            'use_stock',
            'answer',
            'email',
            'firstname',
            'lastname',
            'address1',
            'address2',
            'city',
            'state',
            'zip',
            'country',
            'night_phone_b'
        );
        foreach ($arr as $item) if ($errors[$item] == 'error') 
            $errors[$item] = ' style="border:1px solid '.$style['error-colour'].';" ';
        if ($errors['use_terms']) $errors['use_terms'] = 'border:1px solid '.$style['error-colour'].';';
        if ($errors['captcha']) $errors['captcha'] = 'border:1px solid '.$style['error-colour'].';';
        if ($errors['quantity']) $errors['quantity'] = 'border:1px solid '.$style['error-colour'].';';
    } else {
        $content .= $qp['title'];
        $content .=  $qp['blurb'];
    }
	
	$attr['post'] = get_the_ID();
	if (count($attr)) {
		foreach ($attr as $k => $v) {
			$content .= "<input type='hidden' name='sc[".$k."]' value='".$v."' />";
		}
	}
    $content .= '<input type="hidden" name="form_id" value="'.$form.'" />';

	foreach (explode( ',',$qp['sort']) as $name) {
        switch ( $name ) {
            case 'reference':
            if (!$values['setref']) {
                $required = (!$errors['reference'] ? ' class="required" ' : '');
                $content .= '<p>
                <input type="text" '.$required.$errors['reference'].' id="reference" name="reference" value="' . $values['reference'] . '" rel="'. $values['reference'] . '" onfocus="qpclear(this, \'' . $values['reference'] . '\')" onblur="qprecall(this, \'' . $values['reference'] . '\')"/></p>';
            } else {
                if ($values['combine']) {
                    $checked = 'checked';
                    $ret = array_map ('qp_explode_by_semicolon', explode (',', $values['reference']));
                    if ($qp['refselector'] == 'refdropdown') {
                        $content .= qp_dropdown($ret,$values,'reference',true);
                    } else {
                        $content .= '<p class="payment" >'.$qp['reference'].'</p>';
                        $content .= '<input type="hidden" name="combined_radio_amount" value="0.00" />';
                        foreach ($ret as $item) {
                            if (strrpos($values['reference'],$item[0]) !==false && $values['combine'] != 'initial') 
                                $checked = 'checked';
                            $content .=  '<p><label><input type="radio" style="margin:0; padding: 0; border:none;width:auto;" name="reference" value="' .  $item[0].'&'.$item[1] . '" ' . $checked . '> ' .  $item[0].' '.$item[1] . '</label></p>';$checked='';
                        }
                        
                    }
                    $content .= '<input type="hidden" name="combine" value="checked" />';
                } elseif ($values['explode']  && $qp['refselector'] != 'ignore') {
                    $checked = 'checked';
                    $ref = explode(",",$values['reference']);
                    if ($qp['refselector'] == 'refdropdown') {
                        $content .= qp_dropdown($ref,$values,'reference');
                    } elseif ($qp['refselector'] == 'refradio') {
                        $content .= '<p class="payment" >'.$qp['reference'].'</p>';
                        foreach ($ref as $item)
                            $content .=  '<label>
                            <p><input type="radio" style="margin:0; padding: 0; border:none;width:auto;" name="reference" value="' .  $item . '" ' . $checked . '> ' .  $item . '</label></p>';
                        $checked='';
                    } else {
                        $content .= '<p class="payment" >'.$qp['reference'].'</p><p>';
                        foreach ($ref as $item)
                            $content .=  '<label>
                            <input type="radio" style="margin:0; padding: 0; border:none;width:auto;" name="reference" value="' .  $item . '" ' . $checked . '> ' .  $item . '</label>';
                        $content .=  '</p>';
                        $checked='';
                    }    
                } else {
                    $content .= '<p class="input" >'.$values['reference'].'</p><input type="hidden" name="reference" value="' . $values['reference'] . '" /><input type="hidden" name="setref" value="' . $values['setref'] . '" />';
                }
            }
            break;
            
            case 'stock':
            if ($qp['use_stock']) {
                $requiredstock = (!$errors['use_stock'] && $qp['ruse_stock'] ? ' class="required" ' : '');
                if ($qp['fixedstock'] || isset($_REQUEST["item"])) {
                    $content .= '<p class="input" >'.$values['stock'].'</p>';
                } else {
                    $content .= '<p><input type="text" '.$requiredstock.$errors['use_stock'].' id="stock" name="stock" value="' . $values['stock'] . '" onfocus="qpclear(this, \'' . $values['stock'] . '\')" onblur="qprecall(this, \'' . $values['stock'] . '\')"/>
                </p>';
                }
            }
            break;			
            
            case 'quantity':
            if ($qp['use_quantity']){
                $content .= '<p>
                <span class="input">'.$qp['quantitylabel'].'</span>
                <input type="text" style=" '.$errors['quantity'].'width:3em;margin-left:5px" id="qpquantity'.$t.'" label="quantity" name="quantity" value="' . $values['quantity'] . '" onfocus="qpclear(this, \'' . $values['quantity'] . '\')" onblur="qprecall(this, \'' . $values['quantity'] . '\')" />';
                if ($qp['quantitymax']) $content .= '&nbsp;'.$qp['quantitymaxblurb'];
                $content .= '</p>';
            } else { $content .='<input type ="hidden" id="qpquantity'.$t.'" name="quantity" value="1">';}
            break;
            
            case 'amount':
            if ($qp['use_coupon'] && $values['couponapplied']) 
                $content .= '<p>'.$qp['couponref'].'</p>';
            if ($qp['use_slider'] && !$values['combine']) {
                $content .= '<p style="margin-bottom:0.7em;">'.$qp['sliderlabel'].'</p>
                <input type="range" id="qpamount'.$t.'" name="amount" min="'.$qp['min'].'" max="'.$qp['max'].'" value="'.$values['amount'].'" step="'.$qp['step'].'" data-rangeslider>
                <div class="qp-slideroutput">
                <span class="qp-sliderleft">'.$qp['min'].'</span>
                <span class="qp-slidercenter"><output></output></span>
                <span class="qp-sliderright">'.$qp['max'].'</span>
                </div><div style="clear: both;"></div>';
            } else {
                if (!$values['combine']) {
                    if (!$values['setpay']){
                        $required = (!$errors['amount'] ? ' class="required" ' : '');
                        $content .= '<p>
                        <input type="text" rel="'.$values['amount'].'" '.$required.$errors['amount'].' id="qpamount'.$t.'" label="Amount" name="amount" value="' . $values['amount'] . '" onfocus="qpclear(this, \'' . $values['amount'] . '\')" onblur="qprecall(this, \'' . $values['amount'] . '\' )" />
                        </p>';
                    } else {
                        if ($values['explodepay']) {
                            $ref = explode(",",$values['amount']);
                            if($qp['amtselector'] == 'amtdropdown') {
                                // add combobox script
                                if ($qp['combobox']) {
									array_push($ref,$qp['comboboxword']);
                                    $content .= qp_dropdown($ref,$values,'amount');
                                    $content .= '<div id="otheramount"><input type="text" label="'.$qp['comboboxlabel'].'" onfocus="qpclear(this, \'' . $qp['comboboxlabel'] . '\')" onblur="qprecall(this, \'' . $qp['comboboxlabel'] . '\' )" value="'.$values['otheramount'].'" name="otheramount" style="display: none;" /><input type="hidden" name="use_other_amount" value="false" /></div>';
                                } else {
									$content .= qp_dropdown($ref,$values,'amount');
								}
                            } else {
                                $checked = 'checked';
                                $bron = ($qp['inline_amount'] ? '' : '<p>');
                                $broff = ($qp['inline_amount'] ? '&nbsp;' : '</p>');
                                $mar = ($qp['combobox'] ? '12px 0' : '0');
                                $content .= '<p class="payment" >'.$qp['shortcodeamount'].'</p>';
                                foreach ($ref as $item) {
                                    $content .=  $bron.'<label><input type="radio" id="qptiddles" style="margin:'.$mar.'; padding: 0; border:none;width:auto;" name="amount" value="' .  $item . '" ' . $checked . '> ' .  $item . '</label>'.$broff;
									$checked='';
                                }
                                if ($qp['combobox']) {
                                    $content .=  '<input type="radio" id="qptiddles" style="margin:0; padding: 0; border:none;width:auto;" name="amount" value="otheramount" ' . $checked . '> '.$qp['comboboxword'].'<input type="text" style="width:80%;" value ="'.$values['otheramount'].'" name="otheramount" onfocus="qpclear(this, \'' . $qp['comboboxlabel'] . '\')" onblur="qprecall(this, \'' . $qp['comboboxlabel'] . '\' )" /><input type="hidden" name="use_other_amount" value="false" />';
                                }
                            
                            }
                        }
                        else $content .= '<p class="input" >' . $values['amount'] . '</p><input type="hidden" id="qpamount'.$t.'" name="amount" value="'.$values['fixedamount'].'" />';
                    }
                    $content .= '<input type="hidden" name="radio_amount" value="0.00" />';
                }
            }
            break;
            
            case 'options':
            if ($qp['use_options']){
                $content .= '<p class="input">' . $qp['optionlabel'] . '</p><p>';
                $arr = explode(",",$qp['optionvalues']);
                $br = ($qp['inline_options'] ? '&nbsp;' : '<br>');
                if ($qp['optionselector'] == 'optionsdropdown') {
                    $content .= qp_dropdown($arr,$values,'option1');
                } elseif ($qp['optionselector'] == 'optionscheckbox') {
                    $content .= qp_checkbox($arr,$values,'option1',$br);
                } else {
                    foreach ($arr as $item) {
                        $checked = '';
                        if ($values['option1'] == $item) $checked = 'checked';
                        if ($item === reset($arr)) $content .= '<label><input type="radio" style="margin:0; padding: 0; border: none" name="option1" value="' .  $item . '" id="' .  $item . '" checked> ' .  $item . '</label>'.$br;
                        else $content .=  '<label><input type="radio" style="margin:0; padding: 0; border: none" name="option1" value="' .  $item . '" id="' .  $item . '" ' . $checked . '> ' .  $item . '</label>'.$br;
                    }
                    $content .= '</p>';
                }
            }
            break;
            
            case 'postage':
            if ($qp['use_postage']) {
				$content .= '<p class="input" >'.$qp['postageblurb'].'</p>
				<input type="hidden" name="postage_type" value="'.((htmlentities($qp['postagetype']) == 'postagepercent')? 'percent':'fixed').'" />
				<input type="hidden" name="postage" value="'.htmlentities($qp[$qp['postagetype']]).'" />';
			}
            break;
            
            case 'processing':
            if ($qp['use_process']) {
				$content .= '<p class="input" >'.$qp['processblurb'].'</p>
				<input type="hidden" name="processing_type" value="'.((htmlentities($qp['processtype']) == 'processpercent')? 'percent':'fixed').'" /><input type="hidden" name="processing" value="'.htmlentities($qp[$qp['processtype']]).'" />';
			}
            break;
            
            case 'captcha':
            if ($qp['captcha']) {
                $required = (!$errors['captcha'] ? ' class="required" ' : '');
                if (!empty($qp['mathscaption'])) $content .= '<p class="input">' . $qp['mathscaption'] . '</p>';
                $content .= '<p>' . strip_tags($values['thesum']) . ' = <input type="text" '.$required.' style="width:3em;font-size:100%;'.$errors['captcha'].'" label="Sum" name="maths"  value="' . $values['maths'] . '"></p> 
                <input type="hidden" name="answer" value="' . strip_tags($values['answer']) . '" />
                <input type="hidden" name="thesum" value="' . strip_tags($values['thesum']) . '" />';
            }
            break;
            
            case 'coupon':
            $content .= '<input type="hidden" name="couponapplied" value="'.$values['couponapplied'].'" />';
            if ($qp['use_coupon'] && $values['couponapplied']) 
                $content .= '<input type="hidden" name="couponblurb" value="'.$values['couponblurb'].'" />';
            if ($qp['use_coupon'] && !$values['couponapplied']){
                if ($values['couponerror']) $content .= '<p style="color:'.$style['error-colour'].';">'.$values['couponerror'].'</p>';
                $content .= '<p>'.$values['couponget'].'</p>';
                $content .= '<p><input type="text" id="coupon" name="couponblurb" value="' . $values['couponblurb'] . '" rel="' . $values['couponblurb'] . '" onfocus="qpclear(this, \'' . $values['couponblurb'] . '\')" onblur="qprecall(this, \'' . $values['couponblurb'] . '\')"/></p>
                <p class="submit">
                <input type="submit" value="'.$qp['couponbutton'].'" id="couponsubmit" name="qpapply" />
                </p>';
            }
            break;
            
            case 'terms':
            if ($qp['use_terms']) {
                if ($qp['termspage']) $target = ' target="blank" ';
                $required = (!$errors['use_terms'] ? 'border:'.$style['required-border'].';' : $errors['use_terms']);
                $color = ($errors['use_terms'] ? ' style="color:'.$style['error-colour'].';" ' : '');
                $content .= '<p class="input" '.$errors['use_terms'].'>
                <input type="checkbox" style="margin:0; padding: 0;width:auto;'.$required.'" name="termschecked" value="checked" ' . $values['termschecked'] . '>
                &nbsp;
                <a href="'.$qp['termsurl'].'"'.$target.$color.'>'.$qp['termsblurb'].'</a></p>';
            }
            break;
            
            case 'additionalinfo':
            if ($qp['use_blurb']) $content .= '<p>' . $qp['extrablurb'] . '</p>';
            break;
            
            case 'address':
            if ($qp['use_address']) {
                $content .= '<p>' . $qp['addressblurb'] . '</p>';
                $arr = array('firstname','lastname','email','address1','address2','city','state','zip','country','night_phone_b');
                foreach($arr as $item)
                    if ($address[$item]) {
                    $required = ($address['r'.$item] && !$errors[$item] ? ' class="required" ' : '');
                    $content .='<p><input type="text" id="'.$item.'" name="'.$item.'" '.$required.$errors[$item].' value="'.$values[$item].'" rel="' . $values[$item] . '" onfocus="qpclear(this, \'' . $values[$item] . '\')" onblur="qprecall(this, \'' . $values[$item] . '\')"/></p>';
                    }
            }
            break;
            
            case 'totals':
            if ($qp['use_totals']) {
                $content .= '<p style="font-weight:bold;">'.$qp['totalsblurb'].' '.$c['b'].'<input type="text" id="qptotal" name="total" value="0.00" readonly="readonly" />'.$c['a'].'</p>';
            } else {
             $content .= '<input type="hidden" id="qptotal" name="total"  />';   
            }
            break;
            
            case 'email':
            if ($qp['use_email']) {
                $requiredemail = (!$errors['use_email'] && $qp['ruse_email'] ? ' class="required" ' : '');
                $content .= '<input type="text" '.$requiredemail.$errors['use_stock'].' id="email" name="email" value="' . $values['email'] . '" rel="' . $values['email'] . '" onfocus="qpclear(this, \'' . $values['email'] . '\')" onblur="qprecall(this, \'' . $values['email'] . '\')"/>';
            }
            break;
                
            case 'message':
            if ($qp['use_message']) {
                $requiredmessage = (!$errors['yourmessage'] && $qp['ruse_message'] ? ' class="required" ' : '');
                $content .= '<textarea rows="4" name="yourmessage" '.$requiredmessage.$errors['use_message'].' onblur="if (this.value == \'\') {this.value = \''.$values['yourmessage'].'\';}" onfocus="if (this.value == \''.$values['yourmessage'].'\') {this.value = \'\';}" />' . stripslashes($values['yourmessage']) . '</textarea>';
            }
            break;
            case 'datepicker':
            if ($qp['use_datepicker']) {
                $requiredmessage = (!$errors['yourdatepicker'] && $qp['ruse_datepicker'] ? ' class="required" ' : '');
                $content .= '<input type="text" id="qpdate" name="datepicker" value="' . $values['datepicker'] . '" onfocus="qpclear(this, \'' . $values['datepicker'] . '\')" onblur="qprecall(this, \'' . $values['datepicker'] . '\')"/><script type="text/javascript">jQuery(document).ready(function() {jQuery(\'\#qpdate\').datepicker({dateFormat : \'dd M yy\'});});</script>';
            }
            break;
        }
    }
	
	$buttons = $PaymentsAPI->getButtons();
	foreach ($buttons as $module => $button) {
		$content .= '<p class="submit '.$module.'-payment payment-submit"><input type="submit" value="'.$button.'" id="submit" name="use_'.$module.'_'.$form.'" /></p>';
	}
	
    if ($qp['use_reset']) $content .= '<p><input type="reset" value="'.$qp['resetcaption'] . '" /></p>';
    $content .= '</form>';
	$content .= '<div class="qp-processing-form">'.$send['waiting'].'</div>';
	
	$texts = '
		<div class="qp-loading">'.$send['waiting'].'</div>
		<div class="qp-validating-form">'.$send['validating'].'</div>';
	$modal =	"<div class='qp_payment_modal'>";
	$modal .= 	"	<div class='qp_payment_modal_content'>
						<a href='javascript:void(0);'>x</a>
						<div class='qp_payment_modal_loading'></div>
						<div class='qp_payment_modal_text'>{$texts}</div>
						<div class='qp_payment_modal_button'><input type='button' value='PROCEED TO PAYMENT' /></div>
					</div>";
	$modal .=	"	<div class='qp_payment_modal_bg'></div>";
	$modal .= 	"</div>";
	
	$content .= $modal;
    if ($qp['use_totals'] || $qp['use_slider'] || (isset($qp['combobox']) && $qp['combobox'] == 'checked')) 
        $content .='<script type="text/javascript">jQuery(document).ready(function() { jQuery("#frmPayment'.$t.'").qp(); });</script>';

    $content .= '<script>jQuery("select option:selected").click(); //force calculation by clicking on default values</script>
    <div style="clear:both;"></div></div></div>'."\r\t";
	if (!$qp_modal_output) {
	}
	echo $content;
}

function qp_dropdown($arr,$values,$name,$combine = false) {
    $content='';
    if ($blurb) $content = '<p class="payment" >Reference</p>';
    $content .= '<select name="'.$name.'">';
    if(!$combine) {
        foreach ($arr as $item) {
            $selected = '';
            if ($values[$name] == $item) $selected = 'selected';
            $content .= '<option value="' .  $item . '" ' . $selected .'>' .  $item . '</option>'."\r\t";
        }
    } else {
        foreach ($arr as $item) {
            $selected = (strrpos($values['reference'],$item[0]) !==false && $values['combine'] != 'initial' ? 'selected' : '');
            $content .=  '<option value="' .  $item[0].'&'.$item[1] . '" ' . $selected . '> ' .  $item[0].' '.$item[1] . '</option>';$selected='';
        }
    }
    $content .= '</select>'."\r\t";
    return $content;
}

function qp_checkbox($arr,$values,$name,$br) {
    $content .= '<p class="input">';
    foreach ($arr as $item) {
        $checked = '';
        if ($values[$name.'_'. str_replace(' ','',$item)] == $item) $checked = 'checked';
        $content .= '<label><input type="checkbox" style="margin:0; padding: 0; border: none" name="'.$name.'_' . str_replace(' ','',$item) . '" value="' .  $item . '" ' . $checked . '> ' .  $item . '</label>'.$br;
        }
    $content .= '</p>';
    return $content;
}

function qp_explode_by_semicolon ($_) {return explode (';', $_);}

function qp_handling ($qp,$check,$quantity){
    if ($qp['use_process'] && $qp['processtype'] == 'processpercent') {
        $percent = preg_replace ( '/[^.,0-9]/', '', $qp['processpercent']) / 100;
        $handling = $check * $quantity * $percent;}
    if ($qp['use_process'] && $qp['processtype'] == 'processfixed') {
        $handling = preg_replace ( '/[^.,0-9]/', '', $qp['processfixed']);}
    else $handling = '';
    return $handling;
}

function qp_postage($qp,$check,$quantity){
    $packing='';
    if ($qp['use_postage'] && $qp['postagetype'] == 'postagepercent') {
        $percent = preg_replace ( '/[^.,0-9]/', '', $qp['postagepercent']) / 100;
        $packing = $check * $quantity * $percent;}
    if ($qp['use_postage'] && $qp['postagetype'] == 'postagefixed') {
        $packing = preg_replace ( '/[^.,0-9]/', '', $qp['postagefixed']);}
    else $packing='';
    return $packing;
}

function qp_format_amount($currency,$qp,$amount) {
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

function qp_verify_form(&$v,&$errors,$form) {
    $qp = qp_get_stored_options($form);
    $address = qp_get_stored_address($form);
    $check = preg_replace ( '/[^.,0-9]/', '', $v['amount']);
    $arr = array('amount','reference','quantity','stock','email','yourmessage');
    
    //die(json_encode(compact('qp', 'v')));
    
    foreach ($arr as $item) $v[$item] = filter_var($v[$item], FILTER_SANITIZE_STRING);
	
	if ($qp['use_quantity']) {
		$max = preg_replace ( '/[^0-9]/', '', $qp['quantitymaxblurb']);
		if (is_numeric($v['quantity']) && $v['quantity'] >= 1) {
			if ($qp['quantitymax']) {
				if ($max < $v['quantity']) $errors['quantity'] = 'error';
			}
		} else {
			// is not a number or is 0
			$errors['quantity'] = 'error';
		}
	}

	if (!$v['setpay']) {
		if ((($v['amount'] == $qp['inputamount']) && ($qp['fixedamount'] != 'checked')) || (empty($v['amount']))) {
			$errors['amount'] = 'error';
		}
	}
	if ($qp['allow_amount'] || $v['combine']) $errors['amount'] = '';
		
    if (!$v['setref']) if ($v['reference'] == $qp['inputreference'] || empty($v['reference'])) 
        $errors['reference'] = 'error';
	
    if($qp['captcha'] == 'checked') {
        $v['maths'] = strip_tags($v['maths']); 
        if($v['maths'] <> $v['answer']) $errors['captcha'] = 'error';
        if(empty($v['maths'])) $errors['captcha'] = 'error'; 
    }
    
    if($qp['use_terms'] && !$v['termschecked']) $errors['use_terms'] = 'error';
    
    if($qp['use_address']) {
        $arr = array('firstname','lastname', 'email','address1','address2','city','state','zip','country','night_phone_b');
        foreach ($arr as $item) {
            $v[$item] = filter_var($v[$item], FILTER_SANITIZE_STRING);
            if ($address['r'.$item] && ($v[$item] == $address[$item] || empty($v[$item]))) $errors[$item] = 'error';
        }
    }
    
    if (!$qp['fixedstock'] && $qp['use_stock'] && $qp['ruse_stock'] && ($v['stock'] == $qp['stocklabel'] || empty($v['stock'])))
        $errors['use_stock'] = 'error';
    
    if ($qp['use_message'] && $qp['ruse_message'] && ($v['yourmessage'] == $qp['messagelabel'] || empty($v['yourmessage'])))
        $errors['use_message'] = 'error';
        
    if ($qp['use_email'] && $qp['ruse_email'] && ($v['email'] == $qp['emailblurb'] || empty($v['email'])))
        $errors['email'] = 'error';
        
    if ($qp['ruse_options']) {
        $hasOptions = false;
        $keys = array_keys($v);
        foreach ($keys as $k) {
            if (strpos($k, 'option1') === 0) {
                $hasOptions = true;
                break;
            }
        }
        
        if (!$hasOptions) {
            $optionValues = explode(',', $qp['optionvalues']);
            foreach ($optionValues as $item) {
                $errors['option1_'.str_replace(' ','',$item)] = 'error';
            }
            $errors['option1'] = 'error';
        }
    }
        
    $errors = array_filter($errors);
    return (count($errors) == 0);
}

function qp_process_form($values,$form,$module, $qp_key) {
	
    $currency = qp_get_stored_curr();
    $qp = qp_get_stored_options($form);
    $send = qp_get_stored_send($form);
    $address = qp_get_stored_address($form);
	
	if ($_REQUEST['combine'] == 'checked') {
		$arr = explode('&',$values['reference']);
		$values['reference'] = $arr[0];
		$values['amount'] = $arr[1];
	}
    
    $qp_messages = get_option('qp_messages'.$form);
   	if(!is_array($qp_messages)) $qp_messages = array();

    $amounttopay = qp_total_amount($currency[$form],$qp,$values);
    
    if ($qp['stock'] == $values['stock'] && !$qp['fixedstock']) $values['stock'] ='';
    $arr = array(
        'firstname',
        'lastname',
        'address1',
        'address2',
        'city',
        'state',
        'zip',
        'country',
        'night_phone_b'
    );
    foreach ($arr as $item) {
        if ($address[$item] == $values[$item])
            $values[$item] = '';
    }

    $values['pagetitle'] = get_the_title();
    $values['ip'] = $_SERVER['REMOTE_ADDR'];
    $values['url'] = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
	$values['price'] =  qp_format_amount($currency[$form],$qp,$values['amount']);
	
	$val = $qp[$qp['processtype']];
	if ($qp['processtype'] == 'processpercent') {
		$val = ($values['price'] * $values['quantity'] * (preg_replace( '/[^.,0-9]/', '', $val) / 100));
	}
	if ($qp['use_process'] == '') $val = 0;
	$values['processing'] = qp_format_amount($currency[$form],$qp,$val);
	
	$val = $qp[$qp['postagetype']];
	if ($qp['postagetype'] == 'postagepercent') {
		$val = ($values['price'] * $values['quantity'] * (preg_replace( '/[^.,0-9]/', '', $val) / 100));
	}
	if ($qp['use_postage'] == '') $val = 0;
	$values['shipping'] = qp_format_amount($currency[$form],$qp,$val);
	
    $values['amount'] = $amounttopay;
    $values['custom'] = $qp_key;
	$values['module'] = $module;
    $values['sentdate'] = date_i18n('d M Y');
    
    $qp_messages[] = $values;
    
    update_option('qp_messages'.$form,$qp_messages);

    if (isset($send['mailchimpregion']) && isset($send['mailchimpuser']) && $send['mailchimpregion'] && $send['mailchimpuser'] && $values['email'])
        $content .= qp_mailchimp($values,$send);

	return $content;
}

function qp_current_page_url() {
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

function qp_currency($form) {
    $currency = qp_get_stored_curr();
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

function qp_sanitize($input) {
    if (is_array($input)) foreach($input as $var=>$val) $output[$var] = filter_var($val, FILTER_SANITIZE_STRING);
    return $output;
    }

function register_qp_widget() {register_widget( 'qp_Widget' );}

add_action( 'widgets_init', 'register_qp_widget' );

class qp_widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'qp_widget', // Base ID
            'MultiPay', // Name
            array( 'description' => __( 'MultiPay', 'Add payment form to your sidebar' ), ) // Args
        );
    }
    public function widget( $args, $instance ) {
        extract($args, EXTR_SKIP);
        $id=$instance['id'];
        $amount=$instance['amount'];
        $form=$instance['form'];
        echo qp_loop($instance);
    }
    public function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['id'] = $new_instance['id'];
        $instance['amount'] = $new_instance['amount'];
        $instance['form'] = $new_instance['form'];
        return $instance;
    }
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'amount' => '' , 'id' => '','form' => '' ) );
        $id = $instance['id'];
        $amount = $instance['amount'];
        $form=$instance['form'];
        $qp_setup = qp_get_stored_setup();
        ?>
        <h3>Select Form:</h3>
        <select class="widefat" name="<?php echo $this->get_field_name('form'); ?>">
        <?php
        $arr = explode(",",$qp_setup['alternative']);
        foreach ($arr as $item) {
            if ($item == '') {$showname = 'default'; $item='';} else $showname = $item;
            if ($showname == $form || $form == '') $selected = 'selected'; else $selected = '';
            ?><option value="<?php echo $item; ?>" id="<?php echo $this->get_field_id('form'); ?>" <?php echo $selected; ?>><?php echo $showname; ?></option><?php } ?>
        </select>
        <h3>Presets:</h3>
        <p><label for="<?php echo $this->get_field_id('id'); ?>">Payment Reference: <input class="widefat" id="<?php echo $this->get_field_id('id'); ?>" name="<?php echo $this->get_field_name('id'); ?>" type="text" value="<?php echo attribute_escape($form); ?>" /></label></p>
        <p><label for="<?php echo $this->get_field_id('amount'); ?>">Amount: <input class="widefat" id="<?php echo $this->get_field_id('amount'); ?>" name="<?php echo $this->get_field_name('amount'); ?>" type="text" value="<?php echo attribute_escape($amount); ?>" /></label></p>
        <p>Leave blank to use the default settings.</p>
        <p>To configure the payment form use the <a href="'.get_admin_url().'options-general.php?page=multipay/multipay.php">Settings</a> page</p>
        <?php
    }
}

function qp_generate_css() {
    $code=$corners=$input=$background=$paragraph=$submit='';
    $style = qp_get_stored_style();
    
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

function qp_head() {
	global $PaymentsAPI;
	$PaymentsAPI->onHead();
}

function qp_footer_style() {
    $data = '<style type="text/css" media="screen">'."\r\n".qp_generate_css()."\r\n".'</style>';
    echo $data;
}

function qp_report($atts) {
    extract(shortcode_atts(array( 'form' =>''), $atts));
    return qp_messagetable($form,'');
}

function qp_messagetable ($form,$email) {
    $qp_setup = qp_get_stored_setup();
    $options = qp_get_stored_options ($form);
    $message = get_option('qp_messages'.$form);
    $coupon = qp_get_stored_coupon($form);
    $messageoptions = qp_get_stored_msg();
    $address = qp_get_stored_address($form);
    $c = qp_currency ($form);
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
                $table_content .= qp_messagecontent ($form,$value,$options,$c,$messageoptions,$address,$arr,$i,$email);
                $count = $count+1;
                $i--;
            }
        }
    } else {
        $i=0;
        foreach($message as $value) {
            if ($count < $showthismany ) {
                $table_content .= qp_messagecontent ($form,$value,$options,$c,$messageoptions,$address,$arr,$i,$email);
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

function qp_messagecontent ($form,$value,$options,$c,$messageoptions,$address,$arr,$i,$email) {
    $qp_setup = qp_get_stored_setup();
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

function qp_check_coupon($couponcode,$form) {
    $coupon = qp_get_stored_coupon($form);
    for ($i=1; $i<=$coupon['couponnumber']; $i++) {
        if ($couponcode == $coupon['code'.$i] && $coupon['qty'.$i] > 0) {
            $coupon['qty'.$i] = $coupon['qty'.$i] - 1;
            if ($coupon['qty'.$i] == 0) {
                $coupon['code'.$i] = $coupon['qty'.$i]= '';
                $coupon['expired'.$i] = true;
            }
            update_option( 'qp_coupon'.$form, $coupon );
        }
    }
}

function qp_send_confirmation ($values,$form) {
    $qp = qp_get_stored_options($form);
    $address = qp_get_stored_address($form);
    $auto = qp_get_stored_autoresponder($form);
    $c = qp_currency($form);
    $auto['fromemail'] = ($auto['fromemail'] ? $auto['fromemail'] : get_bloginfo('admin_email'));
    $auto['fromname'] = ($auto['fromname'] ? $auto['fromname'] : get_bloginfo('name'));
    
    $headers = "From: {$auto['fromname']} <{$auto['fromemail']}>\r\n"
. "MIME-Version: 1.0\r\n"
. "Content-Type: text/html; charset=\"utf-8\"\r\n";	
    
    $subject = $auto['subject'];
    
    $rcolon = (strpos($auto['reference'],':') ? '' : ':');
    $acolon = (strpos($auto['amount'],':') ? '' : ':');
    
    $amounttopay = qp_total_amount($values['amount'], $qp, $values);
    $fullamount = $c['b'].$values['amount'].$c['a'];
    
    $details = '<h2>'.__('Order Details','multipay').':</h2>
    <p>'.$auto['reference'].$rcolon.' '.$values['reference'].'</p>
    <p>'.$qp['quantitylabel'].': '.$values['quantity'].'</p>';
    if ($qp['use_stock']) {
        if ($qp['fixedstock']) $details .= '<p>'.$qp['stocklabel'].'</p>';
        else $details .= '<p>'.$qp['stocklabel'].': ' . strip_tags($values['stock']) . '</p>';
    }
    if ($qp['use_options']) $details .= '<p>'.$qp['optionlabel'].': ' . strip_tags($values['option1']) . '</p>';
    
    $details .= '<p>'.$auto['amount'].' '.$acolon.$fullamount.'</p>';
    
    if ($qp['use_message'] && $qp['messagelabel'] !=$values['yourmessage']) $details .= '<p>'.$qp['messagelabel'].': ' . strip_tags($values['yourmessage']) . '</p>';
    
    if ($qp['use_datepicker']) $details .= '<p>'.$qp['datepickerlabel'].': ' . strip_tags($values['datepicker']) . '</p>';
    
    $details .= '<p>'.__('Transaction ID','multipay').': '.$values['tid'].'</p>';
    
    $qppkey = get_option('qpp_key');
    if ($qppkey['authorised'] && $auto['use_autoresponder']) {
        $content = '<p>' . $auto['message'] . '</p>';
        $content = str_replace('<p><p>', '<p>', $content);
        $content = str_replace('</p></p>', '</p>', $content);
        $content = str_replace('[firstname]', $values['firstname'], $content);
        $content = str_replace('[name]', $values['firstname'].' '.$values['lastname'], $content);
        $content = str_replace('[reference]', $values['reference'], $content);
        $content = str_replace('[quantity]', $values['quantity'], $content);
        $content = str_replace('[fullamount]', $fullamount, $content);
        $content = str_replace('[amount]', $amounttopay, $content);
        $content = str_replace('[stock]', $values['stock'], $content);
        $content = str_replace('[option]', $values['option1'], $content);
        $content = str_replace('[details]', $details, $content);

        if ($auto['paymentdetails']) {
            $content .= $details;
        }
        wp_mail($values['email'], $subject, '<html>'.$content.'</html>', $headers);
    }
    
    $subject = 'Payment for '.$values['reference'];
    
    if ($qp['use_address']) {
        $details .= '<h2>'.__('Personal Details','multipay').'</h2>
        <table>
        <tr><td>'.$address['email'].'</td><td>'.$values['email'].'</td></tr></tr>
        <tr><td>'.$address['firstname'].'</td><td>'.$values['firstname'].'</td></tr>
        <tr><td>'.$address['lastname'].'</td><td>'.$values['lastname'].'</td></tr>
        <tr><td>'.$address['address1'].'</td><td>'.$values['address1'].'</td></tr>
        <tr><td>'.$address['address2'].'</td><td>'.$values['address2'].'</td></tr>
        <tr><td>'.$address['city'].'</td><td>'.$values['city'].'</td></tr>
        <tr><td>'.$address['state'].'</td><td>'.$values['state'].'</td></tr>
        <tr><td>'.$address['zip'].'</td><td>'.$values['zip'].'</td></tr>
        <tr><td>'.$address['country'].'</td><td>'.$values['country'].'</td></tr>
        <tr><td>'.$address['night_phone_b'].'</td><td>'.$values['night_phone_b'].'</td></tr>
        </table>';
    }

    $details .= '<p>'.__('Payment made from','multipay').': '.$values['pagetitle'].'</p>
    <p>'.__('URL','multipay').': '.$values['url'].'</p>
    <p>'.__('Senders IP address','multipay').': '.$values['ip'].'</p>';
    
    $content = '<html>'.$details.'</html>';
    
    $confirmemail = (empty($auto['confirmemail']) ? get_bloginfo('admin_email') : $auto['confirmemail']);
    
    wp_mail($confirmemail, $subject, $content, $headers);
}

function qp_total_amount ($currency,$qp,$values) {
    
    $check = qp_format_amount($currency,$qp,$values['amount']);
    
    $quantity = ($values['quantity'] < 1 ? '1' : strip_tags($values['quantity']));
   	if ($qp['use_process'] && $qp['processtype'] == 'processpercent') {
        $percent = preg_replace ( '/[^.,0-9]/', '', $qp['processpercent']) / 100;
        $handling = $check * $quantity * $percent;
        $handling = (float) qp_format_amount($currency,$qp,$handling);
    }
	if ($qp['use_process'] && $qp['processtype'] == 'processfixed') {
        $handling = preg_replace ( '/[^.,0-9]/', '', $qp['processfixed']);
        $handling = (float) qp_format_amount($currency,$qp,$handling);
    }
	if ($qp['use_postage'] && $qp['postagetype'] == 'postagepercent') {
        $percent = preg_replace ( '/[^.,0-9]/', '', $qp['postagepercent']) / 100;
        $packing = $check * $quantity * $percent;
        $packing = (float) qp_format_amount($currency,$qp,$packing);
    }
	if ($qp['use_postage'] && $qp['postagetype'] == 'postagefixed') {
        $packing = preg_replace ( '/[^.,0-9]/', '', $qp['postagefixed']);
        $packing = (float) qp_format_amount($currency,$qp,$packing);
    }
    $amounttopay = $check * $quantity + $handling + $packing;
    $amounttopay = qp_format_amount($currency,$qp,$amounttopay);
    return $amounttopay;
}

function qp_create_user($values) {
    $user_name = $values['firstname'];
    $user_email = $values['email'];
    $user_id = username_exists( $user_name );
    if ( !$user_id and email_exists($user_email) == false and $user_name and $user_email) {
        $password = wp_generate_password( $length=12, $include_standard_special_chars=false );
        $user_id = wp_create_user( $user_name, $password , $user_email );
        wp_update_user(array('ID' =>  $user_id, 'role' => 'subscriber'));
        wp_new_user_notification( $user_id, $notify = 'both' );
    }
}
        
function qp_mailchimp($values,$send) {
    $http_query = http_build_query([
        'u' => $send['mailchimpuser'],
        'id' => $send['mailchimpid'],
        'EMAIL' => $values['email'],
        'FNAME' => $values['firstname'],
        'LNAME' => $values['lastname'],
    ]);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://mailchimp.'.$send['mailchimpregion'].'.list-manage.com/subscribe/post');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $http_query);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "content-type: application/x-www-form-urlencoded"
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    curl_close($ch);
    return '';
}

add_action( 'template_redirect', 'qp_upgrade_ipn' );

function qp_upgrade_ipn() {
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

function qp_admin_notice($message) {
    if (!empty( $message)) echo '<div class="updated"><p>'.$message.'</p></div>';
}
