
// angular.module('allianz', ['ngRoute']);

angular.module("allianz", ["ui.router"]).config(function($stateProvider, $urlRouterProvider) {
  $urlRouterProvider.otherwise("/state1");
  $stateProvider
    .state('state1', {
      url: "/state1",
      templateUrl: "partials/form_1.html"
    })
    .state('state2', {
      url: "/state2",
      templateUrl: "partials/form_2.html"
    })
    .state('state3', {
      url: "/state3",
      templateUrl: "partials/form_3.html"
    })
    .state('state4', {
      url: "/state4",
      templateUrl: "partials/form_4.html"
    })
    .state('state5', {
      url: "/state5",
      templateUrl: "partials/form_5.html"
    })
    .state('state6', {
      url: "/state6",
      templateUrl: "partials/form_6.html"
    })
    //page begin
    .state('state7', {
      url: "/state7",
      templateUrl: "partials/product_list.html"
    })
    .state('state8', {
      url: "/state8",
      templateUrl: "partials/wating.html"
    })
    .state('state9', {
      url: "/state9",
      templateUrl: "partials/not_finish.html"
    })
    .state('state10', {
      url: "/state10",
      templateUrl: "partials/finished.html"
    })
}).run(function($rootScope){
  $rootScope.$on('$viewContentLoaded', function(){
    if($('#sidemenu').is(':not(.close)')){
      $('#main-view').addClass('go');
    }
  });
});







