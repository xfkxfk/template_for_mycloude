angular.module('sniffer', []).controller('allfields', ['$scope', '$http', '$location',
  function($scope, $http, $location) {
    var search = $location.search();

    $scope.field = search.field;
    $scope.field_cn = search.field_cn;

    $scope.load = function(start, type) {
      $http.get ('php/top.php?field=' + $scope.field).then(function (res) {
        $scope.data = res.data;
      });
    };

    $scope.load(0);
  }
]);
