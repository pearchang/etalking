<?php
$VARS['ym'] = $ym = GetParam('ym');
$mm = MODE;
if (MODE == 'export' && !empty($ym))
  $mm = 'detail';
switch ($mm)
{
  case 'detail':
    $DATA_SQL = <<<EOT
SELECT cc.id, '$ym' AS ym, CONCAT(cc.first_name, ' ', cc.last_name, ' / ', cc.chi_name) AS consultant, SUM(c.salary + c.reward) AS total
FROM classroom c, consultant cc WHERE c.status = 10 AND DATE_FORMAT(c.`date`, '%Y-%m') = '$ym' AND c.consultant_confirmed = 10 AND c.open_time < '$current_time' AND c.consultant_id = cc.id
GROUP BY c.consultant_id ORDER BY cc.first_name, cc.last_name, cc.chi_name
EOT;
    //echo $DATA_SQL;
    $CALLBACK = 'cbDetail';
    $TRANSLATE = array (
      'type' => array($var_registration_type),
    );
    $VARS['add_button'] = false;
    $VARS['export_button'] = true;
    $EXPORT_FILENAME = "薪資總表-$ym.csv";
    $EXPORT_FIELDS = ['consultant' => '顧問', 'demo_hour' => 'DEMO時數	', 'demo_salary' => 'DEMO薪資', 'course_hour' => '授課時數', 'course_salary' => '授課薪資', 'adjust' => '獎懲調整', 'total' => '總計'];
    $EXPORT_CALLBACK = 'cbDetail';
    require_once('func.inc.php');
    break;
  default:
    $TABLE = 'classroom';
    $FUNC_NAME = '薪資報表';
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
    $DATA_SQL = "SELECT DATE_FORMAT(`date`, '%Y-%m') AS ym, SUM(salary + reward) AS total FROM classroom WHERE status = 10 AND consultant_confirmed = 10 AND open_time < '$current_time' AND consultant_id > 0 GROUP BY ym ORDER BY ym DESC";
    //echo $DATA_SQL;
    $VARS['export_button'] = true;
    $EXPORT_FILENAME = '薪資總表.csv';
    $EXPORT_FIELDS = ['ym' => '年月', 'demo_hour' => 'DEMO時數	', 'demo_salary' => 'DEMO薪資', 'course_hour' => '授課時數', 'course_salary' => '授課薪資', 'adjust' => '獎懲調整', 'total' => '總計'];
    $EXPORT_CALLBACK = 'fn_callback';

////////////////////////////////////
    require_once('func.inc.php');
////////////////////////////////////
    break;
}
//print_r($VARS);

function cbDetail($r)
{
  global $rs2, $current_time;

  $sql = "SELECT SUM(`hour`) AS `hour`, SUM(salary) AS salary FROM classroom WHERE status = 10 AND DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_id = {$r['id']} AND consultant_confirmed = 10 AND `type` = 10 AND open_time < '$current_time' AND consultant_id > 0";
  $rs2->query($sql);
  $r2 = $rs2->fetch();
  $r['demo_hour'] = empty($r2['hour']) ? 0 : $r2['hour'];
  $r['demo_salary'] = empty($r2['salary']) ? 0 : $r2['salary'];
  // normal (99 adjust)
  $sql = "SELECT SUM(`hour`) AS `hour`, SUM(salary) AS salary FROM classroom WHERE status = 10 AND DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_id = {$r['id']} AND consultant_confirmed = 10 AND `type` NOT IN (10, 99) AND open_time < '$current_time' AND consultant_id > 0";
  $rs2->query($sql);
  $r2 = $rs2->fetch();
  $r['course_hour'] = empty($r2['hour']) ? 0 : $r2['hour'];
  $r['course_salary'] = empty($r2['salary']) ? 0 : $r2['salary'];
  // adjust
  $sql = "SELECT SUM(reward) FROM classroom WHERE status = 10 AND DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_id = {$r['id']} AND consultant_confirmed = 10 AND open_time < '$current_time' AND consultant_id > 0";
  $rs2->query($sql);
  $r['adjust'] = $rs2->record();
  if (empty($r['adjust']))
    $r['adjust'] = 0;

  return $r;
}

function fn_callback($r)
{
  global $rs2, $current_time;

  // demo = 10
  $sql = "SELECT SUM(`hour`) AS `hour`, SUM(salary) AS salary FROM classroom WHERE DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_confirmed = 10 AND `type` = 10 AND open_time < '$current_time' AND consultant_id > 0";
  $rs2->query($sql);
  $r2 = $rs2->fetch();
  $r['demo_hour'] = empty($r2['hour']) ? 0 : $r2['hour'];
  $r['demo_salary'] = empty($r2['salary']) ? 0 : $r2['salary'];
  // normal (99 adjust)
  $sql = "SELECT SUM(`hour`) AS `hour`, SUM(salary) AS salary FROM classroom WHERE DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_confirmed = 10 AND `type` NOT IN (10, 99) AND open_time < '$current_time' AND consultant_id > 0";
  $rs2->query($sql);
  $r2 = $rs2->fetch();
  $r['course_hour'] = empty($r2['hour']) ? 0 : $r2['hour'];
  $r['course_salary'] = empty($r2['salary']) ? 0 : $r2['salary'];
  // adjust
  $sql = "SELECT SUM(reward) FROM classroom WHERE DATE_FORMAT(`date`, '%Y-%m') = '{$r['ym']}' AND consultant_confirmed = 10 AND open_time < '$current_time' AND consultant_id > 0";
  $rs2->query($sql);
  $r['adjust'] = $rs2->record();
  if (empty($r['adjust']))
    $r['adjust'] = 0;

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