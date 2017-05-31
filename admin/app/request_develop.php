<?php
$id = $VARS['id'] = GetParam('id');
switch (MODE)
{
  case 'report':
    $sql = "SELECT c.*, m.member_name FROM classroom c, course_registration r, member m WHERE c.id = $id AND r.classroom_id = c.id AND r.member_id = m.id";
    $rs->query($sql);
    AssignValue($rs, 'cbDemo');
    $sql = "SELECT * FROM questionnaire WHERE type = 10 ORDER BY rank";
    $rs->query($sql);
    AssignValues($rs, 'ques', 'cbQues');
    //print_r($VARS['list']);
    break;
  case 'release':
    // history
    unset ($v);
    $v['member_id'] = $id;
    $v['type'] = 50; // 釋出
    $v['content'] = GetParam('reason');
	
	$v['contact_status'] =8;
    $rs->insert('member_history', $v);
    $sql = "UPDATE member SET status = 70 WHERE id = $id";
    $rs->execute($sql);
    json_output(array ('status' => true));
    break;
  case 'cancel':
    $sql = "SELECT c.*, r.id AS rid, r.member_id FROM classroom c, course_registration r WHERE c.id = $id AND c.id = r.classroom_id";
    $rs->query($sql);
    $r = $rs->fetch();
    unset ($v);
    $v['status'] = 20;
    $rs->update('classroom', $id, $v);
    $rs->update('course_registration', $r['rid'], $v);
    del_meeting_room($id);
    // history
    unset ($v);
    $v['member_id'] = $r['member_id'];
    $v['type'] = 31; // 取消預約
    $v['content'] = '取消DEMO ' . $r['date'] . ' ' . $var_schedule_time[$r['time']] . "\n取消原因: " . GetParam('memo');
    $rs->insert('member_history', $v);
    json_output(array ('status' => true));
    break;
  case 'update':
    $rs->update('member', $id);
    Message('資料已更新', false, MSG_INFO);
    GoLast();
    break;
  case 'check_demo':
    $t = date('YmdH', time() - 3600);
    $sql = "SELECT c.*, r.id AS rid, r.member_id FROM classroom c, course_registration r WHERE r.member_id = $id AND r.classroom_id = c.id AND c.datetime > $t AND r.status = 10 AND c.status = 10 AND c.status2 < 20"; // 完成前不能離開
    $rs->query($sql);
    $r = $rs->count == 0;
    json_output(array('status' => $r));
    break;
  case 'demo':
  
    $VARS['today'] = date('Y-m-d');
    $VARS['twoweeks'] = date('Y-m-d', time() + 86400 * 14);
  
	for($i=7;$i<=23;$i++){
		$VARS['options'][$i] = sprintf('%02d:00:00',$i);
	}
  
	$contact_status = array( 1=>'已接聽',2=>'非本人',3=>'未接',4=>'關機',5=>'空號',6=>'通話中'); // 保留 7=> 預約demo, 8=> 釋出
	$VARS['contact_status'] = $contact_status;
	
    for ($i = BEGIN_TIME; $i <= END_TIME; $i++)
      $hour[] = array ('hour' => $i);
    $VARS['hour_list'] = $hour;

    for ($i = 0; $i <= 9; $i++) // week
    {
      $this_day = $today_time + 86400 * $i;
      $w['date'] = date('Y-m-d', $this_day);
      $w['weekday'] = $var_weekday_short[date('w', $this_day)];
      unset ($hour);
      for ($k = BEGIN_TIME; $k <= END_TIME; $k++)
      {
        $now = $this_day + 3600 * $k + 3599;
        $h['date'] = $w['date'];
        $h['hour'] = $k;
        $h['disabled'] = time() < $now ? '' : 'disabled';
        $hour[] = $h;
      }
      $w['hour_list'] = $hour;
      $week[] = $w;
    }
    $VARS['week_list'] = $week;
	
	$VARS['current_member_id'] = $_GET['id'];
    break;
	
  case 'save':
  
		$v = array();
		$v['member_id'] = $_POST['current_member_id'];
		$v['content'] = $_POST['content'];
		$v['contact_status'] = $_POST['contact_status'];
		$v['next_time'] = $_POST['search_begin'].' '.$_POST['time'];
		$v['type']=20;
		$rs->insert('member_history', $v);
		Message('新增完成', false, MSG_OK);
    echo <<<EOT
<script language="javascript">
parent.window.location.reload();
</script>
EOT;
	exit;
	break;
	
  case 'register':  

    $id = GetParam('id');
    $date = GetParam('date');
    $hour = GetParam('hour');
    $type = GetParam('consultant_type');
    $content = GetParam('content');
	
	if($_POST['contact_status']){
  		$v = array();
		$v['member_id'] = $_POST['id'];
		$v['content'] = $_POST['content'];
		$v['contact_status'] = $_POST['contact_status'];
		$v['next_time'] = $_POST['search_begin'].' '.$_POST['time'];
		$v['type']=20;
		$rs->insert('member_history', $v);
		$content = '';
		unset($v);
	}
	
    if (!empty($content))
      $content = "聯絡事項:\n" . $content;
	  
    // check webex
    $r = check_webex('10', $date, $hour);
    if (!$r)
    {
      json_output(array ('status' => false, 'msg' => 'WebEx教室已用完，無法建立教室'));
      exit;
    }
    // update member.prefer
    $rs2->update('member', $id, array ('perfer' => $type));
    $r = register_free_booking($id, $date, $hour, 10, $content); // 10 =DEMO
    if ($r > 0) // reg_id
    { // 成功，發信
      $rs->select('member', $id);
      $v = $rs->fetch();
      $rs->select('course_registration', $r);
      $r = $rs->fetch();
//      print_r($r);
      $v['demo'] = $r['memo'];
      $v['date'] = $date;
      $v['time_text'] = $var_schedule_time[$hour];
//      print_r($v);
//      exit;
      create_meeting_room($r['classroom_id']);
      // history
//      unset ($v);
//      $v['member_id'] = $r['member_id'];
//      $v['type'] = 30; // 預約
//      $v['content'] = "預約DEMO $date " . $var_schedule_time[$hour];
//      $rs->insert('member_history', $v);

      $m = new MailModule();
      $m->template = 'email_demo';
      $m->vars = $v;
      $m->addAddress($v['email'], $v['member_name']);
      $m->subject = $EMAIL_SUBJECT_DEMO;
      $m->send();
      unset($m);
      Message('DEMO預約成功', false, MSG_OK);
    }
    else
      $msg = 'DEMO預約失敗!';
    json_output(array ('status' => $r, 'msg' => $msg));
    break;
}

// TODO: check group id, status, search member
$id = $VARS['member_id'] = $_POST['member_id'] = GetParam('member_id');

$TABLE = 'member_history';
$FUNC_NAME = '業務開發';
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('content');
$SORT_BY = 'id DESC';
$TRANSLATE = array (
  'type' => array($var_member_history_type),
  'gender' => array($var_member_gender),
);
$RADIO = array ('prefer', 'gender');
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'content', 'height' => 10, 'required' => true),
);
$SPECIAL = "member_id = $id";

// member data
$rs->select('member', $id);
AssignValue($rs);
$VARS['prefer_select'] = GenRadio('prefer', $var_consultant_type, true);
//$VARS['gender_text'] = $var_member_gender[$VARS['gender']];
$VARS['gender_select'] = GenRadio('gender', $var_member_gender, true);

$sql = "SELECT MAX(c.open_time) FROM classroom c, course_registration r WHERE r.member_id = $id AND r.status = 10 AND r.classroom_id = c.id AND c.type = 10 AND c.status = 10 ORDER BY `datetime` DESC"; // 10=demo
$rs->query($sql);
$VARS['can_demo'] = !($rs->count == 0 || $rs->record());

// demo list
$sql = "SELECT c.* FROM classroom c, course_registration r WHERE r.member_id = $id AND r.status = 10 AND r.classroom_id = c.id AND c.type = 10 AND c.status = 10 ORDER BY `datetime` DESC"; // 10=demo
$rs->query($sql);
AssignValues($rs, 'demo', 'cbDemo');
//print_r($VARS['demo_list']);

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////
	$VARS['add_button'] = false;
	$VARS['custom'] = '<a class="btn-flat success" onclick="demo();"><i class="icon-plus"></i> 新增</a>';

function cbQues($r)
{
  global $id, $rs3;

  $r['question'] = $r['text'];
  if ($r['text'] == 'Summary')
    $sql = "SELECT content FROM ques_demo WHERE classroom_id = $id AND ques_id = {$r['id']}";
  else
    $sql = "SELECT i.text AS answer FROM ques_demo d, ques_item i WHERE d.classroom_id = $id AND d.ques_id = {$r['id']} AND d.content = i.id";
  $rs3->query($sql);
  $r['answer'] = nl2br($rs3->count > 0 ? $rs3->record() : '');

  return $r;
}

function cbDemo($r)
{
  global $rs3, $var_schedule_time, $demo_open_time, $demo_close_time;

  $r['time_text'] = $var_schedule_time[$r['time']];
  if ($r['consultant_id'] == 0)
    $r['consultant_text'] = '尚未指派';
  else
  {
    $rs3->select('consultant', $r['consultant_id']);
    $c = $rs3->fetch();
    $r['consultant_text'] = $c['first_name'] . ' ' . $c['last_name'];
  }
  // TODO: can_enter
  $r['can_enter'] = ($demo_open_time >= $r['open_time'] && $demo_close_time <= $r['open_time'] && $r['webex_id'] > 0);
  //echo $demo_close_time;
//  $r['can_enter'] = true;
  $r['can_cancel'] = $demo_close_time <= $r['open_time'];
  // report
  $sql = "SELECT COUNT(*) FROM ques_demo WHERE classroom_id = {$r['id']}";
  $rs3->query($sql);
  $r['report'] = $rs3->count > 0 && $rs3->record() > 0;
  // webex name
  $r['webex_name'] = $rs3->get_value('webex', 'webex_name', $r['webex_id']);
  $r['use_url'] = $r['use_url'] ? 'checked' : '';

  return $r;
}

//function before_index($sql, $filters)
//{
//  global $filter;
//
//  if ($filter != -9999)
//    $sql = "SELECT m.* FROM material m, material_level l WHERE m.id = l.material_id AND l.level_id = $filter $filters";
//  return $sql;
//}


function fn_callback($r)
{
  $r['content'] = nl2br($r['content']);
  $time = substr($r['next_time'],-8);
  if($time=='11:59:59')
	$r['next_time'] = substr($r['next_time'],0,-8).' 上午';
  elseif($time=='17:59:59')
	$r['next_time'] = substr($r['next_time'],0,-8).' 下午';
  elseif($time=='23:59:59')
	$r['next_time'] = substr($r['next_time'],0,-8).' 晚上';
  elseif($r['next_time']=='0000-00-00 00:00:00')
	$r['next_time'] = '';
  else
	$r['next_time'] = substr($r['next_time'],0,-8).' '.((int)substr($r['next_time'],-8,2)).':00';
  return $r;
}

function fn_new()
{
}

function fn_before_add()
{
  $_POST['cdate'] = date('Y-m-d H:i:s');

  return true;
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
