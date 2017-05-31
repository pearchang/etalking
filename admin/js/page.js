//主要NAV功能
$(function(){
	$("#NAV ul li").hover(function(){	
		$(this).removeClass("AA").addClass("BB");
	},function(){
		$(this).removeClass("BB").addClass("AA");
	});
	
	$("#NAV ul li").click(function(){
		$(this).removeClass("AA BB").addClass("CC").siblings().removeClass("CC").addClass("AA");
		var NOW=$(this).index();
		$(".BOX").eq(NOW).fadeIn(0).siblings().fadeOut(0);
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(".CONTENT_L").css({height:H-155});	
		$(".CONTENT_R").css({width:W-263,height:H-155});
		$("#SWITCH").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH2").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH3").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH4").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH5").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH6").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$(".CONTENT_L").css({left:0});
		$(".LBOX_TOP").removeClass("lbb").addClass("laa");
		$(".LBOX_CONTENT").show(0);
		$(".RBOX").eq(0).fadeIn(0);
		$("#BOX_NAV ul li").eq(0).removeClass("B_AA B_BB").addClass("B_CC").siblings().removeClass("B_CC").addClass("B_AA");
	});
	
});

//內容高度
$(function(){
	//document.body.style.overflow = 'hidden';//隱藏卷軸
	$(window).resize(function(){
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(".CONTENT_L").css({height:H-155});	
		$(".CONTENT_R").css({width:W-263,height:H-155});
		$("#SWITCH").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH2").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH3").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH4").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH5").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$("#SWITCH6").css({left:219,top:H/2-90}).removeClass().addClass("hideMenu");
		$(".CONTENT_L").css({left:0});
	}).resize();
	
});

//左右欄開合開關按鈕
$(function(){
	
	$("#SWITCH").click(function(){
		
		var _hide = $(this).hasClass("hideMenu");//宣告如果選單有這個class的情況下叫做_hide
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(this).toggleClass("hideMenu");

		$(this).stop().animate({
			left: _hide ? 0 : 219//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		}).toggleClass("SOPEN");
		
		$(".CONTENT_L").stop().animate({
			left: _hide ? -219 : 0//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});
		
		$(".CONTENT_R").stop().animate({
			width: _hide ? W-44 : W-263//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});

		

	});
	
	$("#SWITCH2").click(function(){
		
		var _hide = $(this).hasClass("hideMenu");//宣告如果選單有這個class的情況下叫做_hide
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(this).toggleClass("hideMenu");

		$(this).stop().animate({
			left: _hide ? 0 : 219//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		}).toggleClass("SOPEN");
		
		$(".CONTENT_L").stop().animate({
			left: _hide ? -219 : 0//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});
		
		$(".CONTENT_R").stop().animate({
			width: _hide ? W-44 : W-263//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});

		

	});
	$("#SWITCH3").click(function(){
		
		var _hide = $(this).hasClass("hideMenu");//宣告如果選單有這個class的情況下叫做_hide
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(this).toggleClass("hideMenu");

		$(this).stop().animate({
			left: _hide ? 0 : 219//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		}).toggleClass("SOPEN");
		
		$(".CONTENT_L").stop().animate({
			left: _hide ? -219 : 0//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});
		
		$(".CONTENT_R").stop().animate({
			width: _hide ? W-44 : W-263//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});

		

	});
	$("#SWITCH4").click(function(){
		
		var _hide = $(this).hasClass("hideMenu");//宣告如果選單有這個class的情況下叫做_hide
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(this).toggleClass("hideMenu");

		$(this).stop().animate({
			left: _hide ? 0 : 219//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		}).toggleClass("SOPEN");
		
		$(".CONTENT_L").stop().animate({
			left: _hide ? -219 : 0//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});
		
		$(".CONTENT_R").stop().animate({
			width: _hide ? W-44 : W-263//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});

		

	});
	$("#SWITCH5").click(function(){
		
		var _hide = $(this).hasClass("hideMenu");//宣告如果選單有這個class的情況下叫做_hide
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(this).toggleClass("hideMenu");

		$(this).stop().animate({
			left: _hide ? 0 : 219//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		}).toggleClass("SOPEN");
		
		$(".CONTENT_L").stop().animate({
			left: _hide ? -219 : 0//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});
		
		$(".CONTENT_R").stop().animate({
			width: _hide ? W-44 : W-263//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});

		

	});
	$("#SWITCH6").click(function(){
		
		var _hide = $(this).hasClass("hideMenu");//宣告如果選單有這個class的情況下叫做_hide
		var W = document.documentElement.clientWidth,
			H = document.documentElement.clientHeight;
		$(this).toggleClass("hideMenu");

		$(this).stop().animate({
			left: _hide ? 0 : 219//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		}).toggleClass("SOPEN");
		
		$(".CONTENT_L").stop().animate({
			left: _hide ? -219 : 0//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});
		
		$(".CONTENT_R").stop().animate({
			width: _hide ? W-44 : W-263//三元運算式，如果符合_hide則執行問號?後面數值，不符合則執行冒號:後面數值
		});

		

	});
});

//右欄NAV功能
$(function(){
	$("#BOX_NAV ul li").hover(function(){	
		$(this).removeClass("B_AA").addClass("B_BB");
	},function(){
		$(this).removeClass("B_BB").addClass("B_AA");
	});
	
	$("#BOX_NAV ul li").click(function(){
		$(this).removeClass("B_AA B_BB").addClass("B_CC").siblings().removeClass("B_CC").addClass("B_AA");
		var RNOW=$(this).index();
		$(".RBOX").eq(RNOW).fadeIn(0).siblings().fadeOut(0);
	});
	
});

//左欄開合功能
$(function(){
	$(".LBOX_TOP").each(function(){
		var $block=$(this),
			$caption=$block.siblings(".LBOX_CONTENT");
		$block.click(function(){ 
			$caption.slideToggle(0);
			$block.toggleClass("laa lbb");
		});	
	});
	
});

//欄位hover
$(function(){
	
	$(".LBOX_CONTENT").find("li").hover(
	function(){
		$(this).addClass("on");		
	},function(){
		$(this).removeClass("on");	
	});
	
	$("tr").hover(function(){
		//mouseover
		$(this).addClass("onn");
	}, function(){
		//mouseout
		$(this).removeClass("onn");
	});
	
	$(".tab1 tr").hover(function(){
		//mouseover
		$(this).addClass("onn");
		$(this).find("td:nth-child(1)").removeClass("onn2").addClass("onnn");
	}, function(){
		//mouseout
		$(this).removeClass("onn");
		$(this).find("td:nth-child(1)").removeClass("onnn").addClass("onn2");
	});
	
	$(".tab2 tr:even").addClass("even");
	
	$(".tab2 tr:even").hover(function(){

		$(this).removeClass("even");
		$(this).addClass("onn");
			
	}, function(){
		
		$(this).removeClass("onn");
		$(this).addClass("even");
		
	});

	
});

