<?php
	/**
		* Base object for payment modules
		*
		* @package		PaymentBase
		* @author 		Russell <RussellReal@gmail.com>
		* @license		https://tldrlegal.com/license/mit-license-MIT-License-3.01
		* @version		Release: 1.0
		* @since		2016-23-10
	*/
	class PaymentBase {
		
		public $active = false, $registered_assets;
		
		public function get_button() { }
		
		public function activate() { $this->active = true; $this->onActivate(); }
		public function deactivate() { $this->active = false; $this->onDeactivate(); }
		
		public function onLoad($parent,$api) { }
		public function onHead() { }
		public function onValidation($data) { }
		public function onProcessing() { }
		public function onActivate() { }
		public function onDeactivate() { }
		
	}
?>