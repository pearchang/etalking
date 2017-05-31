<?php
$id = GetParam('id');

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
  case 'consultant':
    $rs->select('classroom', $id);
    $r = $rs->fetch();
    $VARS = array_merge($VARS, $r);
    $VARS['time_text'] = $var_schedule_time[$r['time']];
    $sql = "SELECT m.* FROM course_registration r, member m WHERE r.classroom_id = $id AND r.status = 10 AND r.member_id = m.id";
    $rs->query($sql);
    $r = $rs->fetch();
    $VARS = array_merge($VARS, $r);
    $VARS['prefer_text'] = $var_consultant_type[$r['prefer']];
    $rs->select('user', $r['sales_id']);
    $r = $rs->fetch();
    $VARS['sales_name'] = $r['user_name'];
    $VARS['sales_first_name'] = $r['first_name'];
    $VARS['sales_last_name'] = $r['last_name'];
    $VARS['in_tel'] = $r['in_tel'];
    $VARS['id'] = $id;
    // get consultant
    $sql = "SELECT id, first_name, last_name, chi_name FROM consultant WHERE status = 10 AND demo_payment >= 10 ORDER BY first_name, last_name";
    $rs->query($sql);
    AssignValues($rs, 'consultant');
    break;
  case 'autocomplete':
    $term = GetParam('term');
    $k = "'%$term%'";
    $sql = "SELECT id, first_name, last_name, chi_name FROM consultant WHERE status = 10 AND demo_payment >= 10 AND first_name LIKE $k OR last_name LIKE $k OR chi_name LIKE $k ORDER BY first_name, last_name";
    $rs->query($sql);
    unset ($v, $vv);
    while (($r = $rs->fetch()))
    {
      $v['id'] = $r['id'];
      $v['label'] = $v['value'] = "{$r['first_name']} {$r['last_name']} ({$r['chi_name']})";
      $vv[] = $v;
    }
    json_output($vv);
    break;
  case 'assign':
    $id = GetParam('id');
    unset ($v);
    $v['consultant_id'] = GetParam('consultant_id');
    $rs->update('classroom', $id, $v);
    Message('指派完成', false, MSG_OK);
    json_output(array ('status' => true));
    break;
  default:
    $TABLE = 'course_registration';
    $FUNC_NAME = 'DEMO排課';
//$SELECT = array ('status');
    $SEARCH = false;
//$SEARCH_KEYS = array ('account', 'tel', 'email');
    $SORT_BY = '`date`, `time`';
    $TRANSLATE = array (
//  'status' => array($var_request_status, $var_request_status_color),
//  'gender' => array($var_member_gender, $var_member_gender_color),

    );
    $DATA_SQL = <<<EOT
SELECT * FROM classroom c, course_registration r, member m
WHERE r.status = 10 AND r.classroom_id = c.id AND c.status = 10 AND c.type = 10 AND c.datetime >= {$today_00} AND r.member_id = m.id AND m.status IN (60, 70)
ORDER BY c.datetime
EOT;
//    echo $DATA_SQL;

////////////////////////////////////
    require_once('func.inc.php');
////////////////////////////////////
    $VARS['add_button'] = false;
    break;
}

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
  global $rs3, $var_schedule_time, $open_time, $close_time;

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
//  $r['can_enter'] = ($open_time >= $r['open_time'] && $close_time <= $r['open_time']);
  $r['can_enter'] = true;
  $r['can_cancel'] = $close_time <= $r['open_time'];
  // report
  $sql = "SELECT COUNT(*) FROM ques_demo WHERE classroom_id = {$r['id']}";
  $rs3->query($sql);
  $r['report'] = $rs3->count > 0 && $rs3->record() > 0;

  return $r;
}


function fn_callback($r)
{
  global $rs3, $var_member_gender, $var_schedule_time;

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
    $r2 = $rs3->fetch();
    $r['consultant_id_text'] = $r2['first_name'] . ' ' . $r2['last_name'];
  }
  $r['time_text'] = $var_schedule_time[$r['time']];
  // report
  $sql = "SELECT COUNT(*) FROM ques_demo WHERE classroom_id = {$r['classroom_id']}";
  //echo $sql;
  $rs3->query($sql);
  $r['report'] = $rs3->count > 0 && $rs3->record() > 0;

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