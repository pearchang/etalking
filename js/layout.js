$(function(){
	
	$('.login_error').hide();
	$('.login_item').click(function(){
		grecaptcha.reset();
	});

	$('.form-time a').on('click', function(event) {
		var id = $(this).data('id');
		$('.free_experience').find('[name=contact_time]').val(id);
	});
	
	$('.sex-swich').on('click', function(event) {
			if($(this).hasClass('male')){
				$('.free_experience').find('[name=gender]').val(1);
			}else{
				$('.free_experience').find('[name=gender]').val(2);
			}
	});
	
	$('.login_btn').click(function(){	
	
		if( $('.login_form').find('[name=account]').val()=='' ){
			$('.login_error .alert').text("請輸入身分證字號或護照號碼");
			$('.login_error').show();
			return false;
		}		
		if( $('.login_form').find('[name=password]').val()=='' ){
			$('.login_error .alert').text("請輸入密碼");
			$('.login_error').show();
			return false;
		}
		if(recaptcha && 
			$('.login_form').find('[name=g-recaptcha-response]').val().length == 0 ){
			$('.login_error .alert').text("請點選我不是機器人");
			$('.login_error').show();
			return false;
		}

		post('member/login', $('.login_form'), function(data){
			if(data.code==1)
				document.location = '/student/booking';
			else{
				$('.login_error .alert').text( data.msg );
				$('.login_error').show();
				if(data.code==2) grecaptcha.reset();
			}
		} ,true );
		
		return false;
	});
	
	$('.free_experience').submit(function(){
		
		if( $(this).find('[name=guest_name]').val()=='' ){
			dialog("請輸入姓名");
			return false;
		}		
		if( $(this).find('[name=tel]').val()=='' ){
			dialog("請輸入電話");
			return false;
		}
		
		RE = /^09[0-9]{8}$/;
		if( !RE.test($(this).find('[name=tel]').val()) ){
			dialog("請輸入手機號碼共10位數字");
			return false;
		}
		
		if( $(this).find('[name=email]').val()=='' ){
			dialog("請輸入Email");
			return false;
		}		
		RE = /^(|[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+)$/;
		if( !RE.test( $(this).find('[name=email]').val() ) ){
			dialog("電子郵件錯誤");
			return false;
		}		
		if( $(this).find('[name=contact_time]').val()=='' ){
			dialog("請選擇聯絡時間");
			return false;
		}
		
		if( !$(this).find('input[type=checkbox]').prop('checked') ){
			dialog("您必須同意接受個資保護聲明");
			return false;
		}

		post('home/experience', $(this), function(data){
			if(data.code==1)
				dialog("感謝您對ETALKING有興趣做進一步的了解，課程顧問將盡快與您聯絡");
			else
				dialog( data.msg );
		} ,false );
		
		return false;
	});
	
});