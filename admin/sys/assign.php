<?php
require('cron.inc.php');

$t = date('YmdH', time() + 3600 * 48);
if (!empty($_GET['classroom_id']))
  $where = " AND id = " . GetParam('classroom_id');
else
  $where = " AND `datetime` <= $t";
// get consultant_id = 0 in 48hr
$sql = "SELECT * FROM classroom WHERE status = 10 AND `type` IN (10, 20, 30, 40, 50) AND (consultant_id = 0 OR webex_id = 0) $where";
//$sql = "SELECT * FROM classroom WHERE status = 10 AND consultant_id = 0";
//echo $sql . "<br>";
$rs->query($sql);
while (($r = $rs->fetch()))
{
  $lv = $r['level_id'];
  // interest
//  $sql = "SELECT i.interest_id, COUNT(i.interest_id) AS qty FROM `course_registration` r, member_interest i WHERE r.classroom_id = {$r['id']} AND r.member_id = i.member_id GROUP BY i.interest_id ORDER BY qty DESC";
//  $rs2->query($sql);
////  echo $sql . $rs2->count . "\n";
//  if ($rs2->count == 0 || $lv == 0)
//  { // mail PM
//  	continue;
//  }
//  $r2 = $rs2->fetch();
//  $int = $r2['interest_id'];
  // consultant
  if ($r['consultant_id'] == 0 && $r['type'] != 10) // 不能是DEMO
  {
    // 取得顧問黑名單
    $sql = "SELECT b.black_id FROM member_bl_consultant b, course_registration r WHERE r.classroom_id = {$r['id']} AND r.member_id = b.member_id AND b.deleted = 0";
    $rs3->query($sql);
    if ($rs3->count > 0)
      $blacklist = $rs3->record_array();
    else
      $blacklist = [];

    $blacklist = implode(', ', $blacklist);
    // consultants
    $sql = <<<EOT
SELECT c.*, IF(s.fixed > 0, 10, s.available) AS avail FROM consultant c, consultant_level l, consultant_schedule s
WHERE c.status = 10 AND c.id = l.consultant_id AND l.level_id = $lv
AND c.id = s.consultant_id AND s.date = '{$r['date']}' AND s.time = {$r['time']} AND (s.available > 0 OR s.fixed > 0)
AND c.id NOT IN ($blacklist)
ORDER BY avail, c.total DESC, c.score DESC, c.first_name, c.last_name
EOT;
//    echo $sql . "<br>";
    $rs2->query($sql);
    $cid = 0;
    while (($r2 = $rs2->fetch()))
    {
      // 確認該時段沒有課
      $sql = "SELECT id FROM classroom WHERE consultant_id = {$r2['id']} AND `date` = '{$r['date']}' AND `time` = {$r['time']} AND status = 10";
      $rs3->query($sql);
      if ($rs3->count == 0 && !in_array($r2['id'], $blacklist)) // 確認是否在黑名單裡
      {
        $cid = $r2['id'];
        break;
      }
    }
    if ($cid)
    { // 有找到顧問了
      unset ($v);
      $v['consultant_id'] = $cid;
      $rs2->update('classroom', $r['id'], $v);
    }
    else
    { // TODO: 記錄起來發給PM

    }
  }

  if ($r['webex_id'] == 0)
  {
    // webex
    $webex_type = 0;
    $special = "";
    switch ($r['type'])
    {
      case 10:
        $webex_type = 10; // DEMO
        $special = " AND status2 <> 20";
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
    $rs2->lock('classroom WRITE, webex READ');
    $sql = "SELECT * FROM webex WHERE status = 10 AND `type` = $webex_type";
    //echo $sql;
    $rs2->query($sql);
    $wid = 0;
    while (($r2 = $rs2->fetch()))
    {
      // 確認該時段沒有被佔，DEMO h-1 ~ h+2，一般 h+0 ~ h+2
//      $sql = "SELECT webex_id FROM classroom WHERE webex_id = {$r2['id']} AND `date` = '{$r['date']}' AND `time` = {$r['time']} AND status = 10";
      $sql = "SELECT webex_id FROM classroom WHERE webex_id = {$r2['id']} AND NOT (begin_time > '{$r['end_time']}' OR end_time < '{$r['begin_time']}') AND status = 10 $special";
      //echo $sql . '<br>';
//      exit;
      $rs3->query($sql);
      if ($rs3->count == 0) {
        $wid = $r2['id'];
        break;
      }
    }
    if ($wid)
    { // 有找到webex_id
      unset ($v);
      $v['webex_id'] = $wid;
      $rs2->update('classroom', $r['id'], $v);
    }
    else
    { // TODO: 記錄起來發給PM

    }
    $rs2->unlock();
  }
}
?>