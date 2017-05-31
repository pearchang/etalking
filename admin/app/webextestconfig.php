<?php
$id = GetParam('id');
$VARS['test'] = false;
$VARS['url'] = "webextestconfig/popup";

switch (MODE)
{
  case 'popup':
    $VARS['date'] = $d = GetParam('date');
    $VARS['time'] = $t = GetParam('time');
	$VARS['w'] = GetParam('w');
	$cnt = $rs->get_count("webex_test","status=10 AND `datetime`='".$VARS['date'].' '.$VARS['time'].":00'");
	$VARS['already'] = (int)$cnt[0];
	$VARS['qyt'] = $rs->get_value("webextestconfig",'qyt',"{$d} {$t}:00",'datetime');
	for($i=$VARS['already']; $i<= WEBEX_TEST_MAX_QYT; $i++)
		$VARS['options'][]=$i;
    break;
	
  case 'save':
    $d = GetParam('date');
    $t = GetParam('time');
	$qyt = $_POST['qyt'];
	$filter = "`datetime`='{$d} {$t}:00'";
	$cnt = $rs->get_count("webextestconfig",$filter);
	if($cnt[0]==1){
		$sql = "UPDATE webextestconfig SET qyt= $qyt WHERE $filter ";
		$rs->query($sql);
	}else{
		$dt = "{$d} {$t}:00";
		$sql = "INSERT INTO webextestconfig (`datetime`,qyt) VALUES ('$dt', $qyt) ";
		$rs->query($sql);
	}
    Message('處理完成', false, MSG_INFO);
    echo <<<EOT
<script language="javascript">
parent.window.location.reload();
</script>
EOT;
	exit;

  default:

    for ($i = BEGIN_TIME; $i <= END_TIME+0.5; $i+=0.5){
		if(ceil($i)==$i){
			$h = $i;
			$hour_begin = $h.":00";
			$hour_end = $h.":29";
		}else{
			$h = floor($i);
			$hour_begin = $h.":30";
			$hour_end = $h.":59";
		}
		$hour[] = array ('hour_begin' => $hour_begin, 'hour_end' => $hour_end);
	}
    $VARS['hour_list'] = $hour;
	$now = GetParam('search_begin') ? strtotime(GetParam('search_begin')) : time();
	$VARS['search_begin'] = GetParam('search_begin') ? GetParam('search_begin') : '';
    $VARS['w'] = $wk = GetParam('w', 0);
    $VARS['w1'] = $wk - 1;
    $VARS['w2'] = $wk + 1;
    $begin = $wk * (DATE_RANGE / 2);
    $end = $begin + (DATE_RANGE / 2);
    for ($i = $begin; $i < $end; $i++) // date
    {
      $t = $now + $i * 86400;
      $d = date('Y-m-d', $t);
      $w['date'] = date('m-d', $t);
      $w['name'] = $var_weekday_short[date('w', $t)];
      unset ($hour);
      for ($k = BEGIN_TIME; $k <= END_TIME+0.5; $k+=0.5)
      {
		$t = ceil($k)==$k ? $k.":00" : floor($k).":30" ;
		$h = array();
        $h['date'] = $d;
        $h['hour'] = $t;
		
        $cnt = $rs->get_count("webex_test","status=10 AND `datetime`='".$h['date'].' '.$h['hour'].":00'");
		$h['already'] =(int)$cnt[0];
		$h['avaliable'] = (int)$rs->get_value("webextestconfig","qyt", "{$d} {$t}:00",'datetime');
        $h['expired'] = date('YmdHi') > date('YmdHi',strtotime("{$d} {$t}:00")) ? true : false;
		$hour[] = $h;
		
      }
      $w['hour_list'] = $hour;
      $week[] = $w;
    }
    $VARS['date_list'] = $week;

    break;
}


$VARS['add_button'] = false;

function cbStudent($r)
{
  global $rs3;

  $sql = "SELECT title FROM member_interest m, interest i WHERE m.member_id = {$r['id']} AND m.interest_id = i.id";
  $rs3->query($sql);
  $r['interest'] = AssignResult($rs3);
  $sql = "SELECT title FROM member_skill m, skill s WHERE m.member_id = {$r['id']} AND m.skill_id = s.id";
  $rs3->query($sql);
  $r['skill'] = AssignResult($rs3);
  $r['note'] = htmlspecialchars($r['note']);
  return $r;
}

function fn_callback($r)
{
  global $rs2, $var_registration_type, $var_schedule_time, $consultant_open_time;

  $r['time_text'] = $var_schedule_time[$r['time']];
  $r['type_text'] = $var_registration_type[$r['type']];
  $r['level_text'] = $rs2->get_value('level', 'level_name', $r['level_id']);
  // consultant
  if ($r['consultant_id'] == 0)
    $r['consultant'] = '尚未指派顧問';
  else
  {
    $sql = "SELECT * FROM consultant WHERE id = {$r['consultant_id']}";
    $rs2->query($sql);
    $rr = $rs2->fetch();
    $r['consultant'] = $rr['first_name'] . ' ' . $rr['last_name'];
  }
  // material
  if ($r['material_id'] == 0)
    $r['material'] = '尚未指定教材';
  else
  {
    $sql = "SELECT * FROM material WHERE id = {$r['material_id']}";
    $rs2->query($sql);
    $rr = $rs2->fetch();
    $r['material'] = $rr['title'];
    $r['material2'] = $rr['eng_title'];
  }
  // webex
  $r['webex_name'] = $rs2->get_value('webex', 'webex_name', $r['webex_id']);
  // persons
  $sql = "SELECT m.*, r.id AS cid, l.level_name FROM course_registration r, member m, level l WHERE r.classroom_id = {$r['id']} AND r.status = 10 AND r.member_id = m.id AND m.level_id = l.id";
  //echo $sql;
  $rs2->query($sql);
  $r['persons'] = AssignResult($rs2, 'cbStudent');
  $r['can_move'] = count($r['persons']) > 1;
  $r['can_enter'] = $r['webex_id'] > 0 && $r['open_time'] <= $consultant_open_time;
  $r['use_url'] = $r['use_url'] ? 'checked' : '';
  return $r;
}

function fn_callback2($r)
{
  global $rs2;

  if (empty($r['classroom']))
    $r['classroom'] = 0;

  $sql = "SELECT COUNT(DISTINCT(`time`)) FROM `classroom` WHERE `type` IN (20, 30) AND `date` = '{$r['date']}' AND `status` = 10"; // 20=1:1 30=1:n
  //echo $sql . '<br>';
  $rs2->query($sql);
  $r['time'] = $rs2->count > 0 ? $rs2->record() : 0;
  $sql = "SELECT COUNT(c.id) FROM `classroom` c, course_registration r WHERE c.type IN (20, 30) AND c.date = '{$r['date']}' AND c.status = 10 AND c.id = r.classroom_id AND r.status = 10"; // 20=1:1 30=1:n
  $rs2->query($sql);
  $r['persons'] = $rs2->count > 0 ? $rs2->record() : 0;
  return $r;
}

function fn_new()
{
}

function fn_add($id)
{
}

function fn_edit($id)
{
}

function fn_modify($id)
{
}
?>