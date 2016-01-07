'use strict';

var inoutControllers = angular.module('inoutControllers', []);

inoutControllers.controller('CollegeListCtrl', ['$scope', 'College',
		function ($scope, College) {
			$scope.colleges = College.query();

			$scope.showBlock = function(id) {
				eval("show" + id + " = true;");
			};
		}
	]);

inoutControllers.controller('DepartmentListCtrl', ['$scope', 'Department',
        function ($scope, Department) {
            $scope.departments = Department.query();
            $scope.showBlock = function(id) {
                eval("show" + id + " = true;");
            };
        }
    ]);