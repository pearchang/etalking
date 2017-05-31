<?php
if (MODE == 'available')
{
  $id = GetParam('id');
  $week = GetParam('week');
  $hour = GetParam('hour');
  $available = GetParam('available');
  $sql = "UPDATE consultant_fixed_schedule SET fixed = $available  WHERE consultant_id = $id AND weekday = $week AND `time` = $hour";
  $rs->execute($sql);
  update_consultant_schedule($id);
  json_output(array ('status' => true));
}

$VARS['consultant_id'] = GetParam('id');
$TABLE = 'consultant';
$FUNC_NAME = '顧問';
$CAN_DELETE = true;
//$SELECT = array ('status');
$SEARCH = true;
$SEARCH_KEYS = array ('account', 'first_name', 'last_name', 'chi_name', 'tel', 'email');
$SORT_BY = 'first_name, last_name';
$TRANSLATE = array (
  'status' => array($var_member_status, $var_member_status_color),
);
$SELECT = array ('location', 'country', 'lang1', 'lang2', 'education');
$RADIO = array('status', 'demo_payment', 'type');
$CHECKBOX = array (
//  array ('field' => 'interest', 'table' => 'consultant_interest', 'key' => 'consultant_id', 'foreign_key' => 'interest_id'),
  array ('field' => 'level', 'table' => 'consultant_level', 'key' => 'consultant_id', 'foreign_key' => 'level_id'),
);
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'intro', 'height' => 5, required => true),
);
$IMG_PATH = '/imgs/consultant/';
$IMG_TABLE = 'consultant_images';
$IMAGES = array (
  0 => array ('title' => '證件照', 'size' => array (500, 387, 275, 212), 'required' => true, 'accept' => '.png, .jpg, .jpeg'),
  1 => array ('title' => '專業照', 'size' => array (500, 387, 275, 212), 'required' => false, 'accept' => '.png, .jpg, .jpeg'),
  2 => array ('title' => '生活照', 'size' => array (500, 387, 275, 212), 'required' => false, 'accept' => '.png, .jpg, .jpeg'),
);
$CONSTRAINT = [
  'account' => ['帳號', CONSTRAINT_UNIQUE],
];

$VARS['status_select'] = GenRadio('status', $var_member_status, true, true);
$VARS['gender_select'] = GenRadio('gender', $var_member_gender, true, true);
$VARS['type_select'] = GenRadio('type', $var_consultant_type, true, true);
$VARS['demo_payment_select'] = GenRadio('demo_payment', $var_demo_payment, true, true);
$VARS['education_select'] = GenSelect('education', $var_education, true);
$VARS['lang1_select'] = GenSelect('lang1', $var_language, false, true);
$VARS['lang2_select'] = GenSelect('lang2', $var_language, false);
$VARS['level_select'] = GenCheckboxBySQL('level', "SELECT id, level_name AS `data` FROM `level` WHERE status = 10 ORDER BY `begin`", true);
//$VARS['interest_select'] = GenCheckboxBySQL('interest', "SELECT id, title AS `data` FROM `interest` WHERE status = 10 ORDER BY eng_title", true);

if (MODE == 'add' || ((MODE == 'modify' || MODE == 'modify2') && !empty(GetParam('password'))))
  $_POST['password'] = GenPassword(GetParam('account'), GetParam('password'));
else
  unset ($_POST['password']);
////////////////////////////////////
	require_once('func.inc.php');
////////////////////////////////////

function fn_callback($r)
{
  return $r;
}

function fn_before_delete($id)
{
  global $rs3;

  $t = date('YmdH', time() - 3600); // 避免還在上課的
  $sql = "SELECT * FROM classroom WHERE consultant_id = $id AND status = 10 AND `datetime` >= $t";
  $rs3->query($sql);
  if ($rs3->count > 0)
  {
    Message('此顧問目前還有被預約的課，無法刪除', false, MSG_ERROR);
    GoLast();
  }
  return true;
}

function fn_new()
{
  global $VARS;

  $VARS['status'] = 20; // 啟用
  $VARS['password'] = makePassword(6);
  $VARS['demo_pay'] = 0;
  $VARS['course_pay'] = 0;
  $VARS['birthdate'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 18));
}

function fn_add($id)
{
  global $rs3;

  for ($i = 0; $i <= 6; $i++)
  {
    for ($k = 0; $k < 24; $k++)
    {
      $v['consultant_id'] = $id;
      $v['time'] = $k;
      $v['weekday'] = $i;
      $v['available'] = 0;
      $rs3->insert('consultant_fixed_schedule', $v);
    }
  }
  update_consultant_schedule($id);
}

function fn_edit($id)
{
}

function fn_modify($id)
{
}
?>