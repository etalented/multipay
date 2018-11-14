<?php
	/**
		* The bridge class which talks to all of the payment modules
		*
		* @package		PaymentsAPI
		* @author 		Russell <RussellReal@gmail.com>
		* @license		https://tldrlegal.com/license/mit-license-MIT-License-3.01
		* @version		Release: 1.0
		* @since		2016-23-10
	*/
	
	class PaymentsAPI {
		
		public $active = array(), $modules = array(), $data = array(), $status = false, $complete = false, $processing = false, $pModules = array();
		
		/**
			* Construct's the PaymentsAPI class and collects the modules for future use
			* 
			* @param 	String 	$m The modules array, this will contain only the activated Modules
			* 
			* @return 	No return data
		*/ 
		function __construct($m) {
			/*
				Build main class
			*/
			$this->modules = $m;
		}
		
		/**
			* Returns all active modules' assets (Scripts, CSS, Etc)
			*
			* @return 	Array	An array of all assets for all active modules.
		*/
		function assets() {
			/*
				Load in the assets for all appended payment methods
				
				Will most likely be called in the scripts section of wordpress init
			*/
			$assets = array();
			foreach ($this->active as $mName => $mObject) {
				if (isset($mObject->assets['scripts'])) {
					foreach ($mObject->assets['scripts'] as $asset => $url) {
						$assets[] = array('type' => 'script', 'url' => $url, 'name' => $asset);
					}
				}
				
				if (isset($mObject->assets['css'])) {
					foreach ($mObject->assets['css'] as $asset => $url) {
						$assets[] = array('type' => 'css', 'url' => $url, 'name' => $asset);
					}
				}
			}
			
			return $assets;
		}
		
		/**
			* Trigger the onLoad event in the Module
			*
			* @return 	No return data
		*/
		function load($module,$data = false) {
			if (isset($this->modules[$module])) {
				$this->modules[$module]->activate();
				$this->modules[$module]->onLoad($this,$data);
				$this->active[$module] = $this->modules[$module];
			}
			
		}
		
		/**
			* Return's the button text for all of the modules
			*
			* @return 	Array	An array of all button texts
		*/
		function getButtons() {
			$buttons = array();
			foreach ($this->modules as $name => $module) {
				if ($this->modules[$name]->active) {
					$buttons[$name] = $this->modules[$name]->get_button();
				}
			}
			return $buttons;
		}
		
		/**
			* Calls the onValidation event on the specified module
			* Gets called upon successful validation
			*
			* @param	String	$module	The module's handle name
			* @param	Object	$json	The json object to expose to the module
			*
			* @return 	No return dataa
		*/
		function onValidation($module,&$json) {
			
			$add = $this->modules[$module]->onValidation($this->data);
			
			$json->qp_key = $this->data['qp_key'];
			$json->currency = $this->data['currencycode'];
			$json->currency_sign = $this->data['currencysign'];
			$json->amount = $this->data['amount_formatted'];
			/*
				Add to json response
			*/
			if ($add) $json->data = $add;
		
		}
		
		/**
			* Calls the onHead global event on all modules
			*
			* @return 	No return data
		*/
		function onHead() {
			
			foreach ($this->modules as $mName => $mObject) {
				$mObject->onHead();
			}
			
		}

		/**
			* Calls the onProcessing event on all "processing" modules
			*
			* @return 	No return data
		*/
		function onProcessing($message) {
			$this->order = $message;
			for ($i = 0; $i < count($this->pModules); $i++) {
				$this->modules[$this->pModules[$i]]->onProcessing();
			}
		}
		
		/**
			* Collects and normalizes all of the available data for the current transaction
			* And sets that data to $this->data for later use
			*
			* @param	String	$amount	The current amount variable
			* @param	Array	$v		Array of all transaction-related variables
			*
			* @return 	No return data
		*/
		function collect($form,$amount,$v,$qp_key) {
	
			$currency = qp_get_stored_curr();
			$current_currency = $currency[$form];
			
			$amount = (float) preg_replace('/([^0-9.,])/','',(($amount)? $amount:0));
			$name = (string) $v['reference'];
			$qty = (float) $v['quantity'];
			$email = '';
			if (isset($_POST['email'])) {
				if (filter_var(trim($v['email']), FILTER_VALIDATE_EMAIL)) {
					$email = $v['email'];
				}
			}
			if (!$amount && isset($_POST['combine'])) {
				$c = array();
				if (strpos($name,'&')) $c = explode('&',$name);
				if (count($c)) {
					$name = $c[0];
					$amount = $c[1];
				}
			}
			$data = array(
				'amount' => $amount,
				'amount_formatted' => qp_format_amount($current_currency,array(),$amount),
				'processing' => 0,
				'postage' => 0,
				'name' => $name,
				'quantity' => $qty,
				'email' => $email
			);
			
			$option = '';
			if (isset($_POST['option1'])) $data['option'] = $_POST['option1'];
			
			if (isset($_POST['yourmessage'])) $data['note'] = $_POST['yourmessage'];
			
			$data['shipping'] = array(
				'firstname' => '',
				'lastname' => '',
				'address1' => '',
				'address2' => '',
				'city' => '',
				'state' => '',
				'zip' => '',
				'country' => '',
				'night_phone_b' => ''
			);
			
			$name = $fname = $lname = '';
			foreach ($_POST as $k => $v) {
				if (array_key_exists($k,$data['shipping'])) {

					if (strlen($v)) {
						$data['shipping'][$k] = $v;
					}
					
				}
			}
			
			$postage = 0;
			if (isset($_POST['postage_type'])) {
				$postage = (float) $_POST['postage'];
				if (strtolower($_POST['postage_type']) == 'percent') $postage = ($qty * $amount) * ($postage * .01);
				
				$postage = $postage;
				
			}
			
			// Processing
			$processing = 0;
			if (isset($_POST['processing_type'])) {
				$processing = (float) $_POST['processing'];
				if (strtolower($_POST['processing_type']) == 'percent') $processing = ($qty * $amount) * ($processing * .01);
				
				$processing = $processing;

			}
			
			$data['postage'] = $postage;
			$data['processing'] = $processing;
			$data['currencycode'] = $current_currency;
			$data['currencysign'] = qp_currency($form);
			$data['qp_key'] = $qp_key;
			
			$this->data = $data;
		}
		
		/**
			* Sets PaymentsAPI into 'Processing' mode
			*
			* @param	String	$module	The name of the module which is in Processing mode
			* 
			* @return 	No return data
		*/
		function setProcessing($module) {
			$this->processing = true;
			$this->pModules[] = $module;
		}
		
		/**
			* Sets PaymentsAPI status to success
			*
			* @param	Array	An array of information that the processing module wishes to display
			* 
			* @return 	No return data
		*/
		function setSuccess($tid, $data = array()) {
			$this->complete = true;
			$this->payment_details = $data;
			$this->tid = $tid;
			$this->status = 'success';
		}
		
		/**
			* Sets PaymentsAPI status to pending
			*
			* @param	Array	An array of information that the processing module wishes to display
			* 
			* @return 	No return data
		*/
		function setPending($data = array()) {
			$this->complete = true;
			$this->payment_details = $data;
			$this->status = 'pending';
		}
		
		/**
			* Sets PaymentsAPI status to failure
			*
			* @param	Array	An array of information that the processing module wishes to display
			* 
			* @return 	No return data
		*/
		function setFailure($data = array()) {
			$this->complete = true;
			$this->payment_details = $data;
			$this->status = 'failure';
		}
	}
?>