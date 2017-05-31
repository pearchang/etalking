<?php
	class Page extends APP{

		function login(){
			
			$this->layout = false;
			if(!$this->recaptcha())
				die(json_encode((object) array('code'=>2,'msg'=>'驗證碼錯誤!')));
			
			$accu = $_POST['account'];
			$pass = $_POST['password'];
			$keep = isset($_POST['keep']) ? true : false;
//			$filter = array('account'=>$accu,'password'=> GenPassword($accu,$pass) );
			$p = GenPassword($accu,$pass);
			$filter = "account = '$accu' AND `password` = '$p' AND status > 0";
			$member = $this->dbconn->fetch_one("member",$filter);
			if(!$member)
				die(json_encode((object) array('code'=>0,'msg'=>'輸入錯誤請重新確認您的帳號與密碼!')));
			if($member['status']!=10 && $member['status']!=30)
				die(json_encode((object) array('code'=>0,'msg'=>'帳號目前無法使用!')));
			
			if($member['status']==30){
				$this->dbconn->update('member',array('status'=>10),array('id'=>$member['id']));
				$_SESSION['member']['status']=10;
			}
			
			$_SESSION['member'] = $member;
			if($keep){
				setcookie('account', $accu, (time() + (86400*365)), '/' );
				setcookie('password', encrypt($pass, $accu ), (time() + (86400*365)), '/' );
			}else{
				setcookie('account','', time()-1 );
				setcookie('password','', time()-1 );
			}
			die(json_encode((object) array('code'=>1,'msg'=>'success')));
		}
	
		function forgot(){
			
			if(isset($_POST['account'])){
				$this->layout = false;
				/*
				if(!$this->recaptcha())
					die(json_encode((object) array('code'=>2,'msg'=>'驗證碼錯誤!')));*/
				$accu = $_POST['account'];
				
				$member = $this->dbconn->fetch_one('member', array('account'=>$accu) );
				if(!$member)
					die(json_encode((object) array('code'=>0,'msg'=>'ID不正確')));
				
				$passwd = substr(MD5(time()),0,8);
				$form = array( 'password'=> GenPassword($accu,$passwd));
				$filter = array( 'account'=> $accu );
				$rs = $this->dbconn->update('member', $form, $filter );
				if($rs){
					if($this->sendmail( '新密碼通知信', "新密碼為：".$passwd , $member['email'], $member['member_name']  ) )
						die(json_encode((object) array('code'=>1,'msg'=>'密碼重新發送成功!'))); //Your password has been sent to your E-mail address.
					else
						die(json_encode((object) array('code'=>0,'msg'=>'密碼重新發送失敗!')));
				}else
					die(json_encode((object) array('code'=>0,'msg'=>'密碼重置失敗'))); //Not a valid ID
			}

			$this->tpl->assign('CAPCHA_PUBLIC_KEY',CAPCHA_PUBLIC_KEY);
			$this->render('member/forgot');
		}
		
		function logout(){
			unset($_SESSION['member']);
			header("Location:/");
			exit;
		}
		


		
	}
?>