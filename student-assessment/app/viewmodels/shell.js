     define(function (require) {
      var router = require('plugins/router');
     
      return {
         router: router,
         activate: function () {
           router.map([
             { route: '', title:'Home', moduleId: 'viewmodels/home', nav: true },
             //{ route:'report', title:'Report', moduleId:'viewmodels/report', nav:true }
           ]).buildNavigationModel();
     
           return router.activate();
         }
       };
    });