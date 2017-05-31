$(function(){	
	render_calendar(1);	
});

function blockui(){
		$.blockUI({
			css: {
				textAlign: 'center',
				border: '3px solid #aaa',
				padding: '15px',
				backgroundColor: '#000', 
				'-webkit-border-radius': '10px', 
				'-moz-border-radius': '10px', 
				opacity: .5, 
				color: '#fff',
				cursor: 'default'
			},
			message: "處理中，請稍候..."
		});
}

function render_calendar( week ){	

	get('/student/booking_ajax?week=' + week, function(data){
		
		$('.calendar').html(data.result);
		
		$('.arrow').click(function(){
			render_calendar( $(this).data('week') );
		});
		
		$('.booking').click(function(e){	
					
			if( !$(this).prop('checked') ) return false;
		
			//var msg = "您確定要劃位 " + $(this).data('date') + " " + $(this).data('time') + ":00 " + $(this).data('title')  + " 嗎？";
			var url = 'ajax.booking.php?date='+ $(this).data('date') + "&time=" + $(this).data('time') + "&type=" + $(this).data('type');
			blockui();
			get( url, function(data){
				$.unblockUI();
				if(data.code==1){
					clear();
					render_calendar(week);
					new_message( data.msg );
				}else{
					dialog_student(data.msg,function(e){				
						clear();
						render_calendar(week);
					});
				}
			});
		});
		
		$('.unbooking').click(function(){

			var msg = "請問您確定要取消課程嗎?";
			var url = 'ajax.booking.php?type=cancel&date='+ $(this).data('date') + "&time=" + $(this).data('time');
			confirm_student( msg ,function(e){
				
				blockui();
				get( url, function(data){
					$.unblockUI();
					if(data.code==1){							
						clear();
						render_calendar(week);
						new_message( data.msg );
					}else{
						dialog_student( data.msg , function(e){
							clear();
							render_calendar(week);
						});
					}						
				});			
			},function(e){
				clear();				
			});
		});
		$.unblockUI();
	},true);
	update_dashboard();
}

function clear(){
	$('#systemConfirm').find('.btn').unbind("click");
	$('#systemConfirm').find('.cancel').unbind("click");
	$('#systemModal').find('.btn').unbind("click");
}

function new_message(msg){
    //html = '<div class="alert-bar-inner row"><a class="button-close"><img src="images/icon_close.png" alt=""></a><span>'+ msg +'</span></div>';
	$('.alert-bar').show().find('span').text(msg);
	/*
	$('.button-close').unbind('click').bind('click',function(){
		$(this).parent().remove();
		if(!$('.button-close').length){
			$('.alert-bar').hide();
		}
	});*/	
}