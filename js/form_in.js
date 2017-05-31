
$(function(){

	/*dropdown setting*/
	var $selector = $(".dropdown");
	$selector.select2({
		theme: "bootstrap"
	});
	$selector.on("select2:select", function(e) {
		$("#main-view").removeClass('freeze');
	});
	$("#main-view").on("scroll", function(){
		$(".dropdown").select2("close");
	})
	$(".occupation-level select").change(function(e) {
		$(this).parents(".occupation-level").next(".occupation-level").find('select').prop("disabled", false);
	});

	/*scroll to section animation*/
	var $tag = $(".tag-set").html();
	$(".tag-box").html("").append($tag).localScroll({
		target:'#main-view'
	});

})

/*filter*/
jQuery.fn.filterByText = function(textbox, selectSingleMatch) {
    return this.each(function() {
        var select = this;
        var options = [];
        $(select).find('li').each(function() {
            options.push({value: $(this).attr('title'), text: $(this).html()});
        });
        $(select).data('options', options);
        $(textbox).bind('change keyup', function() {
            var options = $(select).empty().data('options');
            var search = $.trim($(this).val());
            var regex = new RegExp(search,"gi");
          
            $.each(options, function(i) {
                var option = options[i];
                if(option.text.match(regex) !== null) {
                    $(select).append(
                       $('<li>').text(option.text).attr('title',option.value)
                    );
                }
            });
            if (selectSingleMatch === true && $(select).children().length === 1) {
                $(select).children().get(0).selected = true;
            }
        });          
    });
};


$(function(){
    /*filter*/
    $('#select-ul').filterByText($('#textbox'), true);
    var $text = $('.select-block li.selected').html();
    $('#textbox').val($text);

    $(document).on("click", ".select-block li", function(e) {
        $('.select-block li').removeClass();
        $(this).addClass('selected')
        var $text = $(this).html();
        $('#textbox').val($text);
    });
    



    $.each($(".birth-picker"), function () {
        var v_this = $(this);
        v_this.AnyPicker({
            mode: "datetime",
            selectedValues: v_this.val(),
            dateTimeFormat: "yyy-MM-dd",
            theme: "iOS"
        });
    });
    $.each($(".month-picker"), function () {
        var v_this = $(this);
        v_this.AnyPicker({
            mode: "datetime",
            selectedValues: v_this.val(),
            dateTimeFormat: "yyy-MM",
            theme: "iOS"
        });
    });

})

