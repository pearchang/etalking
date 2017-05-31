<?php
	ini_set('display_errors', 1);
	include 'webex.php';

    $d["UID"] = "etalking_011"; // WebEx username
    $d["PWD"] = "online888"; // WebEx password
    $d["SID"] = "1007837"; //Demo Site SiteID
    $d["PID"] = "JB0Ey05q2bIfwRbitHkTfg"; //Demo Site PartnerID
	
	$w = new Webex;
	$w->set_auth( $d["UID"], $d["PWD"],$d["SID"], $d["PID"]);
	$w->set_url("https://etalking.webex.com");
	$w->sm();

?>