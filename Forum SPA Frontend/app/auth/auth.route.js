(function() {
	'use strict';

	angular
		.module('app')
		.config(config);

	config.$inject = ['$stateProvider'];

	function config($stateProvider) {
		$stateProvider
			.state('login', {
				url: '/login',
				views: {
					'main': {
						templateUrl: 'app/auth/login.html', 
						controller: 'AuthController',
						controllerAs: 'auth'
					}

				}
			})
			.state('signup', {
				url: '/signup',
				views: {
					'main': {
						templateUrl: 'app/auth/signup.html', 
						controller: 'AuthController',
						controllerAs: 'auth'
					}
				}
			});
	}
})();