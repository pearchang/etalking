<?php
error_reporting(E_ALL & ~E_NOTICE);
//ini_set("display_errors", 1);

if (!defined('ADMIN'))
{
  define('ADMIN', false);
  define('PREFIX', '');
  define('HISTORY', 'lpage');
  define('MSG', 'msg');
  define('MSG_LEVEL', 'level');
  define('ERROR', 'error');
  define('POSTDATA', 'postdata');
  define('PAGE_COUNT_VAR', 'ITEMS_PER_PAGE');
}

$xurl = empty($_GET['xurl']) ? '' : $_GET['xurl'];
$u = explode('/', $xurl);
if (is_array($u))
  foreach ($u as $k => $v)
    $u[$k] = htmlspecialchars($v, ENT_QUOTES, 'UTF-8');

require('lib/config.php');
require('lib/const.php');
require('lib/functions.php');

define('SITE_URL', base_url(true, true));

// 多語
if (ADMIN == false && is_array($LANGUAGES))
{
  $preferred = $var_preferred_lang;
  if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
  {
    $max = 0.0;
    $langs = explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
    foreach ($langs as $lang)
    {
      $lang = explode(';', $lang);
      $q = (isset($lang[1])) ? ((float)$lang[1]) : 1.0;
      if ($q > $max)
      {
        $max = $q;
        $preferred = $lang[0];
      }
    }
    $preferred = trim($preferred);
  }
  $preferred = strtolower($preferred);
  $preferred = isset($var_lang_code[$preferred]) ? $var_lang_code[$preferred] : $var_preferred_lang;
  if (empty($u[0]))
  {
    header('Location: ' . SITE_URL . $preferred . "/");
    exit;
  }

  $LANG = $u[0];
  foreach ($LANGUAGES as $idx => $ln)
  {
    if ($LANG == $ln[0])
    {
      $LANGID = $idx;
      define ('LANGID', $idx);
      $LANGNAME = $ln[1];
      define ('LANGNAME', $ln[1]);
      break;
    }
  }
  if (!defined('LANGID'))
  {
    header('Location: ' . SITE_URL . $preferred . '/not_found?p=' . urlencode($_SERVER['REQUEST_URI']));
    exit;
  }
  define ('LANG', $LANG);
  array_shift($u);
}
// 多語 end

//if (defined('LANG')) // preserved
//  define('BASE_URL', SITE_URL . LANG . '/');
//else
  define('BASE_URL', SITE_URL . PREFIX);

//echo SITE_URL . "\n";
//echo BASE_URL;

$FUNC = empty($u[0]) ? 'index' : $u[0];
define ('FUNC', $FUNC);
$MODE = count($u) > 1 ? $u[1] : '';
define ('MODE', $MODE);
$SUB = count($u) > 2 ? $u[2] : '';
define ('SUB', $SUB);

require('lib/db.php');
include('lib/db2.php');
unset ($_GET['xurl']);

$rs = new ResultSet();
$rs2 = new ResultSet();
$rs3 = new ResultSet();
$mrs = new MultiLangResultSet();
$mrs2 = new MultiLangResultSet();
$mrs3 = new MultiLangResultSet();

switch (FUNC)
{
case 'back':
	GoBack();
	break;
case 'blank':
	exit;
}

$php_file = FUNC . '.php';

if (defined('LANG'))
{
  $pfx = LANG . '/';
  $shift = 2;
}
else
{
  $pfx = '';
  $shift = 1;
}
//echo $pfx;

if (isset($MODE))
{
	if (isset($SUB))
	{
		$tpl_file = "{$pfx}{$FUNC}_{$MODE}_$SUB";
		if (!file_exists(TPL_PATH . $tpl_file . $file_ext))
			$tpl_file = "{$pfx}{$FUNC}_$MODE";
	}
	else
		$tpl_file = "{$pfx}{$FUNC}_$MODE";
	if (!file_exists(TPL_PATH . $tpl_file . $file_ext))
		$tpl_file = $pfx . $FUNC;
}
else
{
	//unset($_SESSION['sys'][POSTDATA]);
	$tpl_file = $pfx . $FUNC;
}

$function_exists = false;
if (file_exists(TPL_PATH . $tpl_file . $file_ext))
	$page = $tpl_file;
if (file_exists(APP_PATH . $php_file))
	$function_exists = true;
elseif (!isset($page) && FUNC != 'not_found')
	$page = 404;
$u = parse_url(BASE_URL);
$VARS['CUR_URL'] = $CUR_URL = $u['path'] != '/' ? str_replace($u['path'], '', $_SERVER['REQUEST_URI']) : substr($_SERVER['REQUEST_URI'], 1);
define ('CUR_URL', $CUR_URL);
$MODE_LIST = array('new', 'add', 'edit', 'modify', 'modify2', 'update', 'delete', 'view', 'move', 'export');
$u = explode('?', $CUR_URL);
$p = empty($u[$shift]) ? '?x' : '?' . $u[$shift];
foreach ($MODE_LIST as $v)
{
	$n = strtoupper($v) . '_URL';
	$VARS[$n] = $pfx . FUNC . '/' . $v . $p;
	define ($n, $VARS[$n]);
}

$pp = HISTORY;
if (!isset($_SESSION['sys'][$pp]) || (isset($_SESSION['sys'][$pp]) && !is_array($_SESSION['sys'][$pp])))
	$_SESSION['sys'][$pp] = array();
if (count($_SESSION['sys'][$pp]) > 0 && $_SESSION['sys'][$pp][count($_SESSION['sys'][$pp]) - 1] != $xurl)
	array_push($_SESSION['sys'][$pp], $xurl);
if (count($_SESSION['sys'][$pp]) > 20)
	array_shift($_SESSION['sys'][$pp]);

if (file_exists(APP_PATH . "global.inc.php"))
  require(APP_PATH . "global.inc.php");
if ($function_exists)
{
	unset($u, $uu, $i, $k, $m, $n, $v);
	require(APP_PATH . $php_file);
}
elseif ($page == 404)
{
  header("Location: " . SITE_URL . $pfx . "not_found?p=" . urlencode($_SERVER['REQUEST_URI']));
  exit;
}
if (file_exists(APP_PATH . "post.inc.php"))
	require(APP_PATH . "post.inc.php");

$version = file_get_contents(DOC_ROOT . 'admin/version.txt');

if (isset($page))
{
  $VARS['LANG'] = $LANG;
  $VARS['LANGID'] = $LANGID;
  $VARS['LANGNAME'] = $LANGNAME;
  $VARS['FUNC'] = $FUNC;
	$VARS['MODE'] = $MODE;
	$VARS['SUB'] = $SUB;
  $VARS['VERSION'] = $version;
	// Variables
	$tpl = new Rain\Tpl;
	if (isset($VARS))
		foreach($VARS as $k => $v)
			$tpl->assign($k, $v);
	// Others
	$tpl->assign('SITE_TITLE', SITE_TITLE);
  $tpl->assign('BASE_URL', BASE_URL);
  $tpl->assign('SERVER_HOST', SERVER_HOST);
	$tpl->draw($page);
}
$mm = MSG;
if (isset($_SESSION['sys'][$mm]) && !empty($_SESSION['sys'][$mm]))
{
  $msg = $_SESSION['sys'][$mm];
	unset($_SESSION['sys'][$mm]);
  $nn = MSG_LEVEL;
  $alerts = array (
  	MSG_INFO => array ('alert alert-info', 'icon-exclamation-sign'),
  	MSG_OK => array ('alert alert-success', 'icon-ok-sign'),
  	MSG_WARN => array ('alert alert-warning', 'icon-warning-sign'),
  	MSG_ERROR => array ('alert alert-danger', 'icon-remove-sign'),
  );
  $i = $alerts[$_SESSION['sys'][$nn]];
  unset($_SESSION['sys'][$nn]);
  $s = <<<EOT
<script language='javascript'>
$(function() {
  var err = $('#global_error');
  if (err.length == 0)
    alert('{$msg}');
  else
  {
    $('#global_error_msg').text('{$msg}');
    $('#global_error_icon').addClass('{$i[1]}');
    $('#global_error').addClass('{$i[0]}').css('display', '');
    $(window).scrollTop(0);
  }
});
</script>
EOT;
  echo $s;
}

$nn = ERROR;
if (isset($_SESSION['sys'][$nn]) && is_array($_SESSION['sys'][$nn]))
{
  foreach ($_SESSION['sys'][$nn] as $f => $m)
  {
    $m = addslashes($m);
    $ss[] = "$('#div_$f').addClass('error').append('<span class=\"alert-msg\"><i class=\"icon-remove-sign\"></i> $m</span>');";
  }
  unset($_SESSION['sys'][$nn]);
  $ss = implode("\n", $ss);
  $s = <<<EOT
<script language='javascript'>
$(function() {
{$ss}
});
</script>
EOT;
  echo $s;
}

if (isset($SCRIPTS) && !empty($SCRIPTS) && is_array($SCRIPTS))
{
	$s = implode("\n", $SCRIPTS);
	$s = <<<EOT
<script language="javascript">
$s
</script>
EOT;
	echo $s;
}
?>