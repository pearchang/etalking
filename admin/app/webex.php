<?php
$TABLE = 'webex';
$FUNC_NAME = 'WebEx管理';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('webex_name');
$SORT_BY = 'webex_name';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
  'type' => array($var_webex_type),
);

$RADIO = array('status');
$SELECT = array('type', 'status');
$VARS['status_select'] = GenRadio('status', $var_general_status, false, true);
$VARS['type_select'] = GenSelect('type', $var_webex_type, false, true);

if (MODE == 'modify')
{
  if (null == GetParam('password')) {
    unset($_POST['password']);
  }
  if (null == GetParam('open_pass')) {
    unset($_POST['open_pass']);
  }
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