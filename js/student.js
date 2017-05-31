function dialog_student( msg , callback ){
	
	$('#systemModal').find('p').text(msg);
	$('#systemModal').foundation('reveal', 'open');
	
	if(callback!=undefined){
		$('#systemModal').find('.btn').bind( "click", callback );
	}

}

function confirm_student( msg , callback, cancel ){
	
	$('#systemConfirm').find('p').text(msg);
	$('#systemConfirm').foundation('reveal', 'open');
	
	if(callback!=undefined){
		$('#systemConfirm').find('.btn').bind( "click", callback );
	}
	
	if(cancel!=undefined){
		$('#systemConfirm').find('.cancel').bind( "click", cancel );
	}
}

function update_dashboard(){
	
	get( 'ajax.student.dashboard.php', function(data){

		if(data.dashboard!=undefined && data.dashboard['code']==1){
			$('span#points').text( data.dashboard['points'] );
			$('span#classes').text( data.dashboard['classes'] );
			$('span#overdue').text( data.dashboard['overdue'] );	
		}
	});
}

$(function(){
	update_dashboard();	
});

$(function(){
	$('.enter_webex').click(function(){
		var classroom = $(this).data('id');
		var type = $(this).data('type');
		get('ajax_webex.php?classroom=' + classroom + '&type='+type, function(data){
			if(data.code==1){
				document.location = data.msg;
			}else{
				if(type!=10)
					dialog_student( data.msg );
			}
		});
	});
});