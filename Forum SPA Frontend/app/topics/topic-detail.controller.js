(function() {
	'use strict';

	angular
		.module('app')
		.controller('TopicDetailController', TopicDetailController);

	TopicDetailController.$inject = ['$stateParams', 'Topic', 'searchQuery'];

	/* @ngInject */
	function TopicDetailController($stateParams, Topic, searchQuery) {
		var vm = this;
		searchQuery.query.data = '';
		vm.searchQuery  = searchQuery.query;
		vm.scrollDisabled = false;
		vm.posts = []
		vm.currentPage = 0;
		vm.title = 'TopicDetailController';
		vm.getTopicDetails = getTopicDetails;
		
		var topic = Topic.$find($stateParams.topicId);

		topic.$then(function(topic) {
			vm.topic = topic.$response.data.topic;
		});

		activate();

		////////////////

		function activate() {

		}

		function getTopicDetails() {
			if (vm.scrollDisabled)
				return;
			vm.scrollDisabled = true;

			var posts = topic.posts.$fetch({ page: vm.currentPage+1 });

			posts.$then(function(posts) {
				vm.pages = this.$pageCount;

				if (vm.currentPage < vm.pages) {
					var tempPosts = posts.$response.data.posts;
					for (var i = 0; i < tempPosts.length; i++) {
						vm.posts.push(tempPosts[i]);
					}
					vm.currentPage = this.$page;

					if (vm.currentPage < vm.pages)
						vm.scrollDisabled = false;
					
				}
				else {
					vm.scrollDisabled = true;
				}
			});
		}
	}
})();