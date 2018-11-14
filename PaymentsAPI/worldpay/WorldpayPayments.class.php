<?php
	class WorldpayPayments extends PaymentBase {
		public $assets, $processing = false;
		private $api, $parent;
		
		function __construct() {
			$this->assets = array('scripts' => array(), 'css' => array());
			
			$this->assets['scripts']['worldpay_checkout_js'] = "https://cdn.worldpay.com/v1/worldpay.js";
			$this->assets['scripts']['worldpay_local_js'] =  plugin_dir_url(__FILE__)."local.js";
			
			$this->assets['css']['worldpay_css'] = plugin_dir_url(__FILE__)."local.css";
		}
		
		function get_button() {
			return $this->api['worldpay_submit'];
		}
		
		public function onLoad($parent,$api) {
			$this->api = $api; // mode, api_key, api_password, api_username, merchantid;
			$this->parent = $parent;

			/*
				Check the get variables if we're processing payments for worldpay
			*/
			if (isset($_GET['module']) && $_GET['module'] == 'worldpay') {
				
				if (isset($_REQUEST['token'])) $this->parent->setProcessing('worldpay');
				else {
					$this->parent->setProcessing('worldpay');
					$this->parent->setFailure();
				}
				
			}
		}
		
		public function onHead() {
			
			echo "<script type='text/javascript'> wp_api = {key:'{$this->api['client_key']}',environment:'{$this->api['mode']}'};</script>";
			
		}
		
		public function onProcessing() {
			$currency = qp_get_stored_curr();
			$current_currency = $currency[$_REQUEST['form']];
			$order = $this->parent->order;
			if (isset($_REQUEST['token']) && isset($_REQUEST['qp_key'])) {
				
				/*
					Authenticate into stripe
				*/
				$price = (float) $order['price'];
				$qty = (float) $order['quantity'];
				$shipping = (float) $order['shipping'];
				$processing = (float) $order['processing'];
					
				$amount = ($price * $qty) + $processing + $shipping;
					
				$worldpay = new \Worldpay\Worldpay($this->api['service_key']);
				$worldpay->disableSSLCheck(true);
				
				$info = array("amount" => $amount * 100,
					"currencyCode" => $current_currency,
					"token" => $_REQUEST['token'],
					"name" => $order['reference'],
					"orderDescription" => "Worldpay payment"
				);
					
						
				try {	
					
					$response = $worldpay->createOrder($info);
						
					if ($response['paymentStatus'] === 'SUCCESS') {
						$this->parent->setSuccess($response['orderCode'], array('Transaction_ID' => $response['orderCode'], 'amount' => ($response['amount'] / 100)." ".$response['currencyCode']));
					} else {
						 throw new \Worldpay\WorldpayException(print_r($response, true));
					}
						
				} catch (Exception $e) {
					// Too many requests made to the API too quickly
					$arr = array('Reason' => $e->getMessage());
					$this->parent->setFailure($arr);
				}
			}
		}
		
		public function onValidation($data) {
			
			$returning = array();
			$returning['name'] = $data['name'];
			$returning['amount'] = $data['amount'] * $data['quantity'] + ($data['processing'] + $data['postage']);
			$returning['key'] = $this->api['client_key'];
			
			return $returning;
			
		}
	}
?>