(function() {
	'use strict';

	angular
		.module('app')
		.config(config);

	config.$inject = ['$stateProvider'];

	function config($stateProvider) {
		$stateProvider
			.state('topic-create', {
				url: '/topic/create',
				views: {
					'main': {
						templateUrl: 'app/topics/topic-create.html', 
						controller: 'TopicCreateController',
						controllerAs: 'topic'
					}
				}
			})
	}
})();