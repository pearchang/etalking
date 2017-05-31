<?php
$TABLE = 'plan_category';
$FUNC_NAME = '方案分類';
$CAN_DELETE = true;
$SELECT = array ('period');
$SEARCH = true;
$SEARCH_KEYS = array ('cat_name');
$SORT_BY = 'cat_name';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
);

$RADIO = array('status');
$VARS['status_select'] = GenRadio('status', $var_general_status, false, true);

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
	return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = DOC_STATUS_SHOW;
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