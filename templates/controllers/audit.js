angular.module('sniffer', []).controller('audit', [
  '$scope', '$http', '$pagination', '$stateParams', '$searchService', '$window', '$location',
  function($scope, $http, $pagination, $stateParams, $searchService, $window, $location) {
    $scope.types = $searchService.searchTypes;
    $scope.search = $searchService.default;
    $scope.result = {
      total: 0,
      data: []
    };

    var search = $location.search();
    if (search.search) {
      $scope.search.query = search.search;
    }

    $scope.title = '数据库审计';
    delete $scope.search.extra;

    $window.onscroll = function() {
      $scope.scrollPos = document.body.scrollTop || document.documentElement.scrollTop || 0;
      $scope.showScrollToTop = ($scope.scrollPos > 300);
      $scope.$apply();
    };

    $scope.export = function(format) {
      var cond = JSON.parse (JSON.stringify ($scope.search));
      Object.keys(cond).forEach(function (key) {
          if (cond[key] == null) delete cond[key];
      });
      return 'php/api.php?format=' + format + '&size=5000' + $searchService.serialize(cond);
    };

    $scope.load = function(start, type) {
      $searchService.search(start, type, $scope);
    };

    $scope.load(0);
  }
]);
