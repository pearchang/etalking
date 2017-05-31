$(function(){
	//language
	$languageBar = $('#language-bar');
	$languageBar.find('li:not(.focus,:last)').hide();

  	$languageBar.mouseenter(function() {
		$languageBar.find('li:not(:last)').show();
  	}).mouseleave(function() {
    	$languageBar.find('li:not(.focus,:last)').hide();
  	});

  	//news_content scroll top
  	$('.scroll-top-btn').click(function(){
  		$("body").stop(true,true).animate({
			scrollTop: 0
		}, 600);
  		console.log("TOP");
  		return false;
	});

	//prod list arrow
	$arrow_type = 0; // 可調整模式 同/反向運動
	if($arrow_type){
		console.log("type A");
		$('.arrow-ui a.next-btn').hide();}
	else{
		console.log("type B");
		$('.arrow-ui a.prev-btn').hide();
	}

	for (var i = 0; i <= $('.prod-list').length - 1; i++) {
		$this = $('.prod-list').eq(i);
		$listL = $this.find('li').length;
		if($listL>5){
			$this.siblings('.arrow-ui').show();
		}
		$this.find('ul').css({width:$listL*110});
		console.log($listL);
	};
	$('#product_tea .arrow-ui a').click(function(){
		$this = $(this);
		$thisProdList = $this.parent().siblings('.prod-list').find('ul');
		$thisProdListW = $thisProdList.width();
		$thisProdListX = $thisProdList.position().left;
		console.log("w: "+$thisProdListW+" x: "+$thisProdListX);
		
		if($this.hasClass('prev-btn')){
			console.log("prev ");
			if($arrow_type){
				if($thisProdListX > 550-$thisProdListW){
					$thisProdList.stop(true,true).animate({
						left: $thisProdListX-110
					}, 600);
				}

				$this.siblings().show();
				if($thisProdListX-110==550-$thisProdListW){
					$this.hide();
				}
			}else{
				if($thisProdListX < 0){
					$thisProdList.stop(true,true).animate({
						left: $thisProdListX+110
					}, 600);
				}

				$this.siblings().show();
				if($thisProdListX+110==0){
					$this.hide();
				}
			}
			
		}
		else if($(this).hasClass('next-btn')){
			console.log("next ");
			
			if($arrow_type){
				if($thisProdListX < 0){
					$thisProdList.stop(true,true).animate({
						left: $thisProdListX+110
					}, 600);
				}

				$this.siblings().show();
				if($thisProdListX+110==0){
					$this.hide();
				}
			}
			else{
				if($thisProdListX > 550-$thisProdListW){
					$thisProdList.stop(true,true).animate({
						left: $thisProdListX-110
					}, 600);
				}

				$this.siblings().show();
				if($thisProdListX-110==550-$thisProdListW){
					$this.hide();
				}
			}
		}




		return false;
	});
	
	//store popup
	$('#store .photo-list a').mouseenter(function() {
		$(this).find('img.scale-btn').show();
  	}).mouseleave(function() {
    	$(this).find('img.scale-btn').hide();
  	});

  	var $slideShowList = $('.photo-slide-list');
  	var $photoIndex=0;
	$('#store .photo-list a').click(function(){

		$photoIndex=$(this).parent().index();
  		// console.log($photoIndex);
  		$photoSource = $(this).parent().parent();
  		$slideShowList.find('li').removeClass('focus').eq($photoIndex).addClass('focus');
  		for (var i = 0; i < 6; i++) {
  			// console.log("src",$photoSource.find('li').eq(i).find('.store-photo').attr('src'));
  			$slideShowList.find('li').eq(i).find('img').attr(
  				"src",$photoSource.find('li').eq(i).find('.store-photo').attr('image')
  			);
  		};
		$('#photo-slideshow').bPopup({});
  		return false;
	});

	$('#photo-slideshow a.prev-btn').click(function(){
		$photoIndex--;
		if($photoIndex<0){$photoIndex=5;}
		$slideShowList.find('li').removeClass('focus').eq($photoIndex).addClass('focus');
		return false;
	});
	$('#photo-slideshow a.next-btn').click(function(){
		$photoIndex++;
		if($photoIndex>5){$photoIndex=0;}
		$slideShowList.find('li').removeClass('focus').eq($photoIndex).addClass('focus');
		return false;
	});

	//contact
	var $textClear = $('.send-edm-ui input');

	$textClear.each(function() {
		var $this = $(this),
		_title = $this.attr('title');
		
		$this.focus(function() {
			var _value = $this.val();

			if (_value == _title) {
				$this.val('');
			}
		}).blur(function() {
			var _value = $this.val();

			if (_value == '') {
				$this.val(_title);
			}
		});
	});


});