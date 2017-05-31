<?php
	if($_GET['type']=='student'){
		//setcookie('account','', time()-1 );
		setcookie('password','', time()-1 );
		header("Location:/");

	}elseif($_GET['type']=='enterprise'){
		
		setcookie('enterprise_password','', time()-1 );
		header("Location:/enterprise");

	}else{
		//setcookie('teacher_account','', time()-1 );
		setcookie('teacher_password','', time()-1 );
		header("Location:/teacher");
	}
?>