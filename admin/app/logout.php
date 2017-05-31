<?php
foreach ($_SESSION as $k => $v)
{
	if (substr($k, 0, 5) == 'admin')
		unset ($_SESSION[$k]);
}
GoToPage('');
?>
