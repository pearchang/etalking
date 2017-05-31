$(function(){
	
	var name = $('input[type=radio]').first().attr('name');
	if ($('input[name="' + name + '"]:checked').length > 0)
	{
		$('input').attr('disabled', true);
		$('textarea').attr('readonly', true);
	}
	
	$('.panel').hide();
	
	$('.student').click(function(){
		
		$('.student').removeClass('active');
		$('.panel').hide();
		
		var id = $(this).data('id');		
		$(this).addClass('active');
		$('#panel'+id).show();
		
	});
	
	$('.student:first').click();
	
	$('.submit').click(function(){
		
		check = true;

		$('.panel').each(function(i,item){
			
			if( $(this).find('input[type=radio]:checked').length!=6){
				dialog_teacher( " All fields required ! " );
				$('.student:eq('+ i +')').click();
				check = false;
				return false;
			}
			
			if( $(this).find('textarea').val() == ""){
				dialog_teacher( " Summary is required ! " );
				$('.student:eq('+ i +')').click();
				check = false;
				return false;
			}
			
			
		});
		
		if( check == false ) return false;
		
		post( 'ajax.teacher.report.php?type=class', $('.fill') , function(data){
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