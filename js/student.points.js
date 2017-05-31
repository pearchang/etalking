$(function(){
	
	$('.form-class-select li a').on('click', function(){
		var id = $(this).data('value');
		$('.fill').find('[name=type]').val(id);
	});
	
	$('.fill').find('.submit').click(function(){
		
		var sdate = $('.fill').find('[name=sdate]').val();
		var edate = $('.fill').find('[name=edate]').val();
		var tp    = $('.fill').find('[name=type]').val();
		
		RE = /^\d{2}\/\d{2}\/\d{4}$/;
		if(sdate && !RE.test( sdate ) ){
			dialog_student( "起始日期錯誤");
			return false;
		}
		if(edate && !RE.test( edate ) ){
			dialog_student("結束日期錯誤");
			return false;
		}
		document.location = "/student/points?sdate=" + sdate + "&edate=" + edate + "&type=" + tp ;
		
	});
	/*
	$('.fill').find('.reset').click(function(){
		$('.fill').find('[name=sdate]').val('');
		$('.fill').find('[name=edate]').val('');
		$('.fill').find('[name=type]').val(0);
		$('.fill').find('.type').val('All');
		return false;
	});
	*/
});