angular.module('sniffer', []).controller('optimize',
 ['$scope', '$http', '$pagination', '$stateParams', '$searchService',
  function($scope, $http, $pagination, $stateParams, $searchService) {
    $scope.types = $searchService.searchTypes;
    $scope.search = $searchService.default;
    $scope.result = {
      total: 0,
      data: []
    };

    $scope.title = '性能优化';
    $scope.search.extra = 'duration:[1 *]';
    $scope.showTags = true;

    $scope.export = function(format) {
      var cond = JSON.parse (JSON.stringify ($scope.search));
      Object.keys(cond).forEach(function (key) {
          if (cond[key] == null) delete cond[key];
      });
      return 'php/api.php?format=' + format + '&size=5000' + $searchService.serialize(cond);
    };

    $scope.load = function(start, type) {
      $searchService.search (start, type, $scope);
    };

    $scope.load(0);
  }
]);
