<?php
$id = GetParam('id');

switch (MODE)
{
  case 'popup':
    $rs->select('course', $id);
    AssignValue($rs);
    $sql = "SELECT * FROM material WHERE id = {$VARS['material_id']}";
    $rs2->query($sql);
    $rr = $rs2->fetch();
    $VARS['material'] = $rr['title'];
    $VARS['material2'] = $rr['eng_title'];
    if ($VARS['type'] == 40)
    { // get begin/end
      $sql = "SELECT MIN(`date`) AS `begin`, MAX(`date`) AS `end` FROM classroom WHERE course_id = $id";
      $rs->query($sql);
      $r = $rs->fetch();
      $VARS['date'] = substr($r['begin'], 0, 16) . ' ~ ' . substr($r['end'], 0, 16);
    }
    else
    { // get open
      $sql = "SELECT * FROM classroom WHERE course_id = $id";
      $rs->query($sql);
      $r = $rs->fetch();
      $VARS['date'] = substr($r['open_time'], 0, 16);
    }
    // get first classroom
    $sql = "SELECT MIN(`id`) FROM classroom WHERE course_id = $id";
    $rs->query($sql);
    $cid = $rs->record();
    $sql = "SELECT m.*, r.id AS cid FROM course_registration r, member m WHERE r.classroom_id = {$cid} AND r.status = 10 AND r.member_id = m.id";
    $rs2->query($sql);
    $VARS['persons'] = AssignResult($rs2, 'cbStudent');

    break;
  case 'material':
    $sql = "SELECT * FROM material WHERE status = 10 AND `parent` = $id ORDER BY rank";
    $rs->query($sql);
    $arr = AssignResult($rs);
    json_output(array ('status' => true, data => $arr));
    break;
  case 'before_submit':
    $con = GetParam('consultant_id');
    $date = GetParam('date');
    $time = GetParam('time');
    $date = explode(',', $date);
    $time = explode(',', $time);
    $msg = [];
    foreach ($date as $kk => $dd)
    {
      $tt = $time[$kk];
      $sql = "SELECT * FROM classroom WHERE consultant_id = $con AND `date` = '$dd' AND `time` = '$tt' AND status = 10";
      $rs->query($sql);
      if ($rs->count > 0)
        $msg[] = $dd . ' ' . $tt . ':00';
    }
    if ($msg)
      json_output(['status' => false, 'msg' => "此顧問於下列時間已有排課:\n" . implode("\n", $msg)]);
    else
      json_output(['status' => true]);
    break;
  case 'popup':
    break;
}

$type = GetParam('type');

$TABLE = 'course';
$FUNC_NAME = '選修課程管理';
$SELECT = array ('status');
$SEARCH = false;
//$SEARCH_KEYS = array ('account', 'tel', 'email');
$SEARCH_DATE = array ('begin_date');
$SORT_BY = '`begin_date` DESC';
$TRANSLATE = array (
  'status' => array($var_request_status, $var_request_status_color),
  'type' => array($var_registration_type),
);
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'brief', 'height' => 5),
);
$CAN_DELETE = true;

$RADIO = array('status');
$VARS['status_select'] = GenRadio('status', $var_general_status, false, true);

$IMG_TABLE = 'course_images';
$IMAGES = array (
  0 => array ('title' => '封面', 'size' => array (336, 164, 168, 82), 'required' => false, 'accept' => '.png, .jpg, .jpeg'),
);

$VARS['custom'] = <<<EOT
<a class="btn-flat success" href="/admin/{$VARS['NEW_URL']}&type=40"><i class="icon-plus"></i> 新增選修課程</a>
<a class="btn-flat success" href="/admin/{$VARS['NEW_URL']}&type=50"><i class="icon-plus"></i> 新增大會堂</a>
EOT;

$VARS['time_select'] = GenSelect('time[]', $var_schedule_time, false, true);
$VARS['time_select2'] = GenSelect('time', $var_schedule_time, false, true);

$sql = "SELECT * FROM consultant WHERE status = 10";
$rs->query($sql);
AssignValues($rs, 'con');

if ($type == 40)
  $sql = "SELECT m.* FROM material m, material m2 WHERE m.status = 10 AND m.`type` = 20 AND m.`parent` = 0 AND m.id = m2.parent GROUP BY m.id ORDER BY eng_title";
else
{
  $sql = "SELECT * FROM material WHERE status = 10 AND `type` = 30 ORDER BY eng_title";
  $SELECT = array_merge($SELECT, ['material_id']);
}
$rs->query($sql);
AssignValues($rs, 'mat');

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////
$VARS['add_button'] = false;


function fn_callback($r)
{
  global $rs3;

  if ($r['consultant_id'] == 0)
    $r['consultant_name'] = '尚未指派';
  else
  {
    $rs3->select('consultant', $r['consultant_id']);
    $r2 = $rs3->fetch();
    $r['consultant_name'] = $r2['first_name'] . ' ' . $r2['last_name'];
  }
  if ($r['material_id'] == 0)
    $r['material_name'] = '尚未指派';
  else
  {
    $rs3->select('material', $r['material_id']);
    $r2 = $rs3->fetch();
    $r['material_name'] = $r2['title'];
  }
  // get classroom id
  $sql = "SELECT id FROM classroom WHERE course_id = {$r['id']} AND lesson <= 1";
  $rs3->query($sql);
  $cls = $rs3->record();
  $sql = "SELECT COUNT(id) FROM course_registration WHERE status = 10 AND classroom_id = $cls";
  $rs3->query($sql);
  $r['regs'] = $rs3->record();
  $sql = "SELECT COUNT(id) FROM classroom WHERE course_id = {$r['id']} AND status = 10";
  $rs3->query($sql);
  $r['cnt'] = $rs3->record();

  // 50
  if ($r['type'] == 50)
  {
    $sql = "SELECT * FROM classroom WHERE course_id = {$r['id']}";
    $rs3->query($sql);
    $rr = $rs3->fetch();
    $r['begin_date'] = substr($rr['open_time'], 0, 16);
  }

  $r['can_edit'] = $r['can_delete'] = $r['regs'] == 0;
  return $r;
}

function fn_new()
{
  global $VARS, $var_registration_type;

  $VARS['type'] = GetParam('type', 40);
  $VARS['type_text'] = $var_registration_type[$VARS['type']];
  $VARS['begin_date'] = date('Y-m-d', time() + 86400 * 2);
  $VARS['point'] = '1.0';
  $VARS['status'] = DOC_STATUS_SHOW;

  if (count($VARS['mat_list']) == 0)
  {
    Message('請先建立教材', false, MSG_WARN);
    GoLast();
  }

}

function fn_add($id)
{
  global $rs2;

  $mat = $v['material_id'] = GetParam('material_id');
  $sql = "SELECT * FROM material WHERE status = 10 AND `parent` = $mat ORDER BY rank";
//  echo $sql;
  $rs2->query($sql);
  $material = AssignResult($rs2);

  $v['consultant_id'] = $cid = GetParam('consultant_id');
  $rs2->select('consultant', $cid);
  $ct = $rs2->fetch();
  $v['type'] = GetParam('type');
  $date = GetParam('date');
  $time = GetParam('time');
  $date = explode(',', $date);
  $time = explode(',', $time);
  $v['status'] = 10;
  $v['course_id'] = $id;
  $v['consultant_confirmed'] = 0; // not confirmed
  $v['point'] = GetParam('point');
  $v['hour'] = 1;
  $v['wage'] = $v['salary'] = $ct['course_pay'];
  foreach ($date as $kk => $dd)
  {
    $tt = $time[$kk];
    $v['lesson'] = $kk + 1;
    $v['date'] = $dd;
    $v['time'] = $tt;
    if ($v['type'] == 50)
      $v['material_id'] = $mat;
    else
      $v['material_id'] = $material[$kk]['id'];
    $tm = strtotime("$dd $tt:00:00");
    $v['datetime'] = date('YmdH', $tm);
    $v['open_time'] = $v['begin_time'] = date('Y-m-d H:i:s', $tm);
    $v['end_time'] = date('Y-m-d H:i:s', $tm + 3600 - 1);
    $v['sn'] = getSerialNumber2(SN_ROOM);
    // consultant
    if (($tm - time()) >= (12 * 60 * 60))
      $v['consultant_confirmed'] = 0;
    else
      $v['consultant_confirmed'] = 10;
    $rs2->insert('classroom', $v);
    add_meeting_room($rs2->last_id);
    $v['point'] = 0;
  }
  $vv['begin_date'] = $date[0];
  $rs2->update('course', $id, $vv);
}

function cbLesson($r)
{
  global $rs3;

  $rs3->select('material', $r['material_id']);
  $rr = $rs3->fetch();
  $r['material_text'] = $rr['title'];
  $r['time'] = sprintf('%02d', $r['time']);
  return $r;
}

function fn_edit($id)
{
  global $rs2, $VARS;

  $sql = "SELECT * FROM classroom WHERE status = 10 AND course_id = $id ORDER BY lesson";
  $rs2->query($sql);
  AssignValues($rs2, null, 'cbLesson');
  $rs2->select('consultant', $VARS['consultant_id']);
  $r2 = $rs2->fetch();
  $VARS['consultant_text'] = $r2['first_name'] . ' ' . $r2['last_name'];
  $VARS['material_text'] = $rs2->get_value('material', 'title', $VARS['material_id']);
}

function fn_before_modify($id)
{
  foreach ($_POST as $k => $v)
  {
    if (!in_array($k, ['brief', 'status']) && empty($v))
      unset ($_POST[$k]);
  }
}


function fn_modify($id)
{
  global $rs2;

  if (GetParam('type') == 50)
  { // Hall
    $mid = GetParam('material_id');
    $sql = "UPDATE classroom SET material_id = $mid WHERE course_id = $id";
    $rs2->execute($sql);
  }
}

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

function fn_delete($id)
{
  global $rs2;

  $sql = "UPDATE classroom SET status = 20 WHERE course_id = $id";
  $rs2->execute($sql);
}


?>