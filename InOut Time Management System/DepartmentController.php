<?php namespace sysadmin;

use Department;
use College;
use Response;
use Validator;
use View;
use Input;
use Role;
use User;

class DepartmentController extends \BaseController {

	protected $layout = 'templates.master';

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$data['title'] = 'Departments';
		$data['colleges'] = College::all();
		$data['search'] = '';
		if (Input::has('search')) {
			$data['search'] = Input::get('search');
			$data['departments'] = Department::where('long_name', 'LIKE', "%{$data['search']}%")->orWhere('name', 'LIKE', "%{$data['search']}%")->with('college')->paginate(15);
		}
		else
			$data['departments'] = Department::with('college')->paginate(15);
		$this->layout->content = View::make('systemadmin.main')->with('title', 'Manage Departments')
															   ->nest('innerContent', 'systemadmin.department.viewAll', $data);
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
		$departmentValidationRules = array(
											'college_id' => 'required|numeric',
											'name' => 'required|min:2|unique:departments',
											'long_name' => 'required|min:4'
										  );
		$validator = Validator::make(Input::all(), $departmentValidationRules);
		if ($validator->fails()) {
			$response['code'] = 0;
			$response['text'] = 'Validation failed';
		} else {
			$college = College::find(Input::get('college_id'));
			if ($college) {
				$department = new Department;
				$department->name = Input::get('name');
				$department->long_name = Input::get('long_name');
				$college->departments()->save($department);
				$response['code'] = 1;
				$response['text'] = $department->long_name;
				$response['id'] = $department->id;
			} else {
				$response['code'] = 0;
				$response['text'] = 'Do not change the college id, you bad bad person';
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
		$data['department'] = Department::find($id);
		if ($data['department']) {
			$data['roles'] = Role::all();
			$data['title'] = 'College: ' . $data['department']->long_name;

			$this->layout->content = View::make('systemadmin.main')->with('title', 'Manage Department')
																   ->nest('innerContent', 'systemadmin.department.view', $data);
		}
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
		$departmentValidationRules = array('long_name' => 'required|min:4');
		$validator = Validator::make(Input::all(), $departmentValidationRules);
		if ($validator->fails()) {
			return 0;
		} else {
			$department = Department::find($id);
			if ($department) {
				$department->long_name = Input::get('long_name');
				$department->save();
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

	public function removeUser()
	{
		$response['code'] = 0;
		$rules = array('user_id' => 'required|numeric',
					   'department_id' => 'required|numeric');
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			$response['text'] = 'Validation Failed';
		} else {
			$department = Department::find(Input::get('department_id'));
			$user = User::find(Input::get('user_id'));
			if ($department && $user) {
				$department->users()->detach($user->id);
				$response['code'] = 1;
				$response['text'] = 'User Removed';
			} else {
				$response['text'] = 'Sneaky one, aren\'t you?';
			}
		}

		return Response::json($response);
	}

}