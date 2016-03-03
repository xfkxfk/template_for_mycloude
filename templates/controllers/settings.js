angular.module('sniffer', []).controller('settings', ['$scope', '$http', '$stateParams',
  function($scope, $http, $stateParams) {
    $scope.all_dbs = [];
    $scope.all_users = [];

    $scope.save = function() {
      var regexIsValid = true, invalidOne = '', allres = [];
      $scope.formData.sqli.split ("\n").some(function (reStr) {
        reStr = reStr.trim ();
        if (reStr.length == 0) {
          return false;
        }

        try {
          var re = new RegExp (reStr);
          allres[allres.length] = reStr;
        } catch (e) {
          regexIsValid = false;
          invalidOne = "正则:\n" + reStr + "\n原因:\n" + e;
        }

        return ! regexIsValid;
      });
      if (! regexIsValid) {
        alert (invalidOne);
        return;
      }

      var json = JSON.parse (JSON.stringify ($scope.formData));
      json.sqli = allres;

      $http.post('php/client.php', json).then(function(res) {
        if (res.data.status == -10000) {
          window.location = 'login.php';
          return;
        }

        alert(res.data.descr);
      });
    };

    $scope.load = function() {
      $http.get('php/top.php?field=db').then(function (res) {
        $scope.all_dbs = res.data;
      });
      $http.get('php/top.php?field=user').then(function (res) {
        $scope.all_users = res.data;
      });
      $http.get('data/config.json?ts=' + (new Date())).then(function(res) {
        $scope.formData = res.data;
        $scope.formData.sqli = $scope.formData.sqli.join ("\n");
      });
    };

    $scope.load();
  }
]);
