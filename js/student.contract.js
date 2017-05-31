$(function(){
	
	$('.form-class-select li a').on('click', function(){
		var id = $(this).data('value');
		$('.fill').find('[name=type]').val(id);
	});
	
	$('.fill').find('.submit').click(function(){
		
		var sdate = $('.fill').find('[name=sdate]').val();
		var edate = $('.fill').find('[name=edate]').val();
		var tp    = $('.fill').find('[name=type]').val();
		
		if(sdate!='' || edate!=''){
		
			RE = /^\d{2}\/\d{2}\/\d{4}$/;
			if( !RE.test( sdate ) ){
				dialog_student("起始日期錯誤");
				return false;
			}
			if( !RE.test( edate ) ){
				dialog_student("結束日期錯誤");
				return false;
			}
		}
		
		document.location = "/student/contract?sdate=" + sdate + "&edate=" + edate + "&type=" + tp ;
		
	});
	
	$('.fill').find('.reset').click(function(){
		document.location = "/student/contract";
		return false;
	});
	
});