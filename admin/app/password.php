<?php
if (MODE == 'update')
{
  $opw = GetParam('old_password', null, NO_ESCAPE);
  $pw = GetParam('password', null, NO_ESCAPE);
  $pw2 = GetParam('password2', null, NO_ESCAPE);
  if ($opw == $pw)
    Message('新舊密碼必須不同，請重新輸入!', true);
  if ($pw != $pw2)
    Message('兩次密碼輸入不一致，請重新輸入!', true);
  $password = GenPassword($_SESSION['admin'], $opw);
  if ($_SESSION['admin_id'] > 0)
    $pass = $rs->get_value('user', 'password', $_SESSION['admin_id']);
  else
	  $pass = GetConfig(ADMIN_PW);
	if ($password != $pass)
		Message('原密碼不正確!', true);
	else
	{
    $pw = GenPassword($_SESSION['admin'], GetParam('password2', null, NO_ESCAPE));
    if ($_SESSION['admin_id'] > 0)
      $rs->update('user', $_SESSION['admin_id'], array ('password' => $pw));
    else
  		SetConfig(ADMIN_PW, $pw);
		Message('密碼已變更, 下次登入請使用新密碼!', true, MSG_OK);
	}
}

$VARS['account'] = $_SESSION['admin'];
$VARS['name'] = $_SESSION['admin_name'];
?>
