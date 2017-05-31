$(function(){
	
	$('.login_error').hide();
	//$('.login_success').hide();
	
	$('.forget').click(function(){
		$('input[name=keep]').prop('checked',false);
	});

	$('.login_btn').click(function(){	
	
		if( $('.login_form').find('[name=account]').val()=='' ){
			$('.login_error .alert').text("請輸入帳號");
			$('.login_error').show();
			return false;
		}		
		if( $('.login_form').find('[name=password]').val()=='' ){
			$('.login_error .alert').text("請輸入密碼");
			$('.login_error').show();
			return false;
		}
		if($('.login_form').find('[name=g-recaptcha-response]').val().length == 0 ){
			$('.login_error .alert').text('請勾選我不是機器人');
			$('.login_error').show();
			return false;
		}

		post('enterprise/login', $('.login_form'), function(data){
			if(data.code==1)
				document.location = '/enterprise/employee';
			else{
				$('.login_error .alert').text( data.msg );
				$('.login_error').show();
				if(data.code==2) grecaptcha.reset();
			}
		} ,true );
		
		return false;
	});
	
	
});