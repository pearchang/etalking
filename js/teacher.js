function dialog_teacher( msg , callback ){
	
	$('#systemModal').find('p').text(msg);
	$('#systemModal').foundation('reveal', 'open');
	
	if(callback!=undefined){
		$('#systemModal').find('.btn').bind( "click", callback );
	}

}

function confirm_teacher( msg , callback, cancel ){
	
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
	get( 'ajax.teacher.dashboard.php', function(data){
		if(data.dashboard['code']==1){
			$('span#newclass').text( data.dashboard['newclass'] );
			$('span#classes').text( data.dashboard['classes'] );
			$('span#demo').text( data.dashboard['demo'] );
			$('span#overdue').text( data.dashboard['overdue'] );
			$('span#report').text( data.dashboard['report'] );
		}		
	});
}

$(function(){
	update_dashboard();	
});