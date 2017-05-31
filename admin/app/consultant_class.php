<?php
$id = $VARS['consultant_id'] = GetParam('cid');
$rs->select('consultant', $id);
AssignValue($rs);

switch (MODE)
{
  case 'report':
    $id = GetParam('id');
    $rs->select('classroom', $id);
    AssignValue($rs);
    //print_r($VARS);
    //break;
    $VARS['time_text'] = $var_schedule_time[$VARS['time']];
    $sql = "SELECT r.*, m.first_name, m.last_name FROM course_registration r, member m WHERE r.classroom_id = $id AND r.status = 10 AND r.member_id = m.id";
    //echo $sql;
    $rs->query($sql);
    AssignValues($rs, null, 'cbReport');
    break;
  case 'student_report':
    $id = GetParam('id');
    $rs->select('classroom', $id);
    AssignValue($rs);
    //print_r($VARS);
    //break;
    $VARS['time_text'] = $var_schedule_time[$VARS['time']];
    $sql = "SELECT r.*, m.first_name, m.last_name FROM course_registration r, member m WHERE r.classroom_id = $id AND r.status = 10 AND r.member_id = m.id";
    //echo $sql;
    $rs->query($sql);
    AssignValues($rs, null, 'cbReport2');
    break;
  default:
    $TABLE = 'classroom';
    $FUNC_NAME = '上課紀錄';
    $CAN_DELETE = false;
//$SELECT = array ('status');
    $SEARCH = false;
    $SEARCH_DATE = array ('cdate');
    $SORT_BY = '`datetime` DESC';
    $TRANSLATE = array (
      'type' => array($var_registration_type),
    );
    $SPECIAL = "status = 10 AND `type` <> 10 AND consultant_id = $id";
    $VARS['now'] = $current_datehour;
    $VARS['add_button'] = false;

////////////////////////////////////
    require_once('func.inc.php');
////////////////////////////////////
    break;
}
//print_r($VARS);

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
  global $rs2;

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
  if ($r['type'] == 10) // DEMO
    $r['material'] = '';
  elseif ($r['material_id'] == 0)
    $r['material'] = '尚未指定教材';
  else
  {
    $sql = "SELECT * FROM material WHERE id = {$r['material_id']}";
    $rs2->query($sql);
    $rr = $rs2->fetch();
    $r['material'] = $rr['title'];
    $r['material2'] = $rr['eng_title'];
  }
  // 學員
  $sql = "SELECT m.first_name, m.last_name, r.attend FROM course_registration r, member m WHERE r.classroom_id = {$r['id']} AND r.status = 10 AND r.member_id = m.id";
  //echo $sql;
  $rs2->query($sql);
  $r['list'] = AssignResult($rs2);
  //print_r($r);
  // report
  $sql = "SELECT COUNT(*) FROM ques_student WHERE classroom_id = {$r['id']}";
  $rs2->query($sql);
  $r['report'] = $rs2->count > 0 && $rs2->record() > 0;
  $sql = "SELECT COUNT(*) FROM ques_consultant WHERE classroom_id = {$r['id']}";
  $rs2->query($sql);
  $r['report2'] = $rs2->count > 0 && $rs2->record() > 0;

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