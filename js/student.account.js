$(function(){
	
	$('.interest-btn').click(function(){
		var id = $(this).data('id');
		var val = $('input[class=interest][value="'+ id +'"]');
		
		if(val.prop('checked')){
			val.prop('checked',false);
			$(this).removeClass('active');
		}else{
			val.prop('checked',true);
			$(this).addClass('active');
		}
	});
	
	$('.skill-btn').click(function(){
		var id = $(this).data('id');
		var val = $('input[class=skill][value="'+ id +'"]');
		
		if(val.prop('checked')){
			val.prop('checked',false);
			$(this).removeClass('active');
		}else{
			val.prop('checked',true);
			$(this).addClass('active');
		}
	});
	
	$('.submit').click(function(){
		
		if( $('input[class=interest]:checked').length<3 ){
			dialog_student("喜好/興趣請複選至少3項");
			return false;
		}
		if( $('input[class=skill]:checked').length<1 || $('input[class=skill]:checked').length >3 ){
			dialog_student("欲加強的能力請複選1~3項");
			return false;
		}
		
		var pass = $('input[name=passwd]').val();
		var pass2 = $('input[name=passwd2]').val();
		
		if( pass != "" || pass2!="" ){			
			if( pass != pass2 ){
				dialog_student("兩次密碼不相符!");
				return false;
			}			
			if( !pass.match(/.{6,20}/) || !pass.match(/[0-9]/) || !pass.match(/[A-z]/) ){
				dialog_student("密碼請輸入6~20英數字區分大小寫");
				return false;
			}
		}	
		
		post( '/student/account_save', $('.fill') , function(data){
				/*
				if(data.code==1)
					dialog_student( data.msg , function(){
						document.location.replace(document.location);
					});
				else*/
					dialog_student( data.msg );
				
		});
	});
});