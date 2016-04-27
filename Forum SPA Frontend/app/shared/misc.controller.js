(function() {
	'use strict';

	angular
		.module('app')
		.controller('MiscController', MiscController);

	MiscController.$inject = ['$auth', '$state', '$stateParams', 'Topic'];

	/* @ngInject */
	function MiscController($auth, $state, $stateParams, Topic) {
		var vm = this;
		var statesWithSearch = ['topics', 'topic'];
		vm.title = 'MiscController';
		vm.state = $state;
		vm.stateParams = $stateParams;
		vm.isAuthenticated = isAuthenticated;
		vm.showSearch = false;
		vm.payload = payload;
		vm.logout = logout;

		activate();

		////////////////

		function activate() {
			if (statesWithSearch.indexOf($state.current.name) > -1)
				vm.showSearch = true;
		}

		function isAuthenticated() {
			return $auth.isAuthenticated();
		}

		function payload() {
			return $auth.getPayload();
		}

		function logout() {
			$auth.logout();
			$state.reload();
		}
	}
})();