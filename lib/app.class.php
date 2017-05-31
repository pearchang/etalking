<?php
	class APP {
		
		var $id; //page id;
		var $alias = false;
		var $controller ='';
		var $action = '';
		var $param = '';
		var $dbconn;
		var $content_for_layout;
		var $layout = true;
		var $table = false;		
		var $data = array();	
		var $tpl = false;
		var $url = '';
	
		function init(){
			$this->tpl = load_vendor('raintpl',true);
		}
		
		function check_login(){
			return isset($_SESSION['member'])? true : false;
		}
		
		function render( $file = 'layout', $fetch = false ){
			$f = 'template/'.$file .'.html';
			if(!file_exists( $f ))
				 $this->content_for_layout.= $f." 檔案不存在";
			else{
				if($fetch)	return $this->tpl->draw($file,true);
				else $this->content_for_layout.= $this->tpl->draw($file,true);
			}
		}
		
		function render_layout(){				
				
				GLOBAL $var_contact_time;
				$this->tpl->assign('var_contact_time',$var_contact_time);
				
				$this->tpl->assign('CAPCHA_PUBLIC_KEY',CAPCHA_PUBLIC_KEY);
				
				$this->tpl->assign('MEMBER_LOGIN', $this->check_login() );
				
				$account = isset($_COOKIE['account']) ? $_COOKIE['account'] : '';
				$passwd  = isset($_COOKIE['password']) ? decrypt($_COOKIE['password'], $account) : '';
				
				$this->tpl->assign('MEMBER_ACCOUNT', $account );
				$this->tpl->assign('MEMBER_PASSWORD',$passwd );
				
				$this->tpl->assign('CONTENT_FOR_LAYOUT',$this->content_for_layout);
				$this->tpl->draw('layout');
		}
		
		function output(){
			
			if ($this->layout){
				$this->render_layout();
			}else{
				echo $this->content_for_layout;
			}
		}

		function recaptcha(){
			
			$url = "https://www.google.com/recaptcha/api/siteverify";
			$params['secret'] = CAPCHA_PRIVATE_KEY;
			$params['response'] = $_POST['g-recaptcha-response'];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query( $params ));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
			$result = curl_exec($ch);
			curl_close($ch);
			return $result;
		}
		
		function sendmail( $subject, $body, $email, $name ){
			
			$config_email = $this->dbconn->fetch_one('config','id=1');
			$config_name = $this->dbconn->fetch_one('config','id=2');
			
			$mail = load_vendor('phpmailer', true);
			$mail->From = $config_email['content'];
			$mail->FromName = $config_name['content'];
			$mail->Subject = mb_encode_mimeheader( $subject ,'UTF-8');
			$mail->addAddress( $email, $name);
			$mail->Body = $body;
			if($mail->send()) return true;
			else{
				//echo $mail->ErrorInfo;
				return false;
			}
		}
		
	}
?>