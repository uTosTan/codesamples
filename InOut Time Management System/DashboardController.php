<?php

class DashboardController extends BaseController {
	
	protected $layout = 'templates.master';

	public function __construct()
	{
		$this->beforeFilter('auth');
	}

	public function testMethod()
	{
		$users = User::with(array('departments' => function($query)
		{
			$query->where('id', '=', 2);
		}))->get();

		foreach ($users as $user)
		{
			echo $user->first_name;
		}

	}

	public function getHours($departmentId, $userId, $week, $year)
	{
		$week_start = date('Y-m-d', strtotime($year.'W'.$week));
		$week_end = date('Y-m-d', strtotime($week_start.'+6 days'));

		$logs = Models\Log::where('department_id', '=', $departmentId)
						  ->where('user_id', '=', $userId)
						  ->where('date', '>=', $week_start)
						  ->where('out_time', '!=', 'NULL')
						  ->where('date', '<=', $week_end)
						  ->get();

		$hoursWorked = 0;
		foreach ($logs as $log) {
			$hoursWorked = $hoursWorked + ((strtotime($log->out_time) - strtotime($log->in_time))/3600);
		}

		$hoursWorked = round($hoursWorked, 2);
		return $hoursWorked;
	}

	public function getHoursFromReports($departmentId, $userId, $week, $year) {
		$hoursWorked = 0;
		$report = Report::where('department_id','=', $departmentId)
					   	->where('user_id','=',$userId)
					   	->where('week', '=', $week)
					   	->where('year', '=', $year)
					   	->first();
		if ($report) {
			$hoursWorked = $report->hours;
		}

		return round($hoursWorked,2);
	}

	public function showDashboard()
	{
		$departmentId = Auth::user()->current_department;
		$department = Department::find($departmentId)->long_name;
		$week = date('W');
		$year = date('Y');

		$hoursWorked = $this->getHours($departmentId, Auth::user()->id, $week, $year);

		$this->layout->content = View::make('dashboard.main')
										->with('title', 'Dashboard')
										->with('department', $department)
										->with('hoursWorked', $hoursWorked);
	}

	public function showReport($year=NULL, $week=NULL)
	{
		$departmentId = Auth::user()->current_department;
		$users = Department::find($departmentId)->users;
		$availableReports = Report::select('year', 'week')
						 		  ->where('department_id','=',$departmentId)
								  ->orderBy('year', 'desc')
								  ->orderBy('week', 'desc')
								  ->distinct()
								  ->get();

		if (!$year && !$week) {
			$week = date('W');
			$year = date('Y');
			$week_start = date('Y-m-d', strtotime($year.'W'.$week));
			$week_end = date('Y-m-d', strtotime($week_start.'+6 days'));

			foreach ($users as &$user) {
				$user->hoursWorked = $this->getHours($departmentId, $user->id, $week, $year);
			}

			$code = NULL;
		} else {
			//if ($week < 10) $week = '0'.$week;
			$week_start = date('Y-m-d', strtotime($year.'W'.$week));
			$week_end = date('Y-m-d', strtotime($week_start.'+6 days'));

			foreach ($users as &$user) {
				$user->hoursWorked = $this->getHoursFromReports($departmentId, $user->id, $week, $year);
			}

			$code = $year.'-'.$week;
		}
		
		$this->layout->content = View::make('dashboard.report')
												  ->with('title', 'Report')
												  ->with('users', $users)
												  ->with('weekStart', $week_start)
												  ->with('weekEnd', $week_end)
												  ->with('available_reports', $availableReports)
												  ->with('code', $code);
	}

	public function showUserData()
	{
		$departmentId = Auth::user()->current_department;
		$users = DB::table('users')
						->select('users.id', 'users.first_name', 'users.last_name', DB::raw('DATE_FORMAT(logs.in_time,"%r") as in_time'), DB::raw('DATE_FORMAT(logs.out_time,"%r") as out_time'), 'users.is_logged_in')
						->join('department_user', 'department_user.user_id', '=', 'users.id')
						->join('departments', 'departments.id', '=', 'department_user.department_id')
						->leftJoin(DB::raw('(SELECT user_id, MAX(created_at) MaxDate FROM logs GROUP BY user_id) MaxDates'), function($join) {
								$join->on('MaxDates.user_id', '=', 'users.id');
						  })
						->leftJoin('logs', function($join){
								$join->on('MaxDates.user_id', '=', 'logs.user_id');
								$join->on('MaxDates.MaxDate', '=', 'logs.created_at');
						  })
						->where('departments.id', '=', $departmentId)
						->where('users.id', '<>', Auth::user()->id)
						->get();
		return View::make('dashboard.userdata')->with('users', $users);
	}

	public function showLogData()
	{
		$departmentId = Auth::user()->current_department;
		$logs = Models\Log::where('department_id', '=', $departmentId)
						  ->where('user_id', '=', Auth::user()->id)
						  ->where('out_time', '!=', '')
						  ->orderBy('created_at', 'desc')
						  ->select('date', 'user_id', 'department_id', DB::raw('DATE_FORMAT(out_time,"%r") as out_time'), DB::raw('DATE_FORMAT(in_time,"%r") as in_time'), 'created_at')
						  ->take(5)
						  ->get();

		return View::make('dashboard.logdata')->with('logs', $logs);
	}

	public function updateLogData()
	{
		$rules = array(
				'log_id' => 'required|numeric',
				'in_time' => 'required|date_format:"H:i:s"',
				'out_time' => 'required|date_format:"H:i:s"'
			);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			return Response::json(array('flash' => 'fail'), 500);
		} else {
			$log = Models\Log::find(Input::get('log_id'));

			if ($log)
			{
				if ($log->user == Auth::user()) {
					$log->in_time = Input::get('in_time');
					$log->out_time = Input::get('out_time');
					$log->save();

					return Response::json(array('flash' => 'success'), 200);
				}
			}
			return Response::json(array('flash' => 'fail'), 500);
					
		}
	}

	public function showLogs()
	{
		$departmentId = Auth::user()->current_department;
		$department = Department::find($departmentId)->long_name;
		$logs = Models\Log::where('department_id', '=', $departmentId)
						  ->where('user_id', '=', Auth::user()->id)
						  ->where('out_time', '!=', '')
						  ->orderBy('created_at', 'desc')
						  ->paginate(15);
						  //->get();

		$this->layout->content = View::make('dashboard.logs')
										->with('title', 'Logs')
										->with('logs', $logs)
										->with('department', $department);
	}

	public function showHasNoDept()
	{
		$this->layout->content = View::make('dashboard.hasNoDept')->with('title', 'Error: No Department Assigned');
	}

	public function chooseDepartment()
	{
		$this->layout->content = View::make('dashboard.chooseDepartment')->with('title', 'Choose Department');
	}

	public function chooseDepartmentPost()
	{
		$rules = array(
				'currentDepartment' => 'required'
			);
		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			return Redirect::to('cd')->with('cd_errors', true);
		} else {
			if (!Department::find(Input::get('currentDepartment'))) return Redirect::to('cd')->with('cd_errors', true);
			$user = Auth::user();
			$user->current_department = Input::get('currentDepartment');
			$user->save();
			return Redirect::to('dashboard');
		}
	}

	public function changeStatusSelf()
	{
		$user = Auth::user();
		$current_status = $user->is_logged_in;

		if ($current_status) { // Is logged in (1)
			$user->is_logged_in = 0;
			$outtime = date('H:i:s');

			$log = Models\Log::find($user->current_log);
			$log->out_time = $outtime;
			$log->save();

			$user->current_log = NULL;

			$returnTime = "OUT: " . date('h:i:s A', strtotime($outtime));
		} else {
			$user->is_logged_in = 1;
			$intime = date('H:i:s');

			$log = new Models\Log;
			$log->date = date('Y-m-d');
			$log->in_time = $intime;
			$log->user_id = $user->id;
			$log->department_id = $user->current_department;
			$log->save();

			$user->current_log = $log->id;
			$returnTime = "IN: " . date('h:i:s A', strtotime($intime));
		}

		$user->save();
		return $returnTime;
	}

	public function changeStatus($id, $type)
	{
		$user = User::find($id);
		$status = $user->is_logged_in;

		if ($status) {
			$user->is_logged_in = 0;

			$log = Models\Log::find($user->current_log);
			$log->out_time = date('H:i:s');
			$log->save();
			$user->current_log = NULL;

			$img = ($type == 'self') ? HTML::image('img/clockout.png') : HTML::image('img/red_ball.png');
		} else {
			$user->is_logged_in = 1;

			$log = new Models\Log;
			$log->date = date('Y-m-d');
			$log->in_time = date('H:i:s');
			$log->user_id = $user->id;
			$log->department_id = $user->current_department;
			$log->save();
			$user->current_log = $log->id;

			$img = ($type == 'self') ? HTML::image('img/clockin.png') : HTML::image('img/green_ball.png');
		}

		$user->save();
		return $img;
	}

	public function showAdmin()
	{
		$department = Auth::user()->current_department;

		$role_id = Auth::user()->departments->find($department)->pivot->role_id;

		return Role::find($role_id)->level;
	}

	public function addTime()
	{
		$timeRules = array(
				'date' => 'required|date_format:"Y-m-d"',
				'in_time' => 'required|date_format:"H:i:s"',
				'out_time' => 'required|date_format:"H:i:s"'
			);

		$validator = Validator::make(Input::all(), $timeRules);

		if (!$validator->fails()) {
			$in_time = Input::get('in_time');
			$out_time = Input::get('out_time');

			if (strtotime($out_time) > strtotime($in_time)) {

				$user = Auth::user();

				$log = new Models\Log;
				$log->date = Input::get('date');
				$log->in_time = Input::get('in_time');
				$log->out_time = Input::get('out_time');
				$log->user_id = $user->id;
				$log->department_id = $user->current_department;
				$log->save();

				return 'success';
			}
		}

		return 'invalid';
	}


}