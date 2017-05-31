<?php

	mb_internal_encoding('UTF-8');
	require_once 'PHPMailerAutoload.php';
	$vendor = new PHPMailer;
	if(SMTP){
		//$vendor->IsSMTP();
		//$vendor->Host     = SMTP_HOST;
		//$vendor->Username = SMTP_USER;
		//$vendor->Password = SMTP_PASS;			
		//$vendor->SMTPAuth = true;
		//$vendor->SMTPSecure = "ssl"; //gmail
		//$vendor->Port = 587; //gmail
		//$vendor->SMTPDebug = 2;
	}
	$vendor->isHTML(true);
	$vendor->Charset='UTF-8';	
	$vendor->Encoding = 'base64';
?>