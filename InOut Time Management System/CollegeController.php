<?php namespace sysadmin;

use College;
use Response;
use Validator;
use View;
use Input;

class CollegeController extends \BaseController {

	protected $layout = 'templates.master';

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$data['title'] = 'Colleges';
		$data['search'] = '';
		if (Input::has('search')) {
			$data['search'] = Input::get('search');
			$data['colleges'] = College::where('long_name', 'LIKE', "%{$data['search']}%")->orWhere('name', 'LIKE', "%{$data['search']}%")->with('departments')->paginate(15);
		}
		else
			$data['colleges'] = College::with('departments')->paginate(15);

		$this->layout->content = View::make('systemadmin.main')->with('title', 'Manage Colleges')
															   ->nest('innerContent', 'systemadmin.college.viewAll', $data);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{

	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$collegeValidationRules = array('name' => 'required|unique:colleges',
										'long_name' => 'required|min:4');
		$validator = Validator::make(Input::all(), $collegeValidationRules);
		if ($validator->fails()) {
			$response['code'] = 0;
			$response['text'] = 'Validation failed';
		} else {
			$college = new College;
			$college->name = Input::get('name');
			$college->long_name = Input::get('long_name');
			$college->save();
			$response['code'] = 1;
			$response['text'] = $college->long_name;
			$response['id'] = $college->id;
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
		$data['college'] = College::find($id);
		if ($data['college']) {
			$data['title'] = 'College: ' . $data['college']->long_name;

			$this->layout->content = View::make('systemadmin.main')->with('title', 'Manage College')
																   ->nest('innerContent', 'systemadmin.college.view', $data);
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
		$collegeValidationRules = array('long_name' => 'required|min:4');
		$validator = Validator::make(Input::all(), $collegeValidationRules);
		if ($validator->fails()) {
			return 0;
		} else {
			$college = College::find($id);
			if ($college) {
				$college->long_name = Input::get('long_name');
				$college->save();
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

	public function getDepartments()
	{
		$response['code'] = 0;
		$rules = array('college_id' => 'required|numeric');
		$validator = Validator::make(Input::all(), $rules);
		if ($validator->fails()) {
			$response['text'] = 'Validation Failed';
		} else {
			$college = College::find(Input::get('college_id'));
			if ($college) {
				$response['code'] = 1;
				$response['text'] = 'Success';
				$response['departments'] = $college->departments;
			} else {
				$response['text'] = 'Invalid ID'; 
			}
		}

		return Response::json($response);
	}

}