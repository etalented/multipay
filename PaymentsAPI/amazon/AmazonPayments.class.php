<?php

	class AmazonPayments extends PaymentBase {
		public $assets, $processing = false, $endpoint;
		private $api, $parent;
		
		function __construct() {
			$this->assets = array('scripts' => array(), 'css' => array());
			
			$this->assets['scripts']['amazon_checkout_js_production']	= "https://static-na.payments-amazon.com/OffAmazonPayments/us/js/Widgets.js";
			$this->assets['scripts']['amazon_checkout_js_sandbox'] 		= "https://static-na.payments-amazon.com/OffAmazonPayments/us/sandbox/js/Widgets.js";
			$this->assets['scripts']['amazon_local_js'] =  plugin_dir_url(__FILE__)."local.js";
			
			$this->assets['css']['amazon_css'] = plugin_dir_url(__FILE__)."local.css";
		}
		
		function get_button() {
			return $this->api['amazon_submit'];
		}
		
		public function onLoad($parent,$api) {
			$this->api = $api; // mode, api_key, api_password, api_username, merchantid;
			
			if ($this->api['mode'] == 'SANDBOX') {
				unset($this->assets['scripts']['amazon_checkout_js_production']);
			} else {
				unset($this->assets['scripts']['amazon_checkout_js_sandbox']);
			}
			$this->parent = $parent;

			/*
				Check the get variables if we're processing payments for amazon
			*/
			if (isset($_GET['module']) && $_GET['module'] == 'amazon') {
				
				if (isset($_REQUEST['token'])) $this->parent->setProcessing('amazon');
				else {
					$this->parent->setProcessing('amazon');
					$this->parent->setFailure();
				}
				
			}
		}
		
		public function onHead() {
			
			echo "<script type='text/javascript'> am_api = {key:'{$this->api['client_key']}',environment:'{$this->api['mode']}'};</script>";
			
		}
		
		public function onProcessing() {
			$currency = qp_get_stored_curr();
			$current_currency = $currency[$_REQUEST['form']];
			$order = $this->parent->order;
			global $post;
			$url = get_permalink($post->ID);
			$parsedUrl = parse_url($url);

			if (isset($_REQUEST['resultCode']) && $_REQUEST['resultCode'] == 'Success') {

				/*
					Validate that the signature matches
					
					A valid signature means no tampering
				*/
				
				$_REQUEST['qp_key'] = $_REQUEST['sellerOrderId'];
				
				$signature = $_GET['signature'];
				
				$parameters = $_GET;
				unset($parameters['signature']);
				unset($parameters['module']);
				
				if(isset($parameters['sellerOrderId'])) {
					$parameters['sellerOrderId'] = rawurlencode($parameters['sellerOrderId']);
				}
				
				uksort($parameters, 'strcmp');
				
				$url = get_permalink($post);
				$parsedUrl = parse_url($url);
				if ($parsedUrl['path'] == null) {
					$url .= '/';
				}
				$separator = ($parsedUrl['query'] == NULL) ? '?' : '&';
				$returnURL = $url;
				
				$parseUrl = parse_url($returnURL);

				$stringToSign = "GET\n" . $parseUrl['host'] . "\n" . $parseUrl['path'] . "\n";

				foreach ($parameters as $key => $value) {
					$queryParameters[] = $key . '=' . str_replace('%7E', '~', rawurlencode($value));
				}
				$stringToSign .= implode('&', $queryParameters);
				
				$signatureCalculated = base64_encode(hash_hmac("sha256", $stringToSign, $this->api['secretKey'], true));
				
				$signatureCalculated = str_replace('%7E', '~', rawurlencode($signatureCalculated));
				
				if ($signatureCalculated == $signature) {
					$this->parent->setSuccess($parameters['orderReferenceId'], array('Transaction_ID' => $parameters['orderReferenceId'], 'Amount' => $parameters['amount']." ".$parameters['currencyCode']));
				} else {
					$this->parent->setFailure(array());
				}

			}
			
		}
		
		public function onValidation($data) {
			
			/*
				We need to return a signature for Amazon Pay
			*/
			
			$returning = array();
			$returning['name'] 		= $data['name'];
			$returning['amount']	= $data['amount'] * $data['quantity'] + ($data['processing'] + $data['postage']);
			$returning['client_id']	= $this->api['clientID'];
			
			$returning['payload']	= $this->sign($returning['amount'], $data['qp_key'], $data['currencycode']);
			/*
				Generate 
			*/
			
			return $returning;
			
		}
		
		private function sign($amount,$order_id,$currency) {
					
			// Optional fields
			$currencyCode            = $currency;
			$sellerOrderId           = $order_id;
			$shippingAddressRequired = "false";
			$paymentAction           = "AuthorizeAndCapture"; // other values None,Authorize
			
			$merchantId		= $this->api['merchantID'];
			$accessKey		= $this->api['accessKey'];
			$secretKey		= $this->api['secretKey'];
			$lwaClientId	= $this->api['clientID'];
			
			if (isset($_POST['sc']['post'])) $post = $_POST['sc']['post'];
			
			$url = get_permalink($post);
			$parsedUrl = parse_url($url);
			if ($parsedUrl['path'] == null) {
				$url .= '/';
			}
			$separator = ($parsedUrl['query'] == NULL) ? '?' : '&';
			$url .= $separator.'module=amazon';
			$returnURL = $url;
			
			// Getting the MerchantID/sellerID, MWS secret Key, MWS Access Key from the configuration file
			if ($merchantId == "") {
				throw new InvalidArgumentException("merchantId not set in the configuration file");
			}
			if ($accessKey == "") {
				throw new InvalidArgumentException("accessKey not set in the configuration file");
			}
			if ($secretKey == "") {
				throw new InvalidArgumentException("secretKey not set in the configuration file");
			}
			if ($lwaClientId == "") {
				throw new InvalidArgumentException("Login With Amazon ClientID is not set in the configuration file");
			}
			
			//Addding the parameters to the PHP data structure
			$parameters["accessKey"]               	= $accessKey;
			$parameters["amount"]                  	= $amount;
			$parameters["sellerId"]                	= $merchantId;
			$parameters["returnURL"]               	= $returnURL;
			$parameters["cancelReturnURL"]         	= $cancelReturnURL;
			$parameters["lwaClientId"]             	= $lwaClientId;
			$parameters["sellerNote"]              	= $sellerNote;
			$parameters["sellerOrderId"]			= $sellerOrderId;
			$parameters["currencyCode"]				= $currencyCode;
			$parameters["shippingAddressRequired"] 	= $shippingAddressRequired;
			$parameters["paymentAction"]           	= $paymentAction;
			
			uksort($parameters, 'strcmp');
			
			/*
				Purge the data
			*/
			foreach ($parameters as $k => $v) {
				if (!$v) unset($parameters[$k]);
			}
			//call the function to sign the parameters and return the URL encoded signature
			$Signature = $this->_urlencode($this->_signParameters($parameters, $secretKey));
			
			//add the signature to the parameters data structure
			$parameters["signature"] = $Signature;
			
			//echoing the parameters will be picked up by the ajax success function in the front end
			return $parameters;
			
		}
		private function _signParameters(array $parameters, $key) {
			$algorithm    = "HmacSHA256";
			$stringToSign = $this->_calculateStringToSignV2($parameters);
			return $this->_sign($stringToSign, $key, $algorithm);
		}
		
		private function _calculateStringToSignV2(array $parameters) {
			$data = 'POST';
			$data .= "\n";
			$data .= "payments.amazon.com";
			$data .= "\n";
			$data .= "/";
			$data .= "\n";
			$data .= $this->_getParametersAsString($parameters);
			return $data;
		}
		
		private function _getParametersAsString(array $parameters){
			$queryParameters = array();
			foreach ($parameters as $key => $value) {
				$queryParameters[] = $key . '=' . $this->_urlencode($value);
			}
			return implode('&', $queryParameters);
		}
		
		private function _urlencode($value){
			return str_replace('%7E', '~', rawurlencode($value));
		}
		
		private function _sign($data, $key, $algorithm) {
			if ($algorithm === 'HmacSHA1') {
				$hash = 'sha1';
			} else if ($algorithm === 'HmacSHA256') {
				$hash = 'sha256';
			} else {
				throw new Exception("Non-supported signing method specified");
			}
			return base64_encode(hash_hmac($hash, $data, $key, true));
		}
	}
?>