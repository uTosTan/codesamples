(function() {
	'use strict';

	angular
		.module('app')
		.factory('User', User);

	User.$inject = ['restmod'];

	/* @ngInject */
	function User(restmod) {
		return restmod.model('http://ittc-dev.astate.edu/abhishek/forum-backend/public/user').mix({
			topics: { hasMany: 'Topic' },
			posts: 	{ hasMany: 'Post' }
		});
	}
})();