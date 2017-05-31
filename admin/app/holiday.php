<?php
$id = GetParam('id');
switch (MODE)
{
  
  case "save_time":
		
	$d = GetParam('date');
	$sql = "DELETE FROM holiday WHERE `date`='{$_GET['date']}'";
	$rs->query($sql);
	foreach($_POST['time'] as $key => $value){
		if($value==2){
			$v = array('date'=>$d, 'time'=>$key);
			$rs->insert('holiday', $v);
		}
	}
	Message('更新完成!', null, MSG_OK);
	
    echo <<<EOT
<script language="javascript">
parent.window.location = 'holiday?date={$d}';
</script>
EOT;
	exit;
	break;
  
  case "config_save":
  
	$a = array('8','9');
	foreach ($a as $i)
		SetConfig($i, GetParam("config$i"));
	Message('更新完成!', null, MSG_OK);
	GoLast();
  break;
  
  case "popup":
	
	$d = GetParam('date');
    $sql = "SELECT `time` FROM holiday WHERE `date`='$d' ORDER BY `time`";
    $rs->query($sql);
	$time = array();
	while($row = $rs->fetch()){
		$time[$row['time']] = 2;
	}
	
	for ($i = BEGIN_TIME; $i <= END_TIME; $i++){
		if(!isset($time[$i]))
			$time[$i] = 1;
	}
	ksort($time);
	$VARS['date'] = $d;
	$VARS['time'] = $time;
  break;

  default:
  
	$VARS["config8"] = GetConfig(8);
	$VARS["config9"] = GetConfig(9);
  
	for($i=0;$i<=14;$i++){
		$VARS['start_time'][$i]=sprintf("%02d:00",$i);
	}
	for($i=20;$i<=23;$i++){
		$VARS['end_time'][$i]=sprintf("%02d:00",$i);
	}
	
	$VARS['date'] = GetParam('date') ? GetParam('date') : '' ;

    break;
}


?>