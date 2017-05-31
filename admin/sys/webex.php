<?php
require('cron.inc.php');

$sql = "SELECT q.* FROM webex_queue q, classroom c WHERE q.classroom_id = c.id AND c.status = 10 AND c.webex_id > 0 AND c.type <> 10 AND meeting_key = '' AND end_time >= NOW() ORDER BY c.open_time";
//echo $sql;
$rs->query($sql);
while (($r = $rs->fetch()))
{
  $rr[] = $r;
  $sql = "UPDATE webex_queue SET processing = 1 WHERE id = " . $r['id'];
  $rs2->execute($sql);
}

foreach ($rr as $r)
{
  if ($r['type'] == 'SM')
    $rs = create_meeting_room($r['classroom_id']);
  else
    $rs = delete_meeting_room($r['classroom_id']);
//  echo $webex_xml;
  if (!$rs)
  {
    $sql = "UPDATE webex_queue SET processing = 0 WHERE id = " . $r['id'];
    $rs2->execute($sql);
  }
}
?>