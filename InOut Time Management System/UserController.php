<?php namespace sysadmin;

use User;
use Department;
use College;
use Role;
use Response;
use Validator;
use View;
use Input;

class UserController extends \BaseController {

	protected $layout = 'templates.master';

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$data['title'] = 'Users';
		$data['search'] = '';
		if (Input::has('search')) {
			$data['search'] = Input::get('search');
			$data['users'] = User::where('first_name', 'LIKE', "%{$data['search']}%")->orWhere('last_name', 'LIKE', "%{$data['search']}%")->with('departments.college')->paginate(15);
		}
		else
			$data['users'] = User::with('departments.college')->paginate(15);
		$data['colleges'] = College::all();
		$data['departments'] = Department::all();
		$data['roles'] = Role::all();
		$this->layout->content = View::make('systemadmin.main')->with('title', 'Manage Users')
															   ->nest('innerContent', 'systemadmin.user.viewAll', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$response['code'] = 0;
		$userValidationRules = array(
										'department_id' => 'required|numeric',
										'first_name' => 'required',
										'last_name' => 'required',
										'email' => 'required',
										'role_id' => 'required|numeric'
									);
		$validator = Validator::make(Input::all(), $userValidationRules);
		if ($validator->fails()) {
			$response['text'] = 'Validation failed';
		} else {
			$user = User::where('email', Input::get('email'))->first();
			if (!$user) {
				$user = new User;
				$user->first_name = Input::get('first_name');
				$user->last_name = Input::get('last_name');
				$user->email = Input::get('email');
				$user->save();
			}

			if ($user->departments->contains(Input::get('department_id'))) {
				$response['text'] = 'User already exists';
			} else {
				$department = Department::find(Input::get('department_id'));
				$role = Role::find(Input::get('role_id'));
				if ($department && $role) {
					$user->departments()->attach($department->id, array('role_id' => $role->id));
					$response['code'] = 1;
					$response['text'] = 'User added';
					$response['id'] = $user->id;
					$response['cid'] = $department->college->id;
					$response['did'] = $department->id;
					$response['college'] = $department->college->long_name;
					$response['department'] = $department->name;

				} else {
					$response['text'] = 'Invalid IDs';
				}
			}
		}

		return Response::json($response);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$data['user'] = User::find($id);
		$data['colleges'] = College::all();
		$data['roles'] = Role::all();
		$data['title'] = 'User: ' . $data['user']->first_name . ' ' . $data['user']->last_name;

		$this->layout->content = View::make('systemadmin.main')->with('title', 'Manage Department')
															   ->nest('innerContent', 'systemadmin.user.view', $data);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$userValidationRules = array('first_name' => 'required',
									 'last_name' => 'required');
		$validator = Validator::make(Input::all(), $userValidationRules);
		if ($validator->fails()) {
			return 0;
		} else {
			$user = User::find($id);
			if ($user) {
				$user->first_name = Input::get('first_name');
				$user->last_name = Input::get('last_name');
				$user->save();
				return 1;
			} else {
				return 0;
			}
		}
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function toggleBoolCol()
	{
		$response['code'] = 0;
		$rules = array('user_id' => 'required|numeric',
					   'column' => 'required|alpha');
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			$response['text'] = 'Validation Failed';
		} else {
			$user = User::find(Input::get('user_id'));
			$column = Input::get('column');
			if ($column == 'active') {
				if ($user->is_active == 1) {
					$user->is_active = 0;
				} else {
					$user->is_active = 1;
				}
				$user->save();
			} else if ($column == 'sysadmin') {
				if ($user->is_system_admin == 1) {
					$user->is_system_admin = 0;
				} else {
					$user->is_system_admin = 1;
				}
				$user->save();
			}
			$response['code'] = 1;
		}

		return Response::json($response);
	}

	public function addToDepartment()
	{
		$response['code'] = 0;
		$rules = array('user_id' => 'required|numeric',
					   'department_id' => 'required|numeric',
					   'role_id' => 'required|numeric');
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			$response['text'] = 'Validation Failed';
		} else {
			$user = User::find(Input::get('user_id'));
			$department = Department::find(Input::get('department_id'));
			$role = Role::find(Input::get('role_id'));
			if ($user && $department) {
				if ($user->departments->contains($department->id)) {
					$response['text'] = 'User is already in the department';
				} else {
					$user->departments()->attach($department->id, array('role_id' => $role->id));
					$response = array('code' => 1,
									  'text' => 'User Added',
									  'did' => $department->id,
									  'department' => $department->long_name,
									  'cid' => $college_id,
									  'college' => $college->long_name,
									  'role' => $role->long_name);
				}
			} else {
				$response['text'] = 'Invalid IDs';
			}
		}

		return Response::json($response);
	}

}