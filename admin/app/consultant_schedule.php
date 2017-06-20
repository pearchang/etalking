<?php
$id = $VARS['consultant_id'] = GetParam('cid');

switch (MODE)
{
  default:
    $rs->select('consultant', $id);
    AssignValue($rs);

    for ($i = BEGIN_TIME; $i <= END_TIME; $i++)
      $hour[] = array ('hour' => $i);
    $VARS['hour_list'] = $hour;
    $now = time();
    for ($i = 0; $i < DATE_RANGE; $i++) // date
    {
      $t = $now + $i * 86400;
      $d = date('Y-m-d', $t);
      $w['date'] = date('m-d', $t);
      $w['name'] = $var_weekday_short[date('w', $t)];
      unset ($hour);
      for ($k = BEGIN_TIME; $k <= END_TIME; $k++)
      {
        unset ($h);
        $h['date'] = $d;
        $h['hour'] = $k;
        $sql = "SELECT * FROM `classroom` WHERE `date` = '$d' AND `time` = $k AND `status` = 10 AND `type` <> 99 AND consultant_id = $id";
        $rs->query($sql);
        if ($rs->count == 0)
        { // 判斷schedule
          $sql = "SELECT * FROM `consultant_schedule` WHERE `date` = '$d' AND `time` = $k AND consultant_id = $id";
          //echo $sql;
          $rs->query($sql);
          $r = $rs->fetch();
          $avail = $r['fixed'] > 0 ? 10 : $r['available'];
          if ($r['available'] > 0 || $r['fixed'] > 0)
            $h['data'] = "<span class='available' id='available_$d_$k' data-date='$d' data-time='$k' data-avail='{$r['available']}' data-fixed='{$r['fixed']}'>" . $var_schedule_available_type_short[$avail] . "</span>";
          else
            $h['data'] = "<input type=checkbox name='available[]' id='available_$d_$k' data-date='$d' data-time='$k'>";
        }
        else
        {
          $r = $rs->fetch();
          $h['data'] = $var_registration_type_short[$r['type']];
        }
        $hour[] = $h;
      }
      $w['hour_list'] = $hour;
      $week[] = $w;
    }
    $VARS['date_list'] = $week;
    break;
  case 'add':
    $date = GetParam('date');
    $time = GetParam('time');
    $id = GetParam('id');
    $sql = "UPDATE `consultant_schedule` SET available = 20 WHERE `date` = '$date' AND `time` = $time AND consultant_id = $id";
    $rs->execute($sql);
    json_output(array('status' => true));
    break;
  case 'cancel_fixed':
    $date = GetParam('date');
    $time = GetParam('time');
    $id = GetParam('id');
    $sql = "UPDATE `consultant_schedule` SET fixed = 0 WHERE `date` = '$date' AND `time` = $time AND consultant_id = $id";
    $rs->execute($sql);
    json_output(array('status' => true));
    break;
  case 'cancel':
    $date = GetParam('date');
    $time = GetParam('time');
    $id = GetParam('id');
    $sql = "UPDATE `consultant_schedule` SET available = 0 WHERE `date` = '$date' AND `time` = $time AND consultant_id = $id";
    $rs->execute($sql);
    json_output(array('status' => true));
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