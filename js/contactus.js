$(function(){
	
	$('.contact_us').find('.form-time-select li').click(function(){
		var val = $(this).text();
		$('.contact_us').find('[name=contact_time]').val( val );
	});
	
	$('.contactus_submit').click(function(){
		
		if( $('.contact_us').find('[name=full_name]').val()=='' ){
			dialog("請輸入姓名");
			return false;
		}		
		if( $('.contact_us').find('[name=tel]').val()=='' ){
			dialog("請輸入電話");
			return false;
		}
		
		RE = /^09[0-9]{8}$/;
		if( !RE.test( $('.contact_us').find('[name=phonenumber]').val()) ){
			dialog("請輸入手機號碼共10位數字");
			return false;
		}
		
		if( $('.contact_us').find('[name=email]').val()=='' ){
			dialog("請輸入Email");
			return false;
		}
		
		RE = /^(|[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+)$/;
		if( !RE.test( $('.contact_us').find('[name=email]').val() ) ){
			dialog("電子郵件錯誤");
			return false;
		}
		
		if( $('.contact_us').find('[name=contact_time]').val()=='' ){
			dialog("請選擇聯絡時間");
			return false;
		}

		post('home/contact_us', $('.contact_us'), function(data){
			if(data.code==1)
				dialog("我們將盡速與您聯絡");
			else
				dialog( data.msg );
		} ,false );
		
		return false;
	});
	
});