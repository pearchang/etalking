<?php
require_once (DOC_ROOT . 'lib/etalk.php');

$f = GetParam('f');
switch ($f)
{
  case 'register_free_booking':
    $member_id = GetParam('member_id');
    $date = GetParam('date');
    $time = GetParam('time');
    $type = GetParam('type');
    $r = register_free_booking($member_id, $date, $time, $type);
    if ($r > 0)
      $r = true;
    json_output(array ('result' => $r));
    break;
  case 'cancel_free_booking':
    $member_id = GetParam('member_id');
    $date = GetParam('date');
    $time = GetParam('time');
    json_output(array ('result' => cancel_free_booking($member_id, $date, $time)));
    break;
  case 'register_elective':
    $member_id = GetParam('member_id');
    $course_id = GetParam('course_id');
    $r = register_course($member_id, $course_id);
    if ($r > 0)
      $r = true;
    json_output(array ('result' => $r));
    break;
  case 'cancel_elective':
    $member_id = GetParam('member_id');
    $course_id = GetParam('course_id');
    json_output(array ('result' => cancel_course($member_id, $course_id)));
    break;
  case 'set_available':
    $consultant_id = GetParam('consultant_id');
    $date = GetParam('date');
    $time = GetParam('time');
    json_output(array ('result' => set_available($consultant_id, $date, $time)));
    break;
  case 'cancel_available':
    $consultant_id = GetParam('consultant_id');
    $date = GetParam('date');
    $time = GetParam('time');
    json_output(array ('result' => cancel_available($consultant_id, $date, $time)));
    break;
  case 'activate_contract':
    $id = GetParam('contract_id');
    // SUM bill
    $sql = "SELECT SUM(total) FROM member_contract_bill WHERE contract_id = $id AND paid > 0";
    $rs->query($sql);
    $total = $rs->record();
    $rs->select('member_contract', $id);
    $r = $rs->fetch();
    unset ($v);
    $can_open = false;
    if ($total == $r['price'] && $v['paid_time'] != '0000-00-00 00:00:00')
    {
      $v['paid_time'] = 'NOW()';
      if ($r['sign_time'] != '0000-00-00 00:00:00')
      {
        $v['open_time'] = 'NOW()';
        $can_open = true;
      }
      $rs->update('member_contract', $id, $v);
    }
    if ($r['open_time'] != '0000-00-00 00:00:00')
      $can_open = false;
    if ($can_open)
    {
      // 加點數
      unset ($v);
      $v['member_id'] = $r['member_id'];
      $v['type'] = 20; // 買合約
      $v['target_id'] = $id;
      $v['brief'] = '購買方案 ' . $r['plan_name'];
      $v['io'] = $r['point'] + $r['gift'];
      $sql = "UPDATE member SET point = point + {$v['io']} WHERE id = ". $r['member_id'];
      $rs3->execute($sql);
      $v['balance'] = $rs3->get_value('member', 'point', $r['member_id']);
      $rs2->insert('member_point', $v);
    }
    json_output(array('result' => $can_open));
    break;
}
exit;
?>