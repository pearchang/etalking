<?php
$id = GetParam('id');
switch (MODE)
{
  case 'manage':
    $VARS['date'] = $d = GetParam('date');
    $VARS['t'] = $t = GetParam('time');
    $VARS['time'] = $var_schedule_time[$t];
    $DATA_SQL = "SELECT * FROM classroom WHERE `date` = '$d' AND `time` = $t AND status = 10 AND `type` IN (40, 50) ORDER BY id";
  	require_once('func.inc.php');
    break;
  case 'time':
    $VARS['date'] = $d = GetParam('date');
    unset ($v, $vv);
    for ($i = BEGIN_TIME; $i <= END_TIME; $i++)
    {
      $v['t'] = $i;
      $v['time'] = $var_schedule_time[$i];
      $sql = "SELECT COUNT(id) FROM `classroom` WHERE `type` IN (40, 50) AND `date` = '$d' AND `time` = $i AND `status` = 10"; // 20=1:1 30=1:n
      //echo $sql . '<br>';
      $rs2->query($sql);
      $v['classroom'] = $rs2->count > 0 ? $rs2->record() : 0;
      $sql = "SELECT COUNT(c.id) FROM `classroom` c, course_registration r WHERE c.type IN (40, 50) AND c.date = '$d' AND c.time = $i AND c.status = 10 AND c.id = r.classroom_id AND r.status = 10"; // 20=1:1 30=1:n
      $rs2->query($sql);
      $v['persons'] = $rs2->count > 0 ? $rs2->record() : 0;
      $vv[] = $v;
    }
    $VARS['list'] = $vv;
    break;
  case 'popup':
    $rs->select('classroom', $id);
    AssignValue($rs, 'fn_callback');
    $sql = "SELECT id, sn, open_time, level_id FROM classroom WHERE `date` = '{$VARS['date']}' AND `time` = {$VARS['time']} AND status = 10 AND `type` = 30 AND id <> $id ORDER BY sn";
    //echo $sql;
    $rs->query($sql);
    $VARS['classroom'] = AssignResult($rs);
    // can enter
    $VARS['can_enter'] = $VARS['webex_id'] > 0 && $VARS['open_time'] <= $consultant_open_time;
    $VARS['ref'] = urlencode($_SERVER['HTTP_REFERER']);
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
//    $v['status2'] = 10; // enter
//    $rs->update('classroom', $id, $v);
    $rs->select('webex', $c['webex_id']);
    $w = $rs->fetch();
    if ($c['use_url'])
    {
      header("Location: https://etalking.webex.com/mw3000/mywebex/cmr/cmr.do?siteurl=etalking&AT=start&UserName={$w['account']}&password={$w['password']}");
//    header("Location: {$r['url']}");
      exit;
    }

//    require (DOC_ROOT . '/lib/webex.php');
    // LO first
    if(!isset($_GET['ST']))
    {
      $_SESSION['ref'] = GetParam('ref');
      if (empty($_SESSION['ref']))
        $_SESSION['ref'] = $_SERVER['HTTP_REFERER'];
//      $bu = urlencode("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?id=$id&ref=" . htmlspecialchars(GetParam('ref')));
      //$bu = urlencode("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_NAME']}?id=$id");
      $bu = urlencode("http://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
      header ("Location: https://etalking.webex.com/etalking/p.php?AT=LO&BU=$bu");
//      echo <<<EOT
//<form id="logout" method=POST action="https://etalking.webex.com/etalking/p.php">
//<input type="hidden" name="AT" value="LO">
//<input type="hidden" name="BU" value="$bu">
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
      $url = empty($_SESSION['ref']) ? urlencode(GetParam('ref')) : urlencode($_SESSION['ref']);
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
  case 'consultant':
    $VARS['id'] = $id = GetParam('id');
    $rs->select('classroom', $id);
    $r = $rs->fetch();
    $cid = $r['consultant_id'];
    // interest
//    $sql = "SELECT i.interest_id, COUNT(i.interest_id) AS qty FROM `course_registration` r, member_interest i WHERE r.classroom_id = $id AND r.member_id = i.member_id GROUP BY i.interest_id ORDER BY qty DESC";
//    $rs2->query($sql);
//    if ($rs2->count == 0)
//    {
//      $VARS['not_available'] = true;
//      break;
//    }
//    $r2 = $rs2->fetch();
//    $int = $r2['interest_id'];
    $sql = <<<EOT
SELECT c.* FROM consultant c /*, consultant_level l */
WHERE c.id NOT IN (SELECT consultant_id FROM classroom WHERE status = 10 AND `datetime` = {$r['datetime']}) AND c.id <> $cid
/* AND c.id = l.consultant_id AND l.level_id = {$r['level_id']} */ AND c.status = 10
EOT;
    //echo $sql;
    $rs->query($sql);
    if ($rs->count == 0)
    {
      $VARS['not_available'] = true;
      break;
    }
    AssignValues($rs);
    break;
  case 'change_consultant':
    unset ($v);
    $id = GetParam('id');
    $v['consultant_id'] = GetParam('consultant');
    // 判斷是否open - now >= 12
    $rs->select('classroom', $id);
    $c = $rs->fetch();
    $open = strtotime($c['open_time']);
    if (($open - time()) >= (12 * 60 * 60))
      $v['consultant_confirmed'] = 0;
    else
      $v['consultant_confirmed'] = 10;
    $rs->update('classroom', $id, $v);
    json_output(array('status' => true));
    break;
  case 'material':
    $VARS['id'] = $id = GetParam('id');
    $rs->select('classroom', $id);
    $r = $rs->fetch();

    $mid = $r['material_id'];
    $sql = "SELECT * FROM material WHERE status = 10 AND `type` = 30 AND id <> $mid ORDER BY eng_title";

    //echo $sql;
    $rs->query($sql);
    if ($rs->count == 0)
    {
      $VARS['not_available'] = true;
      break;
    }
    AssignValues($rs);
    break;
  case 'change_material':
    unset ($v);
    $id = GetParam('id');
    $v['material_id'] = GetParam('material');
    $rs->update('classroom', $id, $v);
    // change course
    $rs->select('classroom', $id);
    $r = $rs->fetch();
    $rs->update('course', $r['course_id'], $v);
    json_output(array('status' => true));
    break;
  case 'move':
    $orig = GetParam('id');
    $new = GetParam('classroom');
    if ($new < 0)
    { // new classroom
      $rs->select('classroom', $orig);
      $v = $rs->fetch();
      unset ($v['id'], $v['cdate'], $v['modate'], $v['creator'], $v['modifier'], $v['consultant_id'], $v['webex_id'], $v['consultant_confirmed'], $v['rank'], $v['wage']);
      $v['sn'] = getSerialNumber2(SN_ROOM);
      $rs->insert('classroom', $v);
      $new = $rs->last_id;
      add_meeting_room($new);
    }
    // 更新
    unset ($v);
    foreach ($_POST['member_reg'] as $rid)
    {
      $v['classroom_id'] = $new;
      $rs->update('course_registration', $rid, $v);
    }
    // 檢查舊的
    $sql = "SELECT COUNT(*) FROM course_registration WHERE classroom_id = $orig  AND status = 10";
    $rs->query($sql);
    if ($rs->count == 0 || $rs->record() == 0)
    { // 沒預約了要更新
      unset ($v);
      $v['status'] = 20; // stop
      $rs->update('classroom', $orig, $v);
      del_meeting_room($orig);
    }
    Message('處理完成', false, MSG_INFO);
    echo <<<EOT
<script language="javascript">
parent.window.location.href = parent.window.location.href;
</script>
EOT;

    break;
  default:
//    unset ($vv);
//    for ($i = 0; $i < DATE_RANGE; $i++)
//    {
//      unset ($v);
//      $v['date'] = $d = date('Y-m-d', time() + 86400 * $i);
//      $sql = "SELECT '$d' AS `date`, COUNT(id) AS `classroom` FROM `classroom` WHERE `type` IN (40, 50) AND `date` = '$d' AND `status` = 10"; // 20=1:1 30=1:n
////      echo $sql;
//      $rs->query($sql);
//      if ($rs->count > 0)
//        $v = array_merge($v, fn_callback2($rs->fetch()));
//      $vv[] = $v;
//    }
//    $VARS['list'] = $vv;

    for ($i = BEGIN_TIME; $i <= END_TIME; $i++)
      $hour[] = array ('hour' => $i);
    $VARS['hour_list'] = $hour;
    $now = time();
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
      for ($k = BEGIN_TIME; $k <= END_TIME; $k++)
      {
        $h['date'] = $d;
        $h['hour'] = $k;
        $sql = "SELECT COUNT(id) FROM `classroom` WHERE `type` IN (40, 50) AND `date` = '$d' AND `time` = $k AND `status` = 10"; // 20=1:1 30=1:n
        $rs->query($sql);
        $h['classroom'] = $rs->count == 0 ? 0 : $rs->record();
        $sql = "SELECT COUNT(r.id) FROM `course_registration` r, `classroom` c WHERE c.type IN (40, 50) AND c.date = '$d' AND c.time = $k AND c.status = 10 AND c.id = r.classroom_id AND r.status = 10"; // 20=1:1 30=1:n
        $rs->query($sql);
        $h['persons'] = $rs->count == 0 ? 0 : $rs->record();
        $hour[] = $h;
      }
      $w['hour_list'] = $hour;
      $week[] = $w;
    }
    $VARS['date_list'] = $week;

    break;
}
$TABLE = 'classroom';
$FUNC_NAME = '選修課程';
//$SELECT = array ('status');
$SEARCH = false;
//$SEARCH_KEYS = array ('account', 'tel', 'email');
$SORT_BY = '`date`, `time`';
$TRANSLATE = array (
//  'status' => array($var_request_status, $var_request_status_color),
//  'gender' => array($var_member_gender, $var_member_gender_color),
//$DATA_SQL = "SELECT c.* FROM classroom c LEFT JOIN course_registration r ON c.id = r.classroom_id WHERE c.status = 10 AND c.type = $type AND c.date = '$date' AND c.time = $time AND r.status = 10 GROUP BY c.id";
);
//$SPECIAL = "`datetime` >= '$current_datehour' AND `type` = 10"; // DEMO
//$DATA_SQL = "SELECT * FROM classroom WHERE `date` = '$d' AND `time` = $t ORDER BY `datetime`, id";
////////////////////////////////////
//	require_once('func.inc.php');
////////////////////////////////////
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
  $r['course_name'] = $rs2->get_value('course', 'course_name', $r['course_id']);
  // persons
  $sql = "SELECT m.*, r.id AS cid FROM course_registration r, member m WHERE r.classroom_id = {$r['id']} AND r.status = 10 AND r.member_id = m.id";
  //echo $sql;
  $rs2->query($sql);
  $r['persons'] = AssignResult($rs2, 'cbStudent');
  $r['can_move'] = count($r['persons']) > 1;
  $r['can_enter'] = $r['webex_id'] > 0 && $r['open_time'] <= $consultant_open_time;

  return $r;
}

function fn_callback2($r)
{
  global $rs2;

  if (empty($r['classroom']))
    $r['classroom'] = 0;

  $sql = "SELECT COUNT(DISTINCT(`time`)) FROM `classroom` WHERE `type` IN (40, 50) AND `date` = '{$r['date']}' AND `status` = 10"; // 20=1:1 30=1:n
  //echo $sql . '<br>';
  $rs2->query($sql);
  $r['time'] = $rs2->count > 0 ? $rs2->record() : 0;
  $sql = "SELECT COUNT(c.id) FROM `classroom` c, course_registration r WHERE c.type IN (40, 50) AND c.date = '{$r['date']}' AND c.status = 10 AND c.id = r.classroom_id AND r.status = 10"; // 20=1:1 30=1:n
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