angular.module('sniffer', []).controller('dashboard', ['$scope', '$http',
  function($scope, $http) {
    $scope.updateStatus = 0;
    $scope.statusString = [
      '立即更新',
      '检查中 ..'
    ]

    $scope.load = function() {
      $scope.loading = true;
      $http.get ('php/dashboard.php').then(function (res) {
        if (res.data.status == -10000) {
          window.location = 'login.php';
          return;
        }

        ['cpu_percent', 'disk_percent', 'mem_percent'].forEach(function (key) {
          $scope.sysinfo[key] = res.data[key];
        });

        // $scope.sysinfo['cpu_percent'] = 10;
        // $scope.sysinfo['disk_percent'] = 28;
        // $scope.sysinfo['mem_percent'] = 30;

        $scope.info = res.data;
        $scope.loading = false;
      });
    };

    $scope.checkUpdate = function() {
      $scope.updateStatus = 1;
      $http.get ('http://www.changesec.com/update/index.php').then(function (res) {
        var data = res.data;
        if (data.version > $scope.info.rulesversion) {
          if (confirm ('有新版本，更新吗？')) {
            $http.post ('php/update.php', res.data).then(function (res) {
              alert ('更新完毕，点击确定刷新页面');
              location.reload ();
            });
          }
        } else {
          alert ('您已经是最新版本了！');
          $scope.updateStatus = 0;
        }
      });
    };

    $scope.sysinfo = {
      cpu_options: {
        barColor: "#85c744",
        trackColor: '#edeef0',
        scaleColor: '#d2d3d6',
        scaleLength: 5,
        lineCap: 'square',
        lineWidth: 2,
        size: 90
      },
      cpu_percent: 24,

      disk_options: {
        barColor: "#f39c12",
        trackColor: '#edeef0',
        scaleColor: '#d2d3d6',
        scaleLength: 5,
        lineCap: 'square',
        lineWidth: 2,
        size: 90
      },
      disk_percent: 10,

      mem_options: {
        barColor: "#e73c3c",
        trackColor: '#edeef0',
        scaleColor: '#d2d3d6',
        scaleLength: 5,
        lineCap: 'square',
        lineWidth: 2,
        size: 90
      },
      mem_percent: 20,
    }

    $scope.load();
  }
]);
