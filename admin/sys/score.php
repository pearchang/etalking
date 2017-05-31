<?php
require('cron.inc.php');

// score
$sql = <<<EOT
SELECT r.consultant_id AS id, SUM(i.text) / COUNT(i.text) AS score FROM ques_consultant c, ques_item i, classroom r
WHERE c.ques_id = 19 AND c.content = i.id AND c.classroom_id = r.id
GROUP BY r.consultant_id
EOT;
$rs->query($sql);
unset ($v);
while (($r = $rs->fetch()))
{
  $v['score'] = $r['score'];
  $rs2->update('consultant', $r['id'], $v);
}

// 上課總數
$now = date('YmdH');
$sql = "SELECT consultant_id AS id, COUNT(*) AS total FROM classroom WHERE status = 10 AND consultant_confirmed = 10 AND `datetime` < '$now' AND `hour` > 0 GROUP BY consultant_id";
$rs->query($sql);
unset ($v);
while (($r = $rs->fetch()))
{
  $v['total'] = $r['total'];
  $rs2->update('consultant', $r['id'], $v);
}
?>