<?php
require('cron.inc.php');

// get fixed schedule
// now + 5min
// D+1~27
$consultant_id = GetParam('id', '');
if (!empty($consultant_id))
  $where = "WHERE id = $consultant_id";
else
  $where = '';
$sql = "SELECT * FROM consultant $where";
$rs->query($sql);
while (($r = $rs->fetch()))
{
  update_consultant_schedule($r['id']);
}
?>