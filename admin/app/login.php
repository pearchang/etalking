<?php
if(!empty($_POST['captcha']) && !empty($_SESSION['captcha']))
	if ($_POST['captcha'] != $_SESSION['captcha'])
		Message('驗證碼錯誤!', true);

$account = GetParam('account');
$password = GenPassword($account, GetParam('password'));

if ($account == 'admin')
{
	$pass = GetConfig(ADMIN_PW);
	if ($password != $pass)
		$error = true;
	else
	{
		$_SESSION['admin'] = 'admin';
		$_SESSION['admin_id'] = -1;
		$_SESSION['admin_name'] = '管理者';
		$_SESSION['admin_manager'] = 1;
		$_SESSION['admin_group'] = 0;
    $_SESSION['admin_teaching'] = true;
    $_SESSION['admin_marketing'] = true;
//    $_SESSION['admin_is_sales'] = true;
		// TODO: groups
		GoToPage('main');
	}
}
else
{
	$rs->query("SELECT `id`, `account`, `password`, `user_name`, `is_manager` FROM `user` WHERE account = '$account' AND status = 10 AND deleted = 0");
	if ($rs->count == 0)
		$error = true;
	else
	{
		list ($id, $account, $pass, $name, $manager) = $rs->row();
		if ($password != $pass)
			$error = true;
		else
		{
			$_SESSION['admin'] = $account;
			$_SESSION['admin_id'] = $id;
			$_SESSION['admin_name'] = $name;
			$_SESSION['admin_manager'] = $manager;
			$sql = "SELECT group_id FROM user_group WHERE user_id = $id";
			$rs->query($sql);
			unset ($group);
			while (($r = $rs->fetch()))
				$group[] = $r['group_id'];
			$_SESSION['admin_group'] = $group;
			$_SESSION['admin_groups'] = implode(', ', $group);
			GoToPage('main');
		}
	}

}

if (isset($error))
	Message('帳號或密碼錯誤!', true);
?>
