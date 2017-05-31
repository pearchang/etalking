<?php
	class Page extends APP{
		
		var $layout = false;
		var $mid = false;	//teacher id
		var $perpage = 10;
		var $registration_type;

		function init(){
			
			parent::init();			
			if($this->action=='index' || $this->action=='login' || $this->action=='logout' || $this->action=='forgot') return true;			
			if(!$this->check_login()) forward("/teacher/index");
			$this->mid = $_SESSION['teacher']['id'];
			$this->profile();
			
			GLOBAL $var_registration_type_eng;
			$this->registration_type = $var_registration_type_eng;
		}
		
		function check_login(){
			return isset($_SESSION['teacher'])? true : false;
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
			$teacher = $this->dbconn->fetch_one("consultant",$filter);
			if(!$teacher)
				die(json_encode((object) array('code'=>0,'msg'=> 'Invalid acccount or password.')));
			if($teacher['status']!=10)
				die(json_encode((object) array('code'=>0,'msg'=>'Yor account has been locked.')));
			
			$_SESSION['teacher'] = $teacher;
			if($keep){
				setcookie('teacher_account',$accu, (time() + (86400*365)), '/' );
				setcookie('teacher_password', encrypt($pass, $accu) , (time() + (86400*365)), '/' );
			}else{
				setcookie('teacher_account','', time()-1 );
				setcookie('teacher_password','', time()-1 );
			}
			
			die(json_encode((object) array('code'=>1,'msg'=>'success')));
		}
	
		function logout(){
			unset($_SESSION['teacher']);
			header("Location:/teacher/index");
			exit;
		}
		
		function profile(){
			
			$filter = array( 'id' => $this->mid );
			$teacher = $this->dbconn->fetch_one( 'consultant', $filter );
			if($teacher['status']!=10) alert('Access Deny.','/teacher/logout');			
			$this->tpl->assign('teacher',$teacher);			
		}
	
		function index(){

			if($this->check_login()) header("Location:/teacher/booking");
			$account = isset($_COOKIE['teacher_account']) ? $_COOKIE['teacher_account'] : '';
			$passwd  = isset($_COOKIE['teacher_password']) ? decrypt($_COOKIE['teacher_password'], $account) : '';			
			$this->tpl->assign('teacher_account', $account );
			$this->tpl->assign('teacher_password',$passwd );			
			$this->tpl->assign('CAPCHA_PUBLIC_KEY',CAPCHA_PUBLIC_KEY);
			$this->render('teacher/index');
		}
		
		function booking(){
			$this->render('teacher/booking');
		}

		function booking_ajax(){
			
			$this->layout = false;

			GLOBAL $var_registration_type_eng , $var_schedule_available_type;
			
			//區間			
			$week = isset($_GET['week']) && $_GET['week']>=1 && $_GET['week']<=4 ? $_GET['week'] : 1 ;
			$this->tpl->assign("previous", $week==1 ? false : $week-1 );
			$this->tpl->assign("next", $week==4 ? false : $week+1 );
			
			$sdate = strtotime( date('Y-m-d').' 00:00:00' ) + ( 86400 * 7 * ($week-1) );			
			$edate = $sdate + ( 86400 * 6 );
						
			if( date('Y',$sdate)!= date('Y',$edate) )
				$this->tpl->assign("date_range", date("M d, Y",$sdate).' - '.date("M d, Y",$edate) );
			elseif( date('m',$sdate)!= date('m',$edate) )
				$this->tpl->assign("date_range", date("M d",$sdate).' - '.date("M d, Y",$edate) );
			else
				$this->tpl->assign("date_range", date("M d",$sdate).' - '.date("d, Y",$edate) );
			
			//左邊時段
			$hours = array();
			for ($j = BEGIN_TIME; $j <= END_TIME; $j++)
			{
				$h = sprintf("%02d", $j);
				$hours[$h]['start_time'] = "$h:00";
				$hours[$h]['end_time'] = "$h:45";
			}
			$this->tpl->assign('hours',$hours);
			
			$schedule = array();	
			/*
				0 過去時間 無選項
				1 已註冊可取消
				2 已註冊不可取消
				3 尚可註冊
			*/
			
			//已booking，固定			
			$sql = "SELECT `date`,`time`,`available`, `fixed` FROM consultant_schedule ";
			$sql.= "WHERE consultant_id=".$this->mid." AND `date` BETWEEN '".date('Y-m-d',$sdate)."' AND '".date('Y-m-d',$edate)."' AND `fixed` > 0 ";
			$stm = $this->dbconn->query($sql);			
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){
				$Ymd = str_replace('-','', $row['date'] );
				$h   = $row['time'];
				$schedule[$Ymd][$h] = 2;
				
				$sql = "SELECT `type` FROM classroom ";
				$sql.= "WHERE consultant_id=".$this->mid." AND `date`='".$row['date']."' AND `time`='".$row['time']."' ";
				$sql.= "AND status = 10 AND  consultant_confirmed= 10 AND type!=99";
				$stm2= $this->dbconn->query($sql);
				$cr = $stm2->fetch(PDO::FETCH_ASSOC);
				if($cr){
					$cr = $stm2->fetch(PDO::FETCH_ASSOC);
					if(isset($row['type']))
						$dynamic[$Ymd][$h] = $var_registration_type_eng[ $row['type'] ];
				}
			}	
			
			//已booking，浮動
			$dynamic = array();
			$sql = "SELECT `date`,`time`,`available` FROM consultant_schedule ";
			$sql.= "WHERE consultant_id=".$this->mid." AND `date` BETWEEN '".date('Y-m-d',$sdate)."' AND '".date('Y-m-d',$edate)."' AND available=20 AND fixed = 0 ";
			$stm = $this->dbconn->query($sql);			
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){
				$ymdh = strtotime( $row['date']." ".sprintf("%02d",$h).":00:00" );
				$Ymd = str_replace('-','', $row['date'] );
				$h   = $row['time'];
				
				$sql = "SELECT `type` FROM classroom ";
				$sql.= "WHERE consultant_id=".$this->mid." AND `date`='".$row['date']."' AND `time`='".$row['time']."' ";
				$sql.= "AND status = 10 AND  consultant_confirmed= 10  AND type!=99";
				$stm2= $this->dbconn->query($sql);
				$cr = $stm2->fetch(PDO::FETCH_ASSOC);
			
				if($cr){
					$dynamic[$Ymd][$h] = $var_registration_type_eng[ $cr['type'] ];
					$schedule[$Ymd][$h] = 2;
				}else{
					$schedule[$Ymd][$h] = $ymdh - time() >= 86400 ? 1 : 2; //24小時內無法取消
				}
			}

			//demo
			$demo = array();
			$sql = "SELECT open_time,`type` FROM classroom ";
			$sql.= "WHERE consultant_id=".$this->mid." AND open_time BETWEEN '".date('Y-m-d 00:00:00',$sdate)."' AND '".date('Y-m-d 23:59:59',$edate)."' ";
			$sql.= "AND status=10 AND `type`=10 ";
			$stm = $this->dbconn->query($sql);	
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){
				$ot =  strtotime($row['open_time']);
				$Ymd = date('Ymd', $ot );
				$h   = (int)date("H",$ot);
				$schedule[$Ymd][$h] = 2; //2 無法取消
				$dynamic[$Ymd][$h] = $var_registration_type_eng[$row['type']];
			}
			
			$this->tpl->assign('dynamic',$dynamic);
			
			//不開放
			$sql = "SELECT * FROM `holiday` WHERE `date` BETWEEN '".date('Y-m-d',$sdate)."' AND '".date('Y-m-d',$edate)."'";
			$stm = $this->dbconn->query($sql);
			$holiday = array();
			if($stm->RowCount()>0){
				while($row = $stm->fetch(PDO::FETCH_ASSOC)){
					$holiday[$row['date']][$row['time']]=true;
				}
			}
			//每日時段
			$data = array();
			$now = date('YmdH');
			for($i= $sdate; $i<= $edate; $i+=86400){
				$ymd = date('Ymd',$i);
				$w = date('w',$i);
				$hour = array();
				
			
				for ($j = BEGIN_TIME; $j <= END_TIME; $j++)
				{
					$timestamp = strtotime( date('Y-m-d',$i).' '.sprintf('%02d',$h).':00:00' );
					if( isset($holiday[date('Y-m-d',$i)][$j]) ||  ( $timestamp - time() <= 86400 && !isset($schedule[$ymd][$j]) ) )				
						$hour[$j] = 0;				
					else
						$hour[$j] = isset($schedule[$ymd][$j]) ? $schedule[$ymd][$j] : 3;
						
	
				}
				$data[$ymd] = array( 'date'=>date('Y-m-d',$i), 'week'=> date('N',$i) , 'title'=> date('D m/d',$i), 'hour' => $hour );
				
			}
			unset($schedule);
			$this->tpl->assign('data',$data);
			$result = $this->render('teacher/booking_ajax',true);
			die(json_encode((object) array('result'=> $result ))); 
			
		}
		
		function newclass(){
			
			$this->tpl->assign('regtype',0);
			$level = $this->dbconn->fetch_assoc('level','id','level_name','status=10',false);

			$sdate = strtotime(date("Y-m-d H:00:00")) + 46800;
			$edate = strtotime(date("Y-m-d H:00:00")) + 86400;

			$sql = "SELECT * FROM classroom ";
			$filter = "WHERE consultant_id=".$this->mid." AND open_time BETWEEN '".date('Y-m-d H:i:s',$sdate)."' AND '".date('Y-m-d H:i:s',$edate)."' ";
			$filter.= "AND consultant_confirmed=0 AND status=10 AND `type`>10  AND type!=99 ";

			//課程類型
			if(isset($_GET['regtype']) && $_GET['regtype'] >0 && isset( $this->registration_type[$_GET['regtype']] ) ){
				$filter.= "AND `type`=".$_GET['regtype']." ";
				$this->tpl->assign('regtype',$_GET['regtype']);
			}
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->student = false;
			$this->paginate->url = "/teacher/newclass?regtype=".param('regtype');
			$stm  = $this->dbconn->query( "SELECT COUNT(*) FROM classroom ".$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql = $sql.$filter." ORDER BY open_time ASC LIMIT $cursor,".$this->perpage;
			$stm = $this->dbconn->query($sql);
			$data = array();
			$edate_ymdh = date('YmdH',$edate);
			while($class = $stm->fetch(PDO::FETCH_ASSOC)){
				//日期
				$stime = strtotime( $class['open_time'] );
				$etime = strtotime( $class['open_time'] ) + (45*60);
				$class['date'] = array( 'date'=> date('Y/m/d',$stime) ,
										'week'=> date('l',$stime) ,
										'start_time' => date('H:i',$stime),
										'end_time' => date('H:i',$etime)
								);
				$class['type'] = $this->registration_type[$class['type']];
				
				//即將逾期
				$class['overdue'] = $stime == $sdate ? true : false;
				$class['level'] = isset($level[$class['level_id']]) ? $level[$class['level_id']]: $level[1];
				
				//教材
				if( $class['type']!=20 && $class['type']!=30){					
					$material = $this->dbconn->fetch_one('material','id='.$class['material_id'].' AND ( status=10 OR (status=0 AND deleted=1) )');
					$class['title'] = $material ? $material['eng_title'] :	'';
				}else $class['title'] = '';
				
				//學員
				$sql = "SELECT m.id ,m.first_name,m.last_name FROM member m, course_registration cr ";
				$sql.= 'WHERE cr.member_id = m.id AND cr.classroom_id='.$class['id'].' AND cr.status=10 AND ( m.status=10 OR (m.status=0 AND m.deleted=1) ) GROUP BY m.id ';
				$stm2= $this->dbconn->query($sql);
				while($student = $stm2->fetch(PDO::FETCH_ASSOC)){
					$sql = "SELECT title FROM skill WHERE id in (SELECT skill_id FROM member_skill WHERE member_id='".$student['id']."' )";
					$stm3= $this->dbconn->query($sql);
					$student['member_name'] = $student['first_name'];
					$student['skill'] = array();
					while($row = $stm3->fetch(PDO::FETCH_NUM)){
						array_push($student['skill'], $row[0] );
					}
					$student['skill'] = implode('、',$student['skill']);
					$class['students'][] = $student;
				}
				array_push($data,$class);
			}
			
			unset($this->registration_type[10]);
			$all = array( 0 => 'All');
			$registration_type = $all + $this->registration_type ;			
			$this->tpl->assign('registration_type',$registration_type);
			
			$this->tpl->assign('data',$data);
			$this->render('teacher/newclass');
		}
		
		function classes(){		
			
			$this->tpl->assign('regtype',0);
			$level = $this->dbconn->fetch_assoc('level','id','level_name','status=10',false);
			$url = "/teacher/classes";
			$order = 'ORDER BY `sort` DESC, open_time ASC';

			$range = false;
			if(count($_GET)){
				
				if( isset($_GET['type']) AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['sdate']) 
											AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['edate']) ){
			
				$sdate = date('Y-m-d',strtotime($_GET['sdate']));
				$edate = date('Y-m-d',strtotime($_GET['edate']));
				$range = "open_time BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59' ";
				$order = 'ORDER BY `sort` DESC, open_time DESC';
				
				}
				
				// 1 = 尚未開課 ,  2 = 尚未評鑑
				if($_GET['type']==1 || $_GET['type']==2){
					if($range) $range.=' AND ';
					if($_GET['type']==1)
						$range.= "open_time > '".date('Y-m-d H:00:00')."' ";
					else{
						$_24hr = date('Y-m-d H:i:s', time() - 90000 );
						$range.= "open_time BETWEEN '".$_24hr."' AND '".date('Y-m-d H:i:s')."' AND id not in (SELECT classroom_id FROM ques_student GROUP BY classroom_id) ";
						$range.= " AND id in (SELECT classroom_id FROM course_registration WHERE attend=10 GROUP BY classroom_id ) ";
					}
				}
				
				$url = "/teacher/classes?type=".param('type')."&sdate=".param('sdate')."&edate=".param('edate');
				
			}else{
				$now = date('Y/m/d H:i:s', time() - 2700 );
				$range = 1;//"open_time >= '".$now."' ";
				$order = 'ORDER BY `sort` DESC, open_time ASC';
			}

			if($range) $range = ' AND '.$range;
			$now = date('Y/m/d H:i:s', time() - 2700 );
			$sql = "SELECT *, IF(`open_time`>= '$now', 1, 0) AS `sort` FROM classroom ";
			$filter = "WHERE consultant_id=".$this->mid.$range;
			$filter.= " AND consultant_confirmed =10 AND status=10 AND `type`>10  AND type!=99 ";

			//課程類型
			if(isset($_GET['regtype']) && $_GET['regtype'] >0 && isset( $this->registration_type[$_GET['regtype']] ) ){
				$filter.= " AND `type`=".$_GET['regtype']." ";
				$this->tpl->assign('regtype',$_GET['regtype']);
				$url = "/teacher/classes?regtype=".param('regtype');
			}
			
			$this->paginate = load_component('Paginate',true);
			$this->paginate->student = false;
			$this->paginate->perpage = $this->perpage;
			$this->paginate->url = $url;
			$stm  = $this->dbconn->query( "SELECT COUNT(*) FROM classroom ".$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql = $sql.$filter." $order LIMIT $cursor,".$this->perpage;	
//			echo $sql;	
			$stm = $this->dbconn->query($sql);
			$data = array();
			
			$level = $this->dbconn->fetch_assoc('level','id','level_name',false,false);
			
			while($class = $stm->fetch(PDO::FETCH_ASSOC)){
				
				//等級
				$class['level'] = $level[$class['level_id']];
				
				//日期
				$stime = strtotime( $class['open_time'] );
				$etime = strtotime( $class['open_time'] ) + (45*60);
				$class['date'] = array( 'date'=> date('Y/m/d',$stime) ,
										'week'=> date('l',$stime) ,
										'start_time' => date('H:i',$stime),
										'end_time' => date('H:i',$etime)
								);
				$class['type'] = $this->registration_type[$class['type']];			
				
				
				//即將開始
				$overdue = $stime - time();
				$class['overdue'] = $overdue <= 3600 && $overdue >=0 ? true : false;

				//開啟教室
				$class['webex'] = enter_webex($class['id'],'teacher');

				$class['level'] = isset($level[$class['level_id']]) ? $level[$class['level_id']]: $level[1];
				
				//教材
								
				$material = $this->dbconn->fetch_one('material','id='.$class['material_id'].' AND ( status=10 OR (status=0 AND deleted=1) )');				
				if($material){
					
					$class['title'] = $material['eng_title'];
					$sql = "SELECT file FROM material_files WHERE parent='".$material['id']."' AND tag=0"; //PDF
					$stm2 =  $this->dbconn->query($sql);
					$row = $stm2->fetch(PDO::FETCH_ASSOC);
					$class['material_file'] = $row['file'];
					
					$sql = "SELECT file FROM material_files WHERE parent='".$material['id']."' AND tag=2"; //UCF
					$stm2 =  $this->dbconn->query($sql);
					$row = $stm2->fetch(PDO::FETCH_ASSOC);
					$class['ucf_file'] = $row['file'];
					
				}else{
					$class['material_file'] = false;
					$class['ucf_file'] = false;
					$class['title'] = '';
				}
				
				
				//學員
				$sql = "SELECT m.id ,m.first_name, m.last_name, cr.attend FROM member m, course_registration cr ";
				$sql.= 'WHERE cr.member_id = m.id AND cr.classroom_id='.$class['id'].' AND cr.status=10 AND ( m.status=10 OR (m.status=0 AND m.deleted=1) ) GROUP BY m.id ';
				$stm2= $this->dbconn->query($sql);				
				$attend = false;
				while($student = $stm2->fetch(PDO::FETCH_ASSOC)){
					$sql = "SELECT title FROM skill WHERE id in (SELECT skill_id FROM member_skill WHERE member_id='".$student['id']."' )";
					$stm3= $this->dbconn->query($sql);
					$student['member_name'] = $student['first_name'];
					$student['skill'] = array();
					while($row = $stm3->fetch(PDO::FETCH_NUM)){
						array_push($student['skill'], $row[0] );
					}
					$student['skill'] = implode('、',$student['skill']);
					$class['students'][] = $student;
					if($student['attend']==10) $attend = true;
				}
				
				//評鑑
				$class['report'] = 0;
				$filter = array( 'classroom_id'=> $class['id'] );
				$num = $this->dbconn->fetch_count('ques_student', $filter, false );	
				if($num){
					$class['report'] = 2; //view report
				}elseif( $attend && $stime - time() < 0 &&  time() < $stime + 90000){							
					$class['report'] = 1;
				}
				
				array_push($data,$class);
			}
			
			unset($this->registration_type[10]);
			
			$all = array( 0 => 'All');
			$registration_type = $all + $this->registration_type ;			
			$this->tpl->assign('registration_type',$registration_type);
			
			$this->tpl->assign('regtype', isset($_GET['regtype']) ? $_GET['regtype'] : 0);
			$this->tpl->assign('sdate', isset($_GET['sdate']) ? $_GET['sdate'] : '');
			$this->tpl->assign('edate', isset($_GET['edate']) ? $_GET['edate'] : '');
			$this->tpl->assign('data',$data);
			$this->render('teacher/classes');
		}
		
		function demo(){
		
			$level = $this->dbconn->fetch_assoc('level','id','level_name','status=10',false);
			$url = "/teacher/demo";

			$order = 'ORDER BY `sort` DESC, open_time ASC';
			$range = false;
			if(count($_GET)){
				
				if( isset($_GET['type']) AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['sdate']) 
											AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['edate']) ){
			
				$sdate = date('Y-m-d',strtotime($_GET['sdate']));
				$edate = date('Y-m-d',strtotime($_GET['edate']));
				$range = " open_time BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59' ";
				$order = 'ORDER BY `sort` DESC, open_time ASC';

				}
				
				// 1 = 尚未開課 ,  2 = 尚未評鑑
				if($_GET['type']==1 || $_GET['type']==2){
					if($range) $range.=' AND ';
					if($_GET['type']==1)
						$range.= "open_time > '".date('Y-m-d H:00:00')."' ";
					else{
						$_24hr = date('Y-m-d H:i:s', time() - 90000 );
						$range.= "open_time BETWEEN '".$_24hr."' AND '".date('Y-m-d H:i:s')."' AND id not in (SELECT classroom_id FROM ques_demo GROUP BY classroom_id) ";
						$range.= " AND id in (SELECT cr.classroom_id FROM course_registration cr, member m WHERE cr.member_id= m.id AND (m.status=60 OR m.status=70) AND cr.attend=10 GROUP BY cr.classroom_id ) ";
					}
				}
				
				$url = "/teacher/demo?type=".param('type')."&sdate=".param('sdate')."&edate=".param('edate');
				
			}else{
				$range = '1'; //" open_time > '".date('Y-m-d H:00:00')."' ";
				$order = 'ORDER BY `sort` DESC, open_time ASC';
			}

			if($range) $range = ' AND '.$range;

			$now = date('Y/m/d H:i:s', time() - 3600 );
			$sql = "SELECT *, IF(`open_time`>= '$now', 1, 0) AS `sort` FROM classroom ";
			$filter = " WHERE consultant_id=".$this->mid.$range;
			$filter.= " AND status=10 AND `type`=10 ";
			
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->student = false;
			$this->paginate->url = $url;
			$stm  = $this->dbconn->query( "SELECT COUNT(*) FROM classroom ".$filter); //.$order );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql = $sql.$filter." $order LIMIT $cursor,".$this->perpage;		
			$stm = $this->dbconn->query($sql);
			$data = array();

			while($class = $stm->fetch(PDO::FETCH_ASSOC)){
				//日期
				$stime = strtotime( $class['open_time'] );
				$etime = strtotime( $class['open_time'] ) + (45*60);
				$class['date'] = array( 'date'=> date('Y/m/d',$stime) ,
										'week'=> date('l',$stime) ,
										'start_time' => date('H:i',$stime),
										'end_time' => date('H:i',$etime)
								);
				
				//即將開始
				$overdue = $stime - time();
				$class['overdue'] = $overdue <= 3600 && $overdue >=0 ? true : false;

				//開啟教室
				$class['webex'] = enter_webex($class['id'],'teacher_demo');
				
				//學員
				$attend = false;
				$sql = "SELECT m.sales_id ,m.first_name, m.last_name, m.status, cr.attend FROM member m, course_registration cr ";
				$sql.= 'WHERE cr.member_id = m.id AND cr.classroom_id='.$class['id'];
				$stm2= $this->dbconn->query($sql);
				$student = $stm2->fetch(PDO::FETCH_ASSOC);								
				$class['student'] = $student['first_name'];				
				if($student['attend']==10) $attend = true;
				
				//評鑑
				$class['report'] = 0;
				$filter = array( 'classroom_id'=> $class['id'] );
				$num = $this->dbconn->fetch_count('ques_demo', $filter, false );	
				if($num){
					$class['report'] = 2; //view report
				}elseif( $attend && $stime <= (time() + 3600) && time() < $stime + 10800 ){
					$class['report'] = 1;
				}
				if($student['status']!=60 && $student['status']!=70) $class['report'] = 0; //開發中，釋出
				
				//sales
				$sql = "SELECT first_name,in_tel FROM `user` WHERE id='".$student['sales_id']."'";
				$stm2= $this->dbconn->query($sql);
				$sales = $stm2->fetch(PDO::FETCH_ASSOC);									
				$class['sales'] = $sales['first_name'];
				$class['sales_tel'] = $sales['in_tel'];
				
				array_push($data,$class);
			}
			$this->tpl->assign('sdate', isset($_GET['sdate']) ? $_GET['sdate'] : '');
			$this->tpl->assign('edate', isset($_GET['edate']) ? $_GET['edate'] : '');
			$this->tpl->assign('data',$data);
			$this->render('teacher/demo');
		}
		
		function report_demo(){		
			
			$filter = array('classroom_id'=> $_GET['classroom']);
			$data = $this->dbconn->fetch_assoc('ques_demo', 'ques_id', 'content', $filter, false);
			$this->tpl->assign('data', $data );
			
			$filter = array('id'=> $_GET['classroom'], 'type'=>10, 'consultant_id'=> $this->mid);
			$classroom = $this->dbconn->fetch_one('classroom', $filter );
			if(!$classroom) die("Classroom is not exists!");
			
			$filter = array('classroom_id'=> $_GET['classroom']);
			$cr = $this->dbconn->fetch_one('course_registration', $filter, false );
			if(!$cr) die("Registration is not exists!");
			
			$filter = 'id='.$cr['member_id'].' AND status IN (60, 70)';
			$member = $this->dbconn->fetch_one('member', $filter, false );
			$this->tpl->assign('member',$member);
			if(!$member) die('Student is not exists!');
				
			$sql = "SELECT id, text FROM questionnaire WHERE `type`=10 ORDER BY rank ASC";
			$stm = $this->dbconn->query($sql);
			$items = array();
			while( $row = $stm->fetch(PDO::FETCH_ASSOC)){
				$items[$row['id']] = $row;
			}
			
			$sql = "SELECT id, ques_id, text FROM ques_item ";
			$sql.= "WHERE ques_id in ( SELECT id FROM questionnaire WHERE `type`=10 ) ORDER BY rank ASC";
			$stm = $this->dbconn->query($sql);
			while( $row = $stm->fetch(PDO::FETCH_ASSOC)){
				if(isset($items[$row['ques_id']]))
					$items[$row['ques_id']]['options'][$row['id']] = $row['text'];
			}			
			$this->tpl->assign( 'items', $items );
			
			$this->render('teacher/report_demo');
		}
		
		function report_class(){			

			$data = array();
			$sql = "SELECT member_id, ques_id, content FROM ques_student ";
			$sql.= "WHERE classroom_id='".$_GET['classroom']."'";
			$stm = $this->dbconn->query($sql);
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){
				$data[$row['member_id']][$row['ques_id']] = $row['content'];
			}
			$this->tpl->assign('data', count($data) ? $data : false );

			$filter = 'id='.$_GET['classroom'].' AND `type`>10 AND consultant_id='.$this->mid;
			$classroom = $this->dbconn->fetch_one('classroom', $filter );
			if(!$classroom) die("Classroom is not exists!");
			
			$filter = array('classroom_id'=> $_GET['classroom'], 'attend'=> 10 , 'status'=> 10);
			$cr = $this->dbconn->fetch_all('course_registration', $filter, false );
			if(!$cr) die("Registration is not exists!");
			
			$students = array();
			foreach($cr as $row){
				$filter = array('id'=> $row['member_id']);
				$student = $this->dbconn->fetch_one('member', $filter, false );
				$students[$student['id']] = $student;
			}
			$this->tpl->assign('students',$students);
			$this->tpl->assign('student_amount',count($students));
			
			$sql = "SELECT id, text FROM questionnaire WHERE `type`=30 AND id<18 ORDER BY rank ASC";
			$stm = $this->dbconn->query($sql);
			$items = array();
			while( $row = $stm->fetch(PDO::FETCH_ASSOC)){
				$items[$row['id']] = $row;
			}
			
			$sql = "SELECT id, ques_id, text FROM ques_item ";
			$sql.= "WHERE ques_id in ( SELECT id FROM questionnaire WHERE `type`=30 AND id<18 ) ORDER BY rank ASC";
			$stm = $this->dbconn->query($sql);
			while( $row = $stm->fetch(PDO::FETCH_ASSOC)){
				if(isset($items[$row['ques_id']]))
					$items[$row['ques_id']]['options'][$row['id']] = $row['text'];
			}			
			$this->tpl->assign( 'items', $items );
			
			$this->render('teacher/report_class');
		}		
		
		function account(){
			
			$msg = false;
			if(count($_POST)>0){
				
				$form = $_POST;				
				$form['mdate'] = date('Y-m-d H:i:s');
				if(!empty($form['passwd']))
					$form['password'] = GenPassword( $_SESSION['teacher']['account'] ,$_POST['passwd']);
				
				unset($form['passwd']);
				unset($form['passwd2']);
				$filter = array('id'=> $this->mid );
				$num = $this->dbconn->update('consultant', $form, $filter);
				if($num)	$msg = "Change successfully !";
				else{					
					$msg = "Change fail.";
				}
				if( count($_FILES) ){
					
					include( DOCUMENT_ROOT.'/lib/SimpleImage.php');
					$tag = array( 'avatar0' =>0, 'avatar1' =>1, 'avatar2' =>2 );
					$path = "/imgs/consultant/";

					foreach($_FILES as $key => $file){


		$whitelist = array('jpg', 'jpeg', 'png', 'gif');
		$name      = null;
		$error     = '';
		$extension = strtolower( pathinfo($_FILES[$key]['name'], PATHINFO_EXTENSION) );
		$tmp_name = $_FILES[$key]['tmp_name'];
		$new_name = time().rand(100,999);
		$name     = $path.$new_name.'.'.$extension;
		$thumb     = $path.$new_name.'_s.'.$extension;
		$error    = $_FILES[$key]['error'];
		
		if ($error === UPLOAD_ERR_OK) {

			if (!in_array($extension, $whitelist)) {
				$error = 'Invalid file type uploaded.';
			} else {
				
				try {
					$img = new abeautifulsite\SimpleImage( $tmp_name);					
					$img->best_fit( 500, 387 )->save( DOCUMENT_ROOT.$name, 100);
					$img->best_fit( 275, 212 )->save( DOCUMENT_ROOT.$thumb,100);
				
				} catch(Exception $e) {
					$error = $e->getMessage();
				}
			}
			
			if(empty($error)){
				$filter = array( 'parent' => $this->mid, 'tag' =>  $tag[$key] );
				$old = $this->dbconn->fetch_one('consultant_images', $filter, false);
				$form = $filter;
				$form['image'] = $name;
				$form['thumb'] = $thumb;
				$form['type'] = $extension;
				
				if(isset($old['id'])){
					$num = $this->dbconn->update('consultant_images', $form , $filter);
					if($num){
						@unlink( DOCUMENT_ROOT. $old['image'] );
						@unlink( DOCUMENT_ROOT. $old['thumb'] );
					}
				}else{
					$this->dbconn->insert('consultant_images', $form);
				}				
			}
			
		}
				
						
					}
				}

			}
			$this->tpl->assign('msg',$msg);
			
			GLOBAL $var_education,$var_language;
			
			$this->tpl->assign('var_education',$var_education);
			$this->tpl->assign('var_language',$var_language);			
			
			$data = $this->dbconn->fetch_one('consultant','id='.$this->mid);			
			$this->tpl->assign('data',$data);
			
			//avatar
			$avatar = $this->dbconn->fetch_assoc('consultant_images','tag','image','parent='.$this->mid,'tag ASC');
			$this->tpl->assign('avatar',$avatar);
			
			$this->render('teacher/account');
		}
		
		
		function forgot(){
			
			if(isset($_POST['account'])){
				$this->layout = false;
				/*
				if(!$this->recaptcha())
					die(json_encode((object) array('code'=>2,'msg'=>'驗證碼錯誤!')));*/
				$accu = $_POST['account'];
				
				$member = $this->dbconn->fetch_one('consultant', array('account'=>$accu) );
				if(!$member)
					die(json_encode((object) array('code'=>0,'msg'=>'Not a valid ID.')));
				
				$passwd = substr(MD5(time()),0,8);
				$form = array( 'password'=> GenPassword($accu,$passwd));
				$filter = array( 'account'=> $accu );
				$rs = $this->dbconn->update('consultant', $form, $filter );
				if($rs){
					if($this->sendmail( 'New password.', "Your password below：".$passwd , $member['email'], $member['first_name']  ) )
						die(json_encode((object) array('code'=>1,'msg'=>'Your password has been sent to your E-mail address.')));
					else
						die(json_encode((object) array('code'=>0,'msg'=>'Sending fail.')));
				}else
					die(json_encode((object) array('code'=>0,'msg'=>'Reset fail.')));
			}

			$this->tpl->assign('CAPCHA_PUBLIC_KEY',CAPCHA_PUBLIC_KEY);
			$this->render('teacher/forgot');
		}

		function salary(){
			
			$date = isset($_GET['m']) && preg_match("/^\d{4}\/\d{2}$/",$_GET['m']) ? $_GET['m'] : date('Y/m');
			$date = str_replace("/","-",$date);
			$sdate = strtotime($date."-01 00:00:00");
			$edate = strtotime( $date."-".date('t',$sdate)." 00:00:00");
			
			$sql = "SELECT `date`, SUM(salary) as salary, SUM(reward) as reward FROM classroom ";
			$sql.= "WHERE consultant_id=".$this->mid;
			$sql.= " AND `date` BETWEEN '".date('Y/m/d',$sdate)."' AND '".date('Y/m/d',$edate)."'";
			$sql.= " AND salary>0 AND wage!=0 AND status = 10 AND consultant_confirmed = 10 ";
			$sql.= " GROUP BY `date` ORDER BY `date`";
			$stm = $this->dbconn->query($sql);
			$data = array();
			$total = array('hours'=>0 ,'reward'=>0, 'salary'=>0, 'total'=>0);
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){
				
				$sql = "SELECT SUM(hour) as hour FROM classroom ";
				$sql.= "WHERE `date`='".$row['date']."' AND `type` <> 10 AND consultant_id=".$this->mid;
				$sql.= " AND salary>0 AND wage!=0 AND status = 10 AND consultant_confirmed = 10 ";
				$num = $this->dbconn->query($sql);
				$num = $num->fetch(PDO::FETCH_NUM);
				$row['hour'] = $num[0];

        $sql = "SELECT SUM(hour) as demo FROM classroom ";
        $sql.= "WHERE `date`='".$row['date']."' AND `type` = 10 AND consultant_id=".$this->mid;
        $sql.= " AND salary>0 AND wage!=0 AND status = 10 AND consultant_confirmed = 10 ";
        $num = $this->dbconn->query($sql);
        $num = $num->fetch(PDO::FETCH_NUM);
        $row['demo'] = $num[0];

        $total['hours']+=$row['hour'];
        $total['salary']+=$row['salary'];
        $total['reward']+=$row['reward'];

        $row['salary'] += $row['reward'];

				$data[]=$row;
			}
			$total['total'] = $total['salary'] + $total['reward'];
			
			$this->tpl->assign('total',$total);
			$this->tpl->assign('data',$data);
			$this->tpl->assign('title',date("Y F",$sdate));
			$this->tpl->assign('calendar',date("Y/m",$sdate));
			$this->tpl->assign('next', date('Ym') <= date('Ym',$edate) ? false : date("Y/m",$edate+86400) );
			$this->tpl->assign('prev',date("Y/m",$sdate-86400));
			$this->tpl->assign('current',date("Y/m"));
			$this->tpl->assign('wage',$this->wage());
			$this->render('teacher/salary');
		}
		
		function salary_content(){

			if(!isset($_GET['d']) || !preg_match("/^\d{4}-\d{2}-\d{2}$/",$_GET['d'])) alert("error");
			
			$sql = "SELECT id,open_time,hour,type,salary + reward AS salary,reward,memo,material_id,level_id FROM classroom ";
			$sql.= "WHERE consultant_id=".$this->mid;
			$sql.= " AND `date` = '".$_GET['d']."'";
			$sql.= " AND salary>0 AND wage!=0 AND status = 10 AND consultant_confirmed = 10 ";
			$sql.= " ORDER BY open_time ASC";
			$stm = $this->dbconn->query($sql);
			
			GLOBAL $var_registration_type;
			$level = $this->dbconn->fetch_assoc('level','id','level_name',false,false);	
			$data = array();
			$total = array('hours'=>'' , 'demo'=>'' ,'reward'=>'', 'salary'=>'');
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){				
				
				if($row['type']==10){
					$total['demo']+=$row['hour'];
					$row['demo'] = $row['hour'];
					$row['hour'] = '';					
				}else{
					$row['demo'] = '';
					$total['hours']+=$row['hour'];	
				}
							
				$total['salary']+=$row['salary'];
				$total['reward']+=$row['reward'];
				
				$row['type'] = $this->registration_type[$row['type']];
			
				//等級
				$row['level'] = !$row['level_id'] ?  $level[1]: $level[$row['level_id']];
				
				//日期
				$stime = strtotime( $row['open_time'] );
				$etime = strtotime( $row['open_time'] ) + (45*60);
				$row['date'] = array(   
										'start_time' => date('H:i',$stime),
										'end_time' => date('H:i',$etime)
								);
								
				//教材								
				$material = $this->dbconn->fetch_one('material','id='.$row['material_id']); //.' AND ( status=10 OR (status=0 AND deleted=1) )');
				if($material){					
					$row['title'] = $material['eng_title'];									
				}
				
				$row['students'] = array();
				//學員
				$sql = "SELECT m.id,  m.first_name FROM member m, course_registration cr ";
				$sql.= 'WHERE cr.member_id = m.id AND cr.classroom_id='.$row['id'].' AND cr.status=10 AND ( m.status=10 OR (m.status=0 AND m.deleted=1) ) GROUP BY m.id ';
				$stm2= $this->dbconn->query($sql);
				while($student = $stm2->fetch(PDO::FETCH_ASSOC)){
					$row['students'][] = $student['first_name'];
				}
				$row['students'] = implode(", ",$row['students']);
				$data[]=$row;	
			}

			$this->tpl->assign('total',$total);
			$this->tpl->assign('data',$data);
			$this->tpl->assign('wage',$this->wage());
			$this->tpl->assign('title',date("Y F d (D)", strtotime($_GET['d'].' 00:00:00') )); //2016 July 31 (Sun)
			$this->render('teacher/salary_content');
		}
		
		function wage(){
			$sql = "SELECT course_pay FROM consultant WHERE id=".$this->mid;
			$stm = $this->dbconn->query($sql);
			$stm = $stm->fetch(PDO::FETCH_NUM);
			return $stm[0];
		}
		
	}
?>
