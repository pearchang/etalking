<?php
require('config.php');
require('const.php');
require('db.php');
require('functions.php');

$uid = session_id();
$nn = GetParam('CKEditorFuncNum');
$ff = $_FILES['upload'];
$dir = IMAGE_PATH . $uid;
if (!is_dir($dir))
	mkdir($dir, 0755, true);
$name = strtolower(uniqid() . '.' . substr(strrchr($ff['name'], '.'), 1));
$file = $dir . '/' . $name;
move_uploaded_file($ff['tmp_name'], $file);
$url = TEMP_IMAGE ."$uid/$name";
echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($nn, '$url', '');</script>";
unset ($v);
$v['uid'] = $uid;
$v['filename'] = $ff['name'];
$v['tmpfile'] = $url;
$rs = new ResultSet();
$rs->insert('editor', $v);
?>
