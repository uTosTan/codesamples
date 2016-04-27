(function() {
    'use strict';
    var test;

    angular
    .module('app')
    .config(config);

    config.$inject = ['$urlRouterProvider', '$authProvider', 'restmodProvider'];

    function config($urlRouterProvider, $authProvider, restmodProvider) {
        /*
        * Expose API to $authProvider (login and signup)
        */
        $authProvider.loginUrl = 'http://ittc-dev.astate.edu/abhishek/forum-backend/public/signin';
        $authProvider.signupUrl = 'http://ittc-dev.astate.edu/abhishek/forum-backend/public/signup';

        /*
        * Assign default URL to $urlRouterProvider
        */
        $urlRouterProvider.otherwise('/topics');

        /*
        * Assign Style API to restmodProvider
        */
        restmodProvider.rebase('AMSApi');
    }
})();
