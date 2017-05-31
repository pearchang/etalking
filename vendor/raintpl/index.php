<?php
	require_once 'rain.tpl.class.php';	
	$config = array("tpl_dir" => ROOT.'template'.DS, 'path_replace_list' => array(),'cache_dir'=>DOCUMENT_ROOT."upload/tmp/" );
	RainTPL::configure( $config );
	$vendor = new RainTPL;
?>