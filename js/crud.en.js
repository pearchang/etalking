function post( url, form, callback, recaptcha ){
	
	var d = new Date();
	sign = url.match(/\?/) ? '&' : '?';
	url = url + sign + 'nocache=' + d.getTime();
	
	if(recaptcha && 
		form.find('[name=g-recaptcha-response]').val().length == 0 ){
		alert('請點選我不是機器人');
		return false;
	}

	$.ajax({
		url : url,
		type: "POST",
		dataType:'json',
		data: form.serialize(),
		success: callback,
		error: function(){
            alert('系統發生問題，請稍後再試');
		}
	});
}

function get( url, callback ){
	
	var d = new Date();
	sign = url.match(/\?/) ? '&' : '?';
	url = url + sign + 'nocache=' + d.getTime();
	$.ajax({
		url : url,
		type: "GET",
		dataType:'json',
		success: callback,
		error: function(){
            alert('系統發生問題，請稍後再試');
		}
	});
}

function replace( url){
	location.replace( url != undefined ? url : $(location).attr('href').replace(/#$/,'') );
}

function dialog( msg , callback ){	
	
	$('#systemModal').find('h5').text(msg);
	$('#systemModal').foundation('reveal', 'open');

}