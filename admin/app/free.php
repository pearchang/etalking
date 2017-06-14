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
  case 'manage':
    $VARS['date'] = $d = GetParam('date');
    $VARS['t'] = $t = GetParam('time');
    $VARS['time'] = $var_schedule_time[$t];
    $DATA_SQL = "SELECT * FROM classroom WHERE `date` = '$d' AND `time` = $t AND status = 10 AND `type` IN (20, 30) ORDER BY id";
  	require_once('func.inc.php');
    break;
  case 'time':
    $VARS['date'] = $d = GetParam('date');
    unset ($v, $vv);
    for ($i = BEGIN_TIME; $i <= END_TIME; $i++)
    {
      $v['t'] = $i;
      $v['time'] = $var_schedule_time[$i];
      $sql = "SELECT COUNT(id) FROM `classroom` WHERE `type` IN (20, 30) AND `date` = '$d' AND `time` = $i AND `status` = 10"; // 20=1:1 30=1:n
      //echo $sql . '<br>';
      $rs2->query($sql);
      $v['classroom'] = $rs2->count > 0 ? $rs2->record() : 0;
      $sql = "SELECT COUNT(c.id) FROM `classroom` c, course_registration r WHERE c.type IN (20, 30) AND c.date = '$d' AND c.time = $i AND c.status = 10 AND c.id = r.classroom_id AND r.status = 10"; // 20=1:1 30=1:n
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
    // 取得顧問黑名單
    $sql = "SELECT b.black_id FROM member_bl_consultant b, course_registration r WHERE r.classroom_id = $id AND r.status = 10 AND r.member_id = b.member_id AND b.deleted = 0";
    $rs3->query($sql);
    if ($rs3->count > 0)
      $blacklist = $rs3->record_array();
    else
      $blacklist = [0];

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
    $blacklist = implode(',', $blacklist);
    $sql = <<<EOT
SELECT c.* FROM consultant c, consultant_level l
WHERE c.id NOT IN (SELECT consultant_id FROM classroom WHERE status = 10 AND `datetime` = {$r['datetime']}) AND c.id <> $cid
AND c.id = l.consultant_id AND l.level_id = {$r['level_id']} AND c.status = 10 AND c.id NOT IN ($blacklist)
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
    $v['wage'] = 0;
    $v['salary'] = 0;
    $rs->update('classroom', $id, $v);
    json_output(array('status' => true));
    break;
  case 'material':
    $VARS['id'] = $id = GetParam('id');
    $rs->select('classroom', $id);
    $r = $rs->fetch();
    // Get members
    $sql = "SELECT GROUP_CONCAT(member_id) FROM course_registration WHERE classroom_id = $id AND status = 10";
    $rs3->query($sql);
    $members = $rs3->record();

    $mid = $r['material_id'];
    // interest
    $sql = "SELECT i.interest_id FROM `course_registration` r, member_interest i WHERE r.classroom_id = $id AND r.member_id = i.member_id AND r.status = 10 GROUP BY i.interest_id";
//	echo $sql;
    $rs2->query($sql);
    if ($rs2->count == 0)
    {
      $VARS['not_available'] = true;
      break;
    }
    $int = implode(',', $rs2->record_array());
    if (empty($int))
    {
      $VARS['not_available'] = true;
      break;
    }

    // 先取得目前學員該interest已上過的教材
//    $sql = "SELECT c.material_id FROM classroom c, course_registration r WHERE r.member_id IN ($members) AND r.status = 10 AND r.classroom_id = c.id AND c.material_id <> 0 GROUP BY c.material_id";
    $sql = <<<EOT
SELECT c.material_id FROM classroom c, course_registration r 
WHERE r.member_id IN ($members) AND r.status = 10 AND r.classroom_id = c.id AND c.material_id <> 0
  AND (c.open_time > NOW() OR (c.open_time <= NOW() AND r.attend = 10))
GROUP BY c.material_id
EOT;
    $rs3->query($sql);
    if ($rs3->count == 0)
      $materials = $mid;
    else
      $materials = implode(',', $rs3->record_array()) . ',' . $mid;
    if (empty($materials))
      $materials = $mid;

    $sql = <<<EOT
SELECT m.* FROM material m, material_interest i, material_level l
WHERE m.id = i.material_id AND i.interest_id IN ($int) AND m.id = l.material_id AND l.level_id = {$r['level_id']} AND m.status = 10
AND m.id NOT IN ($materials) GROUP BY m.id
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
  case 'change_material':
    unset ($v);
    $id = GetParam('id');
    $v['material_id'] = GetParam('material');
    $rs->update('classroom', $id, $v);
    json_output(array('status' => true));
    break;
  case 'move':
    $orig = GetParam('id');
    $new = GetParam('classroom');
    if ($new < 0)
    { // new classroom
      $rs->select('classroom', $orig);
      $v = $rs->fetch();
      unset ($v['id'], $v['cdate'], $v['modate'], $v['creator'], $v['modifier'], $v['consultant_id'], $v['webex_id'], $v['consultant_confirmed'], $v['rank'], $v['wage'], $v['meeting_key'], $v['meeting_pw']);
      $v['sn'] = getSerialNumber2(SN_ROOM);
      $rs->insert('classroom', $v);
      $new = $rs->last_id;
      add_meeting_room($new);
    }
    else
    { // 舊的要檢查教材 & 黑名單
      $sql = "SELECT * FROM classroom WHERE id = $new";
      $rs->query($sql);
      $c = $rs->fetch();
      $consultant_id = $c['consultant_id'];
      if ($c['material_id'] != 0)
      { // get members
        $regs = implode(',', $_POST['member_reg']);
        $sql = "SELECT GROUP_CONCAT(member_id) FROM course_registration WHERE id IN ($regs)";
        $rs->query($sql);
        $members = $rs->record();
        // get member's materials
        $sql = <<<EOT
SELECT c.material_id FROM classroom c, course_registration r 
WHERE r.member_id IN ($members) AND r.status = 10 AND r.classroom_id = c.id AND c.material_id <> 0
  AND (c.open_time > NOW() OR (c.open_time <= NOW() AND r.attend = 10))
GROUP BY c.material_id
EOT;
        $rs->query($sql);
        if ($rs->count == 0)
          $materials = [];
        else
          $materials = $rs->record_array();
        if (in_array($c['material_id'], $materials))
        {
          Message('準備移動的學員已上過該教室教材', true);
          exit;
        }
      }
      // 黑名單
      // new class的
      $sql = "SELECT GROUP_CONCAT(member_id) FROM course_registration WHERE classroom_id = $new AND status = 10";
      $rs->query($sql);
      $new_class_members = $rs->record();
      // 要移動的
      $regs = implode(',', $_POST['member_reg']);
      $sql = "SELECT GROUP_CONCAT(member_id) FROM course_registration WHERE id IN ($regs)";
      $rs->query($sql);
      $move_members = $rs->record();
      $members = explode(',', $new_class_members . ',' . $move_members);
      foreach ($members as $mid) {
        $member_blacks_count = 0;
        $teacher_blacks_count = 0;

        // 取得學員黑名單
        $sql = "SELECT black_id FROM member_bl_member WHERE member_id = $mid AND deleted = 0";
        $rs3->query($sql);
        if ($rs3->count > 0)
          $blacklist = $rs3->record_array();
        else
          $blacklist = [];
        foreach ($blacklist as $bb)
        {
          if (in_array($bb, $members)) {
            $member_blacks_count += 1;
          }
        }

        $sql = "SELECT black_id FROM member_bl_consultant WHERE black_id = $consultant_id AND member_id = $mid AND deleted = 0";
        $rs3->query($sql);

        if ($rs3->count > 0) $teacher_blacks_count += 1;

        if (($member_blacks_count > 0)) {
          Message('教室裡或欲移動的學員在黑名單內，無法移動');
        }
        
        if (($teacher_blacks_count > 0)) {
          Message('此課程的顧問在黑名單內，無法移動');
        }

        if (($member_blacks_count > 0) || ($teacher_blacks_count > 0)) {
          GoLast();

          exit;
        }
      }

    }
    // 更新
    unset ($v);
    foreach ($_POST['member_reg'] as $rid)
    {
      $v['classroom_id'] = $new;
      $rs->update('course_registration', $rid, $v);
    }
    // 檢查舊的
    $sql = "SELECT COUNT(*) FROM course_registration WHERE classroom_id = $orig AND status = 10";
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
//      $sql = "SELECT '$d' AS `date`, COUNT(id) AS `classroom` FROM `classroom` WHERE `type` IN (20, 30) AND `date` = '$d' AND `status` = 10"; // 20=1:1 30=1:n
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
        $sql = "SELECT COUNT(id) FROM `classroom` WHERE `type` IN (20, 30) AND `date` = '$d' AND `time` = $k AND `status` = 10"; // 20=1:1 30=1:n
        $rs->query($sql);
        $h['classroom'] = $rs->count == 0 ? 0 : $rs->record();
        $sql = "SELECT COUNT(r.id) FROM `course_registration` r, `classroom` c WHERE c.type IN (20, 30) AND c.date = '$d' AND c.time = $k AND c.status = 10 AND c.id = r.classroom_id AND r.status = 10"; // 20=1:1 30=1:n
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
$FUNC_NAME = '自由選課';
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