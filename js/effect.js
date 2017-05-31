//版本判定
$(function(){
	var WHAT = navigator.userAgent;
	if(WHAT.match(/(MSIE 5.0|MSIE 6.0|MSIE 7.0|MSIE 8.0)/)){
	window.location="update.html";
	}
});



//消除連結虛線
$(function(){
	
	$(window).scrollTop(1);
	   
	$("a").each(function() { 
                var ionFocus = $(this).attr("onFocus"); 
                if (ionFocus == null) { 
                    $(this).attr("onFocus", "this.blur();"); 
                } 
                else { 
                    $(this).attr("onFocus", ionFocus + ";this.blur();"); 
                } 
                $(this).attr("hideFocus", "hidefocus"); 
            });
			
			
				   
});


//主選單下色塊移動
$(function(){
	var $move = $('#move'), 
		$menu = $('#menu'), 
		$first = $menu.find('li:last a');
    //先設定底下色塊在第一個li底下，但寬度取決於a的設定喔
	
	
	$move.css({
		width: 	$first.outerWidth()+2, 
		height: 28, 
		top: 25,
		left: $first.offset().left-5
	});
    //當每個連結a滑入時，設定色塊寬高度與位置和現在的a是相同的
	$menu.find('a').mouseover(function(){
		var $this = $(this);
		
		$move.stop().animate({
			width: 	$this.outerWidth()+2, 
			height: 28, 
			top: 25,
			left: $this.offset().left-5
		}, 400);
	});
	
	//縮放翻轉時
	var WIN = $(window);//視窗
	function AAA(){
		$move.css({"display":"none"});
		$move.css({
		width: 	$first.outerWidth()+2, 
		height: 28, 
		top: 25,
		left: $first.offset().left-5
		});
		/*var $move = $('#move'), 
		$menu = $('#menu'), 
		$first = $menu.find('li:last a');
		//先設定底下色塊在第一個li底下，但寬度取決於a的設定喔
		$move.css({
		width: 	$first.outerWidth()+2, 
		height: 28, 
		top: $first.offset().top-5,
		left: $first.offset().left-5
		});*/
		
		//次選單消失
		$("DIV.MOBILE_SUBMENU").stop().slideUp(0);
	}
	
	AAA();
	WIN.on('orientationchange resize', AAA);
	//翻轉時 end
	
	
	//離開時消失
	$("header.HEADER").mouseleave(function(){
		$move.css({"display":"none"});
	});

	
	
	
});



//手機次選項出現
$(function(){
	   
	$("DIV.MOBILE-NAV").click(function(){
		$("DIV.MOBILE_SUBMENU").stop().slideToggle(300);
	});
	
						   
});




//表單提示文字
$(function(){
	// 把每一個有 .tip 的輸入方塊都抓出來做處理
	//輸入方塊與文字方塊
	$('input[title], textarea[title]').each(function(){
		// 先取出在輸入方塊中的 title 屬性
		var $this = $(this),
			_title = $this.attr('title');

		// 把 title 屬性清空，接著把輸入方塊的值設成 title，並加上 .tipClass 樣式
		$this.attr('title', '').val(_title).addClass('tipClass');
		
		// 當取得焦點時
		$this.focus(function(){
			// 如果目前的值跟 title 是一樣就清空及移除樣式
			if($this.val()==_title){
				$this.val('').removeClass('tipClass');
			}
		}).blur(function(){	// 當失去焦點時
			// 如果目前的值是空的就填入 title 及加入樣式
			if($this.val()==''){
				$this.val(_title).addClass('tipClass');
			}
		});
	});
});



//GOTOTOP
$(function(){
	
	

	$("#GOTOP2").click(function(){
		$("html,body").stop(true,false).animate({scrollTop:0},300);
		return false;	
	});
	
	$("#RRBOX span.B1").click(function(){
		$("html,body").animate({scrollTop:$("#RRBOX.D1").offset().top-170}, 300);
	});
	
	$("#RRBOX span.B2").click(function(){
		$("html,body").animate({scrollTop:$("#RRBOX.D2").offset().top-170}, 300);
	});
	
	$("#RRBOX span.B3").click(function(){
		$("html,body").animate({scrollTop:$("#RRBOX.D3").offset().top-170}, 300);
	});
	
	$("#RRBOX span.B4").click(function(){
		$("html,body").animate({scrollTop:$("#RRBOX.D4").offset().top-170}, 300);
	});
	
	/*var HH= $(window).innerHeight();
	$("#GOTOP").css({top:HH-160});
	$("#GOTOP2").css({top:HH-60});*/
	
	
});




//主視覺切換
$(function(){

	var NN=0;//排行位置先設定為第一個
	var GOGO=0;//座標也先設定為0
	var TT=0; //宣告一個變數，等一下要給計時
	var WIN = $(window);//視窗
	var WW= WIN.innerWidth();
	var BB=$("#V-BOX ul").find("li").length;//判斷有幾個li並給BB數值
	var VW=BB*60;
	$("#V-BOX").css({width:WW});
	$("#V-BOX li").css({width:WW});
	
	$("#V-BTN").css({width:VW,marginLeft:-VW*0.5});
	
	$("span.R-BTN").click(function(){
		if(NN<BB-1){
			NN += 1;
			GOGO = NN*WW*-1;
			$("#V-BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("#V-BTN ul li").eq(NN).addClass("NOW").siblings().removeClass("NOW");
		}else{
			NN=0;
			GOGO=0;
			$("#V-BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("#V-BTN ul li").eq(NN).addClass("NOW").siblings().removeClass("NOW");
		}							   
	});	   
	
	$("span.L-BTN").click(function(){ 
		if(NN>0){
			NN-=1;
			GOGO=NN*WW*-1
			$("#V-BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("#V-BTN ul li").eq(NN).addClass("NOW").siblings().removeClass("NOW");
		}else{
			NN=BB-1;
			GOGO=NN*WW*-1
			$("#V-BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("#V-BTN ul li").eq(NN).addClass("NOW").siblings().removeClass("NOW");
		}							   
	});
	
	
	
	//翻轉時
	function FULL(){
		WW= WIN.innerWidth();
		$("#V-BOX").css({width:WW});
		$("#V-BOX li").css({width:WW});
		$("#V-BOX ul").css({marginLeft:0});
		$("#V-BTN ul li").eq(0).addClass("NOW").siblings().removeClass("NOW");
		NN=0;

	}
	
	FULL();
	WIN.on('orientationchange resize', FULL);
	//翻轉時 end
	
	//第一個顯示
	$("#V-BTN ul li").eq(0).addClass("NOW");
	
	//點小圖換大圖
	$("#V-BTN ul li").click(function(){
		NN=$(this).index();
		$(this).addClass("NOW").siblings().removeClass("NOW");
		GOGO=NN*WW*-1;
		$("#V-BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
	});
	
	

	
	function AUTO(){//輪播計時器
		if(NN<BB-1){
			NN += 1;
			GOGO = NN*WW*-1;
			$("#V-BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("#V-BTN ul li").eq(NN).addClass("NOW").siblings().removeClass("NOW");
			
		}else{
			NN=0;
			GOGO=0;
			$("#V-BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("#V-BTN ul li").eq(NN).addClass("NOW").siblings().removeClass("NOW");
			
		}
		
		TT = setTimeout(AUTO, 4000);
	}
	
	TT = setTimeout(AUTO, 4000); // 呼叫 啟動上面的 function
	
	// 碰到圖與按鈕，停止輪播。滑鼠離開再開始輪播
	$("DIV.VISION").hover(
		function(){ 
			clearTimeout(TT);
			$("DIV.VIDEO-CLOSE").fadeIn(300);
		}, 
		function(){
			TT = setTimeout(AUTO, 4000);
			$("DIV.VIDEO-CLOSE").fadeOut(300);
		}
	);	
	
});




//產品頁切換
$(function(){

	var NN=0;//排行位置先設定為第一個
	var GOGO=0;//座標也先設定為0
	var WIN = $(window);//視窗
	var WW= WIN.innerWidth();
	var PW= $("#P_BOX").innerWidth();
	var PH= $("#P_BOX ul").innerHeight();
	var PB=$("#P_BOX ul").find("li").length;//判斷有幾個li並給BB數值
	var PP=Math.max(PW/5);
	var PP2=Math.max(PW/3);
	$("#P_BOX").css({height:PH,"min-height":270})
	$("span.PL_BTN").addClass("BTN_AO");
	
	
	if(WW<510){
			$("#P_BOX li").css({width:PW});
			$("#P_BOX ul").css({width:PW*PB});
			
		}
	
	if(WW<800 && WW>510){
			$("#P_BOX li").css({width:PP2});
			$("#P_BOX ul").css({width:PP2*PB});
			
		}
	
	if(WW>800){
			$("#P_BOX li").css({width:PP});
			$("#P_BOX ul").css({width:PP*PB});
			
		}
	
	

	
	//翻轉時
	function FULL2(){
	var NN=0;//排行位置先設定為第一個
	var GOGO=0;//座標也先設定為0
	var WIN = $(window);//視窗
	var WW= WIN.innerWidth();
	var PW= $("#P_BOX").innerWidth();
	var PH= $("#P_BOX ul").innerHeight();
	var PB=$("#P_BOX ul").find("li").length;//判斷有幾個li並給BB數值
	var PP=Math.max(PW/5);
	var PP2=Math.max(PW/3);

	$("span.PL_BTN").addClass("BTN_AO");
	
	
	//PC版效果
	if(WW>800){
			$("#P_BOX").css({height:PH,"min-height":250})
			$("#P_BOX li").css({width:PP});
			$("#P_BOX ul").css({width:PP*PB});
			
			if(PB<6){
			$("span.PR_BTN").addClass("BTN_AO");
			$("span.PL_BTN").addClass("BTN_AO");
			}
			
	//右按鈕
	$("span.PR_BTN").click(function(){
		if(NN<PB-5){
			NN += 1;
			GOGO = NN*PP*-1;
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("span.PL_BTN").removeClass("BTN_AO")
			
		}/*else{
			NN=0;
			GOGO=0;
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);

		}*/
		
		if(NN==PB-5){
			$("span.PR_BTN").addClass("BTN_AO");
			$("span.PL_BTN").removeClass("BTN_AO")
			
		}
								   
	});	 
	
		//左按鈕
		$("span.PL_BTN").click(function(){ 
			if(NN>0){
			NN-=1;
			GOGO=NN*PP*-1
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);		
			$("span.PR_BTN").removeClass("BTN_AO")
			}/*else{
			NN=PB-3;
			GOGO=NN*PP*-1
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			}*/	
			
			if(NN==0){
			$("span.PL_BTN").addClass("BTN_AO");
			$("span.PR_BTN").removeClass("BTN_AO")
			
			}			   
		});
		
	}
	
	//平板版效果
	if(WW<800 && WW>510){
		$("#P_BOX").css({height:PH,"min-height":270})
			$("#P_BOX li").css({width:PP2});
			$("#P_BOX ul").css({width:PP2*PB});
	//右按鈕
	$("span.PR_BTN").click(function(){
		if(NN<PB-3){
			NN += 1;
			GOGO = NN*PP2*-1;
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("span.PL_BTN").removeClass("BTN_AO")
			
		}/*else{
			NN=0;
			GOGO=0;
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);

		}*/
		
		if(NN==PB-3){
			$("span.PR_BTN").addClass("BTN_AO");
			$("span.PL_BTN").removeClass("BTN_AO")
			
		}
								   
	});	 
	
		//左按鈕
		$("span.PL_BTN").click(function(){ 
			if(NN>0){
			NN-=1;
			GOGO=NN*PP2*-1
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);		
			$("span.PR_BTN").removeClass("BTN_AO")
			}/*else{
			NN=PB-3;
			GOGO=NN*PP*-1
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			}*/	
			
			if(NN==0){
			$("span.PL_BTN").addClass("BTN_AO");
			$("span.PR_BTN").removeClass("BTN_AO")
			
			}			   
		});
		
	}
	
	//手機版
	if(WW<510){
		$("#P_BOX").css({height:PH,"min-height":270})
			$("#P_BOX li").css({width:PW});
			$("#P_BOX ul").css({width:PW*PB});
	//右按鈕
	$("span.PR_BTN").click(function(){
		if(NN<PB-1){
			NN += 1;
			GOGO = NN*PW*-1;
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("span.PL_BTN").removeClass("BTN_AO")

		}/*else{
			NN=0;
			GOGO=0;
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);

		}*/
		if(NN==PB-1){
			$("span.PR_BTN").addClass("BTN_AO");
			$("span.PL_BTN").removeClass("BTN_AO")
			
		}
									   
	});	   
	
	//左按鈕
	$("span.PL_BTN").click(function(){ 
		if(NN>0){
			NN-=1;
			GOGO=NN*PW*-1
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
			$("span.PR_BTN").removeClass("BTN_AO")
		}/*else{
			NN=PB-1;
			GOGO=NN*PW*-1
			$("#P_BOX ul").stop(true,false).animate({marginLeft:GOGO},300);
		}	*/
			if(NN==0){
			$("span.PL_BTN").addClass("BTN_AO");
			$("span.PR_BTN").removeClass("BTN_AO")
			
			}	
		
								   
	});
	
	}
	
	

	}
	
	FULL2();
	WIN.on('orientationchange resize', FULL2);
	//翻轉時 end
	
	
	

	
});



//首頁加入影片
$(function(){
	
//出現	
$("DIV.YOU-BTN").click(function(){

	var url = $(this).attr('data-url');
	$("DIV.VIDEO-POS").css({"display":"block"});
	
	$("DIV.VIDEO-BOX").append('<iframe src="https://www.youtube.com/embed/' + url + '?wmode=opaque&autoplay=1&loop=1" frameborder="0" width="100%"></iframe>');
	
 });
 
 //關閉
 $("DIV.VIDEO-CLOSE").click(function(){
	
	$("DIV.VIDEO-BOX iframe").remove();
	$("DIV.VIDEO-POS").css({"display":"none"});
	
 });
 
 

	

});


//雜項
$(function(){
	
$(".about-text").find("li:last").css({"border-bottom":0});

	

});




