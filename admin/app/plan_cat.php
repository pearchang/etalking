<?php
$TABLE = 'plan_category';
$FUNC_NAME = '方案分類';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('cat_name');
$SORT_BY = 'cat_name';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
);
$RADIO = array ('status');
$CONSTRAINT = [
  'group_name' => ['分類名稱', CONSTRAINT_UNIQUE],
];


$VARS['status_select'] = GenRadio('status', $var_general_status, true);
//$VARS['is_sales_select'] = GenSelect('is_sales', $var_general_yesno, false, true);
$VARS['is_sales_select'] = GenRadio('is_sales', $var_general_yesno, true);
//$VARS['is_sales_select'] = GenCheckbox('is_sales', $var_general_yesno);
$VARS['permission_select'] = GenCheckboxBySQL('permission', "SELECT id, `name` AS data FROM menu WHERE hide = 0 ORDER BY rank");
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
  $VARS['is_sales'] = 0; // 不是
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