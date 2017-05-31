<?php
$id = $VARS['consultant_id'] = GetParam('cid');

switch (MODE)
{
  case 'update':
    $sql = "UPDATE consultant_fixed_schedule SET available = 0 WHERE consultant_id = $id";
    $rs3->execute($sql);
    $arr = GetParam('available');
    if (!is_array($arr))
    {
      $sql = "UPDATE consultant_fixed_schedule SET available = 0 WHERE consultant_id = $id";
      $rs3->execute($sql);
    }
    else
    {
      foreach ($arr as $v)
      {
        $a = explode(',', $v);
        $sql = "UPDATE consultant_fixed_schedule SET available = 10 WHERE consultant_id = $id AND weekday = {$a[0]} AND `time` = {$a[1]}";
        $rs3->execute($sql);
      }
    }
    update_consultant_schedule($id);
    Message('固定課表更新完成', false, MSG_INFO);
    GoLast();
    break;
  default:
    $rs->select('consultant', $id);
    AssignValue($rs);
    $sql = "SELECT * FROM consultant_fixed_schedule WHERE consultant_id = $id";
    $rs3->query($sql);
    while (($r = $rs3->fetch()))
      $schedule[$r['weekday']][$r['time']] = $r['available'];
    for ($i = BEGIN_TIME; $i <= END_TIME; $i++)
      $hour[] = array ('hour' => $i);
    $VARS['hour_list'] = $hour;

    for ($i = 0; $i <= 6; $i++) // week
    {
      $w['name'] = $var_weekday[$i];
      unset ($hour);
      for ($k = BEGIN_TIME; $k <= END_TIME; $k++)
      {
        $h['week'] = $i;
        $h['hour'] = $k;
        $h['checked'] = $schedule[$i][$k] > 0 ? 'checked' : '';
        $hour[] = $h;
      }
      $w['hour_list'] = $hour;
      $week[] = $w;
    }
    $VARS['week_list'] = $week;
    break;
}


function fn_callback($r)
{
  return $r;
}


function fn_new()
{
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