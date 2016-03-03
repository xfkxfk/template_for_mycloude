var app = angular.module('sniffer', ['ui.router', 'oc.lazyLoad', 'easypiechart', 'ui.select']);

// 保函后，替换掉原始内容
app.directive('includeReplace', function() {
  return {
    require: 'ngInclude',
    restrict: 'A',
    link: function(scope, elem, attrs) {
      elem.replaceWith(elem.children());
    }
  };
});
// 展开 & 关闭菜单
app.directive('metismenu', function() {
  return {
    link: function(scope, elem, attrs) {
      elem.metisMenu();
    }
  }
});
// 日期选择器
app.directive('mydatetimepicker', function() {
  return {
    link: function(scope, elem, attrs) {
      elem.datepicker({
        // todayBtn: "linked",
        language: "zh-CN",
        autoclose: true,
        format: "yyyy-mm-dd 00:00:00"
      });
      elem.on('changeDate', function(e) {
        scope.$apply();
        scope.load(0);
      });
    }
  }
});
app.factory('$searchService', ['$http', '$pagination', function($http, $pagination) {
  var searchService = {};
  // 预先定义的搜索类型
  searchService.searchTypes = ['全部类型', '数据读取', '数据写入', '数据更新'];
  // 默认条件
  searchService.default = {
    from: 0,
    size: 20,
    start_time: moment().startOf('month').format('YYYY-MM-DD 00:00:00'),
    end_time: moment().format('YYYY-MM-DD HH:mm:ss'),
    query: null,
    db_name: null,
    db_user: null,
    type: "0"
  };
  // 拼接查询语句
  searchService.serialize = function(json) {
    var query = '';
    Object.keys(json).forEach(function(e) {
      query = query + '&' + e + '=' + (e === 'size' ? 5000 : escape(json[e]));
    });

    return query;
  };
  // 搜索函数
  searchService.search = function(start, type, $scope, extra) {

    // 修复 start 参数
    if (start == -1) {
      start = Math.floor($scope.result.total / $scope.search.size) * $scope.search.size;
    }
    if (start > $scope.result.total || start < 0) {
      return;
    }

    $scope.loading = true;

    // 处理查找类型参数
    if (type !== undefined) {
      $scope.search.query = $scope.types[type];
    }

    $scope.search.from = start;

    $http.post('php/api.php', $scope.search).then(function(res) {
      $scope.loading = false;
      
      if (res.data.status == -10000) {
        window.location = 'login.php';
        return;
      }

      $scope.result = res.data.data;

      var data = $pagination($scope.search.from, $scope.search.size, $scope.result.total);
      $scope.pages = data.pages;
      $scope.end = data.end;
    });
  };
  return searchService;
}]);
// 生成分页数组的函数
app.factory('$pagination', function() {
  return function(start, size, total) {
    // 修复 end
    var end = (start + size > total) ? total : start + size;

    // 修复 paging 最多 10 个按钮
    var pg_start = Math.floor(start / size);
    if (pg_start > 2) {
      pg_start -= 2;
    } else {
      pg_start = 0;
    }

    var pg_end = Math.floor(total / size);
    if (pg_end - pg_start > 4) {
      pg_end = pg_start + 4;
    }
    if (pg_end - pg_start <= 5 && pg_start > 5) {
      pg_start = pg_end - 4;
    }

    // 从起点到终点算出数组
    var pages = [];
    for (var i = pg_start; i <= pg_end; ++i) {
      pages[pages.length] = i;
    }

    return {
      end: end,
      pages: pages
    };
  }
});

app.config(['$stateProvider', '$urlRouterProvider',
  function($stateProvider, $urlRouterProvider) {
    $urlRouterProvider.otherwise('/pages/dashboard');

    $stateProvider.state('index', {
      url: '/pages/:name',
      abstract: true,
      templateUrl: function($stateParams) {
        return 'templates/pages/' + $stateParams.name + '.html?ver=' + (new Date()).getTime();
      },
      controllerProvider: function(loader) {
        return loader;
      },
      resolve: {
        loader: ['$ocLazyLoad', '$stateParams', function($ocLazyLoad, $stateParams) {
          var url = 'templates/controllers/' + $stateParams.name + '.js?ver=' + (new Date()).getTime();

          return $ocLazyLoad.load(url).then(function() {
            return $stateParams.name;
          });
        }]
      }
    }).state('index.id', {
      url: '?id'
    });

  }
]);
