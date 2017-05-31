<?php
	set_time_limit(0);
	ini_set('display_errors', 1);
	require('cron.inc.php');
	
	$now = date('Y-m-d 00:00:00');
	
	$DATA_SQL = <<<EOT
		WHERE ( ( next_time='0000-00-00 00:00:00' AND TIMESTAMPDIFF(HOUR,'$now',cdate) < -72 ) OR
		 ( next_time!='0000-00-00 00:00:00' AND TIMESTAMPDIFF(HOUR,'$now',next_time) < -72 ) )
		 AND member_id in (SELECT id FROM member WHERE status=60)
EOT;

	$sql = "SELECT member_id FROM member_history ".$DATA_SQL." GROUP BY member_id";

	//echo $sql; exit;

	$rs->query($sql);
	
	$i=0;

	while($member = $rs->fetch()){
		
		$id = $member['member_id'];
		
		$r = array();
		
		$v['member_id'] = $id;

		$v['type'] = 50; // 釋出

		$v['content'] = "系統釋出，三天未與客戶聯繫";

		$v['contact_status'] = 8;
		
		$rs2->insert('member_history', $v);

		$sql = "UPDATE member SET status = 70 WHERE id = $id";

		$rs2->query($sql);
		
		$i++;
	}
	
	echo $i.' done';
	
?>