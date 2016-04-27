(function() {
	'use strict';

	angular
		.module('app')
		.config(config);

	config.$inject = ['$stateProvider'];

	function config($stateProvider) {
		$stateProvider
			.state('post-create', {
				url: '/topic/{topicId:\\d+}/post/create',
				views: {
					'breadcrumbs': {
						templateUrl: 'app/shared/breadcrumbs.html',
						controller: 'TopicDetailController',
						controllerAs: 'topic'
					},                
					'main': {
						templateUrl: 'app/posts/post-create.html', 
						controller: 'PostCreateController',
						controllerAs: 'post'
					}
				}
			})
	}
})();