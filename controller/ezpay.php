<?php
	class Page extends APP{
		
		//以下正式環境使用
		var $CC_MerchantNumber = '761193';
		var $CC_MerchantNumber_Period = '715511';
		var $CC_Code = 'dcP49dXy';
		var $CC_Code_Period = 'cFP96dgt';
		var $CC_url = "https://taurus.neweb.com.tw/NewebmPP/cdcard.jsp";
		
		//以下測試環境使用
		/*
		var $CC_MerchantNumber = '761193';
		var $CC_MerchantNumber_Period = '715511';
		var $CC_Code = 'abcd1234';
		var $CC_Code_Period = 'abcd1234';
		var $CC_url = "https://testmaple2.neweb.com.tw/NewebmPP/cdcard.jsp";
		*/
		
		var $layout = false;
		
		function creditCard($order_number,$amount, $installment){	//刷卡			
			
			$MerchantNumber = $installment==0 ? $this->CC_MerchantNumber : $this->CC_MerchantNumber_Period;
			$Code = $installment==0 ? $this->CC_Code : $this->CC_Code_Period;
			$OrderUrl = HTTP_ROOT.'/ezpay/creditCardFinish';
			$ReturnURL = HTTP_ROOT.'/ezpay/creditCardFeedback';
			$action = $this->CC_url;
			
			$form['MerchantNumber'] = $MerchantNumber;
			$form['OrderNumber'] = $order_number;
			$form['Amount'] = $amount.'.00';			
			$form['OrgOrderNumber']= $order_number;			
			$form['ApproveFlag']=1;
			$form['DepositFlag']=0;
			$form['Englishmode']=0;
			$form['iphonepage']=0;
			$form['op']='AcceptPayment';
			$form['OrderURL'] = $OrderUrl;
			$form['ReturnURL'] = $ReturnURL;
			$form['checksum'] = MD5( $form['MerchantNumber'].$form['OrderNumber'].$Code.$form['Amount'] );			
			$form['Period']= $installment;
			
			$log = "upload/log/ezpay.".$order_number.".txt";
			
			ob_start();
			echo "\n".date('Y-m-d H:i:s')."\n";
			print_r($form);
			file_put_contents($log ,ob_get_contents(), FILE_APPEND );
			ob_clean();
			
			$html = "<form method=POST id=payment action='$action'>\n";
			foreach($form as $key => $val) $html.= "<input type='hidden' name='{$key}' value='{$val}'>\n";			
			$html.= "</form>\n";
			$html.= "<script>document.getElementById('payment').submit();</script>";
			return $html;
		}
		
		function creditCardResult( $MN, $ON, $PRC, $SRC, $Amount, $CS ){//交易結果
			$cc = $MN == $this->CC_MerchantNumber ? $this->CC_Code : $this->CC_Code_Period;
			$chkstr = md5( $MN.$ON.$PRC.$SRC.$cc.$Amount );
			if( strtolower($CS) != strtolower($chkstr) ) return false;
			return $PRC == "0" && $SRC== "0" ? true : false;
		}
		
		function creditCardReturnResult( $MN, $ON, $RR, $PRC, $SRC, $Amount, $CS){//交易結果
			$cc = $MN == $this->CC_MerchantNumber ? $this->CC_Code : $this->CC_Code_Period;
			$chkstr = md5( $MN.$ON.$RR.$PRC.$cc.$SRC.$Amount );
			if( strtolower($CS) != strtolower($chkstr) ) return false;
			return $PRC == "0" && $SRC== "0" ? true : false;
		}
		
		function pay(){
			
			$filter =  array('id' => $this->param,
							 'paid'=>0,
							 'status'=>10);			
			$bill = $this->dbconn->fetch_one('member_contract_bill', $filter, 'id ASC' );			
			if(!$bill) die("Bill is not exists!");			
			
			$filter =  array('id'=> $bill['contract_id'], 
							'member_id' => $_SESSION['member']['id'],
							'paid_time'=>'0000-00-00 00:00:00');
			$contract = $this->dbconn->fetch_one('member_contract', $filter, false );
			if(!$contract) die("Contract is not exists!");

			$bill['check']++;
			$values = array('check'=>$bill['check']);
			$this->dbconn->update('member_contract_bill', $values ,array('id' => $bill['id']));
			
			echo $this->creditCard( $bill['sn'].$bill['check'] , $bill['total'], $bill['payment']==20 ? 0 : $bill['installment'] );
			
		}
		
		function creditCardFeedback(){
	
			if( $this->creditCardReturnResult($_POST['P_MerchantNumber'],
										$_POST['P_OrderNumber'],
										$_POST['final_result'],
										$_POST['final_return_PRC'],
										$_POST['final_return_SRC'],
										$_POST['P_Amount'],
										$_POST['P_CheckSum'] ) ){				
				alert('付款完成','/student/contract');						
			}else{
				alert('付款失敗','/student/contract');
			}			
		}
		
		function creditCardFinish(){
			
			$this->layout = true;
			
			$log = "upload/log/ezpay.".$_POST['OrderNumber'].".txt";
			
			ob_start();
			echo "\n".date('Y-m-d H:i:s')."\n";
			print_r($_POST);
			file_put_contents($log ,ob_get_contents(), FILE_APPEND );
			ob_clean();			
			
			if( $this->creditCardResult($_POST['MerchantNumber'],
										$_POST['OrderNumber'],										
										$_POST['PRC'],
										$_POST['SRC'],
										$_POST['Amount'],
										$_POST['CheckSum'] ) ){
				
				$filter = array( 'sn'=> substr($_POST['OrderNumber'],0,11), 'status'=>10  );
				$bill = $this->dbconn->fetch_one('member_contract_bill', $filter, false);
				if(!$bill){
						file_put_contents($log ,'Bill is not exists!', FILE_APPEND );
						die('Bill is not exists!');
				}
					
				$contract = $this->dbconn->fetch_one('member_contract', 'id='.$bill['contract_id'], false);					
				if(!$contract ){
						file_put_contents($log ,'Contarct is not exists!', FILE_APPEND );
						die('Contarct is not exists!');
				}
					
				$values = array( 'paid_time'	=> date('Y-m-d H:i:s'),
								 'paid'			=> 10,
								 'TradeNo'		=> '',
								 'PaymentType'	=> '',
								 'mdate'		=> date('Y-m-d H:i:s'),
								 'cc_type'		=> 20
								);								
				$filter = array( 'id'=> $bill['id'] );
				$num = $this->dbconn->update('member_contract_bill', $values, $filter);
				if(!$num){
						file_put_contents($log ,'Bill update fail!', FILE_APPEND );
						die('Bill update fail!');
				}
				
				include 'ajax.config.php';
				$url = etalking_function.'?f=activate_contract&member_id='.$contract['member_id'].'&contract_id='.$contract['id'];
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				curl_close($ch);				
			
				//alert('付款完成','/student/contract');				

			}else{
				file_put_contents($log ,'checksum fail!', FILE_APPEND );
				//alert('付款失敗','/student/contract');
			}

		}
		
	}
?>
