<?php
// Email, Email name
$a = array (1, 2, 3, 4, 5, 6, 7, 8, 9, 10);
switch (MODE)
{
case 'update':
	before_log('config');
  foreach ($a as $i)
		SetConfig($i, GetParam("config$i"));
	log_action(ACT_EDIT, 'config', 0, $_POST);
	Message('更新完成!', null, MSG_OK);
	GoLast();
	break;
default:
	foreach ($a as $i)
		$VARS["config$i"] = GetConfig($i);
	RestorePostData();
	break;
}
?>