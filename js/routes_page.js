//recommend modal end active
  var menu_li_active;
  var route = 0;
  
// angular.module('allianz', ['ngRoute']);

angular.module("allianz", ["ui.router"]).config(function($stateProvider, $urlRouterProvider) {
  $urlRouterProvider.otherwise("/state7");
  $stateProvider
    .state('state7', {
      url: "/state7",
      templateUrl: "partials/product_list.html"
    })
    .state('state8', {
      url: "/state8",
      templateUrl: "partials/waiting.html",
    })
    .state('state9', {
      url: "/state9",
      templateUrl: "partials/not_finish.html",
    })
    .state('state10', {
      url: "/state10",
      templateUrl: "partials/finished.html",
    })
    .state('state11', {
      url: "/state11",
      templateUrl: "partials/print.html",
    })
}).run(function($rootScope){
  $rootScope.$on('$viewContentLoaded', function(){
    if($('#sidemenu').is(':not(.close)')){
      $('#sidemenu').toggleClass('close');
      $('.icon-ham').toggleClass('open');
      // $('#page-view').addClass('go');
    }

    //recommend modal end active
    // var menu_li_active;
    $(window).load(function() {
      menu_li_active = $('#sidemenu li.active').index();
      route = 1;
    });
    $('#sidemenu li').on('click', function(event) {
      event.preventDefault();
      if ($(this).find('a').is(':not([data-toggle])')) {
        menu_li_active = $(this).index();
      }else{
        $('#recommend').on('hidden.bs.modal', function(){
          $('#sidemenu li.active').removeClass('active');
          $('#sidemenu li').eq(menu_li_active).addClass('active')
        });
      }
    });

    if (route!=0) {
      $('.table-error .td').css('padding', '0');
    }

    /*add page search form*/
    $('.doc-search.stick').remove();
    var $form = $('.doc-search').addClass('stick');
    $("#page-view").before($form);

  });
});