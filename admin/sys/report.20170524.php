<?php
	set_time_limit(0);
	echo "<pre>";
	ini_set('display_errors', 1);
	
	$save_path = "../../report/";
	require('cron.inc.php');
	include '../../vendor/PHPExcel.php';
	
	$category = array( 1=>"學員資料", 2=> "學員上課狀況", 7=> "學員點數帳目", 3=>"老師基本資料", 4=>"老師薪資報表", 5=>"老師DEMO時數", 8=>"老師上課時數");

	if(isset($_GET['id']) AND is_numeric($_GET['id']) ){
		$sql = "SELECT * FROM report WHERE deleted=0 AND id=".$_GET['id'];
	}else{
		$sql = "SELECT * FROM report WHERE deleted=0 AND cron_date = '0000-00-00 00:00:00' ";
	}
	$rs->query($sql);
	if($rs->count>0){
		while($row = $rs->fetch()){
			switch($row['category']){
				case 1: report_student_profile($row);   break;
				case 2: report_student_classroom($row); break;				
				case 7: report_student_point_monthly($row); break;
				case 3: report_consultant_profile($row);break;
				case 4: report_consultant_salary($row); break;
				case 5: report_consultant_demo($row);   break;
				case 8: report_consultant_classroom($row);break;				
				default:
					echo $row['id']."unknow category.\n";
			}
		}
	}

	//學員資料
	function report_student_profile($data){
		
		GLOBAL $rs2, $save_path, $var_member_status, $var_member2_status, $var_member_status2;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("學員資料");
		$fields = Array('A'=>'登入帳號',
						'B'=>'姓名',
						'C'=>'英文姓名',
						'D'=>'電話',
						'E'=>'EMAIL',
						'F'=>'狀態'
						);
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		//依合約建立日期
		$sdate = $data['sdate'].' 00:00:00';
		$edate = $data['edate'].' 23:59:59';
		$sql = "SELECT account,member_name,first_name,last_name,mobile,email,status FROM member WHERE deleted=0 AND ";
		$sql.= "id in ( SELECT member_id FROM member_contract WHERE deleted=0 AND cdate BETWEEN '$sdate' AND '$edate' )";
		$sql.= "ORDER BY id DESC";
		$i=2;
		$nodata = true;
		$rs2->query($sql);
		$var_member_status+=$var_member2_status+$var_member_status2;
		if($rs2->count>0){
			while($row = $rs2->fetch()){
					$values = Array(
						'A'=> $row['account'],
						'B'=> $row['member_name'],
						'C'=> $row['first_name'].' '.$row['last_name'],
						'D'=> $row['mobile'],
						'E'=> $row['email'],
						'F'=> isset($var_member_status[$row['status']]) ? $var_member_status[$row['status']] : $row['status']
					);
					foreach($values as $j => $val){					
						$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
		}else{
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		send_mail( $data, $nodata );
	}
	

	//學員上課狀況
	function report_student_classroom($data){
		
		GLOBAL $rs2, $save_path, $var_course_attend, $var_registration_type;
		
		$objPHPExcel = new PHPExcel();		
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("學員上課狀況");
		$fields = Array('A'=>'姓名',
						'B'=>'課程時間',
						'C'=>'類型',
						'D'=>'老師',
						'E'=>'課程名稱',
						'F'=>'出席狀態',
						'G'=>'使用點數',						
						'H'=>'上課時數稽核',
						'I'=>'上課時數',
						'J'=>'備註'
						);
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		$sdate = $data['sdate'].' 00:00:00';
		$edate = $data['edate'].' 23:59:59';
		$sql = "SELECT c.id, c.consultant_id, c.hour, c.open_time, c.type, c.point, cr.attend, ";
		$sql.= "cst.first_name, cst.last_name, cst.chi_name,";
		$sql.= "cr.member_id, m.member_name, m.account, mt.title FROM classroom c ";
		$sql.= "INNER JOIN course_registration cr ON cr.classroom_id = c.id ";		
		$sql.= "LEFT JOIN member m ON m.id = cr.member_id ";
		$sql.= "LEFT JOIN consultant cst ON cst.id = c.consultant_id ";
		$sql.= "LEFT JOIN material mt ON mt.id = c.material_id ";
		$sql.= "WHERE c.status=10 ";
		$sql.= "AND c.open_time BETWEEN '$sdate' AND '$edate' ";
		$sql.= "AND c.consultant_id>0 AND c.consultant_confirmed=10 AND cr.status = 10 ";
		$sql.= "ORDER BY c.open_time ASC";

		$i=2;
		$nodata = true;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
					
					$name = $row['first_name'].' '.$row['last_name'];
					if(!empty($row['chi_name'])) $name.= ' /'.$row['chi_name'];
					$values = Array(
						'A'=> $row['member_name'],
						'B'=> substr($row['open_time'],0,16),
						'C'=> $var_registration_type[$row['type']],//類型
						'D'=> $name, //老師
						'E'=> $row['title'], //課程名稱
						'F'=> $var_course_attend[$row['attend']], //出席狀態
						'G'=> $row['point'], //使用點數
						'H'=> '', //上課時數稽核
						'I'=> $row['hour'],
						'J'=> '' //備註
					);
					foreach($values as $j => $val){					
						if( $j=='G' or $j=='H' or $j=='I'  )
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_NUMERIC);
						else	
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
			
			
		}else{
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
		
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		//send_mail( $data, $nodata );		
		$data['filename'] = $filename;
		report_student_point($data);
		
	}
	
	//學員退點
	function report_student_point($data){
		
		GLOBAL $rs2, $save_path;		

		$objPHPExcel = PHPExcel_IOFactory::createReader('Excel2007');
		$objPHPExcel = $objPHPExcel->load( $save_path.$data['filename'] );
		$objPHPExcel->createSheet(1)->setTitle("點數異動明細");
		$objPHPExcel->setActiveSheetIndex(1);
		$objPHPExcel->getActiveSheet();

		$fields = Array('A'=>'姓名',
						'B'=>'異動時間',
						'C'=>'異動點數',
						'D'=>'類型',
            'E'=>'課程類型',
            'F'=>'課程時間',
            'G'=>'取消原因',
						);
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		$sdate = $data['sdate'].' 00:00:00';
		$edate = $data['edate'].' 23:59:59';
		$sql = "SELECT m.member_name, p.type, p.io, p.cdate, p.type, p.brief ";
		$sql.= "FROM member_point p ";				
		$sql.= "LEFT JOIN member m ON m.id = p.member_id ";		
		$sql.= "WHERE p.cdate BETWEEN '$sdate' AND '$edate' AND (p.type >= 100 OR p.type = 10)";
		$sql.= "ORDER BY m.id ASC, p.cdate ASC";

		$i=2;
		$nodata = true;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
			    if ($row['type'] == '140')
          {
            $reason = $row['brief'];
            $type = '退還點數';
            $ctype = '';
            $datetime = '';
          }
          else
          {
            if (strstr($row['brief'], "\n"))
            { // 有取消原因
              $s = explode("\n", $row['brief']);
              $reason = mb_substr($s[1], 5);
              $s = explode(" ", $s[0]);
            }
            else
            { // 一般
              $s = explode(" ", $row['brief']);
              $reason = '';
            }
            if (mb_strlen($s[1]) > 3)
            {
              $s[2] = mb_substr($s[1], 3) . ' ' . $s[2];
              $s[1] = mb_substr($s[1], 0, 3);
            }
            if (count($s) >= 4)
            {
              $type = $s[0];
              $ctype = $s[1];
              $datetime = $s[2] . ' ' . $s[3] . ' ' . $s[4] . ' ' . $s[5] . ' ' . $s[6];
            }
            else
            {
              $type = $s[0];
              $ctype = mb_substr($s[1], 0, 3);
              $datetime = mb_substr($s[1], 3) . ' ' . $s[2];
            }
          }
					$values = Array(
						'A'=> $row['member_name'],
						'B'=> substr($row['cdate'],0,16),
						'C'=> $row['io'],
            'D'=> $type,
            'E'=> $ctype,
            'F'=> $datetime,
            'G'=> $reason,
					);
					
					foreach($values as $j => $val){
						if( $j=='C' )
              $objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_NUMERIC);
						else
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			$filename = $data['filename'];
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
		}else{
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		send_mail( $data, $nodata );		
	}

    function column($a)
	{
	  $s = '';
	  if ($a > 90)
	  {
	    $s = 'A';
		$a -= 26;
	  }
	  return $s . chr($a);
	}
	//學員點數帳目
	function report_student_point_monthly($data){
		
		GLOBAL $rs2, $rs3, $save_path;
		
		$st = strtotime($data['sdate']);
		$et = strtotime($data['edate']);
		
		$sdate = date( 'Y-m-01', $st );
		$edate = date( 'Y-m-t',  $et );
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("學員點數帳目");
		$fields = Array('A'=>'姓名',
						'B'=>'登入帳號',
						'C'=>'總點數(含贈)',
						'D'=>'合約總價(含稅)',
						'E'=>'合約總價(未稅)',
						'F'=>'累計使用點數',
						'G'=>'剩餘點數',
						'H'=>'平均點數-未稅'
						);
		$ascii = 73;

		for($i= strtotime($sdate); $i<= strtotime($edate); $i+=(86400*date('t',$i)) ){
			$fields[column($ascii++)] = date('Y/m',$i);
		}
		
		$fields[column($ascii++)] ='預收收入金額';
		$memo = column($ascii++);
		$fields[$memo] ='備註';
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		//$test = "AND m.id=32148";
		
		$sql = <<<EOT
		SELECT m.id, m.member_name, m.account, SUM(c.point)+SUM(c.gift) as point, SUM(price) as price
		FROM member m, member_contract c		
		WHERE m.id = c.member_id AND c.status=20 $test
		GROUP BY m.id ORDER BY m.id ASC
EOT;

		$col=2;
		$nodata = true;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
				    // point & money
					$sql = "SELECT SUM(p.io) as point, SUM(c.price) AS price FROM member_point p, member_contract c WHERE p.member_id={$row['id']} AND p.target_id = c.id AND p.type = 20 AND p.cdate BETWEEN '$sdate 00:00:00' AND '$edate 23:59:59' ";
					//echo $sql;
					$rs3->query($sql);
					$r3 = $rs3->fetch();
					if (empty($r3['point']))
						continue;
					$row['point'] = $r3['point'];
					$row['price'] = $r3['price'];

					// 退點
          $sql = "SELECT SUM(p.io) as io FROM member_point p, course_registration r, classroom c WHERE p.member_id={$row['id']} AND p.`type` = 110 AND p.target_id = r.id AND r.classroom_id = c.id AND c.open_time BETWEEN '$sdate 00:00:00' AND '$edate 23:59:59'";
          $rs3->query($sql);
          $r3 = $rs3->fetch();
          if($r3 && isset($r3['io']))
            $row['point']+=$r3['io'];
					// 手動贈點
          $used = 0;
					$sql = "SELECT SUM(io) as io FROM member_point WHERE member_id={$row['id']} AND `type` = 140 AND cdate BETWEEN '$sdate 00:00:00' AND '$edate 23:59:59'";
					$rs3->query($sql);
					$r3 = $rs3->fetch();
					if($r3 && isset($r3['io']))
          {
            $row['point'] += $r3['io'];
            $used -= $r3['io'];
          }

					//累計使用點數
					$sql = "SELECT SUM(p.io) as io FROM member_point p, course_registration r, classroom c WHERE p.member_id={$row['id']} AND p.`type` in (10,100,110,130) AND p.target_id = r.id AND r.classroom_id = c.id AND c.open_time BETWEEN '$sdate 00:00:00' AND '$edate 23:59:59'";
					$rs3->query($sql);
					$r3 = $rs3->fetch();
					$used += abs($r3['io']);
					//$balance = $r3['balance'];
					
					//剩餘點數
					$sql = "SELECT p.balance as io FROM member_point p, course_registration r, classroom c WHERE p.member_id={$row['id']} AND p.target_id = r.id AND r.classroom_id = c.id AND c.open_time BETWEEN '$sdate 00:00:00' AND '$edate 23:59:59' ORDER BY p.id DESC LIMIT 1";
//					echo $sql;
//					exit;
					$rs3->query($sql);
					$r3 = $rs3->fetch();
					$balance = abs($r3['io']);
					
					$duty_free = floor($row['price']/1.05);
					$avg_point_price = floor( $duty_free/$row['point'] );
					
					$values = Array(
						'A'=> $row['member_name'],
						'B'=> $row['account'],
						'C'=> $row['point'],
						'D'=> $row['price'],
						'E'=> $duty_free,
						'F'=> $used,
						'G'=> $balance,
						'H'=> $avg_point_price
					);
					
					$ascii = 73;
					$sql = <<<EOT
					SELECT DATE_FORMAT(c.open_time,'%Y%m') as cdate, SUM(io) as io FROM member_point p, course_registration r, classroom c WHERE p.member_id={$row['id']} AND 
					p.`type` in (10,100,110,130) AND 
					p.target_id = r.id AND r.classroom_id = c.id AND c.open_time BETWEEN '$sdate 00:00:00' AND '$edate 23:59:59'
					GROUP BY DATE_FORMAT(c.open_time,'%Y%m')
EOT;
					$rs3->query($sql);
					$monthly = array();
					while( $r3 = $rs3->fetch() ){
						$monthly[$r3['cdate']] = 0 - $r3['io'];
					}
          $sql = "SELECT DATE_FORMAT(cdate, '%Y%m') as cdate, SUM(io) as io FROM member_point WHERE member_id={$row['id']} AND `type` = 140 AND cdate BETWEEN '$sdate 00:00:00' AND '$edate 23:59:59' GROUP BY DATE_FORMAT(cdate,'%Y%m')";
          $rs3->query($sql);
          while( $r3 = $rs3->fetch() )
          {
            $monthly[$r3['cdate']] -= $r3['io'];
          }
          //print_r($monthly);
					for($i= strtotime($sdate); $i<= strtotime($edate); $i+=(86400*date('t',$i)) ){
						$values[column($ascii++)] = (int)$monthly[date('Ym',$i)];
					}
					$values[column($ascii++)] = $duty_free - ( $used * $avg_point_price);
					$values[column($ascii++)] ='';			
					

					foreach($values as $j => $val){
						if( $j=='A' or $j=='B' || $j==$memo)
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$col, $val,PHPExcel_Cell_DataType::TYPE_STRING);
						else	
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$col, $val,PHPExcel_Cell_DataType::TYPE_NUMERIC);
					}
					$col++;
			}
			
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
		}else{
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		send_mail( $data, $nodata );		
	}	
	
	//老師基本資料
	function report_consultant_profile($data){
		
		$content = file_get_contents("../template/country_options.html");
		preg_match_all("/<option value=\"(.[^\"]*)\">(.[^<]*)<\/option>/im",$content,$matches);
		$countries = array();
		foreach($matches[1] as $key=> $value ){
			$countries[$value] = $matches[2][$key];
		}
		
		GLOBAL $rs2, $save_path;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("老師基本資料");
		$fields = Array('A'=>'Name',
						'B'=>'Nationality',
						'C'=>'Standby',
						'D'=>'上課時薪',
						'E'=>'試讀薪資(計件)'						
						);
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		$sdate = $data['sdate'].' 00:00:00';
		$edate = $data['edate'].' 23:59:59';
		$sql = "SELECT first_name,last_name,chi_name,country,course_pay,demo_pay,status FROM consultant WHERE deleted=0 AND ";
		$sql.= "cdate BETWEEN '$sdate' AND '$edate' ";
		$sql.= "ORDER BY id DESC";
		$i=2;
		$nodata = true;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
					$name = $row['first_name'].' '.$row['last_name'];
					if(!empty($row['chi_name'])) $name.= ' /'.$row['chi_name'];
					$values = Array(
						'A'=> $name,
						'B'=> isset($countries[$row['country']]) ? $countries[$row['country']] : $row['country'],
						'C'=> $row['status']==10 ? 'Y' : 'N',
						'D'=> $row['course_pay'],
						'E'=> $row['demo_pay']						
					);					
					
					foreach($values as $j => $val){
						if( $j=='D' or $j=='E')
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_NUMERIC);
						else	
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
		}else{
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		send_mail( $data, $nodata );
		
	}
	
	
	//老師薪資報表
	function report_consultant_salary($data){
		
		GLOBAL $rs2, $save_path;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("老師薪資報表");
		$fields = Array('A'=>'教師編號',
						'B'=>'教師名稱',
						'C'=>'上課日期',						
						'D'=>'學員編號',
						'E'=>'學員姓名',						
						'F'=>'上課時數',
						'G'=>'上課薪資',
						'H'=>'試讀時數',
						'I'=>'試讀時薪(計件)',
						'J'=>'獎勵金',
						'K'=>'扣款',
						'L'=>'薪資小計'
						);
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		$sdate = $data['sdate'].' 00:00:00';
		$edate = $data['edate'].' 23:59:59';
		$sql = "SELECT c.id, c.consultant_id, c.hour, c.wage, c.open_time, c.salary, c.reward, c.type, ";
		$sql.= "cst.first_name, cst.last_name, cst.chi_name,";
		$sql.= "cr.member_id, m.member_name, m.account FROM classroom c ";
		$sql.= "INNER JOIN course_registration cr ON cr.classroom_id = c.id ";		
		$sql.= "LEFT JOIN member m ON m.id = cr.member_id ";
		$sql.= "LEFT JOIN consultant cst ON cst.id = c.consultant_id ";
		$sql.= "WHERE c.status=10 AND cr.status = 10 ";
		$sql.= "AND c.open_time BETWEEN '$sdate' AND '$edate' ";
		$sql.= "AND c.consultant_id>0 AND c.consultant_confirmed=10 ";
		$sql.= "ORDER BY c.open_time ASC";

		$i=2;
		$nodata = true;
		$class_id = 0;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
					if ($class_id != 0 && $class_id == $row['id'])
          {
            $row['hour'] = 0;
            $row['wage'] = 0;
            $row['reward'] = 0;
            $row['salary'] = 0;
          }
          $class_id = $row['id'];
					$name = $row['first_name'].' '.$row['last_name'];
					if(!empty($row['chi_name'])) $name.= ' /'.$row['chi_name'];
					$values = Array(
						'A'=> '',
						'B'=> $name,
						'C'=> substr($row['open_time'],0,10),						
						'D'=> $row['account'],
						'E'=> $row['member_name'],
						'F'=> $row['type']==10 ? '' : $row['hour'],
						'G'=> $row['type']==10 ? '' : $row['salary'],
						'H'=> $row['type']!=10 ? '' : $row['hour'],
						'I'=> $row['type']!=10 ? '' : $row['wage'],
						'J'=> $row['reward']>=0 ? $row['reward'] : '',
						'K'=> $row['reward']<=0 ? $row['reward'] : '',
						'L'=> $row['type']==10 ? ($row['hour']*$row['wage'])+$row['reward'] : $row['salary']+$row['reward']
					);
					foreach($values as $j => $val){
						if($j=='F' || $j=='G' ||$j=='H' || $j=='I' || $j=='J' || $j=='K' || $j=='L')
							 $objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_NUMERIC);
						else $objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
		}else{
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		send_mail( $data, $nodata );
		
	}
	
	
	//老師DEMO時數
	function report_consultant_demo($data){
		
		GLOBAL $rs2, $save_path;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("老師DEMO時數");
		$fields = Array('A'=>'教師編號',
						'B'=>'教師名稱',
						'C'=>'上課日期',
						'D'=>'學員編號',
						'E'=>'學員姓名',
						'F'=>'試讀時數',
						'G'=>'試讀時薪(計件)'
						);
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		$sdate = $data['sdate'].' 00:00:00';
		$edate = $data['edate'].' 23:59:59';
		$sql = "SELECT c.id, c.consultant_id, c.hour, c.wage, c.open_time,";
		$sql.= "cst.first_name, cst.last_name, cst.chi_name,";
		$sql.= "cr.member_id, m.member_name, m.account FROM classroom c ";
		$sql.= "LEFT JOIN course_registration cr ON cr.classroom_id = c.id ";
		$sql.= "LEFT JOIN member m ON m.id = cr.member_id ";
		$sql.= "LEFT JOIN consultant cst ON cst.id = c.consultant_id ";
		$sql.= "WHERE c.type=10 AND c.status=10 ";
		$sql.= "AND c.open_time BETWEEN '$sdate' AND '$edate' ";
		$sql.= "AND c.consultant_id>0 AND c.consultant_confirmed=10 ";
		$sql.= "ORDER BY c.open_time ASC";

		$i=2;
		$nodata = true;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
					$name = $row['first_name'].' '.$row['last_name'];
					if(!empty($row['chi_name'])) $name.= ' /'.$row['chi_name'];
					$values = Array(
						'A'=> '',
						'B'=> $name,
						'C'=> substr($row['open_time'],0,10),
						'D'=> $row['account'],
						'E'=> $row['member_name'],
						'F'=> $row['hour'],
						'G'=> $row['wage']
					);
					foreach($values as $j => $val){
						
						if($j=='F' || $j=='G')
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_NUMERIC);
						else						
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
		}else{
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		send_mail( $data, $nodata );
		
	}
	
	//老師上課時數
	function report_consultant_classroom($data){
		
		GLOBAL $rs2, $save_path;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0)->setTitle("老師上課時數");
		$fields = Array('A'=>'教師編號',
						'B'=>'教師名稱',
						'C'=>'上課日期',
						'D'=>'月份',
						'E'=>'教室編號',
						'F'=>'學員編號',
						'G'=>'學員姓名',
						'H'=>'上課時數',
						'I'=>'上課時薪'
						);
		foreach($fields as $key => $value){
				$objPHPExcel->getActiveSheet()->setCellValue( $key.'1', $value);
		}
		
		$sdate = $data['sdate'].' 00:00:00';
		$edate = $data['edate'].' 23:59:59';
		$sql = "SELECT c.id, c.consultant_id, c.hour, c.wage, c.open_time, c.sn, ";
		$sql.= "cst.first_name, cst.last_name, cst.chi_name,";
		$sql.= "cr.member_id, GROUP_CONCAT(m.member_name) AS member_name, GROUP_CONCAT(m.account) AS account FROM classroom c ";
		$sql.= "LEFT JOIN course_registration cr ON cr.classroom_id = c.id ";
		$sql.= "LEFT JOIN member m ON m.id = cr.member_id ";
		$sql.= "LEFT JOIN consultant cst ON cst.id = c.consultant_id ";
		$sql.= "WHERE c.type!=10 AND c.status=10 AND cr.status = 10 ";
		$sql.= "AND c.open_time BETWEEN '$sdate' AND '$edate' ";
		$sql.= "AND c.consultant_id>0 AND c.consultant_confirmed=10 ";
		$sql.= "GROUP BY c.id ORDER BY c.open_time ASC";

		$i=2;
		$nodata = true;
		$class_id = 0;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
        if ($class_id != 0 && $class_id == $row['id'])
        {
          $row['hour'] = 0;
          $row['wage'] = 0;
          $row['reward'] = 0;
          $row['salary'] = 0;
        }
        $class_id = $row['id'];
					$name = $row['first_name'].' '.$row['last_name'];
					if(!empty($row['chi_name'])) $name.= ' /'.$row['chi_name'];
					$values = Array(
						'A'=> '',
						'B'=> $name,
						'C'=> substr($row['open_time'],0,10),
						'D'=> date('m', strtotime($row['open_time'])),
						'E'=> $row['sn'],
						'F'=> $row['account'],
						'G'=> $row['member_name'],
						'H'=> $row['hour'],
						'I'=> $row['wage']
					);
					foreach($values as $j => $val){
						if($j=='D' || $j=='H' || $j=='I')
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_NUMERIC);
						else
							$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			
			$filename = $data['filename'] ? $data['filename'] : gen_filename( $data['id'] );
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			$nodata = false;
		}else{
			$sql = "UPDATE report SET cron_date='".date('Y-m-d H:i:s')."' WHERE id=".$data['id'];
			$rs2->query($sql);
		}
		send_mail( $data, $nodata );
		
	}
	
	//通知信
	function send_mail( $data, $nodata = false ){
		
		GLOBAL $rs2, $category, $EMAIL_SUBJECT_REPORT;
		$v = array();
		$v['nodata'] = $nodata;
		$v['category'] = $category[$data['category']];
		$v['sdate'] = $data['sdate'];
		$v['edate'] = $data['edate'];
		$sql = "SELECT user_name, email FROM `user` WHERE id='".$data['creator']."'";
		$rs2->query($sql);
		if($rs2->count>0){
			$user = $rs2->fetch();
			if(!empty($user['email'])){				
				$v['name'] = $user['user_name'];				
				$m = new MailModule();
				$m->template = '';
				$m->content = "<html><head></head><body>Dear ".$v['name'].",<br><br>";
				if($nodata) $m->content.= "您的報表無任何資料";
				else 		$m->content.= "您的報表產生完成，請至後台下載";
				$m->content.= "<br>類型：".$v['category']."<br>起訖：".$v['sdate']." ~ ".$v['edate']."<br></body></html>";
				$m->addAddress( $user['email'], $user['user_name'] );
				$m->subject = $EMAIL_SUBJECT_REPORT;
				if(!$m->send()) echo $m->phpmailer->ErrorInfo;
				
			}
		}
	}
	
	function gen_filename( $id ){
		
		GLOBAL $rs3;
		$sql = "SELECT sn FROM report WHERE id = $id ";
		$rs3->query($sql);
		$sn = $rs3->fetch();
		return $sn['sn'].'.xlsx';

		/*
		GLOBAL $rs3;
		$sql = "SELECT COUNT(*) as cnt FROM report WHERE insert_date BETWEEN  '".date('Y-m-d 00:00:00')."' AND '".date('Y-m-d 23:59:59')."'";
		$rs3->query($sql);
		$sn = $rs3->fetch();

		$sn = date('Ymd').'_'.sprintf("%03d",$sn['cnt']);
		$sql = "UPDATE report SET sn='$sn' WHERE id=".$id;
		$rs3->query($sql);
		return $sn.'.xlsx';
		*/
	}



?>