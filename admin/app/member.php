<?php
$id = $VARS['member_id'] = GetParam('id');
$rs->select('member', $id);
AssignValue($rs);
$eid = $VARS['enterprise_id'] = GetParam('eid');
$VARS['enterprise_name'] = $rs->get_value('enterprise', 'ent_name', $eid);
if ($eid)
  $VARS['func_name'] = $VARS['enterprise_name'];
$VARS['gender_text'] = $var_member_gender[$VARS['gender']];
$VARS['type_text'] = $var_member_type[$VARS['type']];
$VARS['add_button'] = true;
$TABLE = 'member';
$FUNC_NAME = '學員';
$CAN_DELETE = $_SESSION['admin_id'] == -1;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('account', 'first_name', 'last_name', 'member_name', 'tel', 'mobile', 'email', 'source', 'track');
$SORT_BY = 'first_name, last_name';
$TRANSLATE = array (
  'status' => array($var_member_status2, $var_member_status_color2),
  'gender' => array($var_member_gender, $var_member_gender_color),
);
$SELECT = array ('education', 'enterprise_id');
$RADIO = array('status', 'prefer', 'gender', 'type', 'level_id');
$CHECKBOX = array (
  array ('field' => 'interest', 'table' => 'member_interest', 'key' => 'member_id', 'foreign_key' => 'interest_id'),
  array ('field' => 'skill', 'table' => 'member_skill', 'key' => 'member_id', 'foreign_key' => 'skill_id'),
);
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'note', 'height' => 3),
  array ('type' => EDITOR_TEXT, 'name' => 'note2', 'height' => 3),
);
$CONSTRAINT = [
  'account' => ['帳號', CONSTRAINT_UNIQUE],
];

$SPECIAL = "status <= 50 AND status > 0";

if ($_SESSION['admin_is_sales'])
  $SPECIAL .= " AND sales_id = " . $_SESSION['admin_id'];
if ($eid)
  $SPECIAL .= " AND enterprise_id = " . $eid;

$VARS['type_select'] = GenRadio('type', $var_member_type, true);
$VARS['status_select'] = GenRadio('status', $var_member_status2, true);
$VARS['gender_select'] = GenRadio('gender', $var_member_gender, true);
$VARS['prefer_select'] = GenRadio('prefer', $var_consultant_type, true);
$VARS['education_select'] = GenSelect('education', $var_member_education, true);
$VARS['level_select'] = GenRadioBySQL('level_id', "SELECT id, level_name AS `data` FROM `level` WHERE status = 10 ORDER BY `begin`", true);
$VARS['interest_select'] = GenCheckboxBySQL('interest', "SELECT id, title AS `data` FROM `interest` WHERE status = 10 ORDER BY eng_title", true);
$VARS['skill_select'] = GenCheckboxBySQL('skill', "SELECT id, title AS `data` FROM `skill` WHERE status = 10 ORDER BY title", true);
$VARS['enterprise_select'] = GenSelectBySQL('enterprise_id', "SELECT id, ent_name AS `data` FROM `enterprise` WHERE status = 10 ORDER BY ent_name", true);

if (MODE == 'add' || ((MODE == 'modify' || MODE == 'modify2') && !empty(GetParam('password'))))
{
  $_POST['passwd']  = GetParam('password');
  $_POST['password'] = GenPassword(GetParam('account'), GetParam('password'));
}
else
  unset ($_POST['password']);
////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  global $var_member_type;
  // tag
  if ($r['enterprise_id'])
    $tag[] = '企';
  if (is_array($tag))
  {
    $tt = '';
    foreach ($tag as $t)
      $tt .= "<span class='label label-blue'>{$t}</span>&nbsp;";
    $r['tag'] = $tt;
  }
  return $r;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = 30; // 未啟用
  $VARS['password'] = makePassword(6);
  $VARS['birthdate'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 8));
}

function fn_add($id)
{
  global $rs2, $EMAIL_SUBJECT_NEW_MEMBER;

  $v['sales_id'] = $_SESSION['admin_id'];
  $rs2->update('member', $id, $v);
  // 發信
  // 發新會員通知
  $m = new MailModule();
  $m->template = 'email_new_member';
  $m->vars = $_POST;
  $m->addAddress($_POST['email'], $_POST['member_name']);
  $m->subject = $EMAIL_SUBJECT_NEW_MEMBER;
  $m->send();
  unset($m);

}

function fn_edit($id)
{
  global $rs2, $VARS;

  // check 合約是否有開通了
  if ($_SESSION['admin_is_sales'])
  {
    $sql = "SELECT * FROM member_contract WHERE member_id = $id AND open_time <> '0000-00-00 00:00:00'";
    $rs2->query($sql);
    if ($rs2->count > 0)
      $VARS['sales_can_not_edit'] = true;
  }

}

function fn_modify($id)
{
}
?>