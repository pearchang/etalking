$(function(){
	
	$('.submit').click(function(){
		
		var pass = $('input[name=passwd]').val();
		var pass2 = $('input[name=passwd2]').val();		
				
		if( pass != "" || pass2!="" ){		
			if( pass != pass2 ){
				dialog("兩次密碼不相符!");
				return false;
			}			
			if( !pass.match(/.{6,20}/) || !pass.match(/[0-9]/) || !pass.match(/[A-z]/) ){
				dialog("密碼不符合規範!<br>請輸入6~20英數字區分大小寫");
				return false;
			}
		}

		if( $('input[name=adm_name]').val()==''	){
			dialog("請輸入管理者姓名");
			return false;
		}
		
		if( $('input[name=adm_email]').val()==''){
			dialog("請輸入管理者E-mail");
			return false;
		}
		
		RE = /^(|[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+)$/;
		if( !RE.test( $('input[name=adm_email]').val() ) ){
			dialog("E-mail格式錯誤");
			return false;
		}
		
		if( $('input[name=adm_tel]').val()==''){
			dialog("請輸入管理者電話");
			return false;
		}		
		
		if( $('input[name=adm_tel_ext]').val()==''){
			dialog("請輸入管理者分機");
			return false;
		}
		
		post( '/enterprise/account_save', $('.fill') , function(data){
				dialog( data.msg );				
		});


	});	
});