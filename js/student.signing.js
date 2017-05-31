$(function(){
	$('.agree').click(function(){
		if( $('.law').find('input:not(:checked)').length ){
			dialog_student("所有項目均必須勾選同意");
			return false;
		}
		$('.agree').hide();		
		var url = "ajax.student.contract.php?id=" + $(this).data('id');
		get( url, function(data){				
				if(data.code==1)
					document.location.replace('/student/contract');
				else{
					dialog_student( data.msg);
					$('.agree').show();
				}					
		});
	});
});