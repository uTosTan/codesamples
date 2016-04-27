(function() {
	'use strict';

	angular
		.module('app')
		.filter('date_parse', date_parse);

	function date_parse() {
		return filterFilter;

		////////////////

		function filterFilter(db_date) {
			var date = Date.parse(db_date);
			return date.toString("ddd dS MMM yy, hh:mm tt");
		}
	}

})();