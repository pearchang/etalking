<?php
if (MODE == 'update')
{
	$id = GetParam('id');
	$v['status'] = GetParam('status');
	$v['note'] = GetParam('note');
	$rs->update('member_contract', $id, $v);
	if ($v['status'] == 20)
		Message('合約審核完成', false, MSG_OK);
	else
		Message('審核不通過已退回', false, MSG_WARN);
	json_output(array('status' => true));
}
$TABLE = 'member_contract';
$FUNC_NAME = '合約';
//$SELECT = array ('status');
$SEARCH = false;
//$SEARCH_KEYS = array ('version');
$SORT_BY = '`begin`, `end`';
//$TRANSLATE = array (
//  'status' => array($var_general_status, $var_general_status_color),
//);
//$EDITORS = array (
//  array ('type' => EDITOR_HTML, 'name' => 'content', 'height' => 600),
//);
$SPECIAL = '`status` = 10'; // 審核中

//$VARS['status_select'] = GenSelect('status', $var_general_status, false, true);
//$_POST['type'] = 10; // member

$VARS['add_button'] = false;
////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
	global $rs3;

	// member
	$rs3->select('member', $r['member_id']);
	$rr = $rs3->fetch();
	$r['member_name'] = $rr['member_name'];
	$r['mobile'] = $rr['mobile'];
	$r['email'] = $rr['email'];
	// plan
	$rs3->select('plan', $r['plan_id']);
	$r2 = $rs3->fetch();
	$r['oprice'] = $r2['price'];
	$r['opoint'] = $r2['point'];
	$r['ogift'] = $r2['gift'];
	return $r;
}

function fn_new()
{
//  global $VARS;

//  $VARS['status'] = DOC_STATUS_SHOW;
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