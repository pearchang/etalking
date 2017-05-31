<?php
require('cron.inc.php');

// get material_id = 0 in 24hr
$t = date('YmdH', time() + 3600 * 24);
$sql = "SELECT * FROM classroom WHERE `datetime` <= $t AND status = 10 AND `type` IN (20, 30) AND material_id = 0 ORDER BY id DESC";
//$sql = "SELECT * FROM classroom WHERE status = 10 AND consultant_id = 0";
$rs->query($sql);
while (($r = $rs->fetch()))
{
  $lv = $r['level_id'];
  // interest
  $sql = "SELECT i.interest_id, COUNT(i.interest_id) AS qty FROM `course_registration` r, member_interest i WHERE r.classroom_id = {$r['id']} AND r.member_id = i.member_id AND r.status = 10 GROUP BY i.interest_id ORDER BY qty DESC";
  $rs2->query($sql);
//  echo $sql . $rs2->count . "\n";
  if ($rs2->count == 0 || $lv == 0)
  { // mail PM
  	continue;
  }
  // Get members
  $sql = "SELECT GROUP_CONCAT(member_id) FROM course_registration WHERE classroom_id = {$r['id']} AND status = 10";
  $rs3->query($sql);
  $members = $rs3->record();
//  echo $members;
  $mid = 0;
  while (($r2 = $rs2->fetch()))
  {
    // 先取得目前學員該interest已上過的教材
//    $sql = "SELECT c.material_id FROM classroom c, course_registration r WHERE r.member_id IN ($members) AND r.status = 10 AND r.attend = 10 AND r.classroom_id = c.id AND c.material_id <> 0 GROUP BY c.material_id";
    $sql = <<<EOT
SELECT c.material_id FROM classroom c, course_registration r 
WHERE r.member_id IN ($members) AND r.status = 10 AND r.classroom_id = c.id AND c.material_id <> 0
  AND (c.open_time > NOW() OR (c.open_time <= NOW() AND r.attend = 10))
GROUP BY c.material_id
EOT;
    //echo $sql . "\n";
    $rs3->query($sql);
    if ($rs3->count == 0)
      $materials = '0';
    else
      $materials = implode(',', $rs3->record_array());
    if (empty($materials))
      $materials = '0';
    $int = $r2['interest_id'];
    // material
    $sql = <<<EOT
SELECT m.* FROM material m, material_level l, material_interest i
WHERE m.status = 10 AND m.type = 10 AND m.id = l.material_id AND l.level_id = $lv AND m.id = i.material_id AND i.interest_id = $int
AND m.id NOT IN ($materials)
EOT;
    //echo $sql . "\n";
    $rs3->query($sql);
    if ($rs3->count > 0)
      break;
  }
  if ($rs3->count == 0)
  { // TODO: 記錄起來發給PM

  }
  else
  {
    $a = $rs3->fetch_array();
    $k = myRand(0, count($a) - 1);
    $m = $a[$k];
    unset ($v);
    $v['material_id'] = $m['id'];
    $rs2->update('classroom', $r['id'], $v);
  }
}
?>