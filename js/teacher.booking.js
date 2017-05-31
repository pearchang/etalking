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
			message: "Processing..."
		});
}

function render_calendar( week ){	

	get('/teacher/booking_ajax?week=' + week, function(data){
		
		$('.calendar').html(data.result);
		
		$('.arrow').click(function(){
			render_calendar( $(this).data('week') );
		});
		
		$('.booking').click(function(e){	
			if( !$(this).prop('checked') ){
				$(this).prop('checked',false);
				return false;
			}
			
			blockui();
			var url = 'ajax.teacher.booking.php?date='+ $(this).data('date') + "&time=" + $(this).data('time');		
			get( url, function(data){
				$.unblockUI();
				if(data.code==1){
					clear();
					render_calendar(week);
					new_message( data.msg );
				}else{
					dialog_teacher(data.msg,function(e){				
						clear();
						render_calendar(week);
					});
				}
			});
		});
		
		$('.unbooking').click(function(){

			var msg = "Are you sure to cancel this schdule?";
			var url = 'ajax.teacher.booking.php?type=cancel&date='+ $(this).data('date') + "&time=" + $(this).data('time');
			confirm_teacher( msg ,function(e){
				
				blockui();
				get( url, function(data){
					$.unblockUI();
					if(data.code==1){							
						clear();
						render_calendar(week);
						new_message( data.msg );
					}else{
						dialog_teacher( data.msg , function(e){
							clear();
							render_calendar(week);
						});
					}						
				});			
			},function(e){
				clear();				
			});
		});
		
	});
	update_dashboard();
}

function clear(){
	$('#systemConfirm').find('.btn').unbind("click");
	$('#systemConfirm').find('.cancel').unbind("click");
	$('#systemModal').find('.btn').unbind("click");
}

function new_message(msg){
    html = '<div class="alert-bar-inner row"><a class="button-close"><img src="images/icon_close.png" alt=""></a><span>'+ msg +'</span></div>';
	$('.alert-bar').show().html( html );
	$('.button-close').unbind('click').bind('click',function(){
		$(this).parent().remove();
		if(!$('.button-close').length){
			$('.alert-bar').hide();
		}
	});	
}