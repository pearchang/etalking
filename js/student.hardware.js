$(function(){
    
	
	/*record effect*/
	function record(){
		$('.record-btn').removeClass('record').addClass('btn').addClass('disable');
		$('.play-btn').removeClass('btn').removeClass('disable');
		$('.start-btn').removeClass('btn').removeClass('disable');
		$('.bars').css('visibility', 'hidden');
	}
	
	function play(){
		$('.play-btn').removeClass('play');
		$('.bars').css('visibility', 'hidden');
	}
	
	$(document).on('click', '.record-btn', function(event) {
		Fr.voice.record();
		if(!$(this).hasClass('disable')){
			$(this).addClass('record');
			$(this).siblings('.bars').css('visibility', 'visible')
			clearTimeout(record);
			setTimeout(record,5000);
		}
		Fr.voice.pause();
	});
	
	$(document).on('click', '.play-btn', function(event) {
		Fr.voice.export(function(url){
			$("#audio").attr("src", url);
			$("#audio")[0].play();
		}, "URL");
		if(!$(this).hasClass('disable')){
			$(this).addClass('play');
			$(this).siblings('.bars').css('visibility', 'visible')
			clearTimeout(play);
			setTimeout(play,5000);
		}
	});
	
	$(document).on('click', '.start-btn', function(event) {
		Fr.voice.stop();
		if(!$(this).hasClass('disable')){
			$('.record-btn').removeClass('btn').removeClass('disable');
			$('.play-btn').addClass('btn').addClass('disable');
			$(this).addClass('btn').addClass('disable');
			$('.bars').css('visibility', 'hidden')
		}

	});
	
});