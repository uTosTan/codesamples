(function() {
	'use strict';

	angular
		.module('app')
		.config(config);

	config.$inject = ['$stateProvider'];

	function config($stateProvider) {
		$stateProvider
			.state('topics', {
				url: '/topics',
				views: {
					'breadcrumbs': {
						templateUrl: 'app/shared/breadcrumbs.html',
						controller: 'TopicController',
						controllerAs: 'topic'
					},
					'main': {
						templateUrl: 'app/topics/topic-list.html', 
						controller: 'TopicController',
						controllerAs: 'topic'
					}
				}
			})
	}
})();