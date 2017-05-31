<?php
	class Page extends APP{
		
		var $layout = false;
		var $mid = false;	//enterprise id
		var $perpage = 10;
		var $registration_type = array( 10 =>'Demo',
										20 =>'One-on-one' ,
										30=> 'Group Class' );

		function init(){
			
			parent::init();			
			if($this->action=='index' || $this->action=='login' || $this->action=='logout' || $this->action=='forgot') return true;			
			if(!$this->check_login()) forward("/enterprise/index");
			$this->mid = $_SESSION['enterprise']['id'];
			$this->profile();
		}
		
		function check_login(){
			return isset($_SESSION['enterprise'])? true : false;
		}
		
		function login(){			

			if(!$this->recaptcha())
				die(json_encode((object) array('code'=>2,'msg'=>'Please check "I\'m not a robot".')));
			
			$accu = $_POST['account'];
			$pass = $_POST['password'];
			$keep = isset($_POST['keep']) ? true : false;
//			$filter = array('account'=>$accu,'password'=> GenPassword($accu,$pass) );
			$p = GenPassword($accu,$pass);
			$filter = "account = '$accu' AND `password` = '$p' AND status > 0";
//			echo $filter;
			$enterprise = $this->dbconn->fetch_one("enterprise",$filter);
			if(!$enterprise)
				die(json_encode((object) array('code'=>0,'msg'=> '帳號或密碼錯誤.')));
			if($enterprise['status']!=10)
				die(json_encode((object) array('code'=>0,'msg'=>'帳號暫停使用')));
			
			$_SESSION['enterprise'] = $enterprise;
			if($keep){
				setcookie('enterprise_account',$accu, (time() + (86400*365)), '/' );
				setcookie('enterprise_password', encrypt($pass, $accu) , (time() + (86400*365)), '/' );
			}else{
				setcookie('enterprise_account','', time()-1 );
				setcookie('enterprise_password','', time()-1 );
			}
			
			die(json_encode((object) array('code'=>1,'msg'=>'success')));
		}
	
		function logout(){
			unset($_SESSION['enterprise']);
			header("Location:/enterprise/index");
			exit;
		}
		
		function profile(){
			
			$filter = array( 'id' => $this->mid );
			$enterprise = $this->dbconn->fetch_one( 'enterprise', $filter );
			if($enterprise['status']!=10) alert('無法存取','/enterprise/logout');			
			$this->tpl->assign('enterprise',$enterprise);			
		}
	
		function index(){

			if($this->check_login()) header("Location:/enterprise/employee");
			$account = isset($_COOKIE['enterprise_account']) ? $_COOKIE['enterprise_account'] : '';
			$passwd  = isset($_COOKIE['enterprise_password']) ? decrypt($_COOKIE['enterprise_password'], $account) : '';			
			$this->tpl->assign('enterprise_account', $account );
			$this->tpl->assign('enterprise_password',$passwd );			
			$this->tpl->assign('CAPCHA_PUBLIC_KEY',CAPCHA_PUBLIC_KEY);
			$this->render('enterprise/index');
		}		

		function employee(){
			
			GLOBAL $var_registration_type;
			
			$filter = "WHERE enterprise_id=".$this->mid." AND status=10 AND deleted=0 ";
			if(param('keyword')){
				$kw = param('keyword');
				$filter.= "AND ( member_name like '%$kw%' OR account like '%$kw%' OR CONCAT(first_name,last_name) like '%$kw%') ";
			}
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->enterprise = true;
			$this->paginate->url = "/enterprise/employee?keyword=".param('keyword');
			$sql = "SELECT COUNT(*) FROM member ".$filter;
			$stm  = $this->dbconn->query($sql);
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql = "SELECT id,first_name,last_name, member_name ,account,point FROM member ".$filter;
			$sql.= "ORDER BY id DESC LIMIT $cursor,".$this->perpage;		
			$stm = $this->dbconn->query($sql);
			$data = array();
			while($member = $stm->fetch(PDO::FETCH_ASSOC)){
				
				//合約到期日
				$sql = "SELECT max(end) as end FROM member_contract ";
				$sql.= "WHERE member_id=".$member['id']." AND status=20 ";				
				$sql.= "AND open_time != '0000-00-00 00:00:00' ";
				
				$stm2 = $this->dbconn->query( $sql );
				$contract = $stm2->fetch(PDO::FETCH_ASSOC);
				$member['expire']= $contract['end'] ? $contract['end']: '';				
				array_push($data,$member);
			}
			$this->tpl->assign('data',$data);
			$this->render('enterprise/employee');
		}
		
		function employee_view(){
			
			GLOBAL $var_registration_type;
			unset($var_registration_type[99]);
			$this->tpl->assign('registration_type', $var_registration_type );
			
			$interest = $this->dbconn->fetch_assoc('interest','id','title','status=10','rank ASC');
			$this->tpl->assign('interest', $interest );
			
			$id = $this->param;
			$employee = $this->dbconn->fetch_one('member', array('id'=>$id,'enterprise_id'=>$this->mid));
			
			$sql = "SELECT cr.classroom_id, DATE_FORMAT(c.open_time,'%Y-%m-%d') as open_time, cr.attend, c.point, mt.title ";
			$sql.= "FROM course_registration cr, classroom c LEFT JOIN material mt ON mt.id = c.material_id ";
			$cnt = "SELECT COUNT(*) FROM course_registration cr, classroom c  LEFT JOIN material mt ON mt.id = c.material_id ";
			
			$filter = "WHERE cr.classroom_id= c.id AND cr.member_id=".$employee['id']." AND c.status=10  AND c.type!=99 AND cr.status=10 AND c.open_time <= NOW() ";
			
			//日期範圍
			if( preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['sdate']) ){
				$sdate = date('Y-m-d',strtotime($_GET['sdate']));
				$edate = date('Y-m-d',strtotime($_GET['sdate']));
				$filter.= " AND c.open_time BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59'";
			}
			
			//課程類型
			if(isset($_GET['type']) && isset($var_registration_type[$_GET['type']])){
				$filter.= " AND c.`type`=".$_GET['type'];
			}
			
			//課程主題
			if(isset($_GET['interest']) && isset($interest[$_GET['interest']])){
				$filter.= " AND mt.id IN ( SELECT material_id FROM material_interest WHERE interest_id=".$_GET['interest']." ) ";
			}
			
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->enterprise = true;
			$this->paginate->url = "/enterprise/employee_view/".$id."?type=".param('type')."&interest=".param('interest'); //."&sdate=".param('sdate')
			$stm  = $this->dbconn->query( $cnt.$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );
			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql = $sql.$filter." ORDER BY c.open_time DESC LIMIT $cursor,".$this->perpage;			
			$stm = $this->dbconn->query($sql);
			$data = $stm->fetchAll(PDO::FETCH_ASSOC);

			$this->tpl->assign('data',$data);
			$this->tpl->assign('employee',$employee);
			$this->render('enterprise/employee_view');
		}
		
		function classes(){
			
			GLOBAL $var_registration_type;
			unset($var_registration_type[99]);
			$this->tpl->assign('registration_type', $var_registration_type );
			
			$interest = $this->dbconn->fetch_assoc('interest','id','title','status=10','rank ASC');
			$this->tpl->assign('interest', $interest );
			
			$id = $this->param;
			$employee = $this->dbconn->fetch_one('member', array('id'=>$id,'enterprise_id'=>$this->mid));
			
			$sql = "SELECT cr.classroom_id, DATE_FORMAT(c.open_time,'%Y-%m-%d') as open_time, cr.attend, c.point, mt.title, mm.first_name, mm.last_name, mm.account, mm.member_name ";
			$sql.= "FROM course_registration cr , member mm,";
			$sql.= "classroom c LEFT JOIN material mt ON mt.id = c.material_id ";
			$cnt = "SELECT COUNT(*) FROM course_registration cr, member mm,  classroom c  LEFT JOIN material mt ON mt.id = c.material_id ";
			
			$filter = "WHERE mm.id = cr.member_id AND cr.classroom_id= c.id AND mm.enterprise_id=".$this->mid." AND c.status=10  AND c.type!=99 AND cr.status=10 AND c.open_time <= NOW() ";
			
			//日期範圍
			if( preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['sdate']) ){
				$sdate = date('Y-m-d',strtotime($_GET['sdate']));
				$edate = date('Y-m-d',strtotime($_GET['sdate']));
				$filter.= " AND c.open_time BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59'";
			}
			
			//課程類型
			if(isset($_GET['type']) && isset($var_registration_type[$_GET['type']])){
				$filter.= " AND c.`type`=".$_GET['type'];
			}
			
			//課程主題
			if(isset($_GET['interest']) && isset($interest[$_GET['interest']])){
				$filter.= " AND mt.id IN ( SELECT material_id FROM material_interest WHERE interest_id=".$_GET['interest']." ) ";
			}
			
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->enterprise = true;
			$this->paginate->url = "/enterprise/classes?type=".param('type')."&interest=".param('interest'); //."&sdate=".param('sdate')
			$stm  = $this->dbconn->query( $cnt.$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );
			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql = $sql.$filter." ORDER BY c.open_time DESC LIMIT $cursor,".$this->perpage;			
			$stm = $this->dbconn->query($sql);
			$data = $stm->fetchAll(PDO::FETCH_ASSOC);

			$this->tpl->assign('data',$data);
			$this->tpl->assign('employee',$employee);
			$this->render('enterprise/classes');
		}
		
		function account(){
			$data = $this->dbconn->fetch_one('enterprise','id='.$this->mid);			
			$this->tpl->assign('data',$data);
			$this->render('enterprise/account');
		}
		
		function account_save(){
			
			$form = $_POST;				
			$form['mdate'] = date('Y-m-d H:i:s');
			if(!empty($form['passwd']))
				$form['password'] = GenPassword( $_SESSION['enterprise']['account'] ,$_POST['passwd']);
				
			unset($form['passwd']);
			unset($form['passwd2']);
			$filter = array('id'=> $this->mid );
			$num = $this->dbconn->update('enterprise', $form, $filter);
			$msg = $num ? "更新完成!" : "更新失敗";
			die(json_encode((object) array('code'=>1,'msg'=>$msg)));

		}
		

		function forgot(){
			
			if(isset($_POST['account'])){
				$this->layout = false;
				$accu = $_POST['account'];
				$member = $this->dbconn->fetch_one('enterprise', array('account'=>$accu) );
				if(!$member)
					die(json_encode((object) array('code'=>0,'msg'=>'帳號錯誤')));
				
				$passwd = substr(MD5(time()),0,8);
				$form = array( 'password'=> GenPassword($accu,$passwd));
				$filter = array( 'account'=> $accu );
				$rs = $this->dbconn->update('enterprise', $form, $filter );
				if($rs){
					if($this->sendmail( '忘記密碼', "您的新密碼：".$passwd , $member['email'], $member['ent_name']  ) )
						die(json_encode((object) array('code'=>1,'msg'=>'您的密碼已經發送到您的電子信箱.')));
					else
						die(json_encode((object) array('code'=>0,'msg'=>'發信失敗')));
				}else
					die(json_encode((object) array('code'=>0,'msg'=>'重寄失敗')));
			}

			$this->tpl->assign('CAPCHA_PUBLIC_KEY',CAPCHA_PUBLIC_KEY);
			$this->render('enterprise/forgot');
		}	

		
	}
?>