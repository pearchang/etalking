$(function(){
	
	$('a.booking').on('click', function(event) {	
		$('#class_intro').hide();
		var level = parseInt( $(this).data('level') );
		var member_level = parseInt( $('.member_level').val() );
		var course_id = $(this).data('courseid');		
		event.preventDefault();
		$('.ensure').unbind("click");
		if(level > member_level  ){
			$('#booking_confirm').fadeToggle(200);
			$('.ensure').click(function(){
				$('#booking_confirm').fadeOut(200);				
				$('#class_intro').hide();
				booking(course_id);
			});
			return false;
		}
		booking(course_id);
		return false;
	});
	
	$('a.class_intro').on('click', function(event) {		
		$('#booking_confirm').hide();		
		var course_id = $(this).data('courseid');		
		var item = $(this);
		event.preventDefault();
				
		$('.class_intro_ensure').unbind("click").click(function(){			
			item.parent().parent().find('a.booking').click();
		});

		if( $(this).data('disabled')==1 ) $('.class_intro_ensure').hide();
		else $('.class_intro_ensure').show();
		
		get('ajax.course.php?course_id='+ course_id ,function(data){
			
			if(data.code!=1){
				alert( data.msg );
				return false;
			}
			
			if($('body').data('type') == 'hall' ){
				
				$('.classintro-card-label').hide();
				$('#class_intro h1').text( data.course.title );
				$('#class_intro .classintro_text_date').text(data.material.date);
				$('#class_intro .classintro_text_title').text(data.material.title);
				$('#class_intro .classintro_text_brief').html(data.material.brief);
				
			}else{
				$('#class_intro h1').text( data.course.title );
				$('.classintro-card-label').html('').show();				
				$.each(data.lesson, function(index,ls){
					var s = document.createElement('span');
					$(s).text('Lesson'+ ls.sn);
					var a = document.createElement('a');
					$(a).append(s).click(function(){
						$('#class_intro .classintro_text_date').text( ls.date);
						$('#class_intro .classintro_text_title').text(ls.title);
						$('#class_intro .classintro_text_brief').html(ls.brief);
					});
					$('.classintro-card-label').append( a );
				});
				$('.classintro-card-label a:first-child').click();
			}
			$('#class_intro').fadeIn(200);
		});		
		
		return false;
	});
	
	$('.light-box-close').on('click', function(event) {
		$('#booking_confirm').fadeOut(200);
		$('#class_intro').hide();
		return false;
	});
	
	$('.getback').on('click', function(event){
		$('#booking_confirm').fadeOut(200);
		$('#class_intro').hide();
		return false;
	});



	
});

function booking( course_id ){	
	get('ajax.booking.elective.php?t='+ $('body').data('type') +'&course_id='+course_id ,function(data){

		if(data.code==1)
			dialog_student( data.msg, function(){
				document.location.replace( document.location );
			});
		else
			dialog_student( data.msg );

		//dialog_student( data.msg );
	});
}