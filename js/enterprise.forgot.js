$(function(){
	
	$('.submit').click(function(){

		if( $('.fill').find('input[name=account]').val() =='' ){
			$('.login_error .radius').text("Enter your ID");
			$('.login_error').show();
			return false;
		}
/*
		if( $('[name=g-recaptcha-response]').val().length == 0 ){
			dialog_student('Please check "I\'m not a robot".');
			return false;
		}
*/
		post( '/enterprise/forgot', $('.fill') , function(data){				
				
				if(data.code==1){
					$('.login_error').hide();
					$('.login_success .success').text( data.msg );
					$('.login_success').show();
					$('.submit').hide();
				}else if(data.code==2){
					grecaptcha.reset();
					$('.login_error .radius').text( data.msg );
					$('.login_error').show();
				}else{
					$('.login_error .radius').text( data.msg );
					$('.login_error').show();
				}				
		});
	});
});