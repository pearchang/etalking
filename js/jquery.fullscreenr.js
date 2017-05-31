/**
* Fullscreenr - lightweight full screen background jquery plugin
* By Jan Schneiders
* Version 1.0
* www.nanotux.com
**/
(function($){	
	$.fn.fullscreenr = function(options) {
		if(options.height === undefined) alert('Please supply the background image height, default values will now be used. These may be very inaccurate.');
		if(options.width === undefined) alert('Please supply the background image width, default values will now be used. These may be very inaccurate.');
		if(options.bgID === undefined) alert('Please supply the background image ID, default #bgimg will now be used.');
		var defaults = { width: 1600,  height: 689, bgID: 'bgimg' };
		var options = $.extend({}, defaults, options); 
		$(document).ready(function() { $(options.bgID).fullscreenrResizer(options);	});
		$(window).bind("resize", function() { $(options.bgID).fullscreenrResizer(options); });		
		return this; 		
	};	
	$.fn.fullscreenrResizer = function(options) {
		// Set bg size
		var ratio = options.height / options.width;
		// Get browser window size
		var browserwidth = $(window).outerWidth(true);
		var browserheight = $(window).outerHeight(true);
		var $MAINHeight = $('#MAIN'); //**** #MAIN
		// Scale the image
		if(browserwidth < 981 && browserheight < 360){
			if ((browserheight/browserwidth) > ratio){
		    	$(this).height(browserheight * 2);
		    	$(this).width(browserheight / ratio * 2);
				} else {
					$(this).height(browserwidth * ratio * 2);
					$(this).width(browserwidth * 2);
				}
			}else if(browserheight < 981 && browserwidth < 360){
				$(this).height(browserwidth* ratio * 2);
				$(this).width(browserwidth  * 2);
				}else{
					if ((browserheight/browserwidth) > ratio){
		    			$(this).height(browserheight);
		    			$(this).width(browserheight / ratio);
					} else {
		    			$(this).width(browserwidth);
		    			$(this).height(browserwidth * ratio );
					}
			}
		// Center the image
		//if ((browserheight/browserwidth) > 1.2){
		//	$(this).css('left', -1600);
		//} else {
			// $(this).css('left', (browserwidth - $(this).width())/2);
		//}
		
		//$(this).css('top', (browserheight - $(this).height())/2);
		$MAINHeight.css('height',browserheight); 
		//**** #MAIN高 = 視窗高
		
		return this; 		
	};
})(jQuery);