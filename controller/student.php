<?php
	class Page extends APP{
		
		var $mid = false;	//member id
		var $history = false;
		var $countdown = false;
		var $perpage = 10;
		var $layout = false;
	
		var $contract = false;
		
		function init(){
			
			parent::init();
			if(!$this->check_login()) forward("/");
			$this->mid = $_SESSION['member']['id'];
			$this->history = isset($_GET['type']) && $_GET['type']=='history' ? true : false;
			$this->countdown = isset($_GET['type']) && $_GET['type']=='countdown' ? true : false;
			$this->member_profile();						
		}
		
		function index(){
			header("Location:/student/booking");
			exit;
		}
		
		function my(){
			
			if($this->expire)
				forward('/student/contract');
			else
				forward('/student/points');
		}
		
		function hardware(){
			$this->render('student/hardware');
		}
		
		function classroom(){
			
			$_GET['regtype'] = isset($_GET['regtype']) ? $_GET['regtype'] : 0 ;
			$_GET['interest'] = isset($_GET['interest']) ? $_GET['interest'] : 0 ;
			$this->tpl->assign('regtype',$_GET['regtype']);
			$this->tpl->assign('interest',$_GET['interest']);
			
			//課程類型
			GLOBAL $var_registration_type;
			unset($var_registration_type[10]);
			unset($var_registration_type[99]);
			$all = array( 0 => "全部類型");
			$this->tpl->assign('var_registration_type', $all+$var_registration_type); 
			
			//興趣
			$interest = $this->dbconn->fetch_assoc('interest','id','title','status=10','rank ASC');
			$all = array( 0 => "全部主題");
			if ($interest)
				$this->tpl->assign('var_interest_type', $all+$interest);
			else
				$this->tpl->assign('var_interest_type', $all);
			
			$data = $this->course_registration(true);
			$this->tpl->assign('registration', $data );
			
			$this->tpl->assign('history', $this->history );
			$this->tpl->assign('type', isset($_GET['type']) ? $_GET['type'] : ''  );
			
			$this->render('student/classroom');
		}
		
		function member_profile(){
			
			$filter = array( 'id' => $this->mid );
			$member = $this->dbconn->fetch_one( 'member', $filter );
			if($member['status']!=10) alert('帳號停權','/member/logout');
			$this->member_level_id = $member['level_id'];
			$filter = array('id'=>$member['level_id']);
			$level = $this->dbconn->fetch_one('level',$filter);
			$member['level'] = $level['level_name'];

			if ($member['level_id'] == $member['original_level_id'])
			  $grade = $member['grade'] - $level['begin'];
			else
			  $grade = 0;
			// calc 堂數
      $sql = "SELECT COUNT(*) AS cnt FROM course_registration WHERE member_id = {$this->mid} AND attend > 0 AND status = 10";
			$stm = $this->dbconn->query( $sql );
			$calc = $stm->fetch(PDO::FETCH_ASSOC);
      $grade += $calc['cnt'];
      $percent = $grade / ($level['end'] - $level['begin'] + 1);
      if ($percent > 1.0)
        $percent = 1;
      $this->tpl->assign('level_percent',$percent);

      $this->tpl->assign('member',$member);
			
			$sql = "SELECT min(begin) as begin, max(end) as end,max(open_time) as open FROM member_contract ";
			$sql.= "WHERE member_id=".$this->mid." AND signed=10 AND status=20 ";
			//$sql.= "AND end >= '".date('Y-m-d 00:00:00')."' ";
			$sql.= "AND paid_time != '0000-00-00 00:00:00' ";
			$sql.= "AND open_time != '0000-00-00 00:00:00' ";
			$sql.= "AND sign_time != '0000-00-00 00:00:00' ";
			$stm = $this->dbconn->query( $sql );
			$contract = $stm->fetch(PDO::FETCH_ASSOC);
			$this->contract = $contract;
			$this->tpl->assign('contract',$contract);			
			
			$now  = date('YmdHi');
			$end  = date('YmdHi',strtotime($contract['end'].' 23:59:59'));
			$open = date('YmdHi',strtotime($contract['open']));
			
			if( $contract['end']!=Null && $contract['open']!=Null && $end >= $now &&   $now >= $open ){				
				$this->expire = false;
			}else{
				$this->expire = true;//沒有合約 跳contract
			}			
			
			if( $contract['end']!=Null && $contract['open']!=Null && $end >= $now && $now >= $open   ){		
				$this->tpl->assign('expire',false);				
			}else{
				if( $contract['end']!=Null && $now >= $end && $now >= $open )
					$this->tpl->assign('expire',true);//alert 合約過期
				else
					$this->tpl->assign('expire',false);
			}
			
		}
		
		function course_registration($no_demo = false){
			
			GLOBAL $var_registration_type;			
			unset($var_registration_type[99]);
			
			if($this->countdown){
				$now = time();
				$within = CLASSROOM_COUNTDOWN * 60 ;
				$datetime = "UNIX_TIMESTAMP(c.open_time) - $now BETWEEN 0 AND $within ";
			}else{
				$now = date('Y/m/d H:i:s', time() - 2700 );
				$datetime = $this->history ? "c.`open_time` < '$now' " : "c.`open_time` >= '$now' " ;
			}
			
			$sql = "SELECT c.*,cr.attend, cr.locked FROM course_registration cr, classroom c ";
			
			$filter = "WHERE cr.classroom_id= c.id AND $datetime AND c.status=10  AND c.type!=99 ";
			if ($no_demo)
				$filter .= "AND c.type <> 10 ";
			$filter.= "AND cr.member_id=".$this->mid." AND cr.status=10 ";
			//課程類型
			if(isset($_GET['regtype']) && $_GET['regtype'] >0 && isset( $var_registration_type[$_GET['regtype']] ) ){
				$filter.= "AND c.type=".$_GET['regtype']." ";
			}
			//課程主題
			if(isset($_GET['interest']) && $_GET['interest']>0 && is_numeric( $_GET['interest']) ){
				$filter.= "AND c.material_id in ( ";
					$filter.= "SELECT m.id FROM material m, material_interest mi ";
						$filter.= "WHERE m.id=mi.material_id AND mi.interest_id=".$_GET['interest']." AND m.status=10 ) ";
			}
			
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->url = "/student/classroom?type=".param('type')."&regtype=".param('regtype')."&interest=".param('interest');
			$stm  = $this->dbconn->query( "SELECT COUNT(*) FROM course_registration cr, classroom c ".$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );
			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql = $sql.$filter." ORDER BY c.open_time " . ($this->history ? 'DESC' : 'ASC') . " LIMIT $cursor,".$this->perpage;
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
				//即將進入教室
				$countdown = $stime - time();
				$class['countdown'] =  $countdown <= 3600  && $countdown >=0  ? true : false;
				
				//開啟教室				
				$class['webex'] = enter_webex($class['id'],'student');		

				//取消
				$class['cancel'] = false;
				if($class['type']!=40 || ( $class['type']==40 && $class['lesson']==1) )			
					//$class['cancel'] = $stime - time() > 86400 ? true : false;
          if ($class['locked'] == 1)
            $class['cancel'] = false;
          else
            $class['cancel'] = $stime - time() > (12 * 3600) ? true : false; // cancel time
						
				//老師
				$consultant = $this->dbconn->fetch_one('consultant','id='.$class['consultant_id'].' AND ( status=10 OR (status=0 AND deleted=1) )');
				$class['consultant'] = $consultant;
				
				//系列
				$class['serial'] = false;

				//評鑑顧問
				$class['report'] = 0;
				$filter = array( 'classroom_id'=> $class['id'], 'member_id'=>$this->mid );
				$num = $this->dbconn->fetch_count('ques_consultant', $filter, false );
				
				if($num){
					$class['report'] = 2; //view report
				}elseif( $class['attend']>0 && $etime < time() &&  time() < $etime + 86400){									
					$class['report'] = 1;
				}

				//顧問給的評鑑
				$class['consultant_report'] = 0;
				$filter = array( 'classroom_id'=> $class['id'], 'member_id'=>$this->mid );
				$num = $this->dbconn->fetch_count('ques_student', $filter, false );
				
				if($num /*&& $class['report']==2*/ ){
					$class['consultant_report'] = 2; //view report
				}/*elseif( $etime < time() &&  time() < $etime + 86400){									
					$class['consultant_report'] = 1;
				}*/
				
				//教材
				$material = $this->dbconn->fetch_one('material','id='.$class['material_id'].' AND ( status=10 OR (status=0 AND deleted=1) )');
				if(!$material){
					$class['material'] = false;
					$class['title'] = '';
					$class['brief'] = '';
					$class['material_file'] = false;
				}else{
					
					//簡介
					$class['title'] = $material['title'];
					$class['brief'] = $material['brief'];
					
					//興趣
					$sql = "SELECT id,title FROM interest WHERE id in ( ";
						$sql.= "SELECT interest_id FROM material_interest WHERE material_id = '".$class['material_id']."' ) AND status=10 ";
					$stm2 =  $this->dbconn->query($sql);
					$class['interest'] = $stm2->fetchAll(PDO::FETCH_ASSOC);
					
					//教材預覽	開課前一小時
					$lifetime = $stime-time();
					if( ( isset($_GET['type']) && $_GET['type']=='history') || ( $lifetime <= 3600 && $lifetime>=0) ){
						
						$sql = "SELECT file FROM material_files WHERE parent='".$material['id']."' AND tag=0"; //PDF
						$stm2 =  $this->dbconn->query($sql);
						$row = $stm2->fetch(PDO::FETCH_ASSOC);
						$class['material_file'] = $row['file'];
						
					}else $class['material_file'] = false;
					
				}
				$class['t'] = $class['type'];
				$class['type'] = $var_registration_type[$class['type']];
				
				array_push($data,$class);
			}		
			return $data;			
		}
		
		function booking(){
			$this->render('student/booking');
		}
		
		function booking_ajax(){
			
			$this->layout = false;
			
			GLOBAL $var_registration_type;
			
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
			
			//已booking
			$sql = "SELECT c.id, c.type, c.open_time, cr.locked FROM course_registration cr, classroom c ";
			$sql.= "WHERE c.id=cr.classroom_id AND member_id=".$this->mid." ";
			$sql.= "AND cr.status=10 AND c.status=10 AND c.type IN (20, 30) AND c.open_time BETWEEN '".date('Y-m-d 00:00:00',$sdate)."' AND '".date('Y-m-d 23:59:59',$edate)."' ";
			$stm = $this->dbconn->query($sql);
			$booking = array();
			while($cr = $stm->fetch(PDO::FETCH_ASSOC)){
				$ot = strtotime($cr['open_time']);
				$Ymd = date('Ymd', $ot );
				$h   = date('H', $ot );
				$cr['title'] = $var_registration_type[$cr['type']];
//				$cr['cancel'] = $ot - time() >=86400 ? false : true;
        if ($cr['locked'] == 1)
          $cr['cancel'] = true;
        else
          $cr['cancel'] = $ot - time() >= (12 * 3600) ? false : true; // cancel time
				$booking[$Ymd][$h] = $cr;
			}
			$this->tpl->assign('booking',$booking);			
			
			$contract_begin = str_replace('-','',$this->contract['begin']);
			$contract_end = str_replace('-','',$this->contract['end']);
			
			//每日時段
			$data = array();
			$now = date('YmdH');
			
			//全日不開放日期
			$stop_booking = array('20161225','20170101','20170111', '20170127', '20170128', '20170129', '20170130', '20170131', '20170201', '20170317');
			
			for($i= $sdate; $i<= $edate; $i+=86400){
				$ymd = date('Ymd',$i);
				$hour = array();
				
				for ($j = BEGIN_TIME; $j <= END_TIME; $j++)
				{
					$h = sprintf("%02d", $j);
					$timestamp = strtotime( date('Y-m-d',$i).' '.$h.':00:00' );
					
					//不開放選課
					if( ($ymd=='20161224' || $ymd=='20161231' || $ymd=='20170126' ) && $h >=18 ){
						$hour[$h] = false;
					}elseif(in_array($ymd,$stop_booking)){
						$hour[$h] = false;
					}else{
					
					
						//開放選課
						if(!in_array($ymd,$stop_booking) && $ymd.$h > $now && $ymd >= $contract_begin  && $ymd <= $contract_end && $timestamp - time() >=86400 ){
						
							if($this->member_level_id==1)
						
								$hour[$h] = array(  20 => $var_registration_type[20]);
							else
								$hour[$h] = array(  20 => $var_registration_type[20],
											30 => $var_registration_type[30]
								);
									
						}else{
							$hour[$h] = false;
						}
					}
				}
				$data[$ymd] = array( 'date'=>date('Y-m-d',$i), 'week'=> date('N',$i) , 'title'=> date('D m/d',$i), 'hour' => $hour );
			}
			$this->tpl->assign('data',$data);						
			$result = $this->render('student/booking_ajax',true);
			die(json_encode((object) array('result'=> $result )));
		}
		
		function contract(){
			
			$type = array( 0 => 'All',
						   1 => '尚未簽約',
						   2 => '尚未付款',
						   3 => '已簽約',
						   4 => '已付款',
						   5 => '已生效'
						);
			$this->tpl->assign('type',$type);
			
			$sql = "SELECT * FROM member_contract ";
			
			$filter = "WHERE member_id=".$this->mid." AND status=20 ";			
			
			if( isset($_GET['type']) AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['sdate']) 
											AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['edate']) ){
			
				$sdate = date('Y-m-d',strtotime($_GET['sdate']));
				$edate = date('Y-m-d',strtotime($_GET['edate']));
				$filter.= "AND cdate BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59' ";
			
				
			}
			
			if( isset($type[$_GET['type']]) ){
					switch($_GET['type']){
						case 1: $filter.= "AND signed!=10 "; break;
						case 2: $filter.= "AND paid_time='0000-00-00 00:00:00' "; break;
						case 3: $filter.= "AND signed=10 "; break;
						case 4: $filter.= "AND paid_time!='0000-00-00 00:00:00' "; break;
						case 5: $filter.= "AND open_time!='0000-00-00 00:00:00' "; break;
					}
			}
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->url = "/student/contract?type=".param('type')."&sdate=".param('sdate')."&edate=".param('edate');
			$stm  = $this->dbconn->query( "SELECT COUNT(*) FROM member_contract ".$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );
			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$data = array();
			$sql = $sql.$filter." ORDER BY id DESC LIMIT $cursor,".$this->perpage;		
			$stm = $this->dbconn->query($sql);
			$data = $stm->fetchAll(PDO::FETCH_ASSOC);
			foreach($data as $key => $row){
				//原價
				$sql = "SELECT price FROM plan WHERE id='".$row['plan_id']."'";
				$stm = $this->dbconn->query($sql);
				$plan = $stm->fetch(PDO::FETCH_ASSOC);
				$data[$key]['plan_price'] = $plan['price'];
				
				//已付款
				$sql = "SELECT SUM(total) FROM member_contract_bill WHERE contract_id=".$row['id']." AND status=10 AND paid_time!='0000-00-00 00:00:00' ";
				$stm = $this->dbconn->query($sql);
				$bill = $stm->fetch(PDO::FETCH_NUM);
				$data[$key]['pay'] = (int)$bill[0];
			}
			$this->tpl->assign('data', $data );			
			$this->tpl->assign('sdate', isset($_GET['sdate']) ? $_GET['sdate'] : '');
			$this->tpl->assign('edate', isset($_GET['edate']) ? $_GET['edate'] : '');
			
			$this->render('student/contract');
		}
		
		function contract_pay(){
			
			GLOBAL $var_payment2, $var_cc_type;
			
			if(!isset($this->param) || !is_numeric($this->param)) forward("/student/contract");
			
			$filter = array( 'id' => $this->param, 'member_id' => $this->mid );
			$contract = $this->dbconn->fetch_one( 'member_contract', $filter, false );
			if(!$contract) alert("(1)合約不存在","/student/contract");
			$this->tpl->assign('contract1',$contract);
			
			//已付款
			$sql = "SELECT SUM(total) FROM member_contract_bill WHERE contract_id=".$contract['id']." AND paid!=0 ";
			$stm =  $this->dbconn->query($sql);
			$bill = $stm->fetch(PDO::FETCH_NUM);
			$this->tpl->assign('paid_total', (int)$bill[0] );
			
			//總期數
			$sql = "SELECT COUNT(*) FROM member_contract_bill WHERE contract_id=".$contract['id']." AND status=10 ";
			$stm =  $this->dbconn->query($sql);
			$bill = $stm->fetch(PDO::FETCH_NUM);
			$this->tpl->assign('period_total', (int)$bill[0] );
			
			//未繳期數
			$sql = "SELECT COUNT(*) FROM member_contract_bill WHERE contract_id=".$contract['id']." AND paid=0 ";
			$stm =  $this->dbconn->query($sql);
			$bill = $stm->fetch(PDO::FETCH_NUM);
			$this->tpl->assign('period_balance', (int)$bill[0] );
			
			//方案名稱
			$sql = "SELECT plan_name FROM plan WHERE id=".$contract['plan_id'];
			$stm =  $this->dbconn->query($sql);
			$plan = $stm->fetch(PDO::FETCH_NUM);
			$this->tpl->assign('plan_name', $plan[0] );
			
			$filter = array( 'contract_id'=>$contract['id'], 'status'=>10 );
			$data = $this->dbconn->fetch_all( 'member_contract_bill', $filter, 'paid_time ASC');			
			$this->tpl->assign('data',$data);
			
			
			$this->tpl->assign('payment',$var_payment2);
			$this->tpl->assign('cctype',$var_cc_type);
			$this->tpl->assign('id',$this->param);
			
			if(isset($_GET['type']) && $_GET['type']=='view') $this->render('student/contract_pay_view');
			else $this->render('student/contract_pay');
		}
		
		function gen_contract( $mc, $contract){
			
			$sales = $this->dbconn->fetch_one('user', "id='".$mc['sales_id']."'" );

			$replace = array('chi_name'=>$mc['chi_name'],
							 'eng_name'=>$mc['eng_name'],
							 'account'=> $mc['account'],
							 'address'=> $mc['address'],
							 'legal'=> $mc['legal'],
							 'email'=> $mc['email'],
							 'payer'=> $mc['legal'] == "" ? $mc['chi_name'] : $mc['legal'],
							 'sales'=> $sales['user_name'],
							 'begin'=> date('Y年m月d日',strtotime($mc['begin'].' 00:00:00')),
							 'end'  => date('Y年m月d日',strtotime($mc['end'].' 00:00:00')),
							 'price' => $mc['price'],
							 'gift' => sprintf('%.1f',$mc['gift']),
							 'point' => sprintf('%.1f',$mc['point']),
							 'plan_name' => $mc['plan_name'],
							 );
							
			foreach($replace as $key => $value)
				$contract = str_replace( '{$'.$key.'}', $value, $contract );
			
			return $contract;
		}
		
		function contract_signing(){
			
			if(!isset($_GET['id']) || !is_numeric($_GET['id'])) forward("/student/contract");
			
			$filter = array( 'id' => $_GET['id'], 'member_id' => $this->mid, 'status' => 20 );
			$mc = $this->dbconn->fetch_one( 'member_contract', $filter, false );
			if(!$mc) alert("合約不存在","/student/contract");
			if($mc['signed']==10) alert("已簽過","/student/contract");
		
			$filter = array( 'id' => $mc['contract_id'], 'status' => 10 );
			$contract = $this->dbconn->fetch_one( 'contract', $filter, false );
			if(!$contract) alert("合約不存在","/student/contract");
			$contract['contract_name'] = $mc['contract_name'];
			$contract['content'] = $this->gen_contract( $mc, $contract['content'] );
			$this->tpl->assign('contract1',$contract);
			$this->render('student/contract_signing');
		}
		
		function contract_history(){
			
			if(!isset($_GET['id']) || !is_numeric($_GET['id'])) forward("/student/contract");
			
			$filter = array( 'id' => $_GET['id'], 'member_id' => $this->mid, 'status' => 20 );
			$mc = $this->dbconn->fetch_one( 'member_contract', $filter, false );
			if(!$mc) alert("(1)合約不存在","/student/contract");			
			if($mc['signed']!=10) alert("未簽約","/student/contract");
		
			$filter = array( 'id' => $mc['contract_id'], 'status' => 10 );
			$contract = $this->dbconn->fetch_one( 'contract', $filter, false );
			if(!$contract) alert("(2)合約不存在","/student/contract");
			$contract['contract_name'] = $mc['contract_name'];
			$contract['content'] = $this->gen_contract( $mc, $contract['content'] );
			$this->tpl->assign('contract1',$contract);
			$this->render('student/contract_history');
		}		

		
		function points(){
			
			GLOBAL $var_registration_type, $var_point_type;
			unset($var_registration_type[99]);
			
			$this->tpl->assign('var_point_type',$var_point_type);
			
			$sql = "SELECT * FROM member_point ";
			
			$filter = "WHERE member_id=".$this->mid." ";			
			
			if( isset($_GET['type']) AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['sdate']) 
											AND preg_match("/^\d{2}\/\d{2}\/\d{4}$/",$_GET['edate']) ){
			
				$sdate = date('Y-m-d',strtotime($_GET['sdate']));
				$edate = date('Y-m-d',strtotime($_GET['edate']));
				$filter.= "AND cdate BETWEEN '".$sdate." 00:00:00' AND '".$edate." 23:59:59'";
			

			}
			if(isset($_GET['type']) && isset($var_point_type[$_GET['type']])){
				$filter.= " AND `type`=".$_GET['type'];
			}

			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->url = "/student/points?type=".param('type')."&sdate=".param('sdate')."&edate=".param('edate');
			$stm  = $this->dbconn->query( "SELECT COUNT(*) FROM member_point ".$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);	
			$cursor = $this->paginate->get_cursor( $total[0] );
			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$data = array();
			$sql = $sql.$filter." ORDER BY id DESC LIMIT $cursor,".$this->perpage;	
			$stm = $this->dbconn->query($sql);
			while($row = $stm->fetch(PDO::FETCH_ASSOC)){
				
				/*
				if($row['type']==10 || $row['type']==100){ // 預約/取消 課程				
					$sql = "SELECT classroom_id FROM course_registration WHERE id=".$row['target_id'];
					$stm2= $this->dbconn->query($sql);
					$row2= $stm2->fetch(PDO::FETCH_ASSOC);
					
					$sql = "SELECT `type`,material_id,open_time FROM classroom WHERE id='".$row2['classroom_id']."'"; 
					$stm2= $this->dbconn->query($sql);
					$row2= $stm2->fetch(PDO::FETCH_ASSOC);
					if(isset($var_registration_type[$row2['type']])){
						$row2['type'] = $var_registration_type[$row2['type']];
						$row2['open_time'] = date('Y-m-d H:i',strtotime($row2['open_time'])).' ~ '.date('H:45',strtotime($row2['open_time']));
					}
					$row['brief'] = $row2;
				}
				*/
				unset ($s);
				switch ($row['type'])
				{
					case 10:
					case 100:
					case 130:
						$sql = "SELECT c.* FROM course_registration r, classroom c WHERE r.id = {$row['target_id']} AND r.classroom_id = c.id";
						$stm2 = $this->dbconn->query($sql);
						$r2 = $stm2->fetch(PDO::FETCH_ASSOC);
						$s[] = "<b>課程類型</b>: <span>{$var_registration_type[$r2['type']]}</span>";
						if($r2['type']==40){	//系列，取區間
							$sql = "SELECT MIN(open_time) as begin, MAX(open_time) as end FROM classroom ";
							$sql.= "WHERE course_id=".$r2['course_id']." AND status=10 AND `type`=40";
							$stm3= $this->dbconn->query($sql);
							$date = $stm3->fetch(PDO::FETCH_ASSOC);
							$date_begin = date('Y-m-d',strtotime($date['begin']));
							$date_end 	= date('Y-m-d',strtotime($date['end']));
							$s[] = "<b>課程時間</b>: <span>{$date_begin} ~ {$date_end}</span>";
						}else{
							$s[] = "<b>課程時間</b>: <span>{$r2['date']} {$r2['time']}:00 ~ {$r2['time']}:45</span>";
						}
						if ($row['type'] == 130)
						{
							$ss = explode("\n", $row['brief']);
							$ss = $ss[count($ss) - 1];
							$ss = str_replace('退點原因: ', '', $ss);
							$s[] = "<b>退點原因</b>: <span>$ss</span>";
						}
						break;
					case 20:
					case 120:
						$sql = "SELECT * FROM member_contract WHERE id = {$row['target_id']}";
						$stm2 = $this->dbconn->query($sql);
						$r2 = $stm2->fetch(PDO::FETCH_ASSOC);
						$s[] = "<b>合約名稱</b>: <span>{$r2['contract_name']}</span>";
						$s[] = "<b>合約期限</b>: <span>{$r2['begin']} ~ {$r2['end']}</span>";
						break;
					case 110:
						break;
				}

				if (is_array($s))
					$row['brief'] = implode('<br>', $s);

				$row['title'] = isset($var_point_type[$row['type']]) ? $var_point_type[$row['type']] : 'unknow';
				$data[]=$row;
			}
			$this->tpl->assign('data', $data );			
			$this->tpl->assign('sdate', isset($_GET['sdate']) ? $_GET['sdate'] : '');
			$this->tpl->assign('edate', isset($_GET['edate']) ? $_GET['edate'] : '');
			$this->render('student/points');
		}
	
		function account(){
			
			GLOBAL $var_member_education;
			$this->tpl->assign('var_member_education',$var_member_education);
			
			$data = $this->dbconn->fetch_one('member','id='.$this->mid);
			$this->tpl->assign('data',$data);
			
			//技能
			$member_skill = $this->dbconn->fetch_assoc('member_skill','skill_id','skill_id','member_id='.$this->mid,false);				
			$skill = $this->dbconn->fetch_all('skill','status=10','id ASC','id,title');
			foreach($skill as $key => $value){
				$skill[$key]['checked'] = isset( $member_skill[$value['id']] ) ? true : false;
			}
			$this->tpl->assign('skill',$skill);
			
			//興趣
			$member_interest = $this->dbconn->fetch_assoc('member_interest','interest_id','interest_id','member_id='.$this->mid,false);				
			$interest = $this->dbconn->fetch_all('interest','status=10','id ASC','id,title');
			foreach($interest as $key => $value){
				$interest[$key]['checked'] = isset( $member_interest[$value['id']] ) ? true : false;
			}
			$this->tpl->assign('interest',$interest);
			
			
			$this->render('student/account');
		}
		
		function account_save(){
			
			$this->layout= false;
			
			if(!count($_POST)) 
				die(json_encode((object) array('code'=> 0 ,'msg'=> "輸入錯誤" )));

			$form = array();
			if(!empty($_POST['passwd']))
				$form['password'] = GenPassword( $_SESSION['member']['account'] ,$_POST['passwd']);
			
			$form['tel'] = $_POST['tel'];
			$form['education'] = $_POST['education'];
			$form['mdate'] = date('Y-m-d H:i:s');
			$filter = array('id'=> $this->mid );
			$num = $this->dbconn->update('member',$form,$filter);
			
			if(!$num)
				die(json_encode((object) array('code'=> 1 ,'msg'=> "資料更新失敗" )));
			
			$this->dbconn->delete('member_interest', array('member_id'=>$this->mid) );
			$this->dbconn->delete('member_skill', array('member_id'=>$this->mid) );
			
			foreach($_POST['interest'] as $val){
				$form =  array('member_id'=>$this->mid, 'interest_id'=>$val);
				$this->dbconn->insert('member_interest',$form);
			}
			
			foreach($_POST['skill'] as $val){
				$form =  array('member_id'=>$this->mid, 'skill_id'=>$val);
				$this->dbconn->insert('member_skill',$form);
			}
			
			die(json_encode((object) array('code'=> 1 ,'msg'=> "資料更新完成" )));
		}

		function rule(){
			$this->render('student/rule');
		}
		
		function class_report(){		
			
			if(!is_numeric($this->param)) alert("error");
			
			$data = array();
			$data_checkbox;
			$sql = "SELECT ques_id,tag,content FROM ques_consultant WHERE classroom_id = ".$this->param." AND member_id=".$this->mid;
			$stm = $this->dbconn->query($sql);
			while( $row = $stm->fetch(PDO::FETCH_ASSOC)){				
				if($row['tag']==0)
					$data[$row['ques_id']] = $row['content'];
				else{
					$explode = explode(",",$row['content']);
					$data_checkbox[$row['ques_id']] = $explode;
				}
			}
			$this->tpl->assign('data', $data );
			$this->tpl->assign('data_checkbox', $data_checkbox );
			
			$filter = array('id'=> $this->param );
			$classroom = $this->dbconn->fetch_one('classroom', $filter );
			if(!$classroom) die("Classroom is not exists!");
			
			$filter = array('classroom_id'=> $this->param );
			$cr = $this->dbconn->fetch_one('course_registration', $filter, false );
			if(!$cr) die("Registration is not exists!");			

			$sql = "SELECT id, text FROM questionnaire WHERE `type`=20 ORDER BY rank ASC";
			$stm = $this->dbconn->query($sql);
			$items = array();
			while( $row = $stm->fetch(PDO::FETCH_ASSOC)){
				$items[$row['id']] = $row;
			}
			$checkbox = array();
			$sql = "SELECT * FROM ques_item ";
			$sql.= "WHERE ques_id in ( SELECT id FROM questionnaire WHERE `type`=20 ) ORDER BY rank ASC";
			$stm = $this->dbconn->query($sql);
			while( $row = $stm->fetch(PDO::FETCH_ASSOC)){
				if(isset($items[$row['ques_id']])){
					if($row['tag']==0)
						$items[$row['ques_id']]['options'][$row['id']] = $row['text'];
					else
						$checkbox[$row['ques_id']]['checkbox'][$row['id']] = $row['text'];
				}
			}			
			$this->tpl->assign( 'items', $items );
			$this->tpl->assign( 'checkbox', $checkbox );
			$this->tpl->assign( 'id', $this->param );
			$this->render('student/class_report');
		}
		
		function consultant_report(){
			
			if(!is_numeric($this->param)) alert("error");
			$filter = array('classroom_id'=> $this->param,'member_id'=>$this->mid);
			$data = $this->dbconn->fetch_assoc('ques_student', 'ques_id', 'content', $filter, false);
			$this->tpl->assign('data', $data );
			
			$filter = array('id'=> $this->param);
			$classroom = $this->dbconn->fetch_one('classroom', $filter );
			if(!$classroom) die("Classroom is not exists!");
			
			$filter = array('classroom_id'=> $this->param);
			$cr = $this->dbconn->fetch_one('course_registration', $filter, false );
			if(!$cr) die("Registration is not exists!");			
			
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
			$this->render('student/consultant_report');
		}
		
		function elective(){
			$this->elective_and_hall('elective');
		}
		
		function hall(){
			$this->elective_and_hall('hall');			
		}
		
		function elective_and_hall( $type ){
			
			$const = $type == 'elective' ? 40 : 50;
			
			$sdate = date('Y-m-d H:i:s');
			$edate = date('Y-m-d 23:59:59',time()+(86400*13));
			
			
			//興趣
			$interest = $this->dbconn->fetch_assoc('interest','id','title','status=10','rank ASC');
			$all = array( 0 => "全部主題");
			if ($interest)	$this->tpl->assign('var_interest_type', $all+$interest);
			else $this->tpl->assign('var_interest_type', $all);
			$this->tpl->assign('interest', isset($_GET['interest']) ? $_GET['interest'] : ''  );
			
			//顧問
			$rs = $this->dbconn->query("SELECT id, first_name, last_name FROM consultant");
			$consultant = array();
			while($row = $rs->fetch(PDO::FETCH_ASSOC)){
				$consultant[ $row['id'] ] =  $row['first_name'].' '.$row['last_name'];
			}
						
			$sql = "SELECT cr.id, cr.open_time, c.id as course_id, c.course_name, c.material_id, c.consultant_id, c.brief, c.point, ci.image  ";
			$cnt_colum = "SELECT COUNT(*) ";
			$table = "FROM classroom cr, course c LEFT JOIN course_images ci ON ci.parent = c.id ";
			$filter = "WHERE c.id = cr.course_id AND cr.type=$const AND cr.open_time BETWEEN '$sdate' AND '$edate' AND c.status=10 AND c.deleted=0 AND cr.status=10 ";
			if($type=='elective')
				$filter.=" AND cr.lesson=1";
			if($_GET['interest'])
				$filter.=" AND c.material_id in (SELECT material_id FROM material_interest WHERE interest_id = ".$_GET['interest']." GROUP BY material_id )";
			
			$this->paginate = load_component('Paginate',true);
			$this->paginate->perpage = $this->perpage;
			$this->paginate->elective = true;
			$this->paginate->url = "/student/$type";
			$stm  = $this->dbconn->query( $cnt_colum.$table.$filter );
			$total = $stm->fetch(PDO::FETCH_NUM);
			$cursor = $this->paginate->get_cursor( $total[0] );			
			$this->tpl->assign("paginate",$this->paginate->pager());
			
			$sql.= $table. $filter ." ORDER BY open_time ASC LIMIT $cursor,".$this->perpage;
			$stm = $this->dbconn->query($sql);
			$data = array();
			
			$limit_person = $type == 'elective' ? ELECTIVE_PERSONS : HALL_PERSONS;
			$full_person = $type == 'elective' ? 2 : 5;
			
			while($classroom = $stm->fetch(PDO::FETCH_ASSOC)){
				
				//老師
				$classroom['consultant'] = $consultant[ $classroom['consultant_id'] ];
				
				//已預約人數
				$filter = array('classroom_id'=>$classroom['id'], 'status'=>10);
				$classroom['registration'] = $this->dbconn->fetch_count('course_registration',$filter, false );
				
				//即將額滿
				$classroom['almost_full'] = $limit_person - $classroom['registration'] <= $full_person ? true : false;
				
				//已選課
				$filter = array('classroom_id'=>$classroom['id'], 'status'=>10, 'member_id'=>$this->mid );
				$classroom['disabled'] = $this->dbconn->fetch_count('course_registration',$filter, false );
				
				//lesson數量
				if($type=='elective'){				
					$filter = array('course_id'=>$classroom['course_id'], 'status'=>10, 'type'=>$const);
					$classroom['lesson_total'] = $this->dbconn->fetch_count('classroom',$filter, false );
				}
				
				//日期區間
				if($type=='elective'){
					$sql = "SELECT MIN(open_time) as begin, MAX(open_time) as end FROM classroom ";
					$sql.= "WHERE course_id=".$classroom['course_id']." AND status=10 AND `type`=$const";
					$stm2= $this->dbconn->query($sql);
					$date = $stm2->fetch(PDO::FETCH_ASSOC);
					$classroom['duration']= $date['begin']==$date['end'] ? 
											date('D Y/n/j',strtotime($date['begin'])) :
											date('D Y/n/j',strtotime($date['begin'])).' ~ '.date('D Y/n/j',strtotime($date['end']));
				}else{
					$classroom['duration']=date('D Y/n/j',strtotime($classroom['open_time']));
				}
				
				//興趣
				$sql = "SELECT id,title FROM interest WHERE id in ( ";
				$sql.= "SELECT interest_id FROM material_interest WHERE material_id = '".$classroom['material_id']."' ) AND status=10 ";
				$stm2 =  $this->dbconn->query($sql);
				$classroom['interest'] = $stm2->fetchAll(PDO::FETCH_ASSOC);
				
				//教材最低等級
				$sql = "SELECT MIN(level_id) FROM material_level WHERE material_id = ".$classroom['material_id'];
				$stm2 =  $this->dbconn->query($sql);
				$level = $stm2->fetch(PDO::FETCH_NUM);
				$classroom['level'] = $level[0];
				
				array_push( $data, $classroom);
			}
			
			$this->tpl->assign('limit_person', $limit_person);
			$this->tpl->assign('data',$data);
			$this->tpl->assign('type',$type);
			$this->render("student/elective");
		}
		
	}
?>
