(function() {
	'use strict';

	angular
		.module('app')
		.factory('Post', Post);

	Post.$inject = ['restmod'];

	/* @ngInject */
	function Post(restmod) {
		return restmod.model('http://ittc-dev.astate.edu/abhishek/forum-backend/public/post').mix('PagedModel', {
			user: { belongsTo: 'User', key: 'user_id' }
		}); 
	}
})();