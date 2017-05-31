<?php
define('PAGE_COUNT_VAR', 'ITEMS_PER_PAGE');
require('../../lib/config.php');
require('../../lib/db.php');

$rs = new ResultSet();
$rs2 = new ResultSet();
$rs3 = new ResultSet();

define ('BEGIN_TIME', $rs->get_value('config','content',8));
define ('END_TIME',   $rs->get_value('config','content',9));

require('../../lib/const.php');
require('../../lib/functions.php');
require('../../lib/etalk.php');

define ('SITE_MAIL', GetConfig(1));
define ('MAIL_FROM', GetConfig(2));

?>