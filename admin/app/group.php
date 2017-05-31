<?php
$TABLE = 'group';
$FUNC_NAME = '組別';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('group_name');
$SORT_BY = 'group_name';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
  'is_sales' => array($var_general_yesno, $var_general_yesno_color),
);
$RADIO = array ('status', 'is_sales');
$CHECKBOX = array (
  array ('field' => 'permission', 'table' => 'group_permission', 'key' => 'group_id', 'foreign_key' => 'menu_id'),
);
$CONSTRAINT = [
  'group_name' => ['組別名稱', CONSTRAINT_UNIQUE],
];


$VARS['status_select'] = GenRadio('status', $var_general_status, true);
//$VARS['is_sales_select'] = GenSelect('is_sales', $var_general_yesno, false, true);
$VARS['is_sales_select'] = GenRadio('is_sales', $var_general_yesno, true);
//$VARS['is_sales_select'] = GenCheckbox('is_sales', $var_general_yesno);
//$VARS['permission_select'] = GenCheckboxBySQL('permission', "SELECT id, `name` AS data FROM menu WHERE hide = 0 ORDER BY rank");
if (MODE == 'new' || MODE == 'edit')
{
  $sql = "SELECT * FROM `menu` WHERE `parent` = 0 AND hide = 0 ORDER BY rank";
  $rs->query($sql);
  unset($menu);
  while (($r = $rs->fetch()))
  {
    $sql = "SELECT id, `name` AS data FROM `menu` WHERE parent = {$r['id']} AND hide = 0 ORDER BY rank";
    $r['submenu'] = GenCheckboxBySQL("permission", $sql);
    $menu[] = $r;
  }
  $VARS['list'] = $menu;
}
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