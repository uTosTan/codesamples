(function() {
	'use strict';

	angular
		.module('app')
		.controller('TopicController', TopicController);

	TopicController.$inject = ['Topic', 'searchQuery'];

	/* @ngInject */
	function TopicController(Topic, searchQuery) {
		var vm = this;
		vm.topics = [];
		vm.currentPage = 0;
		vm.scrollDisabled = false;
		searchQuery.query.data = '';
		vm.searchQuery  = searchQuery.query;
		vm.getTopics = getTopics;

		activate();

		////////////////

		function activate() {
		}

		function getTopics() {
			if (vm.scrollDisabled)
				return;
			vm.scrollDisabled = true;
			
			var topics = Topic.$search({ page: vm.currentPage+1 });

			topics.$then(function(topics) {
				vm.pages = this.$pageCount;

				if (vm.currentPage < vm.pages) {
					var tempTopics = topics.$response.data.topics;
					for (var i = 0; i < tempTopics.length; i++) {
						vm.topics.push(tempTopics[i]);
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