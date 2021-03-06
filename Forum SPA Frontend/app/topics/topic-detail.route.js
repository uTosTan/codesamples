(function() {
	'use strict';

	angular
		.module('app')
		.config(config);

	config.$inject = ['$stateProvider'];

	function config($stateProvider) {
		$stateProvider
			.state('topic', {
				url: '/topic/{topicId:\\d+}',
				views: {
					'breadcrumbs': {
						templateUrl: 'app/shared/breadcrumbs.html',
						controller: 'TopicDetailController',
						controllerAs: 'topic'
					},
					'main': {
						templateUrl: 'app/topics/topic-detail.html', 
						controller: 'TopicDetailController',
						controllerAs: 'topic'
					}
				}
			})
	}
})();