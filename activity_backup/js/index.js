function lineSize(){line_width=$(window).innerWidth()<=1024?75:60}var line_height=75,line_width=60,img_scale=1;$(document).ready(function(){$(".index-banner").slick({dots:!0,infinite:!0,speed:1e3,lazyLoad:"ondemand",autoplay:!0,autoplaySpeed:3e3,pauseOnFocus:!1,pauseOnHover:!0,responsive:[{breakpoint:1024,settings:{arrows:!1}}]}),$(".whyenglish").slick({infinite:!1,slidesToShow:4,slidesToScroll:4,responsive:[{breakpoint:1025,settings:{dots:!0,arrows:!1,slidesToShow:2,slidesToScroll:2}},{breakpoint:640,settings:{dots:!0,arrows:!1,slidesToShow:1,slidesToScroll:1}}]});var e=new ScrollMagic.Controller;if(TweenMax.to($(".index-function>h2"),0,{y:-100,opacity:0}),TweenMax.to($(".index-function>h4"),0,{y:-100,opacity:0}),TweenMax.to($(".func-step_0"),0,{opacity:0}),$(window).innerWidth()>=641){if($(window).innerWidth()>=1025){new ScrollMagic.Scene({triggerElement:".index-function"}).setTween(".func-step_0",.3,{opacity:1,delay:0}).addTo(e)}lineSize();var n=.3,t=.2,i=n,a=.3,c=.2,o=-150;TweenMax.to($(".line1-1"),0,{width:0,height:0,opacity:0});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(2)",offset:o}).setTween(".line1-1",.1,{opacity:1,delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(2)",offset:o}).setTween(".line1-1",n,{width:"+=45.7%",height:4,delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(2)",offset:o}).setTween(".line1-1",t,{height:"+="+line_height+"%",delay:n}).addTo(e);TweenMax.to($(".func-step:nth-child(2) img"),0,{opacity:0,x:100});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(2)",offset:o}).setTween(".func-step:nth-child(2) img",a,{opacity:1,x:0,delay:i}).addTo(e);TweenMax.to($(".func-step:nth-child(2) .text-part"),0,{opacity:0,x:100});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(2)",offset:o}).setTween(".func-step:nth-child(2) .text-part",c,{opacity:1,x:0,delay:i}).addTo(e);TweenMax.to($(".line2-1"),0,{width:0,height:4,opacity:0});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(3)",offset:o}).setTween(".line2-1",.1,{opacity:1,delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(3)",offset:o}).setTween(".line2-1",n,{width:"+=60%",delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(3)",offset:o}).setTween(".line2-1",t,{height:"+="+line_height+"%",delay:n}).addTo(e);TweenMax.to($(".func-step:nth-child(3) img"),0,{opacity:0,x:100});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(3)",offset:o}).setTween(".func-step:nth-child(3) img",a,{opacity:1,x:0,delay:i}).addTo(e);TweenMax.to($(".func-step:nth-child(3) .text-part"),0,{opacity:0,x:100});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(3)",offset:o}).setTween(".func-step:nth-child(3) .text-part",c,{opacity:1,x:0,delay:i}).addTo(e);TweenMax.to($(".line3-1"),0,{width:0,height:4,opacity:0});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(4)",offset:o}).setTween(".line3-1",.1,{opacity:1,delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(4)",offset:o}).setTween(".line3-1",n,{width:"+=60%",delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(4)",offset:o}).setTween(".line3-1",t,{height:"+="+line_height+"%",delay:n}).addTo(e);TweenMax.to($(".func-step:nth-child(4) img"),0,{opacity:0,x:100});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(4)",offset:o}).setTween(".func-step:nth-child(4) img",a,{opacity:1,x:0,delay:i}).addTo(e);TweenMax.to($(".func-step:nth-child(4) .text-part"),0,{opacity:0,x:100,y:-30});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(4)",offset:o}).setTween(".func-step:nth-child(4) .text-part",c,{opacity:1,x:0,delay:i}).addTo(e);TweenMax.to($(".line4-1"),0,{width:0,height:4,opacity:0});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(5)",offset:o}).setTween(".line4-1",.1,{opacity:1,delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(5)",offset:o}).setTween(".line4-1",n,{width:line_width+"%",delay:0}).setClassToggle(".line4-1","line_end").addTo(e),new ScrollMagic.Scene({triggerElement:".func-step:nth-child(5)",offset:o}).setTween(".line4-1",t,{height:"+="+line_height+"%",delay:n}).addTo(e);TweenMax.to($(".func-step:nth-child(5) img"),0,{opacity:0,x:100,scale:img_scale});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(5)",offset:o}).setTween(".func-step:nth-child(5) img",a,{opacity:1,x:30,scale:img_scale,delay:i}).addTo(e);TweenMax.to($(".func-step:nth-child(5) .text-part"),0,{opacity:0,x:100});new ScrollMagic.Scene({triggerElement:".func-step:nth-child(5)",offset:o}).setTween(".func-step:nth-child(5) .text-part",c,{opacity:1,x:0,delay:i}).addTo(e);TweenMax.to($(".index-why>h2"),0,{y:-100,opacity:0}),TweenMax.to($(".index-why>.row>h4"),0,{y:-100,opacity:0});new ScrollMagic.Scene({triggerElement:".index-why"}).setTween(".index-why>h2",.2,{y:0,opacity:1,delay:0}).addTo(e),new ScrollMagic.Scene({triggerElement:".index-why"}).setTween(".index-why>.row>h4",.2,{y:0,opacity:1,delay:.2}).addTo(e);TweenMax.to($(".whyenglish .columns"),0,{opacity:0}),TweenMax.to($(".index-why .btn"),0,{opacity:0});var l=TweenMax.staggerTo(".whyenglish .columns",.4,{opacity:1,ease:Back.easeOut,delay:.5},.15);new ScrollMagic.Scene({triggerElement:".index-why"}).setTween(l).addTo(e),new ScrollMagic.Scene({triggerElement:".index-why"}).setTween(".index-why .btn",.2,{opacity:1,delay:1.2}).addTo(e);TweenMax.to($(".index-now .text-wrapper"),0,{y:100,opacity:0});new ScrollMagic.Scene({triggerElement:".index-now",offset:-30}).setTween(".index-now .text-wrapper",.2,{y:0,opacity:1,delay:0}).addTo(e)}var l=(new TimelineMax).add([TweenMax.to(".index-now",1,{backgroundPosition:"0% -25%"})]);new ScrollMagic.Scene({triggerElement:".index-now",duration:$(window).height()}).setTween(l).addTo(e);TweenMax.to($("header"),0,{y:-10,opacity:0}),TweenMax.to($(".menu-wrapper h1.logo"),0,{scale:2,opacity:0}),TweenMax.to($(".main-menu a"),0,{y:-50,opacity:0}),TweenMax.to($(".freebtn"),0,{y:-110}),$(".main-menu").hasClass("page")&&TweenMax.to($(".menu-move-bar"),0,{opacity:0}),TweenMax.to($(".index-banner .slick-active img"),0,{scale:2,opacity:0}),TweenMax.to($(".hamburger"),0,{opacity:0})}),$(window).load(function(){var e=1;TweenMax.to($("header"),.5,{y:0,opacity:1,delay:.5}),TweenMax.to($(".menu-wrapper h1.logo"),.5,{scale:1,opacity:1,delay:e}),TweenMax.staggerTo(".main-menu a",.2,{y:0,opacity:1,delay:e+.3},.05),TweenMax.to(".freebtn",.3,{y:0,delay:e+.8}),$(".main-menu").hasClass("page")&&TweenMax.to(".menu-move-bar",.2,{opacity:1,delay:e+.8}),TweenMax.to($(".index-banner .slick-active img"),.5,{scale:1,opacity:1,delay:e}),TweenMax.to($(".hamburger"),.5,{opacity:1,delay:e}),TweenMax.to($(".index-function>h2"),.2,{y:0,opacity:1,delay:e+.5}),TweenMax.to($(".index-function>h4"),.2,{y:0,opacity:1,delay:e+.5}),$(window).innerWidth()<=1024&&TweenMax.to($(".func-step_0"),.2,{opacity:1,delay:e+.8})}),$(window).resize(function(e){lineSize()});