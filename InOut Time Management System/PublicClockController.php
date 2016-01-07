<?php

class PublicClockController extends BaseController {

	protected $layout = 'templates.master';

	public function __construct()
	{

		$this->beforeFilter(function() {
			if (!Session::has('deptIdForPC')) {
				if (Auth::check()) {
					if (Helpers::isDeptAdminOrHigher()) {
						Session::put('deptIdForPC', Auth::user()->current_department);
						Session::put('deptNameForPC', Department::find(Session::get('deptIdForPC'))->long_name);
					} else {
						if (Request::header('Referer') != URL::to('/') . '/dashboard') {
							return "Invalid Session";
						}
					}
				} else {

					return "Invalid Session";

				}
			}
		});
	}

	public function showClock()
	{
		$departmentName = Session::get('deptNameForPC');

		$this->layout->content = View::make('publicclock.main')->with('title', 'Public Clock-In')
															   ->with('department', $departmentName);
	}

	public function showPCUsers()
	{
		$departmentId = Session::get('deptIdForPC');
		$users = Helpers::usersWithLatestTime($departmentId);

		return View::make('publicclock.pcusers')->with('users', $users);
	}

	public function changeStatus($id, $type)
	{
		$user = User::find($id);
		if ($user) {	
			$status = $user->is_logged_in;
			$departmentId = ($type == 'self') ? Auth::user()->current_department : Session::get('deptIdForPC');

			if ($user->departments->contains($departmentId)) {
				if ($status) {
					// Is Logged In
					$user->is_logged_in = 0;
					$outtime = date('H:i:s');

					$log = Models\Log::find($user->current_log);
					$log->out_time = $outtime;
					$log->save();
					$user->current_log = NULL;

					$img = ($type == 'self') ? HTML::image('img/clockout.png') : HTML::image('img/red_ball.png');
					$intime = '';
					$outtime =  date('h:i:s A', strtotime($outtime));
				} else {
					// Is Not Logged In
					$user->is_logged_in = 1;
					$intime = date('H:i:s');

					$log = new Models\Log;
					$log->date = date('Y-m-d');
					$log->in_time = $intime;
					$log->user_id = $user->id;
					$log->department_id = $departmentId;
					$log->save();
					$user->current_log = $log->id;

					$img = ($type == 'self') ? HTML::image('img/clockin.png') : HTML::image('img/green_ball.png');
					$outtime = '';
					$intime = date('h:i:s A', strtotime($intime));
				}

				$user->save();
				
				return Response::json(array(
						'img' => $img,
						'intime' => $intime,
						'outtime' => $outtime
					));
			}
		}
	}

}