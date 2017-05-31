//IE old version tip
var userAgent = window.navigator.userAgent;  
if (userAgent.indexOf("MSIE 7.0")>0 || userAgent.indexOf("MSIE 8.0")>0 || userAgent.indexOf("MSIE 9.0")>0) {
  	var url = "http://cybersoft.begonia.tw/browser.html";  
  	setTimeout(
	  function(){
	    $(location).attr('href',url);
	  }, 0);
}

//gotop
function gotopAnimate(){
	var winH = $(window).innerHeight();
	var scrollTop =  $(document).scrollTop();
	var footerTop = $('footer').offset().top;
	var gotop = $(".gotop"),
		gotopHeight = $(".gotop").innerHeight(),
		footerHeight = $("footer").innerHeight(),
		gotopCalc = footerHeight - gotopHeight;

	if( scrollTop + winH >= footerTop ){
		gotop.stop().animate({
			'bottom': footerHeight
		},200,"swing");
	}else{
		gotop.stop().animate({
			'bottom': gotopCalc,
		},200,"swing");
	}
}

$(document).ready(function() {

	$(document).foundation();

	//=================================================================
	//light-box
	$('a[href="#ContactUsNow"]').on('click', function(event) {
		event.preventDefault();
		$('#ContactUsNow').fadeToggle(200);
		return false
	});
	$('.light-box-close').on('click', function(event) {
		$('#ContactUsNow').fadeToggle(200);
		return false
	});
	$('.light-box-wrapper').on('click', function(event) {
		if(event.currentTarget == event.target){
			$(this).fadeToggle(200);
		}
	});

		$('a[href="#preservation-confirm"]').on('click', function(event) {
		event.preventDefault();
		$('#preservation-confirm').fadeToggle(200);
		return false
	});
	$('.light-box-close').on('click', function(event) {
		$('#preservation-confirm').fadeOut(200);
		return false
	});
	$('.getback').on('click', function(event){
		$('#preservation-confirm').fadeOut(200);
		return false
	});
		$('a[href="#classintro"]').on('click', function(event) {
		event.preventDefault();
		$('#classintro').fadeToggle(200);
		return false
	});
	$('.light-box-close').on('click', function(event) {
		$('#classintro').fadeOut(200);
		return false
	});
		$('.getback').on('click', function(event){
		$('#classintro').fadeOut(200);
		return false
	});
	//====
	//=================================================================
	
	//go top
	gotopAnimate()
	var $gotop = $(".gotop");
	$gotop.on("click touchstart",function(){
		$('html, body').animate({
			scrollTop: 0
		}, 400, "swing");
	}).focus(function(){
		$(this).blur();
	});
	$('.main-menu li a').on({
		mouseenter: function(){
	        var p = $(this).parent('li');
			var position = p.position();
			$('.menu-move-bar').show().stop().animate({
				left: position.left,
				opacity: 1,
			},300);
		}
	});
	$('.main-menu').on({
		mouseleave: function(){
	    	// $('.menu-move-bar').fadeOut(300,function(){
	    	// 	$(this).css('left', '');
	    	// });
	    	$('.menu-move-bar').fadeOut();
		}
	});

	//mobile menu click
	/*$(".menu-bar").on('click', function(event) {
		if ($(this).hasClass('is-active')) {
			$('.free-form').slideUp();
			$('.main-menu').slideUp();
		}else{
			$('.main-menu').slideToggle();
		}
		$(this).toggleClass('is-active');
	});*/

	$(".checkbox-toggle").on('click', function(event) {
		if ($(this).is(':checked')) {
			$('body').css('overflowY', 'hidden')
		}else{
			$('body').css('overflowY', 'auto')
		}
	});
	$(".menu-bar").on('click', function(event) {
		if ($(this).hasClass('is-active')) {
			$('.free-form').slideUp();
			$('.main-menu').slideUp();
		}else{
			$('.main-menu').slideToggle();
		}
		$(this).toggleClass('is-active');
	});

	$('.form-close').on('click', function(event) {
		$('.free-form').fadeOut();
	});

	$('.menu a').on('click', function(event) {
		$(".checkbox-toggle").trigger('click');
		if($(this).hasClass('freebtn-mobile')){
			$('.free-form').slideDown();;
		}

	});

	//Menu Free form
	$('.freebtn').on('click', function(event) {
		if($('header').is(':not(.stick)')){
			$('header').toggleClass('stick');
			$('.content').addClass('stick-cont');
		}else if($(this).hasClass('open') && $(document).scrollTop()===0){
			$('header').toggleClass('stick');
			$('.content').removeClass('stick-cont');
		}

		$(this).toggleClass('open');
		$('.free-form').slideToggle();
	}).focus(function(){
		$(this).blur();
	});
	$('.form-time a').on('click', function(event) {
		var time = $(this).data('time');
		$('.form-time input').val(time);
	});
	$('.sex-swich').on('click', function(event) {
		if (!$(this).hasClass("disabled")) {
			$(this).toggleClass('male');
			$(this).toggleClass('female');
			if($(this).hasClass('male')){
				$('.form-sex>input').val('male');
			}else{
				$('.form-sex>input').val('female');
			}
		}
	});

	$('a.close-btn').on('click', function() {
	  $('.reveal-modal').foundation('reveal', 'close');
	});
	$('a.open-form').on('click', function() {
		if($('.freebtn').hasClass('open')){
		}else{
			$('.free-form').slideDown();
			$('.freebtn').addClass('open')
		}
	});


	/*youtube in reveal autoplay*/
	 var ytvideoid;

	$(document).on('click', '.modal-btn', function (e) {
	  	ytvideoid = $(this).data('ytvideoid')
	});	

	$(document).on('close.fndtn.reveal', '[data-reveal]', function () {  
	  $('#video .flex-video #feature-video').remove();
	  $('#video .flex-video').append('<div id="feature-video" />');
	});

    

});

$(window).scroll(function(event) {
	gotopAnimate()
	$('.free-form').slideUp();
	if($('.freebtn').hasClass('open')){
		$('.free-form').slideUp();
		$('.freebtn').removeClass('open')
	}
});

$(window).load(function() {});