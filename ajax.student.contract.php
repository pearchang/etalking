<?php
			include 'config.php';
			include 'ajax.config.php';
			
			$mid = $_SESSION['member']['id'];
			
			$filter = array( 'id' => $_GET['id'] , 'member_id' => $mid );
			$contract = $dbconn->fetch_one('member_contract', $filter);
			if(!$contract) die(json_encode((object) array('code'=>0,'msg'=> '編號錯誤')));
			
			$form = array('signed'=>10, 'sign_time' => date('Y-m-d H:i:s') );
			$filter = array( 'id' => $_GET['id'] , 'member_id' => $mid );
			$rs = $dbconn->update('member_contract', $form , $filter );			
			
			$url = etalking_function.'?f=activate_contract&member_id='.$contract['member_id'].'&contract_id='.$contract['id'];
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$data = curl_exec($ch);
			curl_close($ch);	
			
			if($rs){				
				die(json_encode((object) array('code'=> 1 ,'msg'=> '' )));
			}else{
				die(json_encode((object) array('code'=>0,'msg'=> '簽約失敗')));
			}

?>