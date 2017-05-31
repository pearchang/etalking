<?php
require('cron.inc.php');

// get consultant_id = 0 in 24hr
$t = date('YmdH', $register_time);
//$sql = "SELECT * FROM classroom WHERE `datetime` <= $t AND status = 10 AND consultant_id = 0";
$sql = "SELECT * FROM classroom WHERE status = 10 AND consultant_id = 0";
$rs->query($sql);
while (($r = $rs->fetch()))
{
  // interest
  $sql = "SELECT i.interest_id, COUNT(i.interest_id) AS qty FROM `course_registration` r, member_interest i WHERE r.classroom_id = {$r['id']} AND r.member_id = i.member_id GROUP BY i.interest_id ORDER BY qty DESC";
  $rs2->query($sql);
  $r2 = $rs2->fetch();
  $int = $r2['interest_id'];
  $lv = $r['level_id'];
  // consultant
  $sql = <<<EOT
SELECT c.* FROM consultant c, consultant_level l, consultant_interest i, consultant_schedule s
WHERE c.status = 10 AND c.id = l.consultant_id AND l.level_id = $lv AND c.id = i.consultant_id AND i.interest_id = $int
  AND c.id = s.consultant_id AND s.date = '{$r['date']}' AND s.time = {$r['time']} AND s.available > 0
EOT;
  //echo $sql;
  $rs2->query($sql);
  $cid = 0;
  while (($r2 = $rs2->fetch()))
  {
    // 確認該時段沒有課
    $sql = "SELECT id FROM classroom WHERE consultant_id = {$r2['id']} AND `date` = '{$r['date']}' AND `time` = {$r['time']} AND status = 10";
    $rs3->query($sql);
    if ($rs3->count == 0)
    {
      $cid = $r2['id'];
      break;
    }
  }
  if ($cid)
  { // 有找到顧問了
    unset ($v);
    $v['consultant_id'] = $cid;
    $rs->update('classroom', $r['id'], $v);
  }
  else
  { // TODO: 記錄起來發給PM

  }
  // material
  $sql = <<<EOT
SELECT m.* FROM material m, material_level l, material_interest i
WHERE m.status = 10 AND m.id = l.material_id AND l.level_id = $lv AND m.id = i.material_id AND i.interest_id = $int
EOT;
  //echo $sql;
  $rs2->query($sql);
  $mid = 0;
  if ($rs2->count == 0)
  { // TODO: 記錄起來發給PM

  }
  else
  {
    $a = $rs2->fetch_array();
    $k = myRand(0, count($a) - 1);
    $m = $a[$k];
    unset ($v);
    $v['material_id'] = $m['id'];
    $rs->update('classroom', $r['id'], $v);
  }
  // webex
  $sql = "SELECT * FROM webex WHERE status = 10";
  $rs2->query($sql);
  $wid = 0;
  while (($r2 = $rs2->fetch()))
  {
    // 確認該時段沒有課
    $sql = "SELECT webex_id FROM classroom WHERE webex_id = {$r2['id']} AND `date` = '{$r['date']}' AND `time` = {$r['time']} AND status = 10";
    $rs3->query($sql);
    if ($rs3->count == 0)
    {
      $wid = $r2['id'];
      break;
    }
  }
  if ($wid)
  { // 有找到顧問了
    unset ($v);
    $v['webex_id'] = $wid;
    $rs->update('classroom', $r['id'], $v);
  }
  else
  { // TODO: 記錄起來發給PM

  }
}
?>