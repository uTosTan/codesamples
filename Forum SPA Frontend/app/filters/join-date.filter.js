(function() {
	'use strict';

	angular
		.module('app')
		.filter('join_date', join_date);

	function join_date() {
		return filterFilter;

		////////////////

		function filterFilter(db_date) {
			var date = Date.parse(db_date);
			return date.toString("MMM yyyy");
		}
	}

})();