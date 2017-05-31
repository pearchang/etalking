<?php

	function enter_webex($classroom, $type = 'student', $vars = false){
		
		GLOBAL $dbconn;		
		
		$filter = array( 'id'=> $classroom, 'status' => 10 );
		$classroom = $dbconn->fetch_one('classroom', $filter);
		if(!$classroom) return false;
		
		//demo免登入
		if($classroom['type']!=10 && ( !isset($_SESSION['member']) && !isset($_SESSION['teacher']) ) ){
			return false;
		}
		
		$filter = array('id'=> $classroom['webex_id'], 'status' => 10 );
		$webex = $dbconn->fetch_one('webex', $filter);
		if(!$webex) return false;
		
		$webex['meeting_key'] = $classroom['meeting_key'];
		$webex['meeting_pw'] = $classroom['meeting_pw'];
		
		if($type=='student'){
			$user = $dbconn->fetch_one('member',array('id'=>$_SESSION['member']['id']));
			$webex['email'] = $user['email'];
			$webex['name']  = $user['first_name'];
			$countdown = 300;
		}elseif($type=='teacher'){
			$user = $dbconn->fetch_one('consultant',array('id'=>$classroom['consultant_id']));
			$webex['email']= $user['email'];
			$webex['name'] = $user['first_name'];
			$countdown = 600;			
		}elseif($type=='teacher_demo'){ //teacher demo			
			$user = $dbconn->fetch_one('consultant',array('id'=>$classroom['consultant_id']));
			$webex['email'] = $user['email'];
			$webex['name']  = $user['first_name'];
			$countdown = 3600;
		}else{ //demo
			$user = $dbconn->fetch_one('course_registration', 'classroom_id='.$classroom['id'] , 'id DESC' );
			if(!$user) return false;
			$user = $dbconn->fetch_one('member',array('id'=>$user['member_id']));
			$webex['email'] = $user['email'];
			$webex['name']  = $user['member_name'];
			$countdown = 3600;
		}

		$stime = strtotime( $classroom['open_time'] );
		$etime = strtotime( $classroom['open_time'] ) + ( $type=='teacher_demo' ? 3600 : 2700 );		
		$lifetime = $stime - time();
		
		//備援
		if($classroom['use_url']==1){
			/*
			if($type=='teacher')
				$webex = "https://etalking.webex.com/mw3000/mywebex/cmr/cmr.do?siteurl=etalking&AT=start&username=".$webex['webex_name'].'&attendeeName='.$webex['name'].'&attendeeEmail='.$webex['email'].'&account='.$webex['account'].'&password='.$webex['password'];
			else
			*/
				$webex = "https://etalking.webex.com/mw3000/mywebex/cmr/cmr.do?siteurl=etalking&AT=join&username=".$webex['webex_name'].'&attendeeName='.$webex['name'].'&attendeeEmail='.$webex['email'];
		}
		
		if( $vars ){
			return ($lifetime <= $countdown && $lifetime >=0 ) || ( $etime - time() >0 && $lifetime<=0 ) ? $webex : false;
		}else{
			return ($lifetime <= $countdown && $lifetime >=0 ) || ( $etime - time() >0 && $lifetime<=0 ) ? true : false;
		}
		//else
			//return $lifetime <= $countdown && $lifetime >=0  ? $webex : false;
	}
	
	function enter_webex_test($classroom, $type = 'student', $vars = false){
		
		GLOBAL $dbconn;		
		
		$filter = array( 'id'=> $classroom, 'status' => 10 );
		$classroom = $dbconn->fetch_one('classroom_test', $filter);
		if(!$classroom) return false;
		
		$filter = array('id'=> $classroom['webex_id'], 'status' => 10 );
		$webex = $dbconn->fetch_one('webex', $filter);
		if(!$webex) return false;
		
		$webex['meeting_key'] = $classroom['meeting_key'];
		$webex['meeting_pw'] = $classroom['meeting_pw'];
		
		$user = $dbconn->fetch_one('webex_test', 'classroom_id='.$classroom['id'] , 'id DESC' );
		if(!$user) return false;
		$user = $user['type']==1 ? $dbconn->fetch_one('member',array('id'=>$user['target_id'])) : 
										$dbconn->fetch_one('consultant',array('id'=>$user['target_id']));
		$webex['email'] = $user['email'];
		$webex['name']  = $user['type']==1 ? $user['member_name'] : $user['first_name'];
		$countdown = 3600;

		$stime = strtotime( $classroom['open_time'] );
		$etime = strtotime( $classroom['open_time'] ) + 1740;		
		$lifetime = $stime - time();		
		
		if( $vars ){
			return ($lifetime <= $countdown && $lifetime >=0 ) || ( $etime - time() >0 && $lifetime<=0 ) ? $webex : false;
		}else{
			return ($lifetime <= $countdown && $lifetime >=0 ) || ( $etime - time() >0 && $lifetime<=0 ) ? true : false;
		}
		
	}

function encrypt($encrypt, $salt = '')
{
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
	$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, MD5($salt), $encrypt, MCRYPT_MODE_ECB, $iv);
	$encrypted = base64_encode($passcrypt);
	return trim($encrypted);
}

function decrypt($decrypt, $salt = '')
{
	$decoded = base64_decode($decrypt);
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
	$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, MD5($salt), $decoded, MCRYPT_MODE_ECB, $iv);
	return trim($decrypted);
}

function GenPassword($key, $password)
{
	return md5(substr(sha1($key), 0, 16) . $password);
}


	function param($name){
		if(isset($_POST[$name])) return $_POST[$name];
		elseif(isset($_GET[$name])) return $_GET[$name];
		else return false;
	}

	function fatal_error( $text ){
		echo "<meta charset=\"utf-8\">";
		die($text);
	}
	
	function forward( $url ){
		if(BACKEND) header("Location:/admin/{$url}");
		else header("Location:{$url}");
		exit;
	}
	
	function alert($msg , $redirect = false){
			
			echo "<meta charset=\"utf-8\">";
			echo "<script>";
			echo "alert(\"{$msg}\");";	
			if($redirect)
				echo "document.location = \"{$redirect}\";";
			echo "</script>";				
			exit;
	}
	
	function json_alert($msg){
		die( json_encode( (object) array('code'=>0, 'data' => $msg) ));
	}
	
	function json_bool( $bool ){
		die( json_encode( (object) array('code'=> $bool ? 1 : 0 ) ));
	}
	
	function dateformat($value,$format='Y-m-d H:i:s'){
		return substr($value,0,4)==0 ? '' : date($format,strtotime($value));
	}
	
	function is_date($date){
		return substr($date,0,4)==0 ? false : true ;
	}
	
	function datetime(){
		return date('Y-m-d H:i:s');
	}
	
	function week($time){
		if(!$time) $time = time();
		$week = array(0=>'日',1=>'一',2=>'二',3=>'三',4=>'四',5=>'五',6=>'六');
		return $week[date('w',$time)];
	}
	
	function gen_url($ary){
		$array = array();
		foreach($ary as $key=>$value){
			if($key!='cursor')
				$array[] = $key."=".$value;
		}
		return implode('&',$array);
	}

	function load_component( $name , $front_end = false ){

		$path = $front_end ? DOCUMENT_ROOT. 'lib' . DS :  DOCUMENT_ROOT. 'admin'. DS. 'lib' . DS;
		$classname = $name.'Component';
		if(!class_exists($classname))
			require_once( $path. strtolower($name) .'.php' );
		return new $classname;
	}
	
	function load_helper( $name , $front_end = false ){
		
		$path = $front_end ? DOCUMENT_ROOT. 'lib' . DS :  DOCUMENT_ROOT. 'admin'. DS. 'lib' . DS;
		$classname = $name.'Component';
		if(!class_exists($classname))
			require_once( $path. strtolower($name) .'.helper.php' );	
		return new $classname;
	}
	
	function load_vendor($name , $front_end = false){
		
		$path = $front_end ? DOCUMENT_ROOT. 'vendor' . DS :  DOCUMENT_ROOT. 'admin'. DS. 'vendor' . DS;
		$file = $path. strtolower($name) . DS . 'index.php';
		if(file_exists($file)) require_once($file);
		else die("Vendor：$file 檔案不存在");
		return $vendor;
	}
	
	function pr($array){
		echo "<pre>";
		print_r($array);
		echo "</pre>";
	}
	
		function param_check(){
			
			if(count($_GET)){
				foreach($_GET as $key=>$value){
					if(is_array($value)){
						foreach($value as $key2=>$value2){
							$_GET[$key][$key2]= addslashes($value2);
						}
					}else{
						$_GET[$key]= addslashes($value);
					}
				}
			}
			if(count($_POST)){
				foreach($_POST as $key=>$value){
					if(is_array($value)){
						foreach($value as $key2=>$value2){
							$_POST[$key][$key2]= addslashes($value2);
						}
					}else{
						$_POST[$key]= addslashes($value);
					}
				}
			}
		}
?>