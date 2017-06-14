<?php
// 去除斜線問題 \\ No Global
//foreach ($_POST as $k => $v)
//{
//	if (is_array($v))
//	{
//		foreach ($v as $kk => $vv)
//		{
//			if (substr($vv, -1) == '\\')
//			{
//				$vv = substr($vv, 0, -1);
//				$v[$kk] = $vv;
//				$trans = true;
//			}
//		}
//		if (isset($trans))
//		{
//			$_POST[$k] = $v;
////			$$k = $v;
//			unset($trans);
//		}
//	}
//	elseif (substr($v, -1) == '\\')
//	{
//		$v = substr($v, 0, -1);
//		$_POST[$k] = $v;
////		$$k = $v;
//	}
//}
//foreach ($_GET as $k => $v)
//{
//	if (substr($v, -1) == '\\')
//	{
//		$v = substr($v, 0, -1);
//		$_GET[$k] = $v;
////		$$k = $v;
//	}
//}


// 強迫建立目錄if目錄不存在
function MakeDir($path)
{
 	while (substr($path, -1) == '/')
		$path = substr($path, 0, -1);
	if(file_exists($path))
		return;
	MakeDir(dirname($path));
	mkdir($path, 0777);
}

// 將'改成\'讓SQL語法可以運作
function SQLString($str)
{
	return str_replace("'", "\\'", $str);
}

// 將return(\n)改成文字\n, (\r)刪去, 常用於JavaScript
function RemoveReturn($str)
{
	return str_replace("\n", "\\n", str_replace("\r", '', $str));
}

// 將\改成\\後轉為JavaScript可接受的語法
function ValueString($str)
{
	return SQLString(RemoveReturn(str_replace('\\', '\\\\', $str)));
}

// 用於中文(unicode)的substr
// $str = 傳入字串
// $n = 要擷取字數
// $tail = 尾巴字串
function SubString($str, $n, $tail = '...')
{
	if (mb_strwidth($str) <= $n) // 字串過短就跳回
		return $str;
	$n -= mb_strwidth($tail); // 要先剪掉尾巴長度
	$i = $z = 0;
	do
	{
		$x = mb_strwidth(mb_substr($str, $i, 1));
		if ($z + $x > $n)
			break;
		$z += $x;
		$i++;
	}
	while ($z != $n);
	return mb_substr($str, 0, $i) . $tail;
}

function AssignArray($data, $key, $var = null, $callback = null, $i = 1)
{
	global $VARS;

	if (is_null($var))
	{
		$assign = 'list';
		$nodata = 'nodata';
	}
	else
	{
		$assign = $var . '_list';
		$nodata = $var . '_nodata';
	}
	if (count($data) == 0)
	{
		$VARS[$nodata] = true;
		return;
	}
	$a = $key[0];
	$b = $key[1];
	unset($v, $vv);
	foreach ($data as $aa => $bb)
	{
		$v[$a] = $aa;
		$v[$b] = $bb;
		$v['i'] = $i++;
		if (!is_null($callback))
			$v = call_user_func($callback, $v);
		$vv[] = $v;
	}
	$VARS[$assign] = $vv;
}

// 將result set資料(一筆) assign 到 $VARS
// $rs = ResultSet
// $callback = 回call函式
// $prefix = 每個變數的前置字串, 會自動加底線
function AssignValue($rs, $callback = null, $prefix = '')
{
	global $VARS;

	if ($rs->count == 0)
		return;
	$row = $rs->fetch();
	if (!is_null($callback))
		$row = call_user_func($callback, $row);
	if (!empty($prefix))
		$prefix = strtolower($prefix) . '_';
	foreach ($row as $field => $val)
		$VARS[$prefix . strtolower($field)] = $val;
}

// 將result set資料製作成陣列回傳
// $rs = ResultSet
// $callback = 回call函式
// $i = index起始編號
// $cnt = 處理筆數, -1為全處理
function AssignResult($rs, $callback = null, $i = 1, $cnt = -1, $prefix = '')
{
	if ($rs->count == 0)
		return null;
	$c = 0;
	if (!empty($prefix))
		$prefix = strtolower($prefix) . '_';
	while (($row = $rs->fetch()) && $c != $cnt)
	{
		$row['ii'] = $i - 1;
		$row['i'] = $i++;
		if (!is_null($callback))
      $row = call_user_func($callback, $row);
		foreach ($row as $field => $value)
			$v[$prefix . strtolower($field)] = $value;
		$vv[] = $v;
		unset($v);
		$c++;
	}
	$vv[count($vv) - 1]['LAST'] = true;
	return $vv;
}

// 將result set資料製成兩層陣列回傳
// $rs = ResultSet
// $callback = 回call函式
// $i = index起始編號
// $cnt = 處理筆數, -1為全處理
// $div = 有幾個column
function AssignResult2($rs, $callback = null, $i = 1, $cnt = -1, $div = 2)
{
	if ($rs->count == 0)
		return null;
	$c = 0;
  unset ($v, $vv);
	while (($row = $rs->fetch()) && $c != $cnt)
	{
    $row['colbit'] = $c % $div;
    $row['rowbit'] = floor($c / $div) % $div;
		$row['i'] = $i++;
		if (!is_null($callback))
      $row = call_user_func($callback, $row);
    $v[] = $row;
//		foreach ($row as $field => $value)
//			$v[strtolower($field) . $k] = $value;
		if (count($v) == $div)
		{
			$vv[] = $v;
			unset($v);
		}
		$c++;
	}
	if (isset($v))
		$vv[] = $v;
	return $vv;
}

// 將result set資料製成陣列 assign 到 $VARS
// $rs = ResultSet
// $var = 陣列名稱, 不傳值預設list, 有值會自動加入_list
// $callback = 回call函式
// $i = index起始編號
// $cnt = 處理筆數, -1為全處理
function AssignValues($rs, $var = null, $callback = null, $i = 1, $cnt = -1)
{
	global $VARS;

	if (is_null($var))
	{
		$assign = 'list';
		$nodata = 'nodata';
	}
	else
	{
		$assign = $var . '_list';
		$nodata = $var . '_nodata';
	}
	if ($rs->count == 0)
	{
		$VARS[$nodata] = true;
		return;
	}
	$VARS[$assign] = AssignResult($rs, $callback, $i, $cnt);
}

// 將result set資料製成朗兩層陣列 assign 到 $VARS
// $rs = ResultSet
// $var = 陣列名稱, 不傳值預設list, 有值會自動加入_list
// $callback = 回call函式
// $i = index起始編號
// $cnt = 處理筆數, -1為全處理
// $div = 有幾個column
function AssignValues2($rs, $var = null,  $callback = null, $i = 1, $cnt = -1, $div = 2)
{
	global $VARS;

	if (is_null($var))
	{
		$assign = 'list';
		$nodata = 'nodata';
	}
	else
	{
		$assign = $var . '_list';
		$nodata = $var . '_nodata';
	}
	if ($rs->count == 0)
	{
		$VARS[$nodata] = true;
		return;
	}
	$VARS[$assign] = AssignResult2($rs, $callback, $i, $cnt, $div);
}

function AssignValues3($rs, $var = null,  $callback = null, $div = 2, $i = 1)
{
	global $VARS;

	if (is_null($var))
	{
		$assign = 'list';
		$nodata = 'nodata';
	}
	else
	{
		$assign = $var . '_list';
		$nodata = $var . '_nodata';
	}
//	if ($rs->count == 0)
//	{
//		$VARS[$nodata] = true;
//		return;
//	}
	if ($rs->count != 0)
		$v = AssignResult($rs, $callback, $i, -1);
	else
		$v = array();
	if (count($v) < $div)
		while (count($v) != $div)
			array_push($v, '');
	$VARS[$assign] = $v;
}


// 將URL後的參數刪掉某key=value
// 如fn=abc&mo=zzz&yy=555, RemoveKey('yy')則回傳fn=abc&mo=zzz
// $str = URL
// $k = Key
function RemoveKey($str, $k)
{
	$u = explode('?', $str);
	if (count($u) == 1)
		return $str;
	$n = strlen($k) + 1;
	$z = $k . '=';
	$x = explode('&', $u[1]);
	if (is_array($x))
	{
		foreach ($x as $k => $v)
		{
			if (substr($v, 0, $n) == $z)
				unset($x[$k]);
		}
		if (count($x) == 0)
			return $u[0];
		else
			return $u[0] . '?' . implode('&', $x);
	}
	else
		return $str;
}

// 頁面控制, 頁數是跳動的, 1 11 21 31...
// $sql = 取得資料的SQL
// $icount = 每頁筆數
// $link = 跳頁連結URL, 通常是CUR_URL
// $callback = 回call函式
function PageControl($sql, $params = null, $icount = null, $link = null, $callback = null)
{
	global $VARS, $NO_TOTAL, $conn;

	// Get Count
  $rs = new ResultSet();
  $rs->query($sql, $params);
  $count = $rs->count;
  $rs->free();
	if ($count == 0)
	{
		$VARS['nodata'] = true;
		$VARS['total_item'] = 0;
		$VARS['total_page'] = 0;
		$VARS['cur_page'] = 0;
		$VARS['page_control'] = '&nbsp;';
		//$VARS['page_control'] = '<font color="#FF9900;">1</font>';
	}
	else
	{
		if (empty($link))
			$link = CUR_URL;
//		echo $link;
		if (empty($icount))
			$icount = PAGE_COUNT;
		$link = RemoveKey($link, 'p');
		$pp = (strpos($link, '?') !== false ? '&p=' : '?p=');
    $VARS['plink'] = $link . $pp;
		// Go to
		$to = $count;
		$VARS['total_item'] = $to;
		$total = ceil($count / $icount);
		$VARS['total_page'] = $total;
		$cur = isset($_GET['p']) ? $_GET['p'] : 1;
		if ($cur > $total)
			$cur = $total;
		elseif ($cur < 1)
			$cur = 1;
		// Get Data
		$cc = ($cur - 1) * $icount;
		$rs->query($sql . " LIMIT $cc, $icount", $params);
		$VARS['page_item'] = $count;
		// Set data
		AssignValues($rs, null, $callback, $cc + 1, $icount);
		// Set Page
		$ff = intval(($cur - 1) / 10) * 10;
		$tt = $ff + 10;
    $pages = array();
		for ($i = 1; $i <= $total; $i++)
		{
			if (($i > $ff && $i <= $tt) || $i % 10 == 1)
				$pages[] = $i;
		}
    $VARS['page_list'] = $pages;
		// if ($total % 10 != 1)
		// {
		// 	if ($tt != $total && $cur + 9 < $total)
		// 		$s .= "<a href=\"$link$pp$total\"><font color=\"#CCCCCC\" style=\"font-size: 11pt;\">$total</font></a> ";
		// }
    $VARS['next_page'] = $cur < $total ? $cur + 1 : $total;
    $VARS['prior_page'] = $cur > 1 ? $cur - 1 : 1;
    $VARS['last_page'] = $total;
    $_SESSION['current_page'] = $VARS['cur_page'] = $cur;
		$VARS['page_control'] = genView('page_nav', $VARS);
	}
}

function PageControl2($sql, $cnt, $icount = 25, $link = null, $callback = null)
{
	global $VARS, $rs, $conn;

	// Get Count
	$r = mysqli_query($conn, $cnt);
	$r = mysqli_fetch_row($r);
	$count = $r[0];
	if ($count == 0)
	{
		$VARS['nodata'] = true;
		$VARS['total'] = 0;
		$VARS['total_page'] = 0;
		$VARS['cur_page'] = 0;
	}
	else
	{
		$link = RemoveKey($link, 'p');
		$pp = strchr($link, '?') != -1 ? '&p=' : '?p=';
		// Go to
		$to = $count;
		$VARS['total'] = $to;
		$total = intval(($to + $icount - 1) / $icount);
		$VARS['total_page'] = $total;
		$cur = isset($_GET['p']) ? $_GET['p'] : 1;
		if ($cur > $total)
			$cur = $total;
		elseif ($cur < 1)
			$cur = 1;
		// Get Data
		$cc = ($cur - 1) * $icount;
		$rs->query($sql . " LIMIT $cc, $icount");
		// Set data
		AssignValues($rs, null, $callback, $cc + 1, $icount);
		// Set Page
		for ($i = 1; $i <= $total; $i++)
			$s .= $i != $cur ? "<a class='style17' href=\"$link$pp$i\">$i</a>  " : "<span class='style17'><b>$i</b></span>  ";
		$VARS['page_control'] = "<span class='style17'>" . substr($s, 0, strlen($s) - 3) . '</span>';
    $_SESSION['current_page'] = $VARS['cur_page'] = $cur;
	}
}

// 同PageContrl, 但是二為陣列的$row x $col
// 必須用巢狀<!-- BEGIN list -->
function PageControl3($sql, $cnt, $row, $col, $link = null, $callback = null, $name = '')
{
	global $VARS, $rs, $conn;

	if (!empty($name))
		$name .= '_';
	$icount = $row * $col;
	// Get Count
	$r = mysqli_query($conn, $cnt);
	$r = mysqli_fetch_row($r);
	$count = $r[0];
	if ($count == 0)
	{
		$VARS[$name . 'nodata'] = true;
		$VARS[$name . 'total_item'] = 0;
		$VARS[$name . 'total_page'] = 0;
		$VARS[$name . 'cur_page'] = 0;
	}
	else
	{
		$link = RemoveKey($link, 'p');
		$pp = strchr($link, '?') != -1 ? '&p=' : '?p=';
		// Go to
		$to = $count;
		$VARS[$name . 'total_item'] = $to;
		$total = intval(($to + $icount - 1) / $icount);
		$VARS[$name . 'total_page'] = $total;
		$cur = isset($_GET['p']) ? $_GET['p'] : 1;
		if ($cur > $total)
			$cur = $total;
		elseif ($cur < 1)
			$cur = 1;
		// Get Data
		$cc = ($cur - 1) * $icount;
/*
		$rs->query($sql . " LIMIT $cc, $icount");
		// Set data
		AssignValues2($rs, null, $callback, $cc + 1, $icount);
*/
		unset($v, $val);
		$pi = 0;
		for ($i = 0; $i < $row; $i++)
		{
			$ccc = $cc + $i * $col;
			$rs->query($sql . " LIMIT $ccc, $col");
			if ($rs->count == 0)
				break;
			$pi += $rs->count;
			$v['list'] = AssignResult($rs, $callback, $i * $col + 1, $col);
			while (count($v['list']) != $col)
				array_push($v['list'], '');
			$val[] = $v;
		}
		$VARS[$name . 'page_item'] = $pi;
		$VARS[$name . 'list'] = $val;
		// Set Page
		$s = $cur == 1 ? '' : '<a href="' . $link . $pp . ($cur - 1) . "\">上一頁</a> | ";
		$ff = intval(($cur - 1) / 10) * 10;
		$tt = $ff + 10;
		for ($i = 1; $i <= $total; $i++)
		{
			if ($i > $ff && $i <= $tt)
				$s .= $i != $cur ? "<a href=\"$link$pp$i\">$i</a> . " : "<font color=\"#666666;\"><b>$i</b></font> . ";
			else
			{
				if ($i % 10 == 1)
					$s .= "<a href=\"$link$pp$i\">$i</a> | ";
			}
		}
		if ($total % 10 != 1)
		{
			$s = substr($s, 0, strlen($s) - 3) . ' | ';
			if ($tt != $total && $cur + 9 < $total)
				$s .= "<a href=\"$link$pp$total\">$total</a> | ";
		}
		if ($cur != $total)
			$s .= '<a href="' . $link . $pp . ($cur + 1) . "\">下一頁</a>...";
//		$v = "共<font color=\"#0080C0\">$to</font>筆";
//		$VARS['page_control'] = "<span style=\"font-family:arial;\">[$v][ " . substr($s, 0, strlen($s) - 3) . ' ]</span>&nbsp;';
		$VARS[$name . 'page_control'] = "<span style=\"font-family:arial;\">" . substr($s, 0, strlen($s) - 3) . '</span>&nbsp;';
		$VARS[$name . 'cur_page'] = $cur;
	}
	if ($cur != $total)
	{
		$VARS[$name . 'next_page'] = $cur + 1;
		$VARS[$name . 'last_page'] = $total;
	}
  $_SESSION['current_page'] = $VARS[$name . 'cur_page'] = $cur;
	if ($cur != 1)
		$VARS[$name . 'prior_page'] = $cur - 1;
}

function EscapeString($str)
{
  global $rs;

  if (!is_array($str))
    return $rs->escape($str);
  else
  {
    foreach ($str as $k => $v)
      $str[$k] = EscapeString($v);
    return $str;
  }
}

// 取得$_POST或$_GET的參數
function GetParam($par, $ret = null, $no_escape = false)
{
	$r = isset($_GET[$par]) || isset($_POST[$par]) ? isset($_GET[$par]) ? $_GET[$par] : $_POST[$par] : $ret;
//	if (!$no_escape)
//		$r = EscapeString($r);
	return $r;
}

function GoToPage($str)
{
	header('Location: ' . BASE_URL . $str);
	exit;
}

function GoMain($tail = '')
{
	if ($tail != '')
		GoToPage(FUNC . '?' . $tail);
	else
		GoToPage(FUNC);
}

// 回到上二個page, 如沒記錄就用JS
function GoBack()
{
	$var = HISTORY;
	if (isset($_SESSION['sys'][$var]) && count($_SESSION['sys'][$var]) >= 2)
	{
		array_pop($_SESSION['sys'][$var]);
		$a = array_pop($_SESSION['sys'][$var]);
		header('Location: /' . $a);
	}
	else
	{
		echo <<<EOT
<script language="javascript">
	history.go(-2);
</script>
EOT;
	}
	exit;
}

// 回到上一個page, 如沒記錄就用JS
function GoLast()
{
	$var = HISTORY;
	if (isset($_SESSION['sys'][$var]) && count($_SESSION['sys'][$var]) >= 1)
	{
		$a = array_pop($_SESSION['sys'][$var]);
		header('Location: /' . PREFIX . $a);
	}
	else
	{
		echo <<<EOT
<script language="javascript">
	history.go(-1);
</script>
EOT;
	}
	exit;
}

// 顯示JS訊息
function Message($msg, $goback = false, $alert = MSG_ERROR)
{
	$var = MSG;
	$var2 = MSG_LEVEL;
	if (!empty($_SESSION['sys'][$var]))
		$_SESSION['sys'][$var] .= "\n$msg";
	else
		$_SESSION['sys'][$var] = $msg;
	$_SESSION['sys'][$var2] = $alert;
	if ($goback)
		GoLast();
}

function MessageBox($msg, $goback = false)
{
  echo <<<EOT
<script language=javascript>
  alert ('$msg');
</script>
EOT;
  if ($goback)
  {
    echo <<<EOT
<script language=javascript>
  history.go(-1);
</script>
EOT;
  }
  exit;
}

function MsgErr($arr, $msg = '')
{
  $var = ERROR;
  $_SESSION['sys'][$var] = $arr;
  Message($msg, true);
}

function SavePostData()
{
  $var = POSTDATA;
  $_SESSION['sys'][$var] = $_POST;
}

function RestorePostData()
{
  global $VARS;

  $var = POSTDATA;
  if (isset($_SESSION['sys'][$var]) && is_array($_SESSION['sys'][$var]))
  {
    foreach ($_SESSION['sys'][$var] as $k => $v)
    {
      $_POST[$k] = $VARS[$k] = $v;
      unset($_SESSION['sys'][$var][$k]);
    }
    //print_r($VARS);
    unset($_SESSION['sys'][$var]);
  }
}


function Either($a, $b)
{
	return is_null($a) ? $b : $a;
}

// 將$_POST的資料全部變成global變數
function PostGlobal()
{
	global $VARS;

	if (!is_array($_POST))
		return;
	foreach($_POST as $k => $v)
	{
		global $$k;
		$$k = $v;
		$VARS[$k] = $v;
	}
}

// 將$_POST資料全部製成一個list, 名為post_list
function PostVars()
{
	global $VARS;

	if (!is_array($_POST))
		return;
	foreach($_POST as $k => $v)
	{
		$VARS[$k] = $v;
		unset($vv);
		$vv['name'] = $k;
		$vv['value'] = $v;
		$val[] = $vv;
	}
	$VARS['post_list'] = $val;
}

// 將$_POST儲存至session
function PostSave()
{
	if (isset($_POST))
		$_SESSION['sys'][POSTDATA] = serialize($_POST);
}

// 將上次儲存的POST資料都變成全域變數
function PostLoad()
{
	global $VARS;

	if (isset($_SESSION['sys'][POSTDATA]))
	{
		$data = unserialize($_SESSION['sys'][POSTDATA]);
		foreach($data as $k => $v)
		{
			global $$k;
			$$k = $v;
			$VARS[$k] = $v;
		}
		unset($_SESSION['sys'][POSTDATA]);
	}
}

// 判斷是否有POST data
function HasPost()
{
	return isset($_SESSION['sys'][POSTDATA]);
}

// 取得 TABLE config裡的設定
// $sep = separator, 有設定的話會自動將資料以$sep分開為array
function GetConfig($id, $sep = '')
{
  $rs = new ResultSet();
  $rs->query('SELECT content FROM config WHERE id = ' . $id);
	if ($rs->count == 0)
	{
		$rs->execute("INSERT INTO config SET id = $id, content = ''");
		$data = '';
	}
	else
		$data = $rs->record();
	return $sep == '' || strstr($data, $sep) == '' ? $data : explode($sep, $data);
}

// 將設定資料寫入取得 TABLE config
// $sep = separator, 有設定的話會自動將資料(array)以$sep合併
function SetConfig($id, $data, $sep = ',')
{
  $rs = new ResultSet();
	if (is_array($data))
		$data = implode($sep, $data);
	$sql = "REPLACE config SET content = :data, id = :id";
  $params = array ('data' => $data, 'id' => $id);
	$rs->execute($sql, $params);
}

// 將HTML editor用的圖片移到正確位置
function MoveImages($dest, $val, $prefix = '')
{
	$uid = session_id();
	$rs = new ResultSet();
	if (file_exists(IMAGE_PATH))
	{
		$dpath = DOC_ROOT . $dest;
		if (!file_exists($dpath))
			MakeDir($dpath);
		$sql = "SELECT * FROM editor WHERE uid = '$uid'";
		$rs->query($sql);
		while (($r = $rs->fetch()))
		{
			$f = $r['tmpfile'];
			$s = explode('/', $f);
			$s = $s[count($s) - 1];
//			if (filetype(IMAGE_PATH . $f) != 'file')
//				continue;
			if (empty($prefix))
				$df = $dpath . $s; //$r['filename'];
			else
				$df = $dpath . $prefix . '_' . $s; //$r['filename'];
			if (file_exists($df))
				unlink($df);
// 			echo DOC_ROOT . $f . '<br>';
// 			echo $df . '<br>';
			rename(DOC_ROOT . $f, $df);
			chmod($df, 0666);
// 			echo $f . '<br>';
// 			echo $df . '<br>';
//			if (file_exists(IMAGE_PATH . $f))
//				unlink(IMAGE_PATH . $f);
			$sql = "DELETE FROM editor WHERE uid = '$uid'";
			$rs->execute($sql);
		}
//		ClearTempDir();
	}
//	echo $prefix . '<br>';
	$tmp = TEMP_IMAGE . $uid . '/';
	if ($prefix == '')
		return str_replace($tmp, $dest, $val);
	else
		return str_replace($tmp, $dest . $prefix . '_', $val);
}

// 清除HTML editor暫存圖片
function ClearTempDir()
{
	$uid = session_id();
	if (file_exists(IMAGE_PATH))
	{
		$rs = new ResultSet();
		$date = date('Y-m-d H:i:s', time() - 86400);
		$sql = "DELETE FROM editor WHERE cdate <= '$date'";
		$rs->execute($sql);
//		$sql = "DELETE FROM editor WHERE sessid = '$uid'";
//		$rs->execute($sql);
	}
}

// 建立HTML editor
function NewEditor($instance, $value = '', $height = '400', $width = '100%', $variable = 'editor', $required = false)
{
	global $VARS, $EDITABLE;

  if ($required)
    $req = 'required';
	//$value = ValueString($value);
	ClearTempDir();
//ed.Config['ImageBrowserURL'] = ed.BasePath + 'editor/filemanager/browser/delta/browser.html?Type=Image&Connector=connectors/php/connector.php&ServerPath=$TEMP_IMAGE&WebRoot=DOC_ROOT' ;
//ed.Config['FlashBrowserURL'] = ed.BasePath + 'editor/filemanager/browser/delta/browser.html?Type=Flash&Connector=connectors/php/connector.php&ServerPath=$TEMP_IMAGE&WebRoot=DOC_ROOT' ;
	$uid = session_id();
	$editor = <<<EOT
<textarea name='$instance' id='$instance' $req>$value</textarea>
<input type="hidden" name="{$instance}_uid" value="$uid">
<script language"JavaScript">
CKEDITOR.replace('$instance', {
  filebrowserBrowseUrl : '/js/ckeditor/filemanager2/index.html',
//  filebrowserBrowseUrl : '/js/ckeditor/filemanager/browser/default/browser.html?&Connector=/js/ckeditor/filemanager/browser/default/connectors/php/connector.php',
//   filebrowserImageBrowseUrl : '/js/ckeditor/filemanager/browser/default/browser.html?Type=Images&Connector=/js/ckeditor/filemanager/browser/default/connectors/php/connector.php',
//   filebrowserFlashBrowseUrl : '/js/ckeditor/filemanager/browser/default/browser.html?Type=Flash&Connector=/js/ckeditor/filemanager/browser/default/connectors/php/connector.php',
	filebrowserUploadUrl : '/lib/uploader.php?uid=$uid',
	height: $height,
	allowedContent: true
});
</script>
<font color=blue>* 請確定游標在編輯器內閃動再開始輸入資料!</font><br>
EOT;
	if (empty($variable))
		return $editor;
	else
		$VARS[$variable] = $editor;
}

// 建立一個TextArea (不使用HTML editor時以此取代)
function NewTextArea($instance, $value = '', $rows = '20', $cols = '100%', $variable = 'editor', $required = false)
{
	global $VARS;

  if ($required)
    $req = 'required';
	$dis = EDITABLE ? '' : 'disabled';
	$editor = <<<EOT
<textarea name="$instance" rows="$rows" style="width: $cols;" class="form-control" $req $dis>$value</textarea>
EOT;
	if (empty($variable))
		return $editor;
	else
		$VARS[$variable] = $editor;
}

// 將Form File移到指定位置, 配合database data
function MoveUpload($field, $dest, $id, $postfix = '')
{
	if (is_uploaded_file($_FILES[$field]['tmp_name']))
	{
		$ext = strrchr($_FILES[$field]['name'], '.');
		if ($postfix == '')
			$file = $id . $ext;
		else
			$file = $id . '_' . $postfix . $ext;
		$fn = DOC_ROOT . "$dest/$file";
		if (file_exists($fn))
			unlink($fn);
		if (!is_dir(DOC_ROOT . $dest))
			MakeDir(DOC_ROOT . $dest);
		move_uploaded_file($_FILES[$field]['tmp_name'], $fn);
		chmod($fn, 0666);
		return str_replace('//', '/', "/$dest/$file");
	}
	else
		return '';
}

// 僅將Form File移到指定位置, 與database無關
function MoveUpload2($field, $dest)
{
	if (is_uploaded_file($_FILES[$field]['tmp_name']))
	{
		$ext = strrchr($_FILES[$field]['name'], '.');
		$fn = DOC_ROOT . $dest . $ext;
		if (file_exists($fn))
			unlink($fn);
		move_uploaded_file($_FILES[$field]['tmp_name'], $fn);
		return str_replace('//', '/', $dest . $ext);
	}
	else
		return '';
}

// 生成image file
function MakePicture($field, $varname, $dir, $filename = '', $div = null, $watermark = false)
{
	if (!is_uploaded_file($_FILES[$field]['tmp_name']))
		return '';
	$name = $_FILES[$field]['name'];
	$src = $_FILES[$field]['tmp_name'];
	$ext = strrchr($name, '.');
	$file = strtolower(basename($name, $ext));
	$ext = strtolower($ext);
	if (empty($filename))
		$pic = $file . $ext;
	else
		$pic = $filename . $ext;
	if (!file_exists(DOC_ROOT . $dir))
		MakeDir(DOC_ROOT . $dir);
	$dest = DOC_ROOT . "$dir/$pic";
	if (file_exists($dest))
		unlink($dest);
	if ($ext == '.swf' || $ext == '.gif')
	{ // Flash or gif
		copy($src, $dest);
	}
	else
	{
		if (is_array($div))
		{
			$div_width = $div[0];
			$div_height = $div[1];
		}
		else
		{
			$div_width = IMAGE_WIDTH;
			$div_height = IMAGE_HEIGHT;
		}
		$size = getimagesize($src);
		if ($div_width >= $size[0] && $div_height >= $size[1])
		{
			$div_width = $size[0];
			$div_height = $size[1];
		}
//			copy($src, $dest);
//		else
		{
			$div = $div_width / $size[0];
			if (floor($size[1] * $div) > $div_height)
				$div = $div_height / $size[1];
			if ($div > 1)
			{
				$width = $size[0];
				$height = $size[1];
			}
			else
			{
				$width = floor($size[0] * $div);
				$height = floor($size[1] * $div);
			}
			switch ($ext)
			{
			case '.jpg':
			case '.jpeg':
				$is = imagecreatefromjpeg($src);
				break;
			case '.gif':
				$is = imagecreatefromgif($src);
				break;
			case '.png':
				$is = imagecreatefrompng($src);
				break;
			}
			if ($ext == '.gif')
			{
				$tp = imagecolorat($is, 0, 0);
				$id = imagecreate($width, $height);
				imagepalettecopy($id, $is);
	//			$bg = imagecolorallocate($id, 255, 255, 255);
	//			imagefilledrectangle($id, 0, 0, $width, $height, $bg);
				imagefilledrectangle($id, 0, 0, $width, $height, $tp);
				imagecopyresized($id, $is, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
				imagecolortransparent($id, $tp);
			}
			else
			{
				$id = imagecreatetruecolor($width, $height);
				imagecopyresampled($id, $is, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
			}
			if ($watermark)
			{
				imagealphablending($id, true);
				$f = DOC_ROOT . 'watermark.png';
				$size = getimagesize($f);
				$wm = imagecreatefrompng($f);
				$w = floor($width / 2);
				$h = floor($size[1] * $w / $size[0]);
				$x = floor(($width - $w) / 2);
				$y = floor((3 * $height -  2 * $h) / 4); // 3/4 的位置
				imagecopyresampled($id, $wm, $x, $y, 0, 0, $w, $h, $size[0], $size[1]);
			}
			switch ($ext)
			{
			case '.jpg':
			case '.jpeg':
				imagejpeg($id, $dest, 95);
				break;
			case '.gif':
				imagegif($id, $dest);
				break;
			case '.png':
				imagepng($id, $dest);
				break;
			}
		}
	}
	chmod($dest, 0666);
  $fn = str_replace('//', '/', "$dir/$pic");
  if (substr($fn, 0, 1) == '/')
    $fn = substr($fn, 1);
	$_POST[$varname] = $fn;
	return $_POST[$varname];
}

// 生成縮圖檔, 配合MakePicture
function MakeThumb($dir, $src, $varname = 'thumb', $postfix = 's', $div = null)
{
	$src = DOC_ROOT . $src;
	if (!file_exists($src))
		return '';
	$ext = strrchr($src, '.');
	$file = strtolower(basename($src, $ext));
	$ext = strtolower($ext);
	$thumb = $file . $postfix . $ext;
	if (!file_exists(DOC_ROOT . $dir))
		MakeDir(DOC_ROOT . $dir);
	$dest = DOC_ROOT . "$dir/$thumb";
	if (file_exists($dest))
		unlink($dest);
	if ($ext == '.swf')
	{ // Flash
		copy($src, $dest);
	}
	else
	{
		if (is_array($div))
		{
			$div_width = $div[0];
			$div_height = $div[1];
		}
		else
		{
			$div_width = THUMB_WIDTH;
			$div_height = THUMB_HEIGHT;
		}
		$size = getimagesize($src);
		if ($div_width >= $size[0] && $div_height >= $size[1])
			copy($src, $dest);
		else
		{
			$div = $div_width / $size[0];
			if (floor($size[1] * $div) > $div_height)
				$div = $div_height / $size[1];
			if ($div > 1)
			{
				$width = $size[0];
				$height = $size[1];
			}
			else
			{
				$width = floor($size[0] * $div);
				$height = floor($size[1] * $div);
			}
			switch ($ext)
			{
			case '.jpg':
			case '.jpeg':
				$is = imagecreatefromjpeg($src);
				break;
			case '.gif':
				$is = imagecreatefromgif($src);
				break;
			case '.png':
				$is = imagecreatefrompng($src);
				break;
			}
			if ($ext == '.gif')
			{
				$tp = imagecolorat($is, 0, 0);
				$id = imagecreate($width, $height);
				imagepalettecopy($id, $is);
		//		$bg = imagecolorallocate($id, 255, 255, 255);
		//		imagefilledrectangle($id, 0, 0, $width, $height, $bg);
				imagefilledrectangle($id, 0, 0, $width, $height, $tp);
				imagecopyresized($id, $is, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
				imagecolortransparent($id, $tp);
			}
			else
			{
				$id = imagecreatetruecolor($width, $height);
				imagecopyresampled($id, $is, 0, 0, 0, 0, $width, $height, $size[0], $size[1]);
			}
			switch ($ext)
			{
			case '.jpg':
			case '.jpeg':
				imagejpeg($id, $dest, 95);
				break;
			case '.gif':
				imagegif($id, $dest);
				break;
			case '.png':
				imagepng($id, $dest);
				break;
			}
		}
	}
	chmod($dest, 0666);
  $fn = str_replace('//', '/', "$dir/$thumb");
  if (substr($fn, 0, 1) == '/')
    $fn = substr($fn, 1);
  $_POST[$varname] = $fn;
	return $_POST[$varname];
}

function NullDateTime($s)
{
	return substr($s, 0 , 4) == '0000';
}

function NullDateTimeFormat($s)
{
  return NullDateTime($s) ? '' : $s;
}

// 寄送信件, 可依語系修改(UTF-8)
function SendMail($from, $to, $subject, $content)
{
	require_once('htmlMimeMail.php');
	$mail = new htmlMimeMail();
	$mail->setHeadCharset('UTF-8');
	$mail->setTextCharset('UTF-8');
	$mail->setHtmlCharset('UTF-8');
//	$mail->setReturnPath($from);
	$mail->setFrom($from);
	$mail->setSubject($subject);
	$mail->setHtml($content);
	if (strstr($_SERVER['HTTP_HOST'], 'howto'))
	{
		$mail->setSMTPParams('192.168.0.1');
		$mail->send(array($to), 'smtp');
	}
	else
		$mail->send(array($to));
	unset($mail);
}

// 寄送信件, 信件內容由檔案讀入, 可使用SmartTemplate, 變數由$var傳入
function SendMail3($from, $to, $subject, $file, $var = null)
{
	$tt = new SmartTemplate($file);
	foreach($var as $k => $v)
		$tt->assign($k, $v);
	$tt->assign('web_url', $_SERVER['HTTP_HOST']);
	$content = $tt->result();
	unset($tt);
	require_once('htmlMimeMail.php');
	$mail = new htmlMimeMail();
	$mail->setHeadCharset('Big5');
	$mail->setTextCharset('Big5');
	$mail->setHtmlCharset('Big5');
	$mail->setFrom($from);
	$mail->setSubject(mb_convert_encoding($subject, 'big5', 'utf-8'));
//	$mail->setSubject($subject);
//	$mail->setHtml($content);
	$mail->setHtml(mb_convert_encoding($content, 'big5', 'utf-8'));
	if (strstr($_SERVER['HTTP_HOST'], 'howto'))
	{
		$mail->setSMTPParams('192.168.0.1');
		$mail->send(array($to), 'smtp');
	}
	else
		$mail->send(array($to));
	unset($mail);
}

// 寄送郵件, 以SMTP方式寄出, 少用
function SendMail2($from, $to, $subject, $content, $var = null)
{
	require_once('htmlMimeMail.php');
	if (!is_null($var))
		foreach($var as $k => $v)
			$content = str_replace('%' . $k . '%', $v, $content);
	$content = str_replace('%web_url%', $_SERVER['HTTP_HOST'], $content);
	$mail = new htmlMimeMail();
	$mail->setSMTPParams(GetConfig(4));
	$mail->setHeadCharset('Big5');
	$mail->setTextCharset('Big5');
	$mail->setHtmlCharset('Big5');
//	$mail->setReturnPath($from);
	$mail->setFrom($from);
	$mail->setSubject($subject);
	$mail->setHtml($content);
	if (count($to) > 1)
	{
		$too = array_shift($to);
		$mail->setBcc(implode(';', $to));
	}
	else
		$too = $to[0];
	$mail->send(array($too));
	unset($mail);
}

// 幾乎不用
function MailTo($from, $to, $id, $var)
{
	global $EMAIL_SUBJECT;
	require_once('htmlMimeMail.php');

	$content = GetConfig($id);
	foreach($var as $k => $v)
		$content = str_replace('%' . $k . '%', $v, $content);
	$content = str_replace('%web_url%', $_SERVER['HTTP_HOST'], $content);
	$mail = new htmlMimeMail();
	$mail->setSMTPParams(GetConfig(4));
	$mail->setHeadCharset('Big5');
	$mail->setTextCharset('Big5');
	$mail->setHtmlCharset('Big5');
//	$mail->setReturnPath($from);
	$mail->setFrom($from);
	$mail->setSubject($EMAIL_SUBJECT[$id]);
	$mail->setHtml($content);
	$mail->send(array($to));
	unset($mail);
}

function FillSpace($a)
{
	global $VARS;

	if (!is_array($a))
		$a = array($a);
	foreach ($a as $v)
		if ($VARS[$v] == '')
			$VARS[$v] = '&nbsp;';
}

function CarriageReturn($a)
{
	global $VARS;

	if (!is_array($a))
		$a = array($a);
	foreach ($a as $v)
	{
		if (trim($VARS[$v]) != '')
			$VARS[$v] = nl2br($VARS[$v]);
		else
			$VARS[$v] = '&nbsp;';
	}
}

// 顯示圖片, $s為檔名, 傳入後會判斷屬於哪種檔案回傳正確格式, 如img or flash
// $default: 如果找不到檔案則以$default為預設圖片
function ShowPicture($s, $height = 60, $default = 'images/nopic.jpg')
{
	if ($s == '' || (substr($s, 0, 5) != 'imgs/' && substr($s, 0, 6) != '/imgs/' && substr($s, 0, 8) != '/product') || !file_exists(DOC_ROOT . $s))
	{
		if (ADMIN)
			return "<img src=\"../$default\" height=$height>";
		else
			return '&nbsp;';
	}
	$ext = strrchr($s, '.');
	if ($ext != '.swf')
	{
		if (ADMIN)
			return "<img src=\"../$s?" . time() . "\" border=0 height=$height>";
		else
			return "<img src=\"$s\" border=0 height=$height>";
	}
	else
	{
		$r = getimagesize(DOC_ROOT . $s);
		return <<<EOT
<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="{$r[0]}" height="{$r[1]}" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0">
	<param name="movie" value="$s">
	<param name="quality" value="high">
	<embed src="$s" quality="high" width="{$r[0]}" height="{$r[1]}" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash"></embed>
</object>
EOT;
	}
}

// 不用
// function TransPicture(&$var, $field)
// {
// 	if (!is_array($var))
// 		return;
// 	if (!is_array($field))
// 		$field = array($field);
// 	foreach ($var as $k => $v)
// 	{
// 		if (is_array($v))
// 		{ // two level
// 			foreach ($v as $kk => $vv)
// 			{
// 				if (in_array($kk, $field))
// 					$var[$k][$kk] = ShowPicture($vv);
// 			}
// 		}
// 		else
// 		{
// 			if (in_array($k, $field))
// 				$var[$k] = ShowPicture($v);
// 		}
// 	}
// }

// 刪除table裡fields所指定欄位的檔案
function DeleteFiles($table, $id, $fields = array('picture'), $editor = false, $key = 'id')
{
	global $rs3;

	if (!is_array($fields))
		$fields = array($fields);
	$rs3->select($table, $id, '*', $key);
	if ($rs3->count == 0)
		return;
	$r = $rs3->fetch();
	foreach ($fields as $f)
	{
		if (!empty($r[$f]) && file_exists(DOC_ROOT . $r[$f]))
			unlink(DOC_ROOT . $r[$f]);
		$v[$f] = '';
	}
	$rs3->update($table, $id, $v);
	// TODO: Delete files in editor
}

// 移動排序位置
function RankExchange($table, $dir, $rank, $id, $cond = '1')
{
	$rs = new ResultSet();

	if ($dir == 1)
		$sql = "SELECT id, rank FROM $table WHERE $cond AND rank < $rank ORDER BY rank DESC LIMIT 1";
	else
		$sql = "SELECT id, rank FROM $table WHERE $cond AND rank > $rank ORDER BY rank LIMIT 1";
	$rs->query($sql);
	list ($i, $r) = $rs->row();
	$rs->update($table, $i, array('rank' => $rank));
	$rs->update($table, $id, array('rank' => $r));
	unset($rs);
}

// 後兩者多使用在狀態部分, 如avail(tinyint), 1為on, 0為off, 會自動生成JS去勾選checkbox
function SetBit($fields)
{
	if (!is_array($fields))
		$fields = array($fields);
	foreach ($fields as $f)
		if (empty($_POST[$f]))
			$_POST[$f] = '0';
}

function GetBit($fields)
{
	global $SCRIPTS, $VARS;

	if (!is_array($fields))
		$fields = array($fields);
	foreach ($fields as $f)
  {
		$SCRIPTS[] = "\$(\"#{$f}[value='{$VARS[$f]}']\").prop('checked', true);";
  }
}

function GenSelect($name, $var, $first = true, $required = false)
{
  $required = $required ? ' required' : '';
	$s[] = "<div class=\"ui-select\"><select name='$name' id='$name' $required>";
	if ($first)
		$s[] = "<option value=''></option>";
	if (is_array($var))
	{
		foreach ($var as $kk => $vv)
			$s[] = "<option value=\"$kk\">$vv</option>";
	}
	$s[] = '</select></div>';
	return implode($s);
}

function GenSelectBySQL($name, $sql, $first = true, $required = false)
{
	$rs = new ResultSet();
	$rs->query($sql);
	while(($r = $rs->fetch()))
		$v[$r['id']] = $r['data'];
	return GenSelect($name, isset($v) ? $v : null, $first, $required);
}

function GenRadio($name, $var, $first = true, $required = false)
{
	$required = $required ? ' required' : '';
	if (is_array($var))
	{
		foreach ($var as $kk => $vv)
			$s[] = <<<EOT
<label class="radio">
<input type="radio" name="$name" id="{$name}__{$kk}" value="$kk" $required>
$vv
</label>
EOT;
	}
	if (is_array($s) && $first)
		$s[0] = str_replace('<input', '<input checked', $s[0]);
	return implode(' ', is_array($s) ? $s : array());
}

function GenRadioBySQL($name, $sql, $first = true, $required = false)
{
	$rs = new ResultSet();
	$rs->query($sql);
	while(($r = $rs->fetch()))
		$v[$r['id']] = $r['data'];
	return GenRadio($name, isset($v) ? $v : null, $first, $required);
}

function GenCheckbox($name, $var)
{
	if (is_array($var))
	{
		foreach ($var as $kk => $vv)
			$s[] = <<<EOT
<label class="checkbox-inline">
<input name="{$name}[]" type="checkbox" id="{$name}__{$kk}" value="$kk">
$vv
</label>
EOT;
	}
	return implode(' ', is_array($s) ? $s : array());
}

function GenCheckboxBySQL($name, $sql)
{
	$rs = new ResultSet();
	$rs->query($sql);
	while(($r = $rs->fetch()))
		$v[$r['id']] = $r['data'];
	return GenCheckbox($name, isset($v) ? $v : null);
}

function GenFilter($name, $var)
{
  global $VARS;

  if (is_array($var))
  {
    foreach ($var as $k => $v)
      $vv[] = array ('value' => $k, 'text' => $v);
    $var = $vv;
  }

  $VARS[$name . '_list'] = is_array($var) ? $var : array();
  array_unshift($VARS[$name . '_list'], array ('value' => '-9999', 'text' => '--'));
}

function GenFilterBySQL($name, $sql)
{
	global $VARS;

	$rs = new ResultSet();
	$rs->query($sql);
	AssignValues($rs, $name);
	$v['value'] = -9999;
	$v['text'] = '--';
  if (!isset($VARS[$name . '_list']))
    $VARS[$name . '_list'] = array();
	array_unshift($VARS[$name . '_list'], $v);
}

function GenPassword($key, $password)
{
	return md5(substr(sha1($key), 0, 16) . $password);
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

function GenYearList($name = 'year', $from = 2007, $to = 0)
{
	global $VARS;

	if ($to == 0)
		$to = date('Y');
	for ($i = $from; $i <= $to; $i++)
	{
		$v['year'] = $i;
		$vv[] = $v;
	}
	$VARS[$name . '_list'] = $vv;
}

function SplitArray($a, $s)
{
	foreach ($a as $v)
		$arr[] = $v[$s];
	return $arr;
}

function encrypt($encrypt, $salt = '')
{
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
	$passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, SITE_ENC_KEY . $salt, $encrypt, MCRYPT_MODE_ECB, $iv);
	$encrypted = base64_encode($passcrypt);
	return trim($encrypted);
}

function decrypt($decrypt, $salt = '')
{
	$decoded = base64_decode($decrypt);
	$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND);
	$decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, SITE_ENC_KEY . $salt, $decoded, MCRYPT_MODE_ECB, $iv);
	return trim($decrypted);
}

//
function myRand($min, $max)
{
	return intval(rand($min * 100, $max * 100 + $max - 1) / 100);
}

function GetAlpha()
{
	$r = myRand(1, 52);
	if ($r > 26) // 大寫
		return chr(ord('A') + $r - 26 - 1);
	else
		return chr(ord('a') + $r - 1);
}

function GetDigit()
{
	$r = myRand(0, 9);
	return chr(ord('0') + $r);
}

function makePassword($n = 12)
{
	$s = '';
	for ($i = 0; $i < $n; $i++)
	{
		$z = myRand(566, 688);
		$s .= myRand(0, 999) < $z ? GetAlpha() : GetDigit();
	}
	return $s;
}

function _getInitialNumber($digits)
{
	if ($digits < 3)
		return 0;
	$n = pow(10, $digits - 2);
	$nn = pow(10, $digits - 3);
	$nn = rand($nn, $n - 1);
	return $n + $nn;
}

function getSerialNumber($prefix, $digits = 6, $ym = null)
{
	$rs = new ResultSet();

	$v['prefix'] = $prefix;
	if (empty($ym))
		$ym = date('ym');
	$v['ym'] = $ym;
	$ax = $v['ax'] = floor(rand(SN_MIN * 10, (SN_MAX + 1) * 10 - 1) / 10);
	$rs->lock('sn WRITE');
	$rs->insert('sn', $v);
	$id = $rs->last_id;
	$sql = "SELECT * FROM sn WHERE prefix = '$prefix' AND id < $id";
	$rs->query($sql);
	if ($rs->count != 0)
	{
		$r = $rs->fetch();
		if ($r['ym'] == $v['ym'])
		{
			$v['ax'] += $r['ax'];
			$ax = $v['ax'];
			$rs->update('sn', $id, $v);
		}
		else
		{ // 不同ym, 取init
			$ax = $v['ax'] = _getInitialNumber($digits);
			$rs->update('sn', $id, $v);
		}
		$sql = "DELETE FROM sn WHERE prefix = '$prefix' AND id < $id";
		$rs->execute($sql);
	}
	else // 裡面都沒有的話~
	{
		$ax = $v['ax'] = _getInitialNumber($digits);
		$rs->update('sn', $id, $v);
	}
	$rs->unlock();
	$f = "%0{$digits}d";
	$sn = $ym . sprintf($f, $ax);
	$s = 1;
	for ($i = 0; $i < strlen($sn); $i++)
	{
		$c = substr($sn, $i, 1);
		if (is_numeric($c))
			$s *= (intval($c) == 0 ? 1 : intval($c) + 1);
		else
			$s *= (ord($c) - ord('A') + 13);
		$s %= 199;
	}
	return $sn . ($s % 10);
}

function getSerialNumber2($prefix, $digits = 6)
{
	$rs = new ResultSet();

	$v['prefix'] = $prefix;
	$ym = 0;
	$v['ym'] = $ym;
	$ax = $v['ax'] = 1;
	$rs->lock('sn WRITE');
	$rs->insert('sn', $v);
	$id = $rs->last_id;
	$sql = "SELECT * FROM sn WHERE prefix = '$prefix' AND id < $id";
	$rs->query($sql);
	if ($rs->count != 0)
	{
		$r = $rs->fetch();
		$v['ax'] += $r['ax'];
		$ax = $v['ax'];
		$rs->update('sn', $id, $v);
		$sql = "DELETE FROM sn WHERE prefix = '$prefix' AND id < $id";
		$rs->execute($sql);
	}
	else // 裡面都沒有的話~
	{
		$ax = $v['ax'] = 1;
		$rs->update('sn', $id, $v);
	}
	$rs->unlock();
	$f = "%0{$digits}d";
	$sn = sprintf($f, $ax);
	return $sn;
}

function error404()
{
  header("HTTP/1.0 404 Not Found");
  exit;
}

function json_response($arr = null, $status = true)
{
  $data['status'] = $status;
  $data['data'] = $arr;
  echo json_encode($data);
  exit;
}

function validate_email($e)
{
  $r = <<<EOT
/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
EOT;
  return preg_match($r, $e);
}

function errorlog($msg)
{
  error_log($msg, 3, DOC_ROOT . 'imgs/error_log');
}

function genView($file, $var)
{
  $tpl = new Rain\Tpl;
  $tpl->assign($var);
  return $tpl->draw($file, true);
}

function lookup($table, $id, $value, $key = 'id')
{
  global $rs3;

  $sql = "SELECT `$value` FROM `$table` WHERE `$key` = $id";
  $rs3->query($sql);
  return $rs3->count > 0 ? $rs3->record() : '';
}

function changemode($url, $mode)
{
  $u = explode('?', $url);
  $p = empty($u[1]) ? '?x' : '?' . $u[1];
  $uu = explode('/', $u[0]);
  $uu[1] = $mode;
  return implode('/', $uu) . $p;
}

/*
//  url like: http://stackoverflow.com/questions/2820723/how-to-get-base-url-with-php

echo base_url();    //  will produce something like: http://stackoverflow.com/questions/2820723/
echo base_url(TRUE);    //  will produce something like: http://stackoverflow.com/
echo base_url(TRUE, TRUE); || echo base_url(NULL, TRUE);    //  will produce something like: http://stackoverflow.com/questions/
//  and finally
echo base_url(NULL, NULL, TRUE);
//  will produce something like:
//      array(3) {
//          ["scheme"]=>
//          string(4) "http"
//          ["host"]=>
//          string(12) "stackoverflow.com"
//          ["path"]=>
//          string(35) "/questions/2820723/"
//      }
*/
function base_url($atRoot=FALSE, $atCore=FALSE, $parse=FALSE){
  if (isset($_SERVER['HTTP_HOST'])) {
    $http = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
    $hostname = $_SERVER['HTTP_HOST'];
    $dir =  str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

    $core = preg_split('@/@', str_replace($_SERVER['DOCUMENT_ROOT'], '', realpath(dirname(dirname(__FILE__)))), NULL, PREG_SPLIT_NO_EMPTY);
    $core = $core[0];

    $tmplt = $atRoot ? ($atCore ? "%s://%s/%s/" : "%s://%s/") : ($atCore ? "%s://%s/%s/" : "%s://%s%s");
    $end = $atRoot ? ($atCore ? $core : $hostname) : ($atCore ? $core : $dir);
    $base_url = sprintf( $tmplt, $http, $hostname, $end );
  }
  else $base_url = 'http://localhost/';

  if (substr($base_url, -2) == '//')
    $base_url = substr($base_url, 0, -1);

  if ($parse) {
    $base_url = parse_url($base_url);
    if (isset($base_url['path'])) if ($base_url['path'] == '/') $base_url['path'] = '';
  }

  return $base_url;
}

function json_output($arr)
{
	echo json_encode($arr);
	exit;
}

function getPeriod($date, $period)
{
	$dt = strtotime($date);

	$d = date('d', $dt) - 1;
	$m = date('m', $dt) + $period;
	$y = date('y', $dt);
	if ($d == 0)
	{ // 上個月最後一天
		$date2 = date('Y-m-d', mktime(0, 0, 0, $m, 0, $y));
	}
	else
	{
		$date2 = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
		$date3 = date('Y-m-d', mktime(0, 0, 0, $m, $d - 1, $y));
		if (date('m', $date2) != date('m', $date3))
			$date2 = date('Y-m-d', mktime(0, 0, 0, $m, 0, $y));
	}

	return $date2;
}

function cronjob_call($fn, $params)
{
  $url = CRONJOB_URL . $fn . '?' . $params;
//  echo $url;
//  exit;
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $data = curl_exec($ch);

  if(curl_errno($ch) > 0)
    die(json_encode((object) array('code' => 0, 'msg' => curl_errno($ch) . '系統發生錯誤')));
  curl_close($ch);

  return $data;
}

// globals..
$VARS['current_timestamp'] = time();
?>