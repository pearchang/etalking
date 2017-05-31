<?php
	ini_set('display_errors', 1);
	define('DS',DIRECTORY_SEPARATOR );
	define('DEBUG',0);
	define('SQLLOG',0);	//query過的sql stm 將存在 upload/log.txt
	define('CACHE',0);
	define('CAPCHA_PUBLIC_KEY','6LfC1SITAAAAANviJtPvfaUSuECXGAmixgQXR8BW');
	define('CAPCHA_PRIVATE_KEY','6LfC1SITAAAAAIv-ZWZ7XuiY62w1WgZXsGtlFHuz');
	define('DOCUMENT_ROOT',__DIR__.DS);
	define('HTTP_ROOT', 'http://'.$_SERVER['HTTP_HOST'] );
	define('UPLOAD', DOCUMENT_ROOT . 'upload');
	define('RAINTPL',__DIR__.DS.'upload'.DS.'tmp'.DS);
	
	define('DB_HOST','localhost');
	define('DB_USER','etalking_usr');
	define('DB_PASS','E7GSPdXUMXHy3aDH');
	define('DB_DATABASE','etalking_db');
	define('DB_PORT',3306);
	
	define('SMTP',1);
	define('SMTP_HOST','');
	define('SMTP_USER','');
	define('SMTP_PASS','');
	
	header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store, no-cache, must-revalidate' );
	header( 'Cache-Control: post-check=0, pre-check=0', false );
	header( 'Pragma: no-cache' );
	date_default_timezone_set('Asia/Taipei');
	mb_internal_encoding("UTF-8");
	
	include DOCUMENT_ROOT.'lib/PDO.class.php';
	include DOCUMENT_ROOT.'lib/function.php';
	
	$dbconn = new Database('mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_DATABASE.';charset=UTF8;',DB_USER,DB_PASS);
	$dbconn->query("SET NAMES utf8;");
	
	session_start();
	
	require_once('lib/constant.php');
	require_once('lib/etalk.php');
	
	define('CLASSROOM_COUNTDOWN',60); //進入教室倒數計時(分鐘)
?>
