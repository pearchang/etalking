$(function(){
	
	$('.login_error').hide();
	//$('.login_success').hide();
	
	$('.forget').click(function(){
		$('input[name=keep]').prop('checked',false);
	});

	$('.login_btn').click(function(){	
	
		if( $('.login_form').find('[name=account]').val()=='' ){
			$('.login_error .alert').text("Please enter your ID number.");
			$('.login_error').show();
			return false;
		}		
		if( $('.login_form').find('[name=password]').val()=='' ){
			$('.login_error .alert').text("Please enter your password.");
			$('.login_error').show();
			return false;
		}
		if($('.login_form').find('[name=g-recaptcha-response]').val().length == 0 ){
			$('.login_error .alert').text('Please check "I\'m not a robot".');
			$('.login_error').show();
			return false;
		}

		post('teacher/login', $('.login_form'), function(data){
			if(data.code==1)
				document.location = '/teacher/booking';
			else{
				$('.login_error .alert').text( data.msg );
				$('.login_error').show();
				if(data.code==2) grecaptcha.reset();
			}
		} ,true );
		
		return false;
	});
	
	
});