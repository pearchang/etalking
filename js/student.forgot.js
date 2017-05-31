$(function(){
	
	$('.submit').click(function(){

		if( $('.fill').find('input[name=account]').val() =='' ){
			$('.login_error .alert').text("請輸入ID");
			$('.login_error').show();
			return false;
		}
/*
		if( $('[name=g-recaptcha-response]').val().length == 0 ){
			dialog_student('Please check "I\'m not a robot".');
			return false;
		}
*/
		post( '/member/forgot', $('.fill') , function(data){				
				$('.login_error .alert').text( data.msg );
				$('.login_error').show();
				if(data.code==1) $('.submit').hide();
				if(data.code==2) grecaptcha.reset();				
		});
	});
});