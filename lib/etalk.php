<?
require (__DIR__ . '/webex.php');


function check_webex($type, $date, $time)
{
  $t = strtotime($date . ' ' . $time . ':00:00');
  if ($type == 10) // demo
  {
    $begin_time = date('Y-m-d H:00:00', $t - 3600);
    $end_time = date('Y-m-d H:00:00', $t + 3600 * 2 - 1);
    $special = ""; // " AND status2 <> 20";
  }
  else
  {
    $begin_time = date('Y-m-d H:00:00', $t);
    $end_time = date('Y-m-d H:i:s', $t + 3600 - 1);
    $special = "";
  }

  $webex_type = 0;
  switch ($type)
  {
    case 10:
      $webex_type = 10; // DEMO
      break;
    case 20: // 自由
    case 30:
    case 40: // 選修
      $webex_type = 20; // 一般課程
      break;
    case 50: // 大會堂
      $webex_type = 30; // 大會堂
      break;
  }
  $rs2 = new ResultSet();
  $rs3 = new ResultSet();
  $sql = "SELECT * FROM webex WHERE status = 10 AND `type` = $webex_type";
  $rs2->query($sql);
  $wid = 0;
  while (($r2 = $rs2->fetch()))
  {
    //print_r($r2);
    // 確認該時段沒有被佔，DEMO h-1 ~ h+2，一般 h+0 ~ h+0
    $sql = "SELECT webex_id FROM classroom WHERE webex_id = {$r2['id']} AND NOT (begin_time > '{$end_time}' OR end_time < '{$begin_time}') $special AND status = 10";
    $rs3->query($sql);
    if ($rs3->count == 0) {
      $wid = $r2['id'];
      break;
    }
  }
  //echo $wid;
  //exit;
  return $wid > 0 ? $wid : false;
}

/**
 * 供學員預約自由選課
 * @param int $member_id 會員ID
 * @param string $date 預約日期 Y-m-d
 * @param int $time 時段 (0 ~ 23, 3 = 03:00 ~ 03:45)
 * @param int $type 課程類型 $var_registration_type (20=一對一, 30=一對多)
 * @return bool true預約成功
 */
function register_free_booking($member_id, $date, $time, $type, $content = '')
{
  global $rs2, $rs3, $var_registration_type, $register_time;

  if ($type != 10) // DEMO
  {
    // 判斷可否登記
    $t = strtotime("$date $time:00:00");
    if ($t < $register_time)
      return -1;
    $special = "";
  }
  else
    $special = ""; // " AND status2 <> 20";
  // TODO: 判斷是否在範圍內


  // 是否有時間? status = 10 有效
  $sql = "SELECT * FROM classroom c, course_registration r WHERE r.member_id = $member_id AND r.status = 10 AND r.classroom_id = c.id AND c.status = 10 AND c.date = '$date' AND c.time = $time $special";
//  echo $sql;
//  exit;
  $rs2->query($sql);
  if ($rs2->count > 0)
    return -2;
  switch ($type)
  {
    case 10: // demo
      $v['point'] = $point = 0;
      $v['consultant_confirmed'] = 10;
      break;
    case 20: // 1on1
      $v['point'] = $point = GetConfig(5); // POINT_1on1;
      break;
    case 30: // group
      $v['point'] = $point = GetConfig(6); // POINT_GROUP;
      break;
  }
  // 判斷點數
  $rs2->select('member', $member_id);
  $m = $rs2->fetch();
  if ($m['status'] != 10 && $type != 10) // 啟用 & DEMO
    return -3;
  if ($m['point'] < $point)
    return -4;
  $v['type'] = $type;
  $v['date'] = $date;
  $v['time'] = $time = sprintf('%02d', $time);
  $v['datetime'] = str_replace('-', '', $date . $time);
  $v['open_time'] = "$date $time:00:00";
  $t = strtotime($v['open_time']);
  if ($type == 10) // demo
  {
    $v['begin_time'] = date('Y-m-d H:00:00', $t - 3600);
    $v['end_time'] = date('Y-m-d H:00:00', $t + 3600 * 2 - 1);
  }
  else
  {
    $v['begin_time'] = date('Y-m-d H:00:00', $t);
    $v['end_time'] = date('Y-m-d H:i:s', $t + 3600 - 1);
  }
  $v['status'] = 10;
  $v['level_id'] = $m['level_id'];
  $v['hour'] = 1;
  $new = false;
  if ($type == 10 || $type == 20)
  { // demo || 1on1 都直接開
    $rs2->insert('classroom', $v);
    $cid = $rs2->last_id;
    $new = true;
  }
  else
  { // 取得空的classroom
    // 取得member interest
    $sql = "SELECT interest_id FROM member_interest WHERE member_id = $member_id";
    $rs3->query($sql);
    $interest = $rs3->record_array();
//	print_r($interest);
    // 取得學員黑名單
    $sql = "SELECT black_id FROM member_bl_member WHERE member_id = $member_id AND deleted = 0";
    $rs3->query($sql);
    if ($rs3->count > 0)
      $blacklist = $rs3->record_array();
    else
      $blacklist = [];
    $rs3->lock('classroom AS c WRITE, class_registration AS r WRITE');
    $sql = "SELECT c.id, COUNT(r.id) AS qty FROM classroom c LEFT JOIN course_registration r ON c.id = r.classroom_id WHERE c.status = 10 AND c.type = $type AND c.date = '$date' AND c.time = $time AND c.level_id = {$m['level_id']} AND r.status = 10 GROUP BY c.id";
    $rs3->query($sql);
    $ok = false;
    while (($r = $rs3->fetch()))
    {
//	print_r($r);
      if ($r['qty'] < GROUP_PERSONS)
      {
        // check interest
        $rs4 = new ResultSet();
        $sql = "SELECT i.interest_id FROM course_registration r, member_interest i WHERE r.classroom_id = {$r['id']} AND r.status = 10 AND r.member_id = i.member_id GROUP BY i.interest_id HAVING COUNT(i.interest_id) = {$r['qty']}";
        //echo $sql;
        $rs4->query($sql);
        if ($rs4->count == 0)
          continue;
        $int = $rs4->record_array();
        $ii = array_intersect($int, $interest);
//		print_r($ii);
        if (empty($ii))
          continue;
        // check black list
        $sql = "SELECT member_id FROM course_registration WHERE classroom_id = {$r['id']} AND status = 10";
        $rs4->query($sql);
        if ($rs4->count > 0)
        {
          $members = $rs4->record_array();
          $ii = array_intersect($blacklist, $members);
          if (!empty($ii))
            continue;
        }
        // check materials
        $sql = "SELECT * FROM classroom WHERE id = {$r['id']}";
        $rs4->query($sql);
        $c = $rs4->fetch();
        if ($c['material_id'] != 0)
        { // get
          // get member's materials
          $sql = <<<EOT
SELECT c.material_id FROM classroom c, course_registration r 
WHERE r.member_id = $member_id AND r.status = 10 AND r.classroom_id = c.id AND c.material_id <> 0
  AND (c.open_time > NOW() OR (c.open_time <= NOW() AND r.attend = 10))
GROUP BY c.material_id
EOT;
          $rs4->query($sql);
          if ($rs4->count == 0)
            $materials = [];
          else
            $materials = $rs4->record_array();
          if (in_array($c['material_id'], $materials))
            continue;
        }

        $cid = $r['id'];
        $ok = true;
        break;
      }
    }
//	exit;
    if (!$ok) // 沒找到
    {
      $rs3->insert('classroom', $v);
      $cid = $rs3->last_id;
      $new = true;
    }
  }
  unset ($v);
  $v['status'] = 10; // 有效
  $v['member_id'] = $member_id;
  $v['classroom_id'] = $cid;
  if ($type == 10) // demo
  {
    cronjob_call('assign.php', 'classroom_id=' . $cid);
    while (true)
    {
      $guid = makePassword(32);
      $sql = "SELECT id FROM course_registration WHERE memo = '$guid'";
      $rs2->query($sql);
      if ($rs2->count == 0)
        break;
    }
    $v['memo'] = $guid;
  }
  elseif ($new)
    add_meeting_room($cid);

  $rs3->insert('course_registration', $v);
  $rid = $rs3->last_id;
  $rs3->unlock();
  if ($new)
  {
    unset ($v);
    $v['sn'] = getSerialNumber2(SN_ROOM);
    $rs2->update('classroom', $cid, $v);
  }
  if ($type == 10) // demo
  {
    // history
    if (!empty($content))
      $content = "\n" . $content;
    unset ($v);
    $v['member_id'] = $member_id;
    $v['type'] = 30; // DEMO
	
	$v['contact_status'] =7;
	
	$v['next_time'] = "$date $time:00:00";
    $v['content'] = "預約DEMO 於 $date $time:00 ~ $time:45" . $content;
    $rs2->insert('member_history', $v);
  }
  else
  { // member_point
    unset ($v);
    $v['member_id'] = $member_id;
    $v['type'] = 10; // 選課
    $v['target_id'] = $rid;
    $v['brief'] = "預約 " . $var_registration_type[$type] . "$date $time:00 ~ $time:45";
    $v['io'] = 0 - $point;
    $sql = "UPDATE member SET point = point - $point WHERE id = $member_id";
    $rs3->execute($sql);
    $v['balance'] = $rs3->get_value('member', 'point', $member_id);
    $rs2->insert('member_point', $v);
  }
  return $rid;
}

/**
 * 供學員取消自由選課
 * @param int $member_id 會員ID
 * @param string $date 預約日期 Y-m-d
 * @param int $time 時段 (0 ~ 23, 3 = 03:00 ~ 03:45)
 * @return bool true取消成功
 */
function cancel_free_booking($member_id, $date, $time, $reason = '')
{
  global $rs2, $rs3, $var_registration_type, $register_time;

  // 判斷可否退選
  $t = strtotime("$date $time:00:00");
  if (empty($reason) && $t < cancel_time)
    return -1;
  // 是否有時間? status = 10 有效
  $sql = "SELECT c.*, r.id AS reg_id, r.back, r.locked FROM classroom c, course_registration r WHERE r.member_id = $member_id AND r.status = 10 AND r.classroom_id = c.id AND c.status = 10 AND c.date = '$date' AND c.time = $time";
//  echo $sql;
  $rs2->query($sql);
  if ($rs2->count == 0)
    return -5;
  $r = $rs2->fetch();
  if ($r['locked'] == 1)
    return -8;
  $v['status'] = 20; // 無效
  $rs2->update('course_registration', $r['reg_id'], $v);
  // member_point
  if ($r['back'] == 0) // 沒退過點
  {
    unset ($v);
    $v['member_id'] = $member_id;
    $v['type'] = 100; // 退選
    $v['target_id'] = $r['reg_id'];
    $v['brief'] = "取消預約 " . $var_registration_type[$r['type']] . "$date $time:00 ~ $time:45";
    if (!empty($reason))
      $v['brief'] .= "\n取消原因: $reason";
    $v['io'] = $r['point'];
    $sql = "UPDATE member SET point = point + {$r['point']} WHERE id = $member_id";
    $rs3->execute($sql);
    $v['balance'] = $rs3->get_value('member', 'point', $member_id);
    $rs2->insert('member_point', $v);
  }
  // 判斷classroom還有沒人
//  print_r($r);
  $sql = "SELECT COUNT(*) FROM course_registration WHERE classroom_id = {$r['id']} AND status = 10";
//	echo $sql;
  $rs2->query($sql);
  if ($rs2->count == 0 || $rs2->record() == 0)
  { // 沒人了要更新
    unset ($v);
    $v['status'] = 20; // stop
    $rs2->update('classroom', $r['id'], $v);
    del_meeting_room($r['id']);
  }
  return true;
}

/**
 * 供顧問設定浮動課表
 * @param int $consultant_id 顧問ID
 * @param string $date 日期 Y-m-d
 * @param int $time 時段 (0 ~ 23, 3 = 03:00 ~ 03:45)
 * @param int $type 課表類型 $var_schedule_available_type (前台只使用20)
 * @return bool true設定成功
 */
function set_available($consultant_id, $date, $time, $type = 20)
{
  global $rs2, $rs3;

  $sql = "SELECT id, available, fixed FROM consultant_schedule WHERE consultant_id = $consultant_id AND `date` = '$date' AND `time` = $time";
  $rs2->query($sql);
  $r = $rs2->fetch();
  if ($r['available'] != 0 || $r['fixed'] != 0)
    return -6;
  $v['available'] = $type;
  $rs2->update('consultant_schedule', $r['id'], $v);
  return true;
}

/**
 * 供顧問取消浮動課表
 * @param int $consultant_id 顧問ID
 * @param string $date 日期 Y-m-d
 * @param int $time 時段 (0 ~ 23, 3 = 03:00 ~ 03:45)
 * @return bool true取消成功
 */
function cancel_available($consultant_id, $date, $time, $type = 20)
{
  global $rs2, $rs3;

  $sql = "SELECT id, available, fixed FROM consultant_schedule WHERE consultant_id = $consultant_id AND `date` = '$date' AND `time` = $time";
  $rs2->query($sql);
  $r = $rs2->fetch();
  if ($r['available'] != $type || $r['fixed'] != 0)
    return -7;
  $v['available'] = 0;
  $rs2->update('consultant_schedule', $r['id'], $v);
  return true;
}

/**
 * 供後台設定顧問DEMO時段
 * @param int $consultant_id 顧問ID
 * @param string $date 日期 Y-m-d
 * @param int $time 時段 (0 ~ 23, 3 = 03:00 ~ 03:45)
 * @return bool true設定成功
 */
function set_demo($consultant_id, $date, $time)
{
}

function update_consultant_schedule($consultant_id)
{
  global $rs2;

  $sql = "SELECT * FROM consultant_fixed_schedule WHERE consultant_id = $consultant_id";
  $rs2->query($sql);
  while (($r2 = $rs2->fetch()))
    $tt[$r2['weekday']][$r2['time']] = $r2['available'];
//  print_r($tt);
  $t = strtotime(date('Y-m-d 00:00:00', time() + 300));
  for ($i = 0; $i < (DATE_RANGE + 1); $i++)
  {
    $time = $t + $i * 86400;
    $w = date('w', $time);
    $v['date'] = date('Y-m-d', $time);
    $dt = date('Ymd', $time);
    for ($k = 0; $k < 24; $k++)
    {
      $v['consultant_id'] = $consultant_id;
      $v['time'] = $k;
      $v['datetime'] = $dt . sprintf('%02d', $k);
      $v['fixed'] = $tt[$w][$k];
      $sql = "SELECT * FROM consultant_schedule WHERE consultant_id = $consultant_id AND datetime = " . $v['datetime'] ;
//      echo $sql;
      $rs2->query($sql);
      if ($rs2->count == 0)
        $rs2->insert('consultant_schedule', $v);
      else
      {
        $r = $rs2->fetch();
//        if (($v['available'] == 0 && $r['available'] == 10) || $v['available'] == 10)
//        {
          $sql = "UPDATE consultant_schedule SET fixed = {$v['fixed']} WHERE id = " . $r['id'];
          $rs2->execute($sql);
//        }
      }
    }
  }
}

function add_meeting_room($classroom_id)
{
  global $rs3;

  $v['classroom_id'] = $classroom_id;
  $v['type'] = 'SM';
  $rs3->insert('webex_queue', $v);
}

function del_meeting_room($classroom_id)
{
  global $rs3;

  $v['classroom_id'] = $classroom_id;
  $v['type'] = 'DM';
  $rs3->insert('webex_queue', $v);
}

function create_meeting_room($classroom_id)
{
  global $rs3;

  $rs3->select('classroom', $classroom_id);
  $r = $rs3->fetch();
  if ($r['webex_id'] == 0)
    return false;
  if ($r['status'] != 10 || !empty($r['meeting_key']))
    return true;
  $t = strtotime($r['open_time']) + 3600 * 3; // due time
//  echo $t . time();
  if ($t < time())
  	return true;
  // check type
  $du = 45;
  if ($r['type'] == '10') // demo
  {
//    $t -= 3600; // 提早一小時
    $du = 105;
    $title = 'DEMO';
  }
  // get webex
  $rs3->select('webex', $r['webex_id']);
  $w = $rs3->fetch();
  $pw = makePassword();

  $wx = new WebexAPI;
  $wx->set_auth( $w["account"], $w["password"], WEBEX_SID, WEBEX_PID);
  $wx->set_url(WEBEX_API_URL);
  $k = $wx->sm($w["account"], $pw, $r['open_time'] . ' ' . $title, date('m/d/Y H:i:s', strtotime($r['begin_time'])), $du);
//  echo $k;
  if (!is_numeric($k))
    return false;
  $v['meeting_key'] = $k;
  $v['meeting_pw'] = $pw;
  $rs3->update('classroom', $classroom_id, $v);
  return $k;
}


function delete_meeting_room($classroom_id, $queue = true)
{
  global $rs3;

  $rs3->select('classroom', $classroom_id);
  $r = $rs3->fetch();
  if (empty($r['meeting_key']))
    return true;
  $rs3->select('webex', $r['webex_id']);
  $w = $rs3->fetch();
  $wx = new WebexAPI;
  $wx->set_auth( $w["account"], $w["password"], WEBEX_SID, WEBEX_PID);
  $wx->set_url(WEBEX_API_URL);
  return $wx->dm($r['meeting_key']);
}


//-----------------------------------------------------------------------------------------------------------------------
/**
 * 供學員預約選修課
 * @param int $member_id 會員ID
 * @param int $course_id 課程ID
 * @return bool true預約成功
 */
function register_course($member_id, $course_id)
{
  global $rs2, $rs3, $var_registration_type, $register_time;

  // 取得課程
  $rs2->select('course', $course_id);
  $course = $rs2->fetch();
  // 取得課程時間
  $sql = "SELECT * FROM classroom WHERE status = 10 AND course_id = $course_id AND lesson <= 1";
  $rs2->query($sql);
  $cc = $rs2->fetch();
  $date = $cc['date'];
  $time = $cc['time'];
  // 判斷可否登記
//  $t = strtotime("$date $time:00:00");
//  if ($t < $register_time)
//    return -1;
  // TODO: 判斷是否在範圍內

  // 取得所有classroom
  $sql = "SELECT * FROM classroom WHERE status = 10 AND course_id = $course_id";
  $rs2->query($sql);
  while (($r = $rs2->fetch()))
  {
    $sql = "SELECT * FROM classroom c, course_registration r WHERE r.member_id = $member_id AND r.status = 10 AND r.classroom_id = c.id AND c.status = 10 AND c.date = '{$r['date']}' AND c.time = {$r['time']}";
    $rs3->query($sql);
    if ($rs3->count > 0)
      return -2;
  }

  // 是否有時間? status = 10 有效
  $sql = "SELECT * FROM classroom c, course_registration r WHERE r.member_id = $member_id AND r.status = 10 AND r.classroom_id = c.id AND c.status = 10 AND c.date = '$date' AND c.time = $time";
//  echo $sql;
//  exit;
  $rs2->query($sql);
  if ($rs2->count > 0)
    return -2;
  $point = $course['point'];
  // 判斷點數
  $rs2->select('member', $member_id);
  $m = $rs2->fetch();
  if ($m['status'] != 10) // 啟用
    return -3;
  if ($m['point'] < $point)
    return -4;
  $rs3->lock('classroom AS c WRITE, class_registration AS r WRITE');
  // 取得所有classroom
  $sql = "SELECT * FROM classroom WHERE status = 10 AND course_id = $course_id";
  $rs3->query($sql);
  unset ($v);
  $rid = 0;
  while (($r = $rs3->fetch()))
  {
    $rs4 = new ResultSet();
    $v['member_id'] = $member_id;
    $v['classroom_id'] = $r['id'];
    $v['status'] = DOC_STATUS_SHOW;
    $rs4->insert('course_registration', $v);
    if ($rid == 0)
      $rid = $rs4->last_id;
  }
  $rs3->unlock();
  // member_point
  unset ($v);
  $v['member_id'] = $member_id;
  $v['type'] = 10; // 選課
  $v['target_id'] = $rid;
  $v['brief'] = "預約 " . $var_registration_type[$course['type']] . " " . $course['begin_date'];
  $v['io'] = 0 - $point;
  $sql = "UPDATE member SET point = point - $point WHERE id = $member_id";
  $rs3->execute($sql);
  $v['balance'] = $rs3->get_value('member', 'point', $member_id);
  $rs2->insert('member_point', $v);
  return $rid;
}

/**
 * 供學員取消一般選課
 * @param int $member_id 會員ID
 * @param string $date 預約日期 Y-m-d
 * @param int $time 時段 (0 ~ 23, 3 = 03:00 ~ 03:45)
 * @return bool true取消成功
 */
function cancel_course($member_id, $course_id, $reason = '')
{
  global $rs2, $rs3, $var_registration_type, $register_time;

  // get classroom_id
  $sql = "SELECT * FROM classroom WHERE course_id = $course_id AND status = 10 ORDER BY lesson";
  $rs2->query($sql);
  if ($rs2->count == 0)
    return -5;
  $cls = $rs2->fetch();
  // get member
  $sql = "SELECT * FROM course_registration WHERE classroom_id = {$cls['id']} AND member_id = $member_id AND status = 10";
  $rs3->query($sql);
  if ($rs3->count == 0)
    return -5;
  $reg = $rs3->fetch();

  if ($reg['locked'] == 1)
    return -8;

  // 判斷可否退選
  $t = strtotime($cls['open_time']);
  if (empty($reason) && $t < $register_time)
    return -1;
  // 先退點 member_point
  if ($reg['back'] == 0) // 沒退過點
  {
    unset ($v);
    $v['member_id'] = $member_id;
    $v['type'] = 100; // 退選
    $v['target_id'] = $reg['id'];
    //
    $v['brief'] = "取消預約 " . $var_registration_type[$reg['type']] . " " . $course['begin_date'];
    if (!empty($reason))
      $v['brief'] .= "\n取消原因: $reason";
    $v['io'] = $cls['point'];
    $sql = "UPDATE member SET point = point + {$cls['point']} WHERE id = $member_id";
    $rs3->execute($sql);
    $v['balance'] = $rs3->get_value('member', 'point', $member_id);
    $rs2->insert('member_point', $v);
  }
  // un掉整個reg
  // 取得所有classroom
  $sql = "SELECT * FROM classroom WHERE status = 10 AND course_id = $course_id";
  $rs3->query($sql);
  while (($r = $rs3->fetch()))
  {
    $sql = "UPDATE course_registration SET status = 20 WHERE member_id = $member_id AND classroom_id = {$r['id']}";
    $rs2->execute($sql);
  }

  return true;
}

?>