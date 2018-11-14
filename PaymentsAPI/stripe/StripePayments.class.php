<?php
	class StripePayments extends PaymentBase {
		public $assets, $processing = false;
		private $api, $parent;
		
		function __construct() {
			$this->assets = array('scripts' => array());
			
			$this->assets['scripts']['stripe_checkout_js'] = "https://checkout.stripe.com/checkout.js";
			$this->assets['scripts']['stripe_local_js'] =  plugin_dir_url(__FILE__)."local.js";
		}
		
		function get_button() {
			return $this->api['stripe_submit'];
		}
		
		public function onLoad($parent,$api) {
			$api['mode'] = ((isset($api['sandbox']) && $api['sandbox'] == 'checked')? 'SANDBOX':'PRODUCTION');
			$this->api = $api; // mode, api_key, api_password, api_username, merchantid;
			$this->parent = $parent;

			/*
				Check the get variables if we're processing payments for stripe
			*/
			if (isset($_GET['module']) && $_GET['module'] == 'stripe') {
				
				if (isset($_REQUEST['token'])) $this->parent->setProcessing('stripe');
				else {
					$this->parent->setProcessing('stripe');
					$this->parent->setFailure();
				}
			}
		}
		
		public function onHead() {
			
			echo "<script type='text/javascript'> stripe = {key:'{$this->api['publishable_key']}',environment:'{$this->api['mode']}'};</script>";
			
		}
		
		public function onProcessing() {

		
			$currency = qp_get_stored_curr();
			$current_currency = $currency[$_REQUEST['form']];

			if (isset($_REQUEST['token']) && isset($_REQUEST['qp_key'])) {
				
				/*
					Authenticate into stripe
				*/
				
				try {
					
					$order = $this->parent->order;
					$price = (float) $order['price'];
					$qty = (float) $order['quantity'];
					$shipping = (float) $order['shipping'];
					$processing = (float) $order['processing'];
					
					$amount = ($price * $qty) + $processing + $shipping;
					
					\Stripe\Stripe::setApiKey($this->api['secret_key']);
					
					$charge = \Stripe\Charge::create(array(
						"amount" => $amount * 100,
						"currency" => $current_currency,
						"source" => $_REQUEST['token'])
					);
					
					$this->parent->setSuccess($charge->id, array('Transaction_ID' => $charge->id, 'amount' => $amount." ".$current_currency));
					
				} catch (\Stripe\Error\RateLimit $e) {
					// Too many requests made to the API too quickly
					$arr = array('Reason' => $e->getMessage());
					$this->parent->setFailure($arr);
				} catch (\Stripe\Error\InvalidRequest $e) {
					// Invalid parameters were supplied to Stripe's API
					$arr = array('Reason' => $e->getMessage());
					$this->parent->setFailure($arr);
				} catch (\Stripe\Error\Authentication $e) {
					// Authentication with Stripe's API failed
					// (maybe you changed API keys recently)
					$arr = array('Reason' => $e->getMessage());
					$this->parent->setFailure($arr);
				} catch (\Stripe\Error\ApiConnection $e) {
					// Network communication with Stripe failed
					$arr = array('Reason' => $e->getMessage());
					$this->parent->setFailure($arr);
				} catch (\Stripe\Error\Base $e) {
					// Display a very generic error to the user, and maybe send
					// yourself an email
					$arr = array('Reason' => $e->getMessage());
					$this->parent->setFailure($arr);
				} catch (Exception $e) {
					// Something else happened, completely unrelated to Stripe
					$arr = array('Reason' => $e->getMessage());
					$this->parent->setFailure($arr);
				}
			}
			
		}
		
		public function onValidation($data) {
			
			$returning = array();
			$returning['name'] = $data['name'];
			$returning['email'] = $data['email'];
			$returning['amount'] = $data['amount'] * $data['quantity'] + ($data['processing'] + $data['postage']);
			$returning['key'] = $this->api['publishable_key'];
			$returning['image'] = ((strlen($this->api['stripeimage']))? $this->api['stripeimage']:"https://stripe.com/img/documentation/checkout/marketplace.png");
			$returning['description'] = '';
			return $returning;
			
		}
	}
?>