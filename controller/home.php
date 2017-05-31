<?php
	class Page extends APP{
	
		function index(){
		  $sql = "SELECT * FROM banner WHERE status = 10 ORDER BY cdate DESC";
      $stm = $this->dbconn->query( $sql );
      unset ($v);
      while (($r = $stm->fetch(PDO::FETCH_ASSOC)))
      {
//        print_r($r);
        $sql = "SELECT * FROM banner_images WHERE parent = {$r['id']} ORDER BY tag";
        $stm2 = $this->dbconn->query( $sql );
        $r['images'] = $stm2->fetchAll();
        $v[] = $r;
      }

//      print_r($v);
      $this->tpl->assign('banner',$v);

      $this->render('home/index');
		}

		function experience(){
			
			$this->layout = false;
			$now = date('Y-m-d H:i:s');
			$_POST['cdate'] = $now;
			$_POST['mdate'] = $now;
			$_POST['status'] = 10;			
			
//			$num = $this->dbconn->fetch_count( "request", " (email='".$_POST['email']."' OR tel='".$_POST['tel']."') AND status=10");
			$num = $this->dbconn->fetch_count( "request", " (tel='".$_POST['tel']."')");
			
			if($num>0){
				die(json_encode((object) array('code'=>0,'msg'=>'請勿重複申請')));
			}else{
				$num = $this->dbconn->fetch_count( "member", "(email='".$_POST['email']."' OR mobile='".$_POST['tel']."') AND deleted=0");
				if($num>0)
					die(json_encode((object) array('code'=>0,'msg'=>'此資料已申請預約，請耐心等候課程顧問與您聯繫')));
			}
			
			$newid = $this->dbconn->insert( 'request', $_POST );
			
			if($newid>0){
				//通知信
				GLOBAL $EMAIL_SUBJECT_REQUEST;
				$email = $this->dbconn->fetch_one('config','id=3');
				$email = explode(',',$email['content']);
				if(count($email)){
					foreach($email as $addr ){
						$this->sendmail( $EMAIL_SUBJECT_REQUEST, $EMAIL_SUBJECT_REQUEST.'：'.$_POST['guest_name'], $addr, $addr );
					}
				}
				die(json_encode((object) array('code'=>1,'msg'=>'success')));
			}else
				die(json_encode((object) array('code'=>0,'msg'=>'報名失敗，請稍候再試')));
		}
		
		function contact_us(){
			
			$this->layout = false;	
			
			$body = "姓名：".$_POST['full_name']."<br>";
			$body.= "電話：".$_POST['phonenumber']."<br>";
			$body.= "信箱：".$_POST['email']."<br>";
			$body.= "時間：".$_POST['contact_time']."<br>";
			
			GLOBAL $EMAIL_SUBJECT_CONTACT_US;
			$email = $this->dbconn->fetch_one('config','id=7');
			$email = explode(',',$email['content']);
			$counter = 0;
			if(count($email)){
					foreach($email as $addr ){
						if( $this->sendmail( $EMAIL_SUBJECT_CONTACT_US, $body, $addr, $addr )) $counter++;
					}
			}
			die(json_encode((object) array('code'=>1,'msg'=> $counter)));		
		}
		
		function about(){
			$this->render('home/about');
		}
		
		function faq(){
			$this->render('home/faq');
		}
		
		function classes(){
			$this->render('home/classes');
		}
		
		function cefr(){
			$this->render('home/cefr');
		}
		
		function business(){
			$this->render('home/business');
		}
		
		function privacy(){
			$this->render('home/privacy');
		}
		
		
		
	}
?>