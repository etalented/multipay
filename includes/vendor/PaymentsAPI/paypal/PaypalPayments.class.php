<?php

	/**
		* Base object for payment modules
		*
		* @package		PaymentPayments
		* @author 		Russell <RussellReal@gmail.com>
		* @license		https://tldrlegal.com/license/mit-license-MIT-License-3.01
		* @version		Release: 1.0
		* @since		2016-23-10
	*/
	class PaypalPayments extends PaymentBase {
		
		public $assets, $processing = false, $status;
		private $paypal, $api, $parent;
		
		function __construct() {
			$this->assets = array('scripts' => array(), 'css' => array(), 'classes' => array());
			
			$this->assets['scripts']['paypal_checkout_js'] = "https://www.paypalobjects.com/api/checkout.js";
			$this->assets['scripts']['paypal_local_js'] = plugin_dir_url(__FILE__)."local.js";
		}
		
		private function get_paypal() {
			$this->paypal = new PaypalAPI($this->api['api_username'],$this->api['api_password'],$this->api['api_key'],$this->api['mode']);
		}
		
		public function get_button() {
			return $this->api['paypal_submit'];
		}
		
		public function onLoad($parent,$api) {

			$api['mode'] = ((isset($api['sandbox']) && $api['sandbox'] == 'checked')? 'SANDBOX':'PRODUCTION');
			$this->api = $api; // mode, api_key, api_password, api_username, merchantid;
			$this->parent = $parent;
			/*
				Check the get variables if we're processing payments for paypal
			*/
			if (isset($_GET['module']) && $_GET['module'] == 'paypal') {
				
				if (isset($_GET['token'])) $this->parent->setProcessing('paypal');
				
			}
		}	
		
		public function onProcessing() {
			
			$this->get_paypal();
			
			$this->paypal->setMethod('GetExpressCheckoutDetails');
			$this->paypal->setAttribute('TOKEN',$_GET['token']);
			
			$return = $this->paypal->execute();
			
			if ($return['ACK'] == 'Success') {
				/*
					The paypal request went through,
					
					Now to determine if the payment was processed
				*/
				
				switch ($return['CHECKOUTSTATUS']) {
					case 'PaymentActionNotInitiated':
						//its waiting for us to process it!
						$this->paypal->reloadFromResponse('DoExpressCheckoutPayment');
						$r = $this->paypal->execute();

						if ($r['PAYMENTINFO_0_ACK'] == 'Success') $this->parent->setSuccess($r['PAYMENTINFO_0_TRANSACTIONID'],array('Transaction_ID' => $r['PAYMENTINFO_0_TRANSACTIONID'], 'Amount' => $r['PAYMENTINFO_0_AMT']));
						else {
							$this->parent->setFailure();
						}
					break;
					case 'PaymentActionFailed':
						//payment failed
						$this->parent->setFailure();
					break;
					case 'PaymentActionInProgress':
						//processing/pending
						$this->parent->setPending();
					break;
					case 'PaymentActionCompleted':
						//100% Success
						$this->parent->setSuccess($return['PAYMENTREQUEST_0_TRANSACTIONID'], array('Transaction_ID' => $return['PAYMENTREQUEST_0_TRANSACTIONID'], 'Amount' => $return['PAYMENTREQUEST_0_AMT']));
					break;
				}
			} else {
				$this->parent->setFailure();
			}
		}
		
		public function onValidation($data) {
			
			/*
				This function only triggers when validation succeeds
				
				Returning a value will automatically be added to the validation response
			*/
			
			// Combine firstname and lastname
			$data['shipping']['fullname'] = trim($data['shipping']['firstname'].' '.$data['shipping']['lastname']);
			
			$this->get_paypal();
			
			$this->paypal->setMethod('SetExpressCheckout');
			if (isset($_POST['sc']['post'])) $post = $_POST['sc']['post'];
			
			$url = get_permalink($post);
			$parsedUrl = parse_url($url);
			if ($parsedUrl['path'] == null) {
				$url .= '/';
			}
			$separator = ($parsedUrl['query'] == NULL) ? '?' : '&';
			$url .= $separator . "module=paypal&qp_key=".$data['qp_key'];
			
			$this->paypal->setAttribute('RETURNURL',$url);
			$this->paypal->setAttribute('CANCELURL',$url);
			
			// Create order
			$order = $this->paypal->NewOrder();
			
			// Create item
			$item = $order->NewItem($data['amount'],$data['quantity']);
			$item->setAttribute('NAME',$data['name']);
			
			/*
				Loop through data
			*/
			$array = array(
				'processing' => 'HANDLINGAMT',
				'postage' => 'SHIPPINGAMT',
				'currencycode' => 'CURRENCYCODE',
				'shipping' => array(
					'fullname' => 'SHIPTONAME',
					'address1' => 'SHIPTOSTREET',
					'address2' => 'SHIPTOSTREET2',
					'city' => 'SHIPTOCITY',
					'state' => 'SHIPTOSTATE',
					'zip' => 'SHIPTOZIP',
					'country' => 'SHIPTOCOUNTRY',
					'night_phone_b' => 'SHIPTOPHONENUM'
				)
			);
			foreach ($array as $key => $value) {
				if ($key == 'shipping') {
					// do other loop
					foreach ($array[$key] as $sK => $sV) {
						if (strlen($data[$key][$sK])) $order->setAttribute($sV,$data[$key][$sK]);
					}
				} else {
					if (strlen($data[$key])) $order->setAttribute($value,$data[$key]);
				}
			}
			
			$return = $this->paypal->execute();
			if (strtolower($return['ACK']) == 'success') { 
						
				// Build In-Context code
				$ic = array(
					'id' => $this->api['merchantid'],
					'token' => $return['TOKEN'],
					'environment' => $this->api['mode']
				);
				return $ic;
			}
			
		}
		
		function onHead() {
			echo "<script type='text/javascript'> qp_ic = {id:'{$this->api['merchantid']}',environment:'{$this->api['mode']}'};</script>";
		}
		
	}
?>