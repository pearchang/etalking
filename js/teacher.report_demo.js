$(function(){
	var name = $('input[type=radio]').first().attr('name');
	if ($('input[name="' + name + '"]:checked').length > 0)
	{
		$('input').attr('disabled', true);
		$('textarea').attr('readonly', true);
	}

	$('.submit').click(function(){

		if( $('input[type=radio]:checked').length!=10){
				dialog_teacher( " All fields required ! " );
				return false;
		}
		
		if( $('[name="items[11]"]').val() == ""){
				dialog_teacher( " Summary is required ! " );
				return false;
		}

		post( 'ajax.teacher.report.php?type=demo', $('.fill') , function(data){
			if(data.code==1){
				dialog_teacher( data.msg, function(){
					history.back();
				});
			}else{
				dialog_teacher( data.msg );
			}
		});
		
	
	});
});