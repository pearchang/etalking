<?php
	class Page extends APP{
		
		var $bu = '';
		
		function init(){
			$this->bu = HTTP_ROOT.'/webex_test/bu';
			parent::init();
		}
	
		
		function teacher_demo(){			
			
			$this->layout = false;
			
			if(!isset($_GET['classroom'])) die("error!");
			
			$webex = enter_webex_test( $_GET['classroom'],'teacher_demo', true);	
			
			if(!$webex) die("Classroom is not exists!");
			
			if(is_string($webex)) $this->rescue($webex);
			
			if(!isset($_GET['ST'])){
				$bu = HTTP_ROOT."/webex_test/teacher_demo?classroom=".$_GET['classroom'];
				echo '<meta charset="UTF-8">
					<form id="logout" method=POST action="https://etalking.webex.com/etalking/p.php">
					<input type="hidden" name="AT" value="LO">
					<input type="hidden" name="BU" value="'.$bu.'" >';				
					echo '</form><script>document.getElementById("logout").submit();</script>';				
			}else{
				$bu = $this->bu.'?type=teacher_demo';
				echo '<meta charset="UTF-8">
					<form id="login" method=POST action="https://etalking.webex.com/etalking/m.php">
					<input type="hidden" name="AT" value="JM" ><br>
					<input type="hidden" name="AN" value="'.$webex['name'].'">
					<input type="hidden" name="AE" value="'.$webex['email'].'">
					<input type="hidden" name="MK" value="'.$webex['meeting_key'].'">
					<input type="hidden" name="PW" value="'.$webex['meeting_pw'].'">
					<input type="hidden" name="BU" value="'.$bu.'" >';
				echo '</form><script>document.getElementById("login").submit();</script>';
			}			
		}
		
		function demo(){
			
			$this->layout = false;
		
			if(!isset($_GET['classroom'])) die("error!");
			
			$webex = enter_webex_test( $_GET['classroom'], 'demo',true);

			if(!$webex) die("Classroom is not exists!");

			if(is_string($webex)) $this->rescue($webex);
			
			echo '<meta charset="UTF-8">
					<form id="login" method=POST action="https://etalking.webex.com/etalking/m.php">
					<input type="hidden" name="AT" value="JM" size=50><br>
					<input type="hidden" name="AN" value="'.$webex['name'].'">
					<input type="hidden" name="AE" value="'.trim($webex['email']).'">
					<input type="hidden" name="MK" value="'.$webex['meeting_key'].'">
					<input type="hidden" name="PW" value="'.$webex['meeting_pw'].'">
					<input type="hidden" name="BU" value="'.$this->bu.'?type=demo&classroom='.$_GET['classroom'].'" >';				
			echo '</form><script>document.getElementById("login").submit();</script>';	
		}
		
		function bu(){
			
			if(isset($_POST['AT']) && $_POST['AT']=='JM'){
				
				if($_POST['type']=='student' || $_POST['type']=='demo'){
					if($_POST['ST']=='FAIL'){		
						$this->log($_POST['classroom']);
						if($_POST['RS']=='MeetingNotInProgress'){
							alert("尚未開放，請稍後再試");
						}else
							alert($_POST['RS']);
					}
				}elseif($_POST['type']=='teacher_demo'){
					if($_POST['ST']=='FAIL'){
						$this->log($_POST['classroom']);
						if($_POST['RS']=='MeetingNotInProgress'){
							alert("Coming soon, try it later.");
						}else
							alert($_POST['RS']);
					}
				}else{
					$this->log($_POST['classroom']);
					echo $_POST['RS'];
				}					
			}
			
			if(isset($_GET['AT']) && $_GET['AT']=='JM'){

				if($_GET['ST']!='SUCCESS'){
					$this->log($_GET['classroom']);
				}
			
				if($_GET['type']=='demo'){
					if($_GET['ST']=='SUCCESS'){						
						header("Location:/");
					}
				}elseif($_GET['type']=='student'){
					if($_GET['ST']=='SUCCESS'){	
						header("Location:/student/classroom");
					}
				}elseif($_GET['type']=='teacher_demo'){
					if($_GET['ST']=='SUCCESS'){
						header("Location:/teacher/demo");
					}
				}else{
					$this->log($_GET['classroom']);
					echo $_POST['RS'];
				}			
			}
			
			exit;
		}
		
		function log( $classroom ){
			
			$log = "upload/log/webex_test.".$classroom.".txt";			
			ob_start();
			echo date('Y-m-d H:i:s')."\n";
			print_r($_POST);
			print_r($_GET);
			file_put_contents($log ,ob_get_contents(), FILE_APPEND);
			ob_clean();
		}
		
		function updateAttend( $classroom_id ){
		
				$values = array('attend' => 10, 'mdate' => date('Y/m/d H:i:s'));
				
				if($this->action=='demo')
					$filter = array( 'classroom_id' => $classroom_id);
				else
					$filter = array( 'classroom_id' => $classroom_id, 'member_id' => $_SESSION['member']['id'] );
				
				//$this->dbconn->update('course_registration', $values, $filter);			
		}
		
		function rescue( $url ){
			header("Location:".$url);
			exit;
		}
		

		
	}
?>