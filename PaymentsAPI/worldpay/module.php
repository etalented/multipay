<?php
	/*
		Paypal Module
	*/
	
	include('init.php');
	include('WorldpayPayments.class.php');
	
	$modules['worldpay'] = new WorldpayPayments();
?>