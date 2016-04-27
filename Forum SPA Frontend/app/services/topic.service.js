(function() {
	'use strict';

	angular
		.module('app')
		.factory('Topic', Topic);

	Topic.$inject = ['restmod'];

	/* @ngInject */
	function Topic(restmod) {
		return restmod.model('http://ittc-dev.astate.edu/abhishek/forum-backend/public/topic').mix('PagedModel', {
			posts: 	{ hasMany: 'Post' },
			user: 	{ belongsTo: 'User', key: 'user_id' },
		}); 
	}
})();