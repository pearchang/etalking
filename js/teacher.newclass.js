$(function(){
	
	//$(document).scrollTop( $('.nav-groups').offset().top );
	
	$('.submit').click(function(){		
		
		var url = 'ajax.teacher.newclass.php?id='+ $(this).data('id') + "&type=10";
			
		get( url, function(data){
				
			if(data.code==1){
				document.location.replace(document.location);
			}else{
				dialog_teacher( data.msg , function(){
					document.location.replace(document.location);
				});	
			}				
		});
		
	});
	
	$('.cancel').click(function(){		
		
		var msg = "Are you sure to cancel this schdule?";
		var url = 'ajax.teacher.newclass.php?id='+ $(this).data('id') + "&type=20";

		confirm_teacher( msg ,function(){
			
			get( url, function(data){
				
				if(data.code==1)
					document.location.replace(document.location);
				else
					dialog_teacher( data.msg , function(){
						document.location.replace(document.location);
					});
				
			});
			
		},function(){
			document.location.replace(document.location);
			
		});
	});	
	
});