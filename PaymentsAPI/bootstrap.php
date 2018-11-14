<?php
	/*
		Bootstrap
	*/
	// Load PaymentBase class 
	include('PaymentBase.class.php');
	
	// Load PaymentsAPI class
	include('PaymentsAPI.class.php');
	
	// List of modules for PaymentBase
	$modules = array();
	
	/*
		Find all installed modules
	*/
	$directory = new RecursiveDirectoryIterator(plugin_dir_path( __FILE__ ));
	
	foreach (new RecursiveIteratorIterator($directory) as $filename => $file) {
		if ($file->getFilename() == 'module.php') {
			include($filename);
		}
	}
	
	$PaymentsAPI = new PaymentsAPI($modules);
?>