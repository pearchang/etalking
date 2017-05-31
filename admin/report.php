<?php
	$save_path = "../../report";
	require('cron.inc.php');
	include '../../lib/PHPExcel/Autoloader.php';
	
	// 1=>"學員資料", 2=> "學員上課狀況", 3=>"老師基本資料", 4=>"老師薪資報表", 5=>"老師DEMO時數"
	
	echo "<pre>";
	$sql = "SELECT * FROM report WHERE deleted=0 AND cron_date = '0000-00-00' ";
	$rs->query($sql);
	if($rs->count>0){
		while($row = $rs->fetch()){
			switch($row['category']){
				case 1: report_student_profile($row);   break;
				case 2: report_student_classroom($row); break;
				case 3: report_consultant_profile($row);break;
				case 4: report_consultant_salary($row); break;
				case 5: report_consultant_demo($row);   break;
				
				default:
					echo $row['id']."unknow category.\n";
			}
		}
	}

	//學員資料
	function report_student_profile($data){
		
		GLOBAL $rs2, $save_path;
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
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
		echo $sql;
		$rs2->query($sql);
		if($rs2->count>0){
			while($row = $rs2->fetch()){
					$values = Array(
						'A'=> $row['account'],
						'B'=> $row['member_name'],
						'C'=> $row['first_name'].' '.row['last_name'],
						'D'=> $row['mobile'],
						'E'=> $row['email'],
						'F'=> $row['status']
					);
					foreach($fields as $j => $val){					
						$objPHPExcel->getActiveSheet()->setCellValueExplicit( $j.$i, $val,PHPExcel_Cell_DataType::TYPE_STRING);
					}
					$i++;
			}
			$filename = 'student_profile.'.date('Ymd',$sdate).'-'.date('Ymd',$edate).'.xlsx';
			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->save( $save_path.$filename );
			
			$sql = "UPDATE report SET cron_date='".date('Y-m-d')."',filename='".$filename."' WHERE id=".$data['id'];
			$rs2->query($sql);
			return true;
		}else{
			//無資料
		}
	}
	

	//學員上課狀況
	function report_student_classroom($data){
		GLOBAL $rs;
		
	}
	
	
	//老師基本資料
	function report_consultant_profile($data){
		GLOBAL $rs;
		
	}
	
	
	//老師薪資報表
	function report_consultant_salary($data){
		GLOBAL $rs;
		
	}
	
	
	//老師DEMO時數
	function report_consultant_demo($data){
		GLOBAL $rs;
		
	}


?>