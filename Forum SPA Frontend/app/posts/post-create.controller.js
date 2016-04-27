(function() {
    'use strict';

    angular
        .module('app')
        .controller('PostCreateController', PostCreateController);

    PostCreateController.$inject = ['Post', '$auth', '$state', '$stateParams'];

    /* @ngInject */
    function PostCreateController(Post, $auth, $state, $stateParams) {
        var vm = this;
        vm.createPost = createPost;

        activate();

        ////////////////

        function activate() {
        	if (!$auth.isAuthenticated())
        		$state.go('login', {});
        }

        function createPost() {
        	var data = {
        		topic_id: $stateParams.topicId,
        		content: vm.content
        	};

        	var newPost = Post.$create(data);

        	newPost.$then(
                function(post) {
        		  $state.go('topic', { topicId: $stateParams.topicId });
                },
                function(error) {
                    console.log(error); // add error handling
                }
            );
        }
    }
})();