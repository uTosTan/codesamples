(function() {
	'use strict';

	angular
		.module('app')
		.factory('searchQuery', searchQuery);

	searchQuery.$inject = [];

	/* @ngInject */
	function searchQuery() {
		var service = {
			query: query
		};
		return service;

		////////////////

		function query() {
			return { query: { data: '' } };
		}
	}
})();