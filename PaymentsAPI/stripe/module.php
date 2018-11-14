<?php
	/*
		Paypal Module
	*/
	
	include('init.php');
	include('StripePayments.class.php');
	
	$modules['stripe'] = new StripePayments();
?>