<?php
$id = $VARS['consultant_id'] = GetParam('cid');
$rs->select('consultant', $id);
AssignValue($rs);

switch (MODE)
{
  case 'update':
    $id = GetParam('id');
    $rs->select('classroom', $id);
    $r = $rs->fetch();
    $v['hour'] = GetParam('hour');
    $v['salary'] = $v['hour'] * $r['wage'];
    $v['reward'] = GetParam('reward');
    $v['memo'] = GetParam('memo');
    $rs->update('classroom', $id, $v);
    json_output(array('status' => true));
    break;
  case 'popup':
    $id = GetParam('id');
    $rs->select('classroom', $id);
    AssignValue($rs);
    break;
  case 'detail':
    $VARS['ym'] = $ym = GetParam('ym');
    $DATA_SQL = "SELECT * FROM classroom WHERE status = 10 AND consultant_id = $id AND DATE_FORMAT(`date`, '%Y-%m') = '$ym' AND consultant_confirmed = 10 AND open_time < '$current_time' ORDER BY `open_time`";
    $CALLBACK = 'cbDetail';
    $TRANSLATE = array (
      'type' => array($var_registration_type),
    );
    $VARS['add_button'] = false;
    $NAME = '薪資調整';
    require_once('func.inc.php');
    break;
  default:
    $TABLE = 'classroom';
    $FUNC_NAME = '上課紀錄';
    $CAN_DELETE = false;
//$SELECT = array ('status');
    $SEARCH = false;
    //$SEARCH_DATE = array ('cdate');
    $VARS['add_button'] = false;
    //$SORT_BY = '`datetime` DESC';
    $TRANSLATE = array (
      'type' => array($var_registration_type),
    );
    $EDITORS = array (
      array ('type' => EDITOR_TEXT, 'name' => 'memo', 'height' => 3, 'required' => true),
    );
    $DATA_SQL = "SELECT consultant_id, DATE_FORMAT(`date`, '%Y-%m') AS ym, SUM(salary + reward) AS total FROM classroom WHERE status = 10 AND consultant_id = $id AND consultant_confirmed = 10 AND open_time < '$current_time' GROUP BY ym ORDER BY ym DESC";
    //echo $DATA_SQL;

////////////////////////////////////
    require_once('func.inc.php');
////////////////////////////////////
    break;
}
//print_r($VARS);

function cbDetail($r)
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
  elseif ($r['type'] == 99)
    $r['material'] = '調整原因: ' . $r['memo'];
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

  $r['total'] = $r['salary'] + $r['reward'];

  return $r;
}

function fn_callback($r)
{
  global $rs2, $current_time;

  // demo = 10
  $sql = "SELECT SUM(`hour`) AS `hour`, SUM(salary) AS salary FROM classroom WHERE status = 10 AND DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_id = {$r['consultant_id']} AND consultant_confirmed = 10 AND `type` = 10 AND open_time < '$current_time'";
  $rs2->query($sql);
  $r2 = $rs2->fetch();
  $r['demo_hour'] = $r2['hour'];
  $r['demo_salary'] = $r2['salary'];
  // normal (99 adjust)
  $sql = "SELECT SUM(`hour`) AS `hour`, SUM(salary) AS salary FROM classroom WHERE status = 10 AND DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_id = {$r['consultant_id']} AND consultant_confirmed = 10 AND `type` NOT IN (10, 99) AND open_time < '$current_time'";
  $rs2->query($sql);
  $r2 = $rs2->fetch();
  $r['course_hour'] = $r2['hour'];
  $r['course_salary'] = $r2['salary'];
  // adjust
  $sql = "SELECT SUM(reward) FROM classroom WHERE status = 10 AND DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_id = {$r['consultant_id']} AND consultant_confirmed = 10 AND open_time < '$current_time'";
  $rs2->query($sql);
  $r['adjust'] = $rs2->record();

//  $sql = "SELECT SUM(salary) FROM classroom WHERE DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_id = {$r['consultant_id']} AND consultant_confirmed = 10 AND `type` = 99";
//  $rs2->query($sql);
//  $r['adjust'] = $rs2->record();

  return $r;
}


function fn_new()
{
  global $VARS;

  $ym = GetParam('ym');
  $time = strtotime($ym . '-01');
  $VARS['date'] = date('Y-m-t', $time);
}

function fn_before_add()
{
//  $ym = GetParam('ym');
//  $time = strtotime($ym . '-01');
  $time = strtotime(GetParam('date'));
  $_POST['consultant_id'] = GetParam('cid');
  $_POST['status'] = 10;
  $_POST['type'] = 99; // adjust
//  $_POST['date'] = date('Y-m-t', $time);
  $_POST['date'] = date('Y-m-d', $time);
  $_POST['time'] = 23;
  $_POST['open_time'] = $_POST['date'] . ' 23:59:59';
//  $_POST['datetime'] = date('Ymt23', $time);
  $_POST['datetime'] = date('Ymd23', $time);
  $_POST['consultant_confirmed'] = 10;
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