<?php
$eid = $VARS['enterprise_id'] = GetParam('eid');
$VARS['enterprise_name'] = $rs->get_value('enterprise', 'ent_name', $eid);

$VARS['member_id'] = $id = GetParam('mid');
$TABLE = 'member_bl_member';
$FUNC_NAME = '學員黑名單';
$SEARCH = false;
$SORT_BY = '`first_name`, `last_name`';
$SPECIAL = 'member_id = ' . $id;
$VARS['popup_add'] = true;
$DATA_SQL = <<<EOT
SELECT m.*, b.memo AS bmemo, b.id AS bid FROM member_bl_member b, member m
WHERE b.black_id = m.id AND b.member_id = $id AND b.deleted = 0
EOT;

$rs->select('member', $id);
AssignValue($rs);

if (MODE == 'new')
{
  $sql = "SELECT black_id FROM member_bl_member WHERE member_id = $id AND deleted = 0";
  $rs->query($sql);
  if ($rs->count > 0)
  {
    $black = $rs->record_array();
    $black = implode(',', $black);
  }
  else
    $black = '0';
  $black .= ',' . $id;
  $sql = "SELECT * FROM member WHERE id NOT IN ($black) AND status = 10 AND deleted = 0 ORDER BY first_name, last_name"; // activate
  $rs->query($sql);
  AssignValues($rs);
}

$CAN_DELETE = false;
////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////
$CAN_DELETE = true;

function fn_callback($r)
{
  $r['id'] = $r['bid'];
  $r['memo'] = $r['bmemo'];
	return $r;
}

function fn_before_new()
{
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