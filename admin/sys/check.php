<?php
require('cron.inc.php');

// 檢查23小時內不正常的classroom
$begin = time();
$end = time() + 23 * 60 * 60;
$begin_date = date('Y-m-d H:00:00', $begin);
$end_date = date('Y-m-d H:00:00', $end);

$sql = "SELECT * FROM classroom WHERE status = 10 AND open_time BETWEEN '$begin_date' AND '$end_date' ORDER BY open_time";
echo $sql;
$rs->query($sql);
unset ($error);
while (($c = $rs->fetch()))
{
  unset ($v);
  // check webex
  if ($c['webex_id'] == 0)
    $v['webex'] = 'X';
  if ($c['type'] != 10 && $c['consultant_id'] == 0)
    $v['consultant'] = 'X';
  if ($c['type'] != 10 && $c['material_id'] == 0)
    $v['material'] = 'X';
  if (empty($c['meeting_key']) || empty($c['meeting_pw']))
    $v['meeting'] = 'X';
  if (is_array($v))
  { // 有錯誤
    $v['name'] = $c['sn'] . ' ' . $c['open_time'];
    $v['type'] = $c['type'];
    $v['id'] = $c['id'];
    $error[] = $v;
  }
}

if ($error)
{
  $s = "";
  foreach ($error as $e)
  {
    $s .= "<tr><td>{$e['id']}</td><td nowrap align='left'>{$e['name']}</td><td>{$e['type']}</td><td>{$e['webex']}</td><td>{$e['meeting']}</td><td>{$e['consultant']}</td><td>{$e['material']}</td></tr>";
  }
  $content = <<<EOT
<style>
td { text-align:center; }
</style>
<table border=1 cellspacing=0 cellpadding=2>
<tr><th>ID</th><th>CLASSROOM</th><th>TYPE</th><th>WEBEX</th><th>MEETING</th><th>CONSULTANT</th><th>MATERIAL</th></tr>
$s
</table>
EOT;
  $header = <<<EOT
MIME-Version: 1.0
Content-type: text/html; charset=utf-8
From: system@etalkingonline.com
EOT;

  mail('tom@begonia.tw', 'ETALKING CLASSROOM WARNING LIST', $content, $header);
}
echo $content;
?>