$(function(){

	$('.form-time-select li a').on('click', function(event) {
		var id = $(this).data('id');
		$('.etalking-form').find('[name=contact_time]').val(id);
	});

	$('.sex-swich').on('click', function(event) {
		if($(this).hasClass('male')){
			$('.etalking-form').find('[name=gender]').val(1);
		}else{
			$('.etalking-form').find('[name=gender]').val(2);
		}
	});

	$('.etalking-form').submit(function(event){

		if( $(this).find('[name=guest_name]').val()=='' ){
			dialog("請輸入姓名",event);
			return false;
		}
		if( $(this).find('[name=tel]').val()=='' ){
			dialog("請輸入電話",event);
			return false;
		}

		RE = /^09[0-9]{8}$/;
		if( !RE.test($(this).find('[name=tel]').val()) ){
			dialog("請輸入手機號碼共10位數字",event);
			return false;
		}

		// RE = /^\d+$/;
		// if( !RE.test($(this).find('[name=age]').val()) ){
		// 	dialog("年齡請輸入數字",event);
		// 	return false;
		// }

		if( $(this).find('[name=email]').val()=='' ){
			dialog("請輸入Email",event);
			return false;
		}
		RE = /^(|[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+)$/;
		if( !RE.test( $(this).find('[name=email]').val() ) ){
			dialog("電子郵件錯誤",event);
			return false;
		}
		// if( $(this).find('[name=contact_time]').val()=='' ){
		// 	dialog("請選擇聯絡時間",event);
		// 	return false;
		// }

		if( $(this).find('#check:checked').length == 0 ){
			dialog("請勾選同意個資保護聲明",event);
			return false;
		}
		
        var tel = $(this).find('[name=tel]').val();
		post('/home/experience', $(this), function(data){
			if(data.code==1)
			{
				if (act == '161115' && track == '001') {
				console.log('yeah');
				  VA.remoteLoad({
				    whiteLabel: { id: 8, siteId: 1207, domain: 'vbtrax.com' },
				    conversion: true,
				    conversionData: {
				      step: 'default',   // conversion name
				      revenue: '',   // revenue share
				      orderTotal: '',   // order total
				      order: tel
					  //order: $(this).find('[name=tel]').val()  // order number
					  //order: '0979797979'
				    },
				    locale: "en-US", mkt: true
				  });
				  console.log(tel);
				  console.log('0979797979');
				}
				dialog("恭喜你<br>報名成功",event);
				setTimeout(function() {
					window.location.href = '/';
				}, 3000);
			}
			else
				dialog( data.msg,event );
		} ,false );

		return false;
	});

});

function post( url, form, callback, recaptcha ){

	var d = new Date();
	sign = url.match(/\?/) ? '&' : '?';
	url = url + sign + 'nocache=' + d.getTime();

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

function replace( url){
	location.replace( url != undefined ? url : $(location).attr('href').replace(/#$/,'') );
}

function dialog( msg , event ){
	$('.light-box').find('h2').html(msg);
	$('#ContactUsNow').fadeToggle(200);
}

function getParameterByName(name, url) {
	if (!url) url = window.location.href;
	name = name.replace(/[\[\]]/g, "\\$&");
	var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
		results = regex.exec(url);
	if (!results) return null;
	if (!results[2]) return '';
	return decodeURIComponent(results[2].replace(/\+/g, " "));
}

// get affiliate
var act = getParameterByName('act');
var track = getParameterByName('track');
if (act)
{
	$('input[name=source]').val(act);
	$('input[name=track]').val(track);
}