<?php
	/*
		Paypal Module
	*/

	include('AmazonPayments.class.php');
	
	$modules['amazon'] = new AmazonPayments();
?>