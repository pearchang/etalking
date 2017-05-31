<?php
	include 'config.php';
	
	$filter = array( 'parent' => $_SESSION['teacher']['id'], 'tag' => $_GET['tag'] );
	$old = $dbconn->fetch_one('consultant_images', $filter, false);
			
	if(isset($old['id'])){
		@unlink( DOCUMENT_ROOT. $old['image'] );
		@unlink( DOCUMENT_ROOT. $old['thumb'] );
	}
	
	$rs = $dbconn->delete('consultant_images', $filter );
	
	if($rs){				
		die(json_encode((object) array('code'=> 1 ,'msg'=> '' )));
	}else{
		die(json_encode((object) array('code'=>0,'msg'=> 'Fail.')));
	}	
?>