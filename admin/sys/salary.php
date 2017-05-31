<?php
require('cron.inc.php');

//$t = date('YmdH', time() - 3600 - 300);
//$where = " AND `datetime` <= $t";
// get salary = 0
$sql = "SELECT * FROM classroom WHERE status = 10 AND `type` IN (10, 20, 30, 40, 50) AND consultant_confirmed = 10 AND wage = 0 $where";
//echo $sql . "<br>";
$rs->query($sql);
while (($r = $rs->fetch()))
{
  // get consultant
  $rs2->select('consultant', $r['consultant_id']);
  $c = $rs2->fetch();
  switch ($r['type'])
  {
    case 10: // demo
      if ($c['demo_payment'] == 20)
        $salary = $c['demo_pay'];
      else
      { // 以堂計，確認同時段有沒有計算過
        $sql = "SELECT * FROM classroom WHERE status = 10 AND consultant_confirmed = 10 AND wage <> 0 AND consultant_id = {$c['id']} AND id <> {$r['id']} AND `datetime` = '{$r['datetime']}'";
        $rs2->query($sql);
        $salary = $rs2->count > 0 ? 0 : $c['demo_pay'];
      }
      break;
    case 20: // free
    case 30:
    case 40:
    case 50:
      $salary = $c['course_pay'];
      break;
  }
  unset ($v);
  $v['wage'] = $salary;
  $v['salary'] = round($r['hour'] * ($v['wage'] * 1.0));
  $rs2->update('classroom', $r['id'], $v);
}
?>