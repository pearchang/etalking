<?php
//include('config.local.php');

if (!isset($DBHOST))
{
  // 資料庫設定
  $DBHOST = 'localhost';
  $DBUSER = 'etalking2016';
  $DBPASS = 'nqYt00!1';
  $DBNAME = 'etalking2016';
}

// Page title
define ('SITE_TITLE', 'eTalking');
// Web Email
//define ('SITE_MAIL', 'tom@begonia.tw');
//define ('MAIL_FROM', 'ETalking');
// 前端每頁資料筆數(default)
define ('ITEMS_PER_PAGE', 20);
// 後端每頁資料筆數(default)
define ('ITEMS_PER_PAGE_ADMIN', 40);

define ('WEBEX_SID', '1007837');
define ('WEBEX_PID', 'JB0Ey05q2bIfwRbitHkTfg');
define ('WEBEX_API_URL', 'https://etalking.webex.com');

// define ('SMTP_RELAY', 'localhost');
// define ('SMTP_USER', SITE_MAIL);
// define ('SMTP_PASS', 'xx');
?>