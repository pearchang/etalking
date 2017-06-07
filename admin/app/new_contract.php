<?php
$TABLE = 'member';
$FUNC_NAME = '學員';
$SEARCH = false;
//$SELECT = array ('gender');
$RADIO = array('status', 'prefer', 'gender', 'type', 'level_id');
$CHECKBOX = array (
  array ('field' => 'interest', 'table' => 'member_interest', 'key' => 'member_id', 'foreign_key' => 'interest_id'),
  array ('field' => 'skill', 'table' => 'member_skill', 'key' => 'member_id', 'foreign_key' => 'skill_id'),
);
$EDITORS = array (
  array ('type' => EDITOR_TEXT, 'name' => 'contract_memo', 'height' => 3),
  array ('type' => EDITOR_TEXT, 'name' => 'contract_note', 'height' => 3),
  array ('type' => EDITOR_TEXT, 'name' => 'note', 'height' => 3),
  array ('type' => EDITOR_TEXT, 'name' => 'note2', 'height' => 3),
);
$CONSTRAINT = [
  'account' => ['帳號', CONSTRAINT_UNIQUE],
];

$id = GetParam('id');
$rs->select('member', $id);
AssignValue($rs);

$VARS['payment_select'] = GenSelect('payment', $var_payment2, false, true);
$VARS['installment_select'] = GenSelect('installment', $var_installment, false, false);
$VARS['type_select'] = GenRadio('type', $var_member_type, true);
$VARS['status_select'] = GenRadio('status', $var_member_status, true);
$VARS['gender_select'] = GenRadio('gender', $var_member_gender, true);
$VARS['prefer_select'] = GenRadio('prefer', $var_consultant_type, true);
$VARS['education_select'] = GenSelect('education', $var_member_education, true);
$VARS['level_select'] = GenRadioBySQL('level_id', "SELECT id, level_name AS `data` FROM `level` WHERE status = 10 ORDER BY `begin`", true);
$VARS['interest_select'] = GenCheckboxBySQL('interest', "SELECT id, title AS `data` FROM `interest` WHERE status = 10 ORDER BY eng_title", true);
$VARS['skill_select'] = GenCheckboxBySQL('skill', "SELECT id, title AS `data` FROM `skill` WHERE status = 10 ORDER BY title", true);
$sql = "SELECT id, version AS `data` FROM `contract` WHERE `type` = 10 AND status = 10 AND `begin` <= '$today' AND `end` >= '$today' ORDER BY version DESC"; // type=10 學員
$VARS['contract_id_select'] = GenSelectBySQL('contract_id', $sql, true);
$VARS['enterprise_select'] = GenSelectBySQL('enterprise_id', "SELECT id, ent_name AS `data` FROM `enterprise` WHERE status = 10 ORDER BY ent_name", true);
//$sql = "SELECT * FROM `plan` WHERE status = 10 AND `begin` <= '$today' AND `end` >= '$today' ORDER BY period, plan_name";
//$rs->query($sql);
//AssignValues($rs, 'plan');
$sql = "SELECT * FROM `plan_category` WHERE status = 10 ORDER BY cat_name";
$rs->query($sql);
AssignValues($rs, 'cat');

if (MODE == 'add' || (MODE == 'modify' && null != GetParam('password')))
{
  // begin
  $date = date('Y-m-d', strtotime(GetParam('begin')));
  if ($date < date('Y-m-d'))
    Message('生效日不得小於今天', true, MSG_WARN);

  $_POST['passwd'] = GetParam('password');
  $_POST['password'] = GenPassword(GetParam('account'), GetParam('password'));
  $_POST['original_level_id'] = GetParam('level_id');
}
else
  unset ($_POST['password']);

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

  $VARS['status'] = 10; // 啟用
  $VARS['password'] = $VARS['mobile']; //makePassword(6);
  $VARS['begin'] = date('Y-m-d', time() + 86400); // 隔天
  $VARS['birthdate'] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d'), date('Y') - 8));
}

function fn_add($id)
{
}

function fn_edit($id)
{
}

function fn_modify($id)
{
  global $rs3, $EMAIL_SUBJECT_NEW_MEMBER, $var_schedule_time;

  // contract
  $v['status'] = 10; // 有效
  $v['member_id'] = $id;
  $v['contract_id'] = GetParam('contract_id');
  $v['plan_id'] = GetParam('plan_id');
  $v['begin'] = GetParam('begin');
  $v['price'] = GetParam('price');
  $v['gift'] = GetParam('gift');
  $v['period'] = GetParam('period');
  $v['grade'] = GetParam('grade');
  $v['contract_name'] = GetParam('contract_name');
  // plan
  $rs3->select('plan', $v['plan_id']);
  $r = $rs3->fetch();
  $v['plan_name'] = $r['plan_name'];
  $v['point'] = $r['point'];
  $v['end'] = getPeriod($v['begin'], $v['period']);
  $v['sn'] = getSerialNumber(SN_CONTRACT);
  $v['chi_name'] = GetParam('member_name');
  $v['eng_name'] = GetParam('first_name') . ' ' . GetParam('last_name');
  $v['birthdate'] = GetParam('birthdate');
  $v['legal'] = GetParam('legal');
  $v['tel'] = GetParam('tel') . ' ' . GetParam('mobile');
  $v['email'] = GetParam('email');
  $v['address'] = GetParam('address');
  $v['account'] = GetParam('account');
  $v['memo'] = GetParam('contract_memo');
  $v['note'] = GetParam('contract_note');
  $v['sales_id'] = $_SESSION['admin_id'];
  $rs3->insert('member_contract', $v);
  $cid = $rs3->last_id;
  // bill
  foreach ($_POST['payment'] as $idx => $p)
  {
    $v['payment'] = $p;
    $v['installment'] = $_POST['installment'][$idx];
    $v['total'] = $_POST['total'][$idx];
    $v['sn'] = getSerialNumber(SN_BILL);
    $v['contract_id'] = $cid;
    $v['status'] = 10;
    $rs3->insert('member_contract_bill', $v);
  }
  // 移除還沒DEMO的
  $sql = "SELECT c.*, r.id AS rid FROM course_registration r, classroom c WHERE r.member_id = $id AND r.classroom_id = c.id AND c.status = 10 AND c.status2 < 20"; // 20 done
  $rs3->query($sql);
  if ($rs3->count > 0)
  {
    unset ($v);
    $c = $rs3->fetch();
    if ($c['status2'] == 0)
    { // cancel
      $v['status'] = 20;
      $rs3->update('classroom', $c['id'], $v);
      $rs3->update('course_registration', $c['rid'], $v);
      unset ($v);
      $v['member_id'] = $id;
      $v['type'] = 31; // cancel
      $v['content'] = "取消DEMO {$c['date']} " . $var_schedule_time[$c['time']];
      $rs3->insert('member_history', $v);
    }
    else
    { // done history
      $v['status2'] = 20;
      $rs3->update('classroom', $c['id'], $v);
      unset ($v);
      $v['member_id'] = $id;
      $v['type'] = 32; // DEMO OK
      $v['content'] = "DEMO完成 {$c['date']} " . $var_schedule_time[$c['time']];
      $rs3->insert('member_history', $v);
    }
  }

  // 發新會員通知
  $m = new MailModule();
  $m->template = 'email_new_member';
  $m->vars = $_POST;
  $m->addAddress($_POST['email'], $_POST['member_name']);
  $m->subject = $EMAIL_SUBJECT_NEW_MEMBER;
  $m->send();
  unset($m);

  Message('新合約(會員)建立完成', false, MSG_OK);
  GoToPage('request');
}
?>