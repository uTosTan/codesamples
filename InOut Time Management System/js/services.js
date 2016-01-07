'use strict';

var inoutServices = angular.module('inoutServices', ['ngResource']);

inoutServices.factory('Department', ['$resource',
    function ($resource) {
        return $resource('departments-json', {}, {
            query: {method: 'GET', isArray:true}
        });
    }
]);

inoutServices.factory('College', ['$resource',
		function ($resource) {
			return $resource('colleges-json', {}, {
				query: {method: 'GET', isArray:true}
			});
		}
	]);

