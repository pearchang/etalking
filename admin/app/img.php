<?php
$f = GetParam('f');
if ($f != '' && (substr($f, 0, 6) != '/imgs/' || substr($f, 0, 8) != '/images/') && file_exists(DOC_ROOT . $f))
	readfile(DOC_ROOT . $f);
exit;
?>
