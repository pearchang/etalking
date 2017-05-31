<?php
if (!isset($_SESSION['admin']))
{
  echo <<<EOT
<script language=javascript>
  top.location.href = '/admin/';
</script>
EOT;
  exit;
//  GoToPage('');
}

// 取得user status
if ($_SESSION['admin_id'] > 0 )
{
	$rs->select('user', $_SESSION['admin_id']);
	$r = $rs->fetch();
	if ($r['status'] != 10) // 非啟用
		GoToPage('logout');
	$_SESSION['admin'] = $r['account'];
	$_SESSION['admin_name'] = $r['user_name'];
	$_SESSION['admin_manager'] = $r['is_manager'];
  $_SESSION['admin_teaching'] = false;
  $_SESSION['admin_marketing'] = false;
	$sql = "SELECT u.group_id, g.group_name FROM user_group u, `group` g WHERE u.user_id = {$_SESSION['admin_id']} AND u.group_id = g.id";
	$rs->query($sql);
	unset ($group);
	while (($r = $rs->fetch()))
  {
    $group[] = $r['group_id'];
    if (strpos($r['group_name'], '教學部') !== false)
      $_SESSION['admin_teaching'] = true;
    if (strpos($r['group_name'], '行銷部') !== false)
      $_SESSION['admin_marketing'] = true;
  }
	$_SESSION['admin_group'] = $group;
	$_SESSION['admin_groups'] = implode(', ', $group);
	$_SESSION['admin_is_sales'] = false;
	// is_sales
	if (!$_SESSION['admin_manager'])
	{
		$sql = "SELECT * FROM `group` WHERE id IN ({$_SESSION['admin_groups']}) AND is_sales <> 0";
		$rs->query($sql);
		if ($rs->count > 0)
			$_SESSION['admin_is_sales'] = true;
	}

}
// 取得權限 & MENU
$sql = "SELECT * FROM `menu` WHERE `parent` = 0 AND hide = 0 ORDER BY rank";
$rs->query($sql);
//$menu = $rs->fetch_array();
while (($r = $rs->fetch()))
  $menu[$r['rank']] = $r;
unset ($_SESSION['PRIV']);
if ($_SESSION['admin_group'] == 0)
	$sql = 'SELECT *, 2 AS perm FROM `menu` WHERE hide = 0 AND `parent` <> 0 ORDER BY rank';
else
	$sql = "SELECT m.*, 2 AS perm FROM menu m, group_permission p WHERE p.group_id IN ({$_SESSION['admin_groups']}) AND p. menu_id = m.id GROUP BY m.id ORDER BY m.rank";
//echo $sql;
//exit;
$rs->query($sql);
if ($rs->count == 0)
{
	Message('您沒有管理權限! 請聯絡管理者!');
	GoToPage('logout');
}
$i = 1;
while (($r = $rs->fetch()))
{
  //print_r($r);
	$_SESSION['PRIV'][$r['code']] = $r['perm'];
	if ($r['hide'] != 0)
		continue;
	$r['i'] = $i++;
	//$val[] = $r;
  $sql = "SELECT rank FROM menu WHERE `id` = " . $r['parent'];
  $rs2->query($sql);
  $rank = $rs2->record();
  if (FUNC == $r['code'])
  {
    $menu[$rank]['selected'] = true;
    $VARS['selected_mainmenu'] = $menu[$rank]['id'];
  }
  $menu[$rank]['menu_list'][] = $r;
	// get child
	$sql = "SELECT * FROM menu WHERE `parent` = " . $r['id'];
	//print_r($r);
	//echo $sql;
	$rs2->query($sql);
	while (($r2 = $rs2->fetch()))
	{
//		print_r($r2);
		$_SESSION['PRIV'][$r2['code']] = $_SESSION['PRIV'][$r['code']];
		$_SESSION['PRIV_P'][$r2['code']] = $r['code'];
	}
}
//print_r($_SESSION['PRIV']);
//print_r($menu);
foreach ($menu as $k => $v)
{
  //print_r($v['menu_list']);
  if (count($v['menu_list']) == 0)
    unset ($menu[$k]);
  else
    unset ($menu[$k]['code']);
}
//print_r($menu);
$VARS['menu_list'] = $menu; //$val;
$arr = array('', 'go', 'top', 'left', 'main', 'summary', 'logout', 'img', 'back', 'picture', 'tools');
foreach ($arr as $k)
	$_SESSION['PRIV'][$k] = PERM_ALL;

// 判斷權限
if (MODE != 'use_url' && !isset($_SESSION['PRIV'][FUNC]) || $_SESSION['PRIV'][FUNC] == 0)
	GoToPage('logout');

if (FUNC == 'main')
{
	$VARS['menu_name'] = '首頁';
//	$VARS['menu_memo'] = '歡迎使用<b>' . SITE_TITLE . '</b>後端管理介面<br>請使用左方功能管理網站';
}
else
{
	$sql = "SELECT * FROM `menu` WHERE code = :func";
	$rs->query($sql, array('func' => $FUNC));
	$r = $rs->fetch();
	$VARS['menu_name'] = $r['name'];
	$VARS['menu_code'] = $r['code'];
	// get parent id
  $sql = "SELECT * FROM `menu` WHERE id = " . $r['parent'];
  $rs->query($sql);
  $r = $rs->fetch();
  if ($r['parent'] != 0)
  {
    $VARS['menu_code'] = $r['code'];
    $VARS['selected_mainmenu'] = $r['parent'];
  }
}
$VARS['admin_id'] = $_SESSION['admin_id'];
$VARS['admin_name'] = $_SESSION['admin_name'];
$VARS['admin_manager'] = $_SESSION['admin_manager'];
$VARS['admin_is_sales'] = $_SESSION['admin_is_sales'];
$VARS['admin_teaching'] = $_SESSION['admin_teaching'];
$VARS['admin_marketing'] = $_SESSION['admin_marketing'];
$VARS['menu_code'] = isset($_SESSION['PRIV_P'][FUNC]) ? $_SESSION['PRIV_P'][FUNC] : FUNC;
$VARS['func_name'] = '';

switch ($_SESSION['PRIV'][$FUNC])
{
case 1:
	$EDITABLE = false;
	$DELETABLE = false;
	break;
case 2:
	$EDITABLE = true;
	$DELETABLE = false;
	break;
case 3:
	$EDITABLE = true;
	$DELETABLE = true;
	break;
}
define ('EDITABLE', $EDITABLE);
define ('DELETABLE', $DELETABLE);
if (!$EDITABLE)
	$VARS['NOEDIT'] = 'disabled';
if (!$DELETABLE)
	$VARS['NODELETE'] = 'disabled';

$VARS['can_change_password'] = $_SESSION['PRIV']['password'];
unset($arr, $val, $k, $v, $i, $menu);
?>