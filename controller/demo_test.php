<?php
	class Page extends APP{
	
		function enter(){
			
			$this->layout = false;
			
			$webex= false;
			$date = false;

			$filter = array('memo'=> $this->param, 'status' => 10 );
			$classroom = $this->dbconn->fetch_one('classroom_test', $filter);
			if(!$classroom) alert("預約不存在","/");
			$this->tpl->assign('classroom_id',$classroom['id']);
			
			if($classroom){		
			
				$stime = strtotime( $classroom['open_time'] );
				$etime = strtotime( $classroom['open_time'] ) + (29*60);
				$date = array( 'date'=> date('Y/m/d',$stime) ,
									'week'=> date('l',$stime) ,
									'start_time' => date('H:i',$stime),
									'end_time' => date('H:i',$etime)
							);
				$webex = enter_webex_test($classroom['id']);
				$this->tpl->assign('teacher', isset($_GET['teacher'])? true : false );
				$this->tpl->assign('disable', $webex ? '' : 'disable');
				
			}

			$this->tpl->assign('date',$date);
			$this->render('demo_test/enter');
		}
		
	}
?>