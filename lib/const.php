<?php
if (get_magic_quotes_gpc()) {
  $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
  while (list($key, $val) = each($process)) {
    foreach ($val as $k => $v) {
      unset($process[$key][$k]);
      if (is_array($v)) {
        $process[$key][stripslashes($k)] = $v;
        $process[] = &$process[$key][stripslashes($k)];
      } else {
        $process[$key][stripslashes($k)] = stripslashes($v);
      }
    }
  }
  unset($process);
}

define ('DOC_ROOT', dirname(dirname(__FILE__)) . '/');

if (!empty($_SERVER['DOCUMENT_ROOT']))
{
	session_start();
	// 網站目錄位置
	$_SESSION['DOC_ROOT'] = DOC_ROOT;
}

// Unicode適用
mb_internal_encoding('UTF-8');
// HTML樣版位置
define ('TPL_PATH', DOC_ROOT . PREFIX . 'template/');
// 程式位置
define ('APP_PATH', DOC_ROOT . PREFIX . 'app/');
define ('LIB_PATH', DOC_ROOT . 'lib/');
// 圖片暫存位置
define ('TEMP_IMAGE', '/tmpimg/');
define ('IMAGE_PATH', stripcslashes(DOC_ROOT . TEMP_IMAGE));
// SmartTemplate用
// $_CONFIG['base_url'] = null;
$_CONFIG['tpl_dir'] = TPL_PATH;
$_CONFIG['cache_dir'] = DOC_ROOT . '_cache/' . PREFIX;
$_CONFIG['php_enabled'] = true;
$_CONFIG['auto_escape'] = false;
$_CONFIG['decimal_char'] = '.';
$_CONFIG['thousands_sep'] = ',';
$file_ext = '.html';

//define ('SITE_URL', $_SERVER['HTTP_HOST']);

define ('MSG_INFO', 1);
define ('MSG_OK', 2);
define ('MSG_WARN', 3);
define ('MSG_ERROR', 4);
define ('PAGE_COUNT', constant(PAGE_COUNT_VAR));

define ('EDITOR_TEXT', 1);
define ('EDITOR_HTML', 2);

// 圖片與縮圖預設大小
define ('THUMB_WIDTH', 130);
define ('THUMB_HEIGHT', 130);
define ('IMAGE_WIDTH', 270);
define ('IMAGE_HEIGHT', 270);

define ('PERM_READ', 1);
define ('PERM_WRITE', 2);
define ('PERM_ALL', 3);

define ('FIELD_TEXT', 1);
define ('FIELD_IMAGE', 2);


// TABLE "configs"裡的index
define ('ADMIN_PW', 99);

define ('NO_ESCAPE', true);

define ('SN_MIN', 7);
define ('SN_MAX', 17);

define ('DOC_TYPE_NEWS', 1);
define ('DOC_TYPE_DOCUMENT', 2);
define ('DOC_TYPE_CATEGORY', 3);
define ('DOC_TYPE_BANNER', 4);

define('CRONJOB_URL', 'http://' . $_SERVER["HTTP_HOST"] . '/admin/sys/');

require ('constant.php');

$special_chars = array (',', ' ', '!', '.', '(', ')', '?', '<', '>', ';', ':', '/', '\\', "\'", "'", '"', '@', '#', '&', '$', '%', '^', '*', '+');

$today = date('Y-m-d');
$today_time = strtotime($today);
$current_hour = date('H');
$current_datehour = date('YmdH');
$today_00 = date('Ymd00');
$register_time = strtotime(date('Y-m-d H:00:00')) + 86400 + 3599; // 一小時-1
$cancel_time = strtotime(date('Y-m-d H:00:00')) + (12 * 3600) + 3599; // 一小時-1
$open_time = date('Y-m-d H:i:s', time() + 5 * 60);
$close_time = date('Y-m-d H:i:s', time() - 45 * 60);
$consultant_open_time = date('Y-m-d H:i:s', time() + 10 * 60);
$current_time = date('Y-m-d H:i:s');
// open_time <= $open_time & open_time <= $close_time
$demo_open_time = date('Y-m-d H:i:s', time() + 60 * 60);
$demo_close_time = date('Y-m-d H:i:s', time() - 120 * 60);
//-------------------------------------------------------------
use Rain\Tpl;
require('Rain/autoload.php');
require('class.mail.php');
require('Rain/Tpl/Plugin/CustomParser.php');

Tpl::configure($_CONFIG);
Tpl::registerPlugin( new Rain\Tpl\Plugin\CustomParser() );

// 取得IP
if (!empty($_SERVER['HTTP_CLIENT_IP']))
	$ip = $_SERVER['HTTP_CLIENT_IP'];
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
	$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
else
	$ip = $_SERVER['REMOTE_ADDR'];
define('REMOTE_ADDR', $ip);

// host
$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5)) == 'https' ? 'https' : 'http';
if ($_SERVER['HTTP_HOST'] == "etalking.freelancer.tw")
  define ('SERVER_HOST', $protocol . '://etalk.freelancer.tw/');
else
  define ('SERVER_HOST', $protocol . '://' . $_SERVER['HTTP_HOST'] . '/');

?>
