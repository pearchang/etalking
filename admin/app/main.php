<?php

// 檢查23小時內不正常的classroom
$begin = time();
$end = time() + 23 * 60 * 60;
$begin_date = date('Y-m-d H:00:00', $begin);
$end_date = date('Y-m-d H:00:00', $end);

$sql = "SELECT * FROM classroom WHERE status = 10 AND open_time BETWEEN '$begin_date' AND '$end_date' ORDER BY open_time";
//echo $sql;
$rs->query($sql);
unset ($error);
while (($c = $rs->fetch()))
{
  unset ($v, $vv);
  // check webex
  if ($c['webex_id'] == 0)
    $v['webex'] = true;
  if ($c['consultant_id'] == 0)
    $v['consultant'] = true;
  if ($c['type'] != 10 && $c['material_id'] == 0)
    $v['material'] = true;
  if (empty($c['meeting_key']) || empty($c['meeting_pw']))
    $v['meeting'] = true;
  $vv = false;
  if(count($v))
    foreach ($v as $k => $z)
      $vv |= $z;
  $v['error'] = $vv;
  $v['sn'] = $c['sn'];
  $v['open_time'] = $c['open_time'];
  $v['type'] = $var_registration_type[$c['type']];
  $v['id'] = $c['id'];
  $error[] = $v;
}

if ($error)
  $VARS['classroom_list'] = $error;
?>