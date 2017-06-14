<?php
$TABLE = 'enterprise';
$FUNC_NAME = '企業帳號';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('ent_name');
$SORT_BY = '`ent_name`';
$TRANSLATE = array (
  'status' => array($var_general_status, $var_general_status_color),
);

$RADIO = array('status');
$VARS['status_select'] = GenRadio('status', $var_general_status, false, true);

if (MODE == 'add' || ((MODE == 'modify') && null != GetParam('password'))) {
  $_POST['password'] = GenPassword(GetParam('account'), GetParam('password'));
} else {
  unset ($_POST['password']);
}

////////////////////////////////////
require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r) {
  return $r;
}

function fn_new() {
  global $VARS;

  $VARS['password'] = makePassword(6);
  $VARS['status'] = DOC_STATUS_SHOW;
}

function fn_add($id) {
}

function fn_edit($id) {
}

function fn_modify($id) {
}
?>