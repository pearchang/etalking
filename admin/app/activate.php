<?php
if (MODE == 'update')
{
	$id = GetParam('id');
	$v['open_time'] = 'NOW()';
	$rs->update('member_contract', $id, $v);
	Message('已開通合約', false, MSG_OK);
	// 加點數
	$rs->select('member_contract', $id);
	$r = $rs->fetch();
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
$SPECIAL = "`status` = 20 AND open_time = '0000-00-00 00:00:00' AND paid_time <> '0000-00-00 00:00:00' AND sign_time <> '0000-00-00 00:00:00'"; // 審核中

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