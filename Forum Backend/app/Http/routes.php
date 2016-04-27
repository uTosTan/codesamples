<?php

use App\User;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

// host angular here
Route::get('/', function () {
	return view('welcome');
});

Route::post('signup', function (Request $request) {

	$validator = Validator::make($request->all(), [
	    'name' => 'required|max:255',
	    'email' => 'required|email|max:255',
	    'password' => 'required|max:255'
	]);

	if ($validator->fails()) {
	    return response()->json(['error' => 'Validation Error', 'validation_errors' => $validator->errors()], 400);
	}

	$credentials = $request->only('name', 'email', 'password');
	$credentials['password'] = Hash::make($credentials['password']);

	try {
		$user = User::create($credentials);
	} catch (Exception $e) {
		return response()->json(['error' => 'User already exists.'], 409);
	}

	$token = JWTAuth::fromUser($user);

	return response()->json(compact('token'));
});

Route::post('signin', function (Request $request) {

	$validator = Validator::make($request->all(), [
	    'email' => 'required|email|max:255',
	    'password' => 'required|max:255'
	]);

	if ($validator->fails()) {
	    return response()->json(['error' => 'Validation Error', 'validation_errors' => $validator->errors()], 400);
	}

	$credentials = $request->only('email', 'password');

	try {
		$user = User::where('email', $request->email)->firstOrFail();
	} catch (ModelNotFoundException $e) {
		return response()->json(['error' => 'invalid_credentials'], 401);
	}

	if ( ! $token = JWTAuth::attempt($credentials, ['name' => $user->name ])) {
		return response()->json(['error' => 'invalid_credentials'], 401);
	}

	return response()->json(compact('token'));
});

// Test route for restricted data (to be removed)
Route::get('restricted', ['middleware' => 'jwt.auth', function () {
	$token = JWTAuth::getToken();
    $user = JWTAuth::toUser($token);

    return response()->json([
    	'data' => [
        	'email' => $user->email,
            'registered_at' => $user->created_at->toDateTimeString()
        ]
    ]);
}]);

Route::get('user/{id}', function($id) {
	$user = User::find($id);

	return response()->json(['user' => $user]);
});

Route::get('test', function() {
	$users = User::paginate(10);

	return $users;
});

Route::resource('topic/{id}/posts', 'TopicController@showPosts');
Route::resource('topic', 'TopicController');
Route::resource('post', 'PostController');
