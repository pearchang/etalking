$(function(){
	var name = $('input[type=radio]').first().attr('name');
	if ($('input[name="' + name + '"]:checked').length > 0)
	{
		$('input').attr('disabled', true);
		$('textarea').attr('readonly', true);
		if($('.demand-side-radio[value=209]').prop('checked')){
			$('.demand-side-text').show();
		}
	}

	$('.submit').click(function(){

		if( $('input[type=radio]:checked').length!=10){
				dialog_student( "所有欄位必填" );
				return false;
		}
		
		if($('.demand-side-radio[value=209]').prop('checked') && $('.demand-side-text').val()==''  ){
			dialog_student( "請填寫『不完全符合需求』的說明" );
			$('.demand-side-text').focus();
			return false;
		}
		
		if( $('[name="items[30]"]').val() == ""){
			dialog_student( "請填寫意見回饋" );
			return false;
		}

		post( 'ajax.student.report.php?type=class', $('.fill') , function(data){
			if(data.code==1){
				dialog_student( data.msg, function(){
					history.back();
				});
			}else{
				dialog_student( data.msg );
			}
		});
		
	
	});
});