<?php
	class Page extends APP{
		
		var $hash_key    = AllPay_HashKey;
		var $hash_iv     = AllPay_HashIV;
		var $merchant_id = AllPay_Merchant_Id;
		var $service_url      = '/allpay/CheckOutFeedback';
		var $client_back_url  = '/student/contract';
		var $order_result_url = '/allpay/CheckOutFeedback';
		
		//測試環境
		//var $gateway_url = "http://payment-stage.allpay.com.tw/Cashier/AioCheckOut";		
		//正式環境
//		var $gateway_url = "https://payment.allpay.com.tw/Cashier/AioCheckOut/V2";
		var $gateway_url = "https://payment.ecpay.com.tw/Cashier/AioCheckOut/V2";
		
		function init(){	
			parent::init();
			$this->service_url = HTTP_ROOT. $this->service_url;
			$this->client_back_url = HTTP_ROOT. $this->client_back_url;
			$this->order_result_url = HTTP_ROOT. $this->order_result_url;
		}
		
		function pay(){
			
			$this->layout = false;
			
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
			
			include('lib/AllPay.Payment.Integration.php');
			
			try {       
				$obj = new AllInOne();
   
				//服務參數
				$obj->ServiceURL  = $this->gateway_url;
				$obj->HashKey     = $this->hash_key;
				$obj->HashIV      = $this->hash_iv;
				$obj->MerchantID  = $this->merchant_id;
				
				//基本參數
				$obj->Send['ReturnURL']         = $this->service_url ;		//付款完成通知回傳的網址
				$obj->Send['MerchantTradeNo']   = $bill['sn'].$bill['check']; //訂單編號
				$obj->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');		//交易時間
				$obj->Send['TotalAmount']       = $bill['total'];			//交易金額
				$obj->Send['TradeDesc']         = 'Contract';				//交易描述
				$obj->Send['ChoosePayment']     = PaymentMethod::Credit;	 //付款方式:ALL=全功能
				//$obj->Send['Remark']     		= //備註
				$obj->Send['ClientBackURL']	= HTTP_ROOT. '/student/contract';
				$obj->Send['OrderResultURL']	= $this->order_result_url;	//信用卡
				
				if($bill['payment']==30){
					$obj->SendExtend['CreditInstallment'] = $bill['installment'];
					$obj->SendExtend['InstallmentAmount'] = $bill['total'];
					$obj->SendExtend['Redeem'] = false;
					$obj->SendExtend['UnionPay'] = false;					
				}
				
				//訂單的商品資料
				array_push($obj->Send['Items'], array(
							'Name' => $contract['contract_name'],
							'Price' => (int) $bill['total'],
							'Currency' => "元",
							'Quantity' => (int) "1"));

				$log = "upload/log/allpay.".$bill['sn'].$bill['check'].".txt";
			
				ob_start();
				echo "\n".date('Y-m-d H:i:s')."\n";
				print_r($obj);
				file_put_contents($log ,ob_get_contents(), FILE_APPEND );
				ob_clean();			
							
				//產生訂單(auto submit至AllPay)
				$obj->CheckOut();
    
			} catch (Exception $e) {
				echo $e->getMessage();
			}						
		}	

		
		function CheckOutFeedback(){
			
			$this->layout = false;
			
			$log = "upload/log/allpay.".$_POST['MerchantTradeNo'].".txt";
			
			ob_start();
			echo "\n".date('Y-m-d H:i:s')."\n";
			print_r($_POST);
			file_put_contents($log ,ob_get_contents(), FILE_APPEND );
			ob_clean();
			
			include('lib/AllPay.Payment.Integration.php');
	
			try
			{
				$oPayment = new AllInOne();
				
				/* 服務參數 */
				$oPayment->HashKey		= $this->hash_key;
				$oPayment->HashIV		= $this->hash_iv;
				$oPayment->MerchantID	= $this->merchant_id;
				
				/* 取得回傳參數 */
				$arFeedback = $oPayment->CheckOutFeedback();
				
				/* 檢核與變更訂單狀態 */
				if ($arFeedback['RtnCode']==1) {
					
					$filter = "sn = '".substr($_POST['MerchantTradeNo'],0,11)."' AND status=10";
					$bill 	  = $this->dbconn->fetch_one('member_contract_bill', $filter, false);
					if(!$bill){
						file_put_contents($log ,'Bill is not exists!', FILE_APPEND );
						die('Bill is not exists!');
					}
					
					$contract = $this->dbconn->fetch_one('member_contract', 'id='.$bill['contract_id'], false);					
					if(!$contract ){
						file_put_contents($log ,'Contarct is not exists!', FILE_APPEND );
						die('Contarct is not exists!');
					}
					
					$values = array( 'paid_time'	=> str_replace('/','-',$_POST['PaymentDate']),
									 'paid'			=> 10,
									 'TradeNo'		=> $_POST['TradeNo'],
									 'PaymentType'	=> $_POST['PaymentType'],
									 'mdate'		=> date('Y-m-d H:i:s'),
									 'cc_type'		=> 10
									);
					$filter = array( 'id'=> $bill['id'],'total'=> $_POST['TradeAmt'] );
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
					
					alert('付款完成','/student/contract');
					
				} else {
					file_put_contents($log ,'checksum fail!', FILE_APPEND );
					alert('付款失敗','/student/contract');
				}
			}
			catch (Exception $e)
			{
				// 例外錯誤處理。
				file_put_contents($log , $e->getMessage() , FILE_APPEND );
				print '付款失敗' . $e->getMessage();
			}
		}
		
	}
?>