<?php
// auth
if (!in_array($FUNC, array('index', 'login', 'go', 'logout', 'etalking_function')))
  require(APP_PATH . '/auth.inc.php');

require ('log.inc.php');
// get config
define ('SITE_MAIL', GetConfig(1));
define ('MAIL_FROM', GetConfig(2));

?>