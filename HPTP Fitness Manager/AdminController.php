<?php

class AdminController extends BaseController {

	protected $layout = 'templates.master';

	protected $hasUnverifiedUsers;

	protected $userRules;

	public function __construct()
	{
		$this->beforeFilter('auth.admin');
		$this->hasUnverifiedUsers = Client::where('is_verified', '=', 0)->count();
		$this->hasUsersInWaitlist = Client::where('on_waitlist', '=', 1)->count();
		View::share('actionName', explode('@', Route::currentRouteAction())[1]);

		$this->userRules = array(
					'email' => 'required|email',
					'first_name' => 'required',
					'last_name' => 'required'
		);
	}

	public function getIndex()
	{
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Administration Panel');
	}

	public function getUsers()
	{
		$users = User::with('roles', 'info')->where('is_verified', '=', 1)->orderBy('id')->paginate(10);
		$this->layout->content = View::make('admin.dashboard')->with('hasUnverifiedUsers', $this->hasUnverifiedUsers)->with('title', 'Users')
										->nest('innerContent', 'admin.userlist', array('users' => $users));
	}

	public function getTrainers()
	{
		$trainers = Trainer::with('user')->orderBy('id')->paginate(10);
		$semesters = Semester::all();
		$sections = Section::all();
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Trainers')
										->nest('innerContent', 'admin.trainerlist', array('trainers' => $trainers, 'semesters' => $semesters, 'sections' => $sections));

	}

	public function getTrainersJson($query = NULL)
	{
		$trainers = Response::json(DB::table('trainers')
					->join('users', 'users.id', '=', 'trainers.user_id')
					->select('users.email')
					->where('users.is_active', '=', 1)
					->get());
		return $trainers;
	}

	public function getAdmins()
	{
		$admins = Admin::with('user')->orderBy('id')->paginate(10);
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Administrators')
										->nest('innerContent', 'admin.adminlist', array('admins' => $admins));
	}

	public function postTestCase()
	{
		return 'okay';
	}

	public function postAddTrainer()
	{
		$validator = Validator::make(Input::all(), $this->userRules);

		if ($validator->fails()) {
			return 'invalid';
		} else { 
			$doesUserExist = User::where('email', '=', Input::get('email'))->count();

			if ($doesUserExist < 1) {
				$user = new User;
				$user->email = Input::get('email');
				$user->first_name = Input::get('first_name');
				$user->last_name = Input::get('last_name');
				$user->save();
			} else {
				$user = User::where('email', '=', Input::get('email'))->first();
				if ($user->client) {
					return 'invalid';
				}
			}

			$trainer = new Trainer;
			$trainer->semester_id = Input::get('semester');
			$trainer->section_id = Input::get('section');
			$user->trainer()->save($trainer);

			return 'success';
		}
	}

	public function postAddAdmin()
	{
		$validator = Validator::make(Input::all(), $this->userRules);

		if ($validator->fails()) {
			return 'invalid';
		} else { 
			$doesUserExist = User::where('email', '=', Input::get('email'))->count();

			if ($doesUserExist < 1) {
				$user = new User;
				$user->email = Input::get('email');
				$user->first_name = Input::get('first_name');
				$user->last_name = Input::get('last_name');
				$user->save();
			} else {
				$user = User::where('email', '=', Input::get('email'))->first();
				if ($user->client) {
					return 'invalid';
				}
			}

			$admin = new Admin;
			$user->admin()->save($admin);

			return 'success';
		}
	}

	public function postDeleteTrainer()
	{

		$ids = json_decode(Input::get('ids'));
		$ids = implode(',', $ids);
		$user = Trainer::destroy($ids);
		return 1;
	}

	public function postDeleteAdmin()
	{
		$ids = json_decode(Input::get('ids'));
		$ids = implode(',', $ids);
		$admin = Admin::destroy($ids);
		return 1;
	}

	public function getClients()
	{
		$search = Input::get('search');
		if (empty($search)) {
			$clients = Client::with('user')->where('on_waitlist', '=', '0')->orderBy('id')->paginate(10);
		} else {
			$clients = Client::with(array('user' => function($query) use ($search) {
														$query->where('email', 'LIKE', "%$search%");
													}))->where('on_waitlist', '=', '0')->orderBy('id')->paginate(10);
		}
		
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Clients')
										->with('hasUsersInWaitlist', $this->hasUsersInWaitlist)
										->nest('innerContent', 'admin.clientlist', array('clients' => $clients));
	}

	public function getPrintClient($id)
	{
		$client = Client::with('user')->where('id','=',$id)->first();

		return View::make('admin.printClient', array('client' => $client));
	}

	public function postClients()
	{
		$search = Input::get('search');
		$activities = Input::get('activities');
		$interests = Input::get('interests');

		if (isset($activities) && isset($interests)) {
			$clients = DB::table('client_activity')
								->join('client_interest', 'client_interest.client_id', '=', 'client_activity.client_id')
								->whereIn('activity_id', $activities)
								->whereIn('interest_id', $interests)
								->groupBy('client_id')
								->having(DB::raw('COUNT(DISTINCT activity_id)'), '=', count($activities))
								->having(DB::raw('COUNT(DISTINCT interest_id)'), '=', count($interests))
								->select('client_activity.client_id')->get();
		} else if (isset($activities) && !isset($interests)) {
			$clients = DB::table('client_activity')
								->whereIn('activity_id', $activities)
								->groupBy('client_id')
								->having(DB::raw('COUNT(DISTINCT activity_id)'), '=', count($activities))
								->select('client_id')->get();
		} else if (!isset($activities) && isset($interests)) {
			$clients = DB::table('client_interest')
								->whereIn('interest_id', $interests)
								->groupBy('client_id')
								->having(DB::raw('COUNT(DISTINCT interest_id)'), '=', count($interests))
								->select('client_id')->get();
		}


		if (isset($clients)) {
			foreach ($clients as $client) {
				$clientIds[] = $client->client_id;
			}
			$clients = Client::with(array(
					'user' => function($query) use ($search) {
						if (isset($search)) $query->where('email', 'LIKE', "%$search%");
					}))->whereIn('id', $clientIds)->orderBy('id')->paginate(10);
		} else {
			$clients = Client::with(array(
					'user' => function($query) use ($search) {
						if (isset($search)) $query->where('email', 'LIKE', "%$search%");
					}))->orderBy('id')->paginate(10);
		}

		$this->layout->content = View::make('admin.dashboard')->with('title', 'Clients')
										->with('hasUsersInWaitlist', $this->hasUsersInWaitlist)
										->nest('innerContent', 'admin.clientlist', array('clients' => $clients));
	}

	public function getWaitlist()
	{
		$clients = Client::with('user')->where('on_waitlist', '=', '1')->orderBy('id')->paginate(10);
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Clients')
										->with('hasUsersInWaitlist', $this->hasUsersInWaitlist)
										->nest('innerContent', 'admin.waitlist', array('clients' => $clients));		
	}

	public function getVerify()
	{
		$users = User::where('is_verified', '=', 0)->orderBy('id')->paginate(10);
		$this->layout->content = View::make('admin.dashboard')->with('hasUnverifiedUsers', $this->hasUnverifiedUsers)
										->nest('innerContent', 'admin.verifylist', array('users' => $users));
	}

	public function getSemesters()
	{
		$semesters = Semester::all();
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Semesters')
										->nest('innerContent', 'admin.semesterlist', array('semesters' => $semesters));
	}

	public function getEditSemester()
	{
		$semester = Semester::all()->first();
		return View::make('admin.modals.editSemester')->with('semester', $semester);
	}

	public function getSections()
	{
		$sections = Section::all();
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Sections')
										->nest('innerContent', 'admin.sectionlist', array('sections' => $sections));
	}

	public function postUpdateSemester()
	{
		$sid = Input::get('sid');
		$field = Input::get('type');
		$value = Input::get('value');
		$semester = Semester::find($sid);
		$semester->$field = $value;
		$semester->save();
	}

	public function postUpdateSection()
	{
		$sid = Input::get('sid');
		$field = Input::get('type');
		$value = Input::get('value');

		$section = Section::find($sid);
		$section->$field = $value;
		$section->save();
	}

	public function postUpdateStatus($id)
	{
		$user = User::find($id);
		$status = $user->is_active;

		if ($status == 1) {
			$user->is_active = 0;
			$img = HTML::image('img/redcross.png');
		} else {
			$user->is_active = 1;
			$img = HTML::image('img/greentick2.png');
		}

		$user->save();
		return $img;
	}

	public function postApproveClient()
	{
		$client = Client::find(Input::get('cid'));
		$user = User::find($client->user_id);

		if ($client->on_waitlist == 1) {
			$user->is_active = 1;
			$client->on_waitlist = 0;
			$user->save();
			$client->save();
		}
	}

	public function postActivateUser($id)
	{
		$user = User::find($id);
		$verified = $user->is_verified;

		if ($verified == 0) {
			$user->is_verified = 1;
			$user->is_active = 1;
			$user->save();
		}
	}

	public function postUpdateRole($id)
	{
		$user = User::find($id);
		$user->role_id = Input::get('roleId');
		$user->save();
	}

	/* Modal views (JS) */

	public function getAddTrainerModal()
	{
		$semesters = Semester::all();
		$sections = Section::all();
		return View::make('admin.modals.addTrainer')->with('semesters', $semesters)->with('sections', $sections);
	}

	public function getAddAdminModal()
	{
		return View::make('admin.modals.addAdmin');
	}

	public function getDeleteTrainerModal()
	{
		$ids = Input::get('ids');
		$users = User::whereIn('id', $ids)->get();
		$ids = json_encode($ids);
		return View::make('admin.modals.deleteTrainer')->with('ids', $ids)->with('users', $users);
	}

	public function getDeleteAdminModal()
	{
		$ids = Input::get('ids');
		$users = User::whereIn('id', $ids)->get();
		$ids = json_encode($ids);
		return View::make('admin.modals.deleteAdmin')->with('ids', $ids)->with('users', $users);
	}

	public function getDeleteClientModal()
	{

	}

	public function postUpdateTrainer()
	{
		$id = Input::get('id');
		$trainer = Input::get('trainer');
		if ($trainer != '') {
			$trainerId = DB::table('trainers')
						 ->select('trainers.id')
						 ->join('users', 'users.id', '=', 'trainers.user_id')
						 ->where('users.email', '=', $trainer)
						 ->first()->id;
			
			$trainer = Trainer::find($trainerId);

			$client = Client::find($id);
			$client->trainer()->associate($trainer);
			$client->save();
		} else {
			$client = Client::find($id);
			$client->trainer_id = NULL;
			$client->save();
		}
		return 1;
	}

	public function postUpdateTrainerSemester() {
		$sid = Input::get('sid');
		$tid = Input::get('tid');

		$semester = Semester::find($sid);

		$trainer = Trainer::find($tid);
		$trainer->semester()->associate($semester);
		$trainer->save();
	}

	public function postUpdateTrainerSection() {
		$sid = Input::get('sid');
		$tid = Input::get('tid');

		$section = Section::find($sid);

		$trainer = Trainer::find($tid);
		$trainer->section()->associate($section);
		$trainer->save();
	}

	public function getTestTrainer()
	{
		$trainerid = DB::table('trainers')
					 ->select('trainers.id')
					 ->join('users', 'users.id', '=', 'trainers.user_id')
					 ->where('users.email', '=', 'jreynolds@astate.edu')
					 ->first()->id;
		$trainer = Trainer::find(7);
		$client = Client::find(1);
		
		return $client->trainer()->associate($trainer);
	}

	public function getViewPreQuestionnaire($id)
	{
		$client = Client::find($id);
		return View::make('admin.modals.prequestionnaire')->with('client', $client);
	}

	public function getRequests()
	{
		$requests = DB::table('requests')->get();

		$this->layout->content = View::make('admin.dashboard')->with('title', 'Requests')
										->nest('innerContent', 'admin.requestList', array('requests' => $requests));
	}

	public function getApproveRequest($rid,$tid,$cid)
	{
		$trainer = Trainer::find($tid);
		$client = Client::find($cid);
		$client->trainer()->associate($trainer);
		$client->save();	

		DB::table('requests')->where('id', '=', $rid)->delete();

		return Redirect::to('admin/requests');
	}

	public function getRejectRequest($rid)
	{
		DB::table('requests')->where('id', '=', $rid)->delete();

		return Redirect::to('admin/requests');
	}

	public function getClientSearch()
	{

	}

	public function getFilterClients()
	{
		$activities = Activity::all();
		$interests = Interest::all();
		$this->layout->content = View::make('admin.dashboard')->with('title', 'Filter Clients')
															  ->nest('innerContent', 'admin.filterClients', array('activities' => $activities, 'interests' => $interests));
	}

	public function getViewCe($id)
	{
		$client = Client::find($id);
		$ce = $client->evaluation;

		if ($ce) {
			return View::make('admin.viewCe')->with('client', $client)->with('ce', $ce);
		} else {
			return "Client has not submitted an evaluation.";
		}
	}

	public function getViewPre($id)
	{
		$client = Client::find($id);
		$pre = $client->ptest;

		if ($pre) {
			$preDetail = $pre->ptestDetail;

			return View::make('admin.viewPre')->with('client', $client)->with('pre', $pre)->with('preDetail', $preDetail);
		} else {
			return "Client does not have Pre Test";
		}
	}

	public function getViewPost($id)
	{
		$client = Client::find($id);
		$post = $client->potest;

		if ($post) {
			$postDetail = $post->potestDetail;

			return View::make('admin.viewPost')->with('client', $client)->with('post', $post)->with('postDetail', $postDetail);
		} else {
			return "Client does not have Post Test.";
		}
	}

	public function getViewFiles($id)
	{
		$client = Client::find($id);
		$wfiles = $client->wfiles;

		if ($wfiles) {
			return view::make('admin.viewFiles')->with('client', $client)->with('wfiles', $wfiles);
		} else {
			return 'Client does not have Workout Files';
		}
	}

	public function getDownload($file_orig, $file) {
		return Response::download('uploads/'.$file_orig,$file);
	}

}