(function() {
	'use strict';

	angular
		.module('app')
		.controller('AuthController', AuthController);

	AuthController.$inject = ['$auth', '$state',];

	/* @ngInject */
	function AuthController($auth, $state) {
		var vm = this;
		vm.title = 'AuthController';
		vm.login = login;
		vm.signup = signup;

		activate();

		////////////////

		function activate() {
		}

		function login() {
			var credentials = {
				email: vm.email,
				password: vm.password
			}

			$auth.login(credentials).then(function(data) {
				$state.go('topics', {});
			}).catch(function(response) {
				vm.error = response.data.error;
			});
		}

		function signup() {
			vm.error;

			var user = {
				name: vm.name,
				email: vm.email,
				password: vm.password
			}

			$auth.signup(user).then(function(response) {
				$state.go('login', {});
			}).catch(function(response) {
				vm.error = response.data.error;
			});
		}		
	}
})();