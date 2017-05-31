<?php
$id = GetParam('id');
switch (MODE)
{
  case 'use_url':
    unset ($v);
    $v['use_url'] = GetParam('use_url');
    $rs->update('classroom', $id, $v);
    json_output(array('status' => true));
    break;
  case 'done':
    $rs->select('classroom', $id);
    $c = $rs->fetch();
    $v['status2'] = 20; // done & leave
    $rs->update('classroom', $id, $v);

    $sql = "SELECT * FROM course_registration WHERE classroom_id = $id";
    $rs->query($sql);
    $r = $rs->fetch();
    // history
    $memo = GetParam('memo');
    if (!empty($memo))
      $memo = "\n備註:\n" . $memo;
    unset ($v);
    $v['member_id'] = $r['member_id'];
	
	$sql = "UPDATE member SET demo_done=1 WHERE id=".$v['member_id'];
	$rs->query($sql);
    $v['type'] = 32; // DEMO OK
    $v['content'] = "DEMO完成 {$c['date']} " . $var_schedule_time[$c['time']] . $memo;
    $rs->insert('member_history', $v);
    json_output(array('status' => true));
    break;
  case 'fail':
    $rs->select('classroom', $id);
    $c = $rs->fetch();
    $v['status'] = 20; // cancel
    $v['status2'] = 0; // fail & leave
    $rs->update('classroom', $id, $v);

    $sql = "SELECT * FROM course_registration WHERE classroom_id = $id";
    $rs->query($sql);
    $r = $rs->fetch();
    $rs->update('course_registration', $r['id'], $v);

    // history
    unset ($v);
    $v['member_id'] = $r['member_id'];
    $v['type'] = 31; // cancel
    $v['content'] = "DEMO失敗 {$c['date']} " . $var_schedule_time[$c['time']] . "\n失敗原因: " . GetParam('memo');
    $rs->insert('member_history', $v);
    json_output(array('status' => true));
    break;
  case 'enter':
    $rs->select('classroom', $id);
    $c = $rs->fetch();
//    AssignValue($rs, 'fn_callback');
//    $sql = "SELECT * FROM webex WHERE status = 10 ORDER BY webex_name";
//    //echo $sql;
//    $rs->query($sql);
//    $VARS['webex'] = AssignResult($rs);
//    break;
//  case 'webex':
//    $v['webex_id'] = GetParam('webex_id');
    unset ($v);
    $v['status2'] = 10; // enter
    $rs->update('classroom', $id, $v);
    $rs->select('webex', $c['webex_id']);
    $w = $rs->fetch();
    if ($c['use_url'])
    {

      header("Location: https://etalking.webex.com/mw3000/mywebex/cmr/cmr.do?siteurl=etalking&AT=start&UserName={$w['account']}&password={$w['password']}");
//    header("Location: {$r['url']}");
      exit;
    }

//    require (DOC_ROOT . '/lib/webex.php');
    if(!isset($_GET['ST']))
    {
      $_SESSION['ref'] = $_SERVER['HTTP_REFERER'];

      $bu = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
      header ("Location: https://etalking.webex.com/etalking/p.php?AT=LO&BU=$bu");
//      echo <<<EOT
//<meta charset="UTF-8">
//<form id="logout" method=POST action="https://etalking.webex.com/etalking/p.php">
//<input type="hidden" name="AT" value="LO">
//<input type="hidden" name="BU" value="'.$bu.'" >';
//</form>
//<script>document.getElementById("logout").submit();</script>
//EOT;
    }
    else
    {
      $data['service'] = "user.GetLoginTicket";
      $data['xml_body'] = "";
      $wx = new WebexAPI;
      $wx->set_auth($w["account"], $w["password"], WEBEX_SID, WEBEX_PID);
      $wx->set_url(WEBEX_API_URL);
      $tk = $wx->getTicket();
      $url = urlencode($_SESSION['ref']); //$_SERVER['HTTP_REFERER']);
      unset ($_SESSION['ref']);
      echo <<<EOT
<form method=POST action="https://etalking.webex.com/etalking/p.php">
  <input type="hidden" name="AT" value="LI">
  <input type="hidden" name="TK" value="$tk">
  <input type="hidden" name="WID" value="{$w['account']}">
  <input type="hidden" name="MU" value="https://etalking.webex.com/etalking/m.php?AT=HM&MK={$c['meeting_key']}&BU=$url">
</form>
<script language="javascript">
  document.forms[0].submit();
</script>
EOT;
    }
    exit;
    break;
  case 'history':
    $DATA_SQL = "SELECT * FROM member_history WHERE member_id = $id ORDER BY id DESC";
    break;
	
  case 'ajax':
  
    $gfilter = '';
	if(GetParam('filter') && GetParam('filter')!='-9999'){
		$gfilter = " AND g.id = ".GetParam('filter');
	}
	GenFilterBySQL('filter2', "SELECT u.id AS value, u.user_name AS text FROM `user` u, `group` g, user_group ug WHERE g.is_sales = 1 AND ug.group_id = g.id $gfilter AND ug.user_id = u.id GROUP BY (u.id) ORDER BY u.first_name, u.last_name");
	$option = array();
	foreach($VARS['filter2_list'] as $val){
		$option[]= "<option value='".$val['value']."'>".$val['text']."</option>";
	}
	json_output(array('data'=>$option));
  exit;
   break;
  
  case 'change_sales':
	
	$VARS['options'] = array();
	$rs->query("SELECT u.id AS value, u.user_name AS text FROM `user` u, `group` g, user_group ug WHERE g.is_sales = 1 AND ug.group_id = g.id AND ug.user_id = u.id GROUP BY (u.id) ORDER BY u.first_name, u.last_name");
	while($row = $rs->fetch()){
		$VARS['options'][] = $row;
	}
	$VARS['sales_id'] = GetParam('sales_id');
	$VARS['cid'] = GetParam('cid');
  break;
  
  case 'change_sales_save':
	$rs->query( 'UPDATE classroom SET demo_sales = "'.$_POST['sales_id'].'" WHERE id= '.$_POST['cid']);
	Message('變更成功', false, MSG_OK);
    echo <<<EOT
<script language="javascript">
parent.window.location.reload();
</script>
EOT;
  exit;
  break;
  default:
  
  GenFilterBySQL('filter', "SELECT g.id AS value, g.group_name AS text FROM `group` g WHERE g.is_sales = 1");
  $FILTER = 'm.sales_id';
  $FILTER_NAME = '組別';
  
  $gfilter = '';
  if(GetParam('filter') && GetParam('filter')!='-9999'){
	$gfilter = " AND g.id = ".GetParam('filter');
  }
  
  GenFilterBySQL('filter2', "SELECT u.id AS value, u.user_name AS text FROM `user` u, `group` g, user_group ug WHERE g.is_sales = 1 AND ug.group_id = g.id $gfilter AND ug.user_id = u.id GROUP BY (u.id) ORDER BY u.first_name, u.last_name");
  $FILTER2 = 'm.sales_id';
  $FILTER2_NAME = '組員';
  
  
    $TABLE = 'course_registration';
    $FUNC_NAME = 'DEMO列表';
//$SELECT = array ('status');
    $SEARCH = false;
//$SEARCH_KEYS = array ('account', 'tel', 'email');
    $SORT_BY = 'datetime';
    $TRANSLATE = array (
//  'status' => array($var_request_status, $var_request_status_color),
//  'gender' => array($var_member_gender, $var_member_gender_color),

    );
	if(GetParam('search_begin')){
		
		$s =  date('Ymd00', strtotime(GetParam('search_begin')) );
		
		$e =  date('Ymd23', strtotime(GetParam('search_end')) );
		
		$range = " c.datetime BETWEEN '$s' AND '$e' ";
		
		$VARS['search_begin'] = GetParam('search_begin');
		$VARS['search_end'] = GetParam('search_end');
		
	}else{
	
		$range =  date('YmdH', time() - 3600 - 9600); // 1hr10min
		$range =  " c.datetime >= $range ";
	}
	
  $sales_filter = '';
  if(GetParam('filter2') && GetParam('filter2')!='-9999'){
	$sales_filter = " AND ( c.demo_sales = '".GetParam('filter2'). "' OR c.creator = '".GetParam('filter2'). "'  )";
  }
    $DATA_SQL = <<<EOT
SELECT *, c.creator AS create_sales, c.id AS cid FROM classroom c, course_registration r, member m
WHERE r.status = 10 AND r.classroom_id = c.id AND c.status = 10 AND c.type = 10 AND $range $sales_filter AND r.member_id = m.id AND m.status IN (60, 70)
ORDER BY c.datetime
EOT;
    break;
}

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////
$VARS['add_button'] = false;
if(!GetParam('request') && MODE != 'history')
	$VARS['search_date'] = true;

function fn_callback($r)
{
  global $rs3, $var_member_gender, $var_schedule_time, $demo_open_time, $demo_close_time;

  if (MODE == 'history')
    return fn_callback_history($r);
  $rs3->select('member', $r['member_id']);
  $rr = $rs3->fetch();
  $r['member_name'] = $rr['member_name'];
  $r['mobile'] = $rr['mobile'];
  $r['email'] = $rr['email'];
  $r['gender_text'] = $var_member_gender[$rr['gender']];
  if ($r['consultant_id'] == 0)
    $r['consultant_id_text'] = '尚未指派';
  else
  {
    $rs3->select('consultant', $r['consultant_id']);
    $rr = $rs3->fetch();
    $r['consultant_id_text'] = "{$rr['first_name']} {$rr['last_name']} ({$rr['chi_name']})";
  }
  $r['time_text'] = $var_schedule_time[$r['time']];

  $r['can_enter'] = ($demo_open_time >= $r['open_time'] && $demo_close_time <= $r['open_time'] && $r['webex_id'] > 0);
  //echo $demo_close_time;
//  $r['can_enter'] = true;
  $r['can_cancel'] = $demo_close_time <= $r['open_time'];
  // report
  $sql = "SELECT COUNT(*) FROM ques_demo WHERE classroom_id = {$r['classroom_id']}";
  $rs3->query($sql);
  $r['report'] = $rs3->count > 0 && $rs3->record() > 0;
  // webex name
  $r['webex_name'] = $rs3->get_value('webex', 'webex_name', $r['webex_id']);
  $r['use_url'] = $r['use_url'] ? 'checked' : '';

  //業務
  $r['creator_text'] = $rs3->get_value('user', 'user_name', $r['create_sales']);
  if(!$r['demo_sales']){
	  $r['demo_sales'] = $r['create_sales'];
	  $r['demo_sales_text'] = $r['creator_text'];
  }
  else $r['demo_sales_text'] = $rs3->get_value('user', 'user_name', $r['demo_sales']);
  return $r;
}

function fn_callback_history($r)
{
  $r['content'] = nl2br($r['content']);

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
