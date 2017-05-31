<?php
$TABLE = 'user';
$FUNC_NAME = '使用者';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('account', 'user_name', 'first_name', 'last_name', 'tel', 'in_tel', 'email');
$SORT_BY = 'user_name';
$TRANSLATE = array (
  'status' => array($var_member_status, $var_member_status_color),
  'is_manager' => array($var_general_yesno, $var_general_yesno_color),
);
$SPECIAL = "id > 0";
$CONSTRAINT = [
  'account' => ['帳號', CONSTRAINT_UNIQUE],
];

$CHECKBOX = array (
  array ('field' => 'group', 'table' => 'user_group', 'key' => 'user_id', 'foreign_key' => 'group_id'),
);
$RADIO = array ('status');

$VARS['status_select'] = GenRadio('status', $var_member_status, true);
$VARS['is_manager_select'] = GenRadio('is_manager', $var_general_yesno, true);
$VARS['group_select'] = GenCheckboxBySQL('group', 'SELECT id, group_name AS data FROM `group` WHERE status = 10 ORDER BY group_name');

if (MODE == 'add' || (MODE == 'modify' && !empty(GetParam('password'))))
{
  $_POST['passwd'] = GetParam('password');
  $_POST['password'] = GenPassword(GetParam('account'), GetParam('password'));
}
else
  unset ($_POST['password']);
////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  global $rs3;

  $sql = "SELECT group_name FROM user_group u, `group` g WHERE u.user_id = {$r['id']} AND u.group_id = g.id";
  $rs3->query($sql);
  while (($rr = $rs3->fetch()))
    $g[] = "<span class='label label-blue'>{$rr['group_name']}</span>&nbsp;";
  $r['group_name'] = is_array($g) ? implode($g) : '';

  return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = 10; // member active
  $VARS['is_manager'] = 0; // 不是
  $VARS['password'] = makePassword(8);
}

function fn_add($id)
{
  global $EMAIL_SUBJECT_NEW_USER;

  // 發新帳號通知
  $m = new MailModule();
  $m->template = 'email_new_user';
  $m->vars = $_POST;
  $m->addAddress($_POST['email'], $_POST['user_name']);
  $m->subject = $EMAIL_SUBJECT_NEW_USER;
  $m->send();
  unset($m);
}

function fn_edit($id)
{
}

function fn_modify($id)
{
}
?>