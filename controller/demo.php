<?php
	class Page extends APP{
	
		function enter(){
			
			$this->layout = false;
			
			$filter = array('memo'=> $this->param );
			$cr = $this->dbconn->fetch_one('course_registration', $filter);
			if(!$cr) alert("預約不存在","/");
			
			$this->tpl->assign('classroom_id',$cr['classroom_id']);
			
			$webex= false;
			$date = false;

			$filter = array('id'=> $cr['classroom_id'], 'status' => 10 );
			$classroom = $this->dbconn->fetch_one('classroom', $filter);
			if(!$classroom) alert("預約不存在-2","/");
			
			if($classroom){		
			
				$stime = strtotime( $classroom['open_time'] );
				$etime = strtotime( $classroom['open_time'] ) + (45*60);
				$date = array( 'date'=> date('Y/m/d',$stime) ,
									'week'=> date('l',$stime) ,
									'start_time' => date('H:i',$stime),
									'end_time' => date('H:i',$etime)
							);
				$webex = enter_webex($classroom['id'],'demo');
				$this->tpl->assign('disable', $webex ? '' : 'disable');
				
			}

			$this->tpl->assign('date',$date);
			$this->render('demo/enter');
		}
		
	}
?>