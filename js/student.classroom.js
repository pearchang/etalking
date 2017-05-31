$(function(){	
	
	$('.booking').click(function(){
		
		if( !$(this).prop('checked') ) return false;
		
		var msg = "您確定要劃位 " + $(this).data('date') + " " + $(this).data('time') + ":00 " + $(this).data('title')  + " 嗎？";
		var url = 'ajax.booking.php?date='+ $(this).data('date') + "&time=" + $(this).data('time') + "&type=" + $(this).data('type');
		
		get( url, function(data){
				
				if(data.code==1)
					document.location.replace(document.location);
				else
					dialog_student( data.msg , function(){
						document.location.replace(document.location);
					});
				
		});

	});
	
	$('.unbooking').click(function(){
		
		var msg = "請問您確定要取消課程嗎?";
		var url = 'ajax.booking.php?type=cancel&date='+ $(this).data('date') + "&time=" + $(this).data('time');

		confirm_student( msg ,function(){
			
			get( url, function(data){
				
				if(data.code==1)
					document.location.replace(document.location);
				else
					dialog_student( data.msg , function(){
						document.location.replace(document.location);
					});
				
			});
			
		},function(){
			document.location.replace(document.location);
			
		});
	});	
	
	$('.unbooking_elective').click(function(){
		
		var msg = "請問您確定要取消課程嗎?";
		var t = $(this).data('type') == 40 ? 'elective' : 'hall';
		var url = 'ajax.booking.elective.php?type=cancel&course_id='+ $(this).data('courseid') + "&t=" + t;

		confirm_student( msg ,function(){
			
			get( url, function(data){
				
				if(data.code==1)
					document.location.replace(document.location);
				else
					dialog_student( data.msg , function(){
						document.location.replace(document.location);
					});
				
			});
			
		},function(){
			document.location.replace(document.location);
			
		});
	});	
	
});