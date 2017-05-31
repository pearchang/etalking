<?php
$eid = $VARS['enterprise_id'] = GetParam('eid');
$VARS['enterprise_name'] = $rs->get_value('enterprise', 'ent_name', $eid);

$id = GetParam('id');
switch (MODE)
{
  case 'report':
    $id = GetParam('id');
    $mid = GetParam('mid');
    $rs->select('classroom', $id);
    AssignValue($rs);
    //print_r($VARS);
    //break;
    $VARS['time_text'] = $var_schedule_time[$VARS['time']];
    $sql = "SELECT r.*, m.first_name, m.last_name FROM course_registration r, member m WHERE r.classroom_id = $id AND r.status = 10 AND r.member_id = m.id AND m.id = $mid";
    //echo $sql;
    $rs->query($sql);
    AssignValues($rs, null, 'cbReport');
    break;
  case 'student_report':
    $id = GetParam('id');
    $mid = GetParam('mid');
    $rs->select('classroom', $id);
    AssignValue($rs);
    //print_r($VARS);
    //break;
    $VARS['time_text'] = $var_schedule_time[$VARS['time']];
    $sql = "SELECT r.*, m.first_name, m.last_name FROM course_registration r, member m WHERE r.classroom_id = $id AND r.status = 10 AND r.member_id = m.id AND m.id = $mid";
    //echo $sql;
    $rs->query($sql);
    AssignValues($rs, null, 'cbReport2');
    //print_r($VARS['list']);
    break;
  case 'cancel':
    $sql = "SELECT c.*, r.id AS rid, r.member_id FROM classroom c, course_registration r WHERE r.id = $id AND r.classroom_id = c.id";
    $rs->query($sql);
    $r = $rs->fetch();
    // check time
    if (date('Y-m-d H:i:s') > $r['open_time'])
      json_output(array ('status' => false, 'msg' => '已超過上課時間，無法取消'));
    cancel_free_booking($r['member_id'], $r['date'], $r['time'], GetParam('reason'));
    // history
    unset ($v);
    $v['member_id'] = $r['member_id'];
    $v['type'] = 70; // 客服
    $v['content'] = '客服取消 ' . $var_registration_type[$r['type']] . " {$r['date']} {$r['time']}:00 ~ {$r['time']}:45";
    $rs->insert('member_history', $v);
    json_output(array ('status' => true));
    break;
  case 'back':
    $sql = "SELECT c.*, r.member_id FROM classroom c, course_registration r WHERE r.id = $id AND r.classroom_id = c.id";
    $rs->query($sql);
    $r = $rs->fetch();
    unset ($v);
    $v['member_id'] = $r['member_id'];
    $v['type'] = 130; // 退點
    $v['target_id'] = $id;
    $v['brief'] = '客服退點 ' . $var_registration_type[$r['type']] . " {$r['date']} {$r['time']}:00 ~ {$r['time']}:45\n退點原因: " . GetParam('reason');
    $v['io'] = $r['point'];
    $sql = "UPDATE member SET point = point + {$r['point']} WHERE id = ". $r['member_id'];
    $rs3->execute($sql);
    $v['balance'] = $rs3->get_value('member', 'point', $r['member_id']);
    $rs2->insert('member_point', $v);

    unset ($v);
    $v['back'] = 1;
    $rs->update('course_registration', $id, $v);
    // history
    unset ($v);
    $v['member_id'] = $r['member_id'];
    $v['type'] = 70; // 客服
    $v['content'] = '客服退點 ' . $var_registration_type[$r['type']] . " {$r['date']} {$r['time']}:00 ~ {$r['time']}:45\n退點原因: " . GetParam('reason');
    $rs->insert('member_history', $v);
    json_output(array ('status' => true));
    break;
  case 'lock':
    $data = GetParam('data');
    $sql = "UPDATE course_registration SET locked = {$data} WHERE id = $id";
    $rs->execute($sql);
    json_output(array ('status' => true));
    break;
  default:
    $member_id = $id = $VARS['member_id'] = $_POST['member_id'] = GetParam('mid');
    $rs->select('member', $id);
    AssignValue($rs);
    $VARS['gender_text'] = $var_member_gender[$VARS['gender']];
    $VARS['add_button'] = true;

    $VARS['func_name'] = '上課紀錄';
    $TABLE = 'course_registration';
    $FUNC_NAME = '上課紀錄';
    $SORT_BY = '`open_time` DESC';
    $TRANSLATE = array (
      'type' => array($var_registration_type),
      'attend' => array($var_course_attend),
    );
    $DATA_SQL = "SELECT c.*, r.id AS rid, r.member_id, r.attend, r.back, r.locked FROM course_registration r, classroom c WHERE r.member_id = $id AND r.status <> 20 AND r.classroom_id = c.id AND c.status = 10 AND c.type > 10 ORDER BY c.open_time DESC"; //
    $VARS['now'] = $current_datehour;
    $VARS['add_button'] = false;

////////////////////////////////////
    require_once('func.inc.php');
////////////////////////////////////
    break;
}

function cbReport($r)
{
  global $rs2, $member_id;

  $member_id = $r['member_id'];
  $sql = "SELECT * FROM questionnaire WHERE type = 30 ORDER BY rank";
  //echo $sql;
  $rs2->query($sql);
  $r['ques_list'] = AssignResult($rs2, 'cbQues');
  //print_r($r);
  return $r;
}

function cbQues($r)
{
  global $id, $member_id, $rs3;

  $r['question'] = $r['text'];
  if ($r['text'] == 'Summary')
    $sql = "SELECT content FROM ques_student WHERE classroom_id = $id AND ques_id = {$r['id']} AND member_id = $member_id";
  else
    $sql = "SELECT i.text AS answer FROM ques_student s, ques_item i WHERE s.classroom_id = $id AND s.ques_id = {$r['id']} AND s.member_id = $member_id AND s.content = i.id";
  //echo $sql . "\n";
  $rs3->query($sql);
  $r['answer'] = nl2br($rs3->count > 0 ? $rs3->record() : '');

  return $r;
}

function cbReport2($r)
{
  global $rs2, $member_id;

  $member_id = $r['member_id'];
  $sql = "SELECT * FROM questionnaire WHERE type = 20 ORDER BY rank";
  //echo $sql;
  $rs2->query($sql);
  $r['ques_list'] = AssignResult($rs2, 'cbQues2');
  //print_r($r);
  return $r;
}

function cbQues2($r)
{
  global $id, $member_id, $rs3;

  $r['question'] = $r['text'];
  // tag
  $sql = "SELECT * FROM ques_consultant WHERE classroom_id = $id AND ques_id = {$r['id']} AND member_id = $member_id";
  //cho $sql;
  $rs3->query($sql);
  $answer = '';
  while (($r2 = $rs3->fetch()))
  {
    $tt = $r['id'] . ',' . $r2['tag'];
    if (in_array($tt, array ('11,0', '18,0', '26,1', '29,0')))
      $answer .= trim($r2['content']) . "\n";
    elseif (!empty($r2['content']))
    { // 取item text
      $rs4 = new ResultSet();
      $sql = "SELECT GROUP_CONCAT(`text`) FROM ques_item WHERE id IN ({$r2['content']})";
      $rs4->query($sql);
      $answer .= $rs4->record() . "\n";
    }
  }
  $r['answer'] = nl2br(trim($answer));
  return $r;
}

function fn_callback($r)
{
  global $rs2, $current_datehour, $member_id;

  $r['time2'] = substr('0' . $r['time'], -2);
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
  // can cancel
  if ($r['datetime'] > $current_datehour && $r['attend'] == 0 && !$_SESSION['admin_is_sales'])
    $r['can_cancel'] = true;

  // report
  $sql = "SELECT COUNT(*) FROM ques_student WHERE classroom_id = {$r['id']} AND member_id = $member_id";
  $rs2->query($sql);
  $r['report'] = $rs2->count > 0 && $rs2->record() > 0;
  $sql = "SELECT COUNT(*) FROM ques_consultant WHERE classroom_id = {$r['id']} AND member_id = $member_id";
  $rs2->query($sql);
  $r['report2'] = $rs2->count > 0 && $rs2->record() > 0;

  return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = 10; // 審核中
}

function fn_add($id)
{
}

function fn_edit($id)
{
}

// TODO: EDIT

function fn_modify($id)
{
}
?>