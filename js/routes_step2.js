
// angular.module('allianz', ['ngRoute']);

angular.module("allianz", ["ui.router","ui.router","ngTouch"]).config(function($stateProvider, $urlRouterProvider) {
  $urlRouterProvider.otherwise("/state1");
  $stateProvider
    .state('state1', {
      url: "/state1",
      templateUrl: "partials/form_7.html"//拍照
    })
    .state('state2', {
      url: "/state2",
      templateUrl: "partials/form_8.html"//簽名
    })
    .state('state3', {
      url: "/state3",
      templateUrl: "partials/test.html"
    })
}).run(function($rootScope){
  $rootScope.$on('$viewContentLoaded', function(){
    if($('#sidemenu').is(':not(.close)')){
      $('#main-view').addClass('go');
    }
  });
}).controller('SIGN', function ($scope) {
  $scope.prevSlide = function () {
    $('#myCarousel').carousel('prev');
  };
  $scope.nextSlide = function () {
    $('#myCarousel').carousel('next');
  };
});