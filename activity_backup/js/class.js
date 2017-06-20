$(function() {
  $('.status.form-class').on('click', function(){
    $(this).find('.form-class-select').toggleClass('active');
  });

  $('.class-member .name').on('mouseenter click', function(){
    $('.class-member .tip').removeClass('active');
    $(this).find('.tip').toggleClass('active');
  });

  $('.class-member .name').on('mouseleave', function(){
    $('.class-member .tip').removeClass('active');
  });

  $('.form-class a').on('click', function(event) {
    var classStatus = $(this).data('class');
    var thisInput = $(this).parents('.form-class');
    thisInput.find('input').val(classStatus);
  });

  $('.alert-bar a.button-close').click(function() {
		$('.alert-bar').slideUp(500);
  });

  $(window).resize(function(){
    var _winWidth = $(this).width();
    if(_winWidth < 1024) {
        $('.level-circle').circleProgress({
          value: 0.75,
          size: 50,
          fill: {
            gradient: ["#005AB5"]
          }
        });
    } else {
        $('.level-circle').circleProgress({
          value: 0.75,
          size: 70,
          fill: {
            gradient: ["#005AB5"]
          }
        });
    };
  }).resize();
  $('.demand-side input[type=radio]').click(function(){
    $('.demand-side-text').hide();
    if($(this).hasClass('demand-side-radio')) {
      $('.demand-side-text').show();
    };
  });
});

var nowTemp = new Date();
var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
var checkin = $('#datepicker1').fdatepicker().on('changeDate', function (ev) {
  if (ev.date.valueOf() > checkout.date.valueOf()) {
    var newDate = new Date(ev.date)
    newDate.setDate(newDate.getDate() + 1);
    checkout.update(newDate);
  }
  checkin.hide();
  $('#datepicker2')[0].focus();
}).data('datepicker');
var checkout = $('#datepicker2').fdatepicker().on('changeDate', function (ev) {
  checkout.hide();
}).data('datepicker');


$(function(){
  $('#datepicker3').fdatepicker({
    format: 'yyyy/mm',
    startView: 'year',
    minView: 'year',
  });
});


