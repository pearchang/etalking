<?php
$TABLE = 'contract';
$FUNC_NAME = '顧問合約';
//$SELECT = array ('status');
$CAN_DELETE = true;
$SEARCH = true;
$SEARCH_KEYS = array ('version');
$SEARCH_DATE = array ('begin', 'end');
$SORT_BY = '`id` DESC';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
);
$EDITORS = array (
  array ('type' => EDITOR_HTML, 'name' => 'content', 'height' => 600, 'required' => true),
);
$SPECIAL = '`type` = 20';
$RADIO = array ('status');

$VARS['status_select'] = GenRadio('status', $var_general_status, false, true);
$_POST['type'] = 20; // consultant

// TODO: 判斷只有一筆顯示
////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
	return $r;
}

function fn_before_new()
{
  global $VARS, $rs3;

  $id = GetParam('id');
  if (empty($id))
    $VARS['status'] = DOC_STATUS_SHOW;
  else
  {
    $rs3->select('contract', $id);
    AssignValue($rs3);
//    print_r($VARS);
  }
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