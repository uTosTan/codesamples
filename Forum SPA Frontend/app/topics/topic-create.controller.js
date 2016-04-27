(function() {
    'use strict';

    angular
        .module('app')
        .controller('TopicCreateController', TopicCreateController);

    TopicCreateController.$inject = ['Topic', '$auth', '$state'];

    /* @ngInject */
    function TopicCreateController(Topic, $auth, $state) {
        var vm = this;
        vm.createTopic = createTopic;

        activate();

        ////////////////

        function activate() {
        	if (!$auth.isAuthenticated())
        		$state.go('login', {});
        }

        function createTopic() {
        	var data = {
        		title: vm.title,
        		content: vm.content
        	};

        	var newTopic = Topic.$create(data);

        	console.log(newTopic);

        	newTopic.$then(
        		function(topic) {
        			$state.go('topic', { topicId: topic.$response.data.topic.id });
        		},
        		function(error) {
        			console.log(error); // add error handling
        		}
        	);
        }
    }
})();