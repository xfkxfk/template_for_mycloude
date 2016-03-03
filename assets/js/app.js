require.config({
  baseUrl: './',

  waitSeconds: 120,

  paths: {
    sidebar: 'assets/js/sidebar',
    master: 'assets/js/master',
  },

  shim: {
    app: {
      deps: ['master', 'sidebar']
    }
  },

  urlArgs: ''
});

require(['app'], function() {
  angular.bootstrap(document, ['sniffer']);
});
