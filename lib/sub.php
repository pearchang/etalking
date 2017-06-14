<?php
function GenCat($v, $top = true)
{
	global $page;

	if ($v == 0)
	{
		$page->assign('last', '主目錄');
		$page->assign('cat', 0);
	}
	else
	{
		$sql = 'SELECT parent, cat_name FROM categories WHERE id = ' . $v;
		$rs = mysql_query($sql);
		$row = mysql_fetch_row($rs);
		$v = $row[0];
		$page->assign('last', $row[1]);
		mysql_free_result($rs);
		while ($v != 0)
		{
			$sql = 'SELECT id, parent, cat_name, level FROM categories WHERE id = ' . $v;
			$rs = mysql_query($sql);
			$row = mysql_fetch_row($rs);
			$v = $row[1];
			$vv['id'] = $row[0];
			$vv['name'] = $row[2];
			$vv['level'] = $row[3] + 1;
			$val[] = $vv;
			mysql_free_result($rs);
		}
		if ($top)
		{
			$vv['id'] = 0;
			$vv['name'] = '主目錄';
			$vv['level'] = 1;
			$val[] = $vv;
		}
		if (isset($val))
			$val = array_reverse($val);
		$page->assign('path', $val);
	}
}

function MergeDate()
{
	return $_POST['year'] . '-' . $_POST['month'] . '-' . $_POST['day'];
}

function SplitDate($date)
{
	global $VARS;

	if (!isset($date))
		return;
	$VARS['year'] = substr($date, 0, 4);
	$VARS['month'] = substr($date, 5, 2);
	$VARS['day'] = substr($date, 8, 2);
}

//function GetAlpha()
//{
//	$r = rand(1, 52);
//	if ($r > 26) // 大寫
//		return chr(ord('A') + $r - 26 - 1);
//	else
//		return chr(ord('a') + $r - 1);
//}
//
//function GetDigit()
//{
//	$r = rand(0, 9);
//	return chr(ord('0') + $r);
//}
//
//function GenPassword()
//{
//	$a = rand(2, 3);
//	$b = rand(2, 3);
//	$c = 8 - $a - $b;
//	$s = '';
//	for ($i = 0; $i < $a; $i++)
//		$s .= GetAlpha();
//	for ($i = 0; $i < $b; $i++)
//		$s .= GetDigit();
//	for ($i = 0; $i < $c; $i++)
//		$s .= GetAlpha();
//	return $s;
//}

function GetKey()
{
	$s = '';
	for ($i = 0; $i < 16; $i++)
	{
		$a = rand(0, 10);
		$s .= $a < 4 ? GetDigit() : GetAlpha();
	}
	return $s;
}
?>