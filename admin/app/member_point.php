<?php
$eid = $VARS['enterprise_id'] = GetParam('eid');
$VARS['enterprise_name'] = $rs->get_value('enterprise', 'ent_name', $eid);

$id = $VARS['member_id'] = $_POST['member_id'] = GetParam('mid');
$rs->select('member', $id);
AssignValue($rs);
$VARS['gender_text'] = $var_member_gender[$VARS['gender']];
$VARS['add_button'] = false;

if (MODE == 'point')
{
  $p = GetParam('point');
  $c = GetParam('content');
  unset ($v);
  $v['member_id'] = $id;
  $v['type'] = 140; // 贈點
  $v['target_id'] = 0;
  $v['brief'] = $c;
  $v['io'] = $p;
  $sql = "UPDATE member SET point = point + {$p} WHERE id = $id";
  $rs3->execute($sql);
  $v['balance'] = $rs3->get_value('member', 'point', $id);
  $rs2->insert('member_point', $v);
  json_output(array('status' => true));
}

$VARS['func_name'] = '點數明細';
$TABLE = 'member_point';
$FUNC_NAME = '點數明細';
$SORT_BY = '`id` DESC';
$TRANSLATE = array (
  'type' => array($var_point_type),
);
$SPECIAL = "member_id = $id";
if (!$_SESSION['admin_is_sales'] || $_SESSION['admin_manager'])
{
  $VARS['custom'] = <<<EOT
<a class="btn-flat success" href="javascript: $.fancybox({type : 'iframe', href : '/admin/member_point/popup?mid=$id', title : '異動點數'});"><i class="icon-plus"></i> 異動點數</a>
EOT;
}

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  global $rs3, $var_registration_type;

//  10 => '預約課程', // course_registration
//  20 => '新增合約', // member_contract
//  100 => '取消課程', // course_registration
//  110 => '補償',
//  120 => '取消合約', // member_contract
//  130 => '退還點數',  // course_registration

  $r['brief'] = nl2br($r['brief']);
  switch ($r['type'])
  {
    case 10:
    case 100:
    case 130:
      $sql = "SELECT c.* FROM course_registration r, classroom c WHERE r.id = {$r['target_id']} AND r.classroom_id = c.id";
      $rs3->query($sql);
      $r2 = $rs3->fetch();
      $s[] = '<b>課程類型</b>: ' . $var_registration_type[$r2['type']];
      $s[] = "<b>課程時間</b>: {$r2['date']} {$r2['time']}:00 ~ {$r2['time']}:45";
      if ($r['type'] == 130)
      {
        $ss = explode("\n", $r['brief']);
        $ss = $ss[count($ss) - 1];
        $ss = str_replace('退點原因: ', '', $ss);
        $s[] = "<b>退點原因</b>: $ss";
      }
      elseif ($r['type'] == 100)
      {
        $ss = explode("\n", $r['brief']);
        $ss = $ss[count($ss) - 1];
        $ss = str_replace('取消原因: ', '', $ss);
        $s[] = "<b>取消原因</b>: $ss";
      }
      break;
    case 20:
    case 120:
      $sql = "SELECT * FROM member_contract WHERE id = {$r['target_id']}";
      $rs3->query($sql);
      $r2 = $rs3->fetch();
      $s[] = "<b>合約名稱</b>: {$r2['contract_name']}";
      $s[] = "<b>合約期限</b>: {$r2['begin']} ~ {$r2['end']}";
      break;
    case 110:
      break;
  }

  if (is_array($s))
    $r['brief'] = implode('<br>', $s);
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

// TODO: EDIT

function fn_modify($id)
{
}
?>