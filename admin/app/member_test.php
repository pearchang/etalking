<?php
$eid = $VARS['enterprise_id'] = GetParam('eid');
$VARS['enterprise_name'] = $rs->get_value('enterprise', 'ent_name', $eid);

$id = $VARS['member_id'] = $_POST['member_id'] = GetParam('mid');
$rs->select('member', $id);
AssignValue($rs);
$VARS['gender_text'] = $var_member_gender[$VARS['gender']];
$VARS['add_button'] = true;

$VARS['func_name'] = '電腦測試紀錄';
$TABLE = 'member_history';
$FUNC_NAME = '電腦測試紀錄';
$SORT_BY = 'id DESC';
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'content', 'height' => 5),
);
$TRANSLATE = array (
  'subtype' => array($var_member_history_subtype),
);
$VARS['subtype_select'] = GenSelect('subtype', $var_member_history_subtype, true, true);
$SPECIAL = "member_id = $id AND `type` = 80";

////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  $r['content'] = nl2br($r['content']);
  return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['type'] = 80; // 電腦測試
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