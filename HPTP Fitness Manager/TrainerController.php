<?php

class TrainerController extends BaseController {

	protected $layout = 'templates.master';

	public function __construct()
	{
		$this->beforeFilter('auth');
		$this->beforeFilter('trainer');
		View::share('actionName', explode('@', Route::currentRouteAction())[1]);
	}

	public function getIndex()
	{
		$clients = Auth::user()->trainer->clients;
		if (count($clients) > 0) {
			$this->layout->content = View::make('trainer.main')->with('title', 'Dashboard')->with('clients', $clients)
															   ->nest('innerContent', 'trainer.info');
		} else {
			$this->layout->content = View::make('dashboard.trainer')->with('title', 'Dashboard');

		}
	}

	public function getView($id)
	{
		$client = Client::find($id);
		$clients = Auth::user()->trainer->clients;

		if ($client->trainer_id = Auth::user()->trainer->id) {
			$this->layout->content = View::make('trainer.main')->with('title', $client->user->first_name . ' ' . $client->user->last_name)->with('clients', $clients)
																	->nest('innerContent', 'trainer.viewClient', array('theClient' => $client));
		}
	}

	public function getAddPretest($id) {
		$client = Client::find($id);
		$clients = Auth::user()->trainer->clients;
		$ptest = $client->ptest;

		if (!$ptest) {
			$ptest = new Ptest;
			$ptest->trainer_id = Auth::user()->trainer->id;
			$client->ptest()->save($ptest);
			$ptest_detail = new PtestDetail;
			$ptest->ptestDetail()->save($ptest_detail);
		} else {
			$ptest = $client->ptest;
			$ptest_detail = $ptest->ptestDetail;
		}

		if ($client->trainer_id = Auth::user()->trainer->id) {
			$this->layout->content = View::make('trainer.main')->with('title', $client->user->first_name . ' ' . $client->user->last_name)->with('clients', $clients)
																	->nest('innerContent', 'trainer.addPretest', array('theClient' => $client, 'ptest' => $ptest));
		}
	}

	public function postAddPretest() {
		$client = Client::find(Input::get('clientId'));

		if (!$client->ptest) {
			$ptest = new Ptest;
			$ptest->trainer_id = Auth::user()->trainer->id;
			$client->ptest()->save($ptest);
			$ptest_detail = new PtestDetail;
			$ptest->ptestDetail()->save($ptest_detail);
		} else {
			$ptest = $client->ptest;
			$ptest_detail = $ptest->ptestDetail;
		}

		$ptestInputs = Input::only('resting_bp', 'resting_bp_cat', 'resting_hr', 'hr_target_low', 'hr_target_high', 'height', 'weight', 
								   'bmi', 'bmi_cat', 'waist_to_hip', 'waist_to_hip_cat');

		$ptestDetailsInputs = Input::only('sct_prot', 'sct_score', 'sct_cat', 'fa_upper_prot', 'fa_upper_score', 'fa_upper_cat', 'fa_lower_prot', 
										 'fa_lower_score', 'fa_lower_cat', 'ms_upper_prot', 'ms_upper_score', 'ms_upper_cat', 'ms_lower_prot', 
										 'ms_lower_score', 'ms_lower_cat', 'me_core_prot', 'me_core_score', 'me_core_cat', 'me_upper_prot', 
										 'me_upper_score', 'me_upper_cat', 'bc_fat', 'bc_cat');

		foreach ($ptestInputs as $key => $input) {
			$ptest->$key = $input;
		}
		foreach ($ptestDetailsInputs as $key => $input) {
			$ptest_detail->$key = $input;
		}

		$ptest->push();

		return Redirect::to('trainer/add-pretest/' . $client->id);
	}

	public function getAddPosttest($id) {
		$client = Client::find($id);
		$clients = Auth::user()->trainer->clients;
		$potest = $client->potest;

		if (!$potest) {
			$potest = new Potest;
			$potest->trainer_id = Auth::user()->trainer->id;
			$client->potest()->save($potest);
			$potest_detail = new PotestDetail;
			$potest->potestDetail()->save($potest_detail);
		} else {
			$potest = $client->potest;
			$potest_detail = $potest->potestDetail;
		}

		if ($client->trainer_id = Auth::user()->trainer->id) {
			$this->layout->content = View::make('trainer.main')->with('title', $client->user->first_name . ' ' . $client->user->last_name)->with('clients', $clients)
																	->nest('innerContent', 'trainer.addPosttest', array('theClient' => $client, 'potest' => $potest));
		}
	}

	public function postAddPosttest() {
		$client = Client::find(Input::get('clientId'));

		if (!$client->potest) {
			$potest = new Potest;
			$potest->trainer_id = Auth::user()->trainer->id;
			$client->potest()->save($potest);
			$potest_detail = new PotestDetail;
			$potest->potestDetail()->save($potest_detail);
		} else {
			$potest = $client->potest;
			$potest_detail = $potest->potestDetail;
		}

		$potestInputs = Input::only('resting_bp', 'resting_bp_cat', 'resting_hr', 'hr_target_low', 'hr_target_high', 'height', 'weight', 
								   'bmi', 'bmi_cat', 'waist_to_hip', 'waist_to_hip_cat');

		$potestDetailsInputs = Input::only('sct_prot', 'sct_score', 'sct_cat', 'fa_upper_prot', 'fa_upper_score', 'fa_upper_cat', 'fa_lower_prot', 
										 'fa_lower_score', 'fa_lower_cat', 'ms_upper_prot', 'ms_upper_score', 'ms_upper_cat', 'ms_lower_prot', 
										 'ms_lower_score', 'ms_lower_cat', 'me_core_prot', 'me_core_score', 'me_core_cat', 'me_upper_prot', 
										 'me_upper_score', 'me_upper_cat', 'bc_fat', 'bc_cat');

		foreach ($potestInputs as $key => $input) {
			$potest->$key = $input;
		}
		foreach ($potestDetailsInputs as $key => $input) {
			$potest_detail->$key = $input;
		}

		$potest->push();

		return Redirect::to('trainer/add-posttest/' . $client->id);
	}

	public function getAddPosttest2($id) {
		$client = Client::find($id);
		$clients = Auth::user()->trainer->clients;

		if ($client->trainer_id = Auth::user()->trainer->id) {
			$this->layout->content = View::make('trainer.main')->with('title', $client->user->first_name . ' ' . $client->user->last_name)->with('clients', $clients)
																	->nest('innerContent', 'trainer.addPosttest', array('theClient' => $client));
		}
	}

	public function getAdd($id)
	{
		$client = Client::find($id);
		$clients = Auth::user()->trainer->clients;

		if ($client->trainer_id = Auth::user()->trainer->id) {
			$this->layout->content = View::make('trainer.main')->with('title', $client->user->first_name . ' ' . $client->user->last_name)->with('clients', $clients)
																	->nest('innerContent', 'trainer.addWorkout', array('theClient' => $client));
		}
	}

	public function putAdd2() {
		$rules = array(
				'type' => 'required',
				'name' => 'required',
				'reps' => 'required',
				'sets' => 'required'
			);

		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {

		} else {
			$workout = new Workout;
			$workout->type = Input::get('type');
			$workout->name = Input::get('name');
			$workout->sets = Input::get('sets');
			$workout->reps = Input::get('reps');
			$workout->machine = Input::get('machine');
			$workout->comments = Input::get('comments');

			$client = Client::find(Input::get('clientId'));

			$workout = $client->workouts()->save($workout);

			return Redirect::to('trainer/view/'.$client->id);
		}
	}

	public function putAdd() {
		$rules = array(
				'workoutDocument' => 'required|max:150|mimes:doc,docx,xls,xlsx'
			);
		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			return "Invalid File Upload! Must be Excel or Word File. Must be less than 100kb.";
		} else {
			$destination_path = 'uploads';
			$file = Input::file('workoutDocument');
			$filename_orig = $file->getClientOriginalName();
			$filename = md5(rand(0,1000000)+time()) .'.'. $file->getClientOriginalExtension();
			$uploadSuccess = $file->move($destination_path, $filename);
			if ($uploadSuccess) {
				$wfile = new Wfile;
				$client = Client::find(Input::get('clientId'));

				$wfile->client()->associate($client);
				$wfile->trainer()->associate(Auth::user()->trainer);

				$wfile->original_file_name = $filename_orig;
				$wfile->file_name = $filename;
				$wfile->desc = Input::get('desc');

				$wfile->save();

				return Redirect::to('trainer/view/'.$client->id);

			} else {
				return 'Upload failed!';
			}
		}
	}

	public function getDownload($file_orig, $file) {
		return Response::download('uploads/'.$file_orig,$file);
	}

	public function postRequest()
	{
		$cemail = Input::get('email');
		$tid = Auth::user()->trainer->id;

		$client = DB::table('users')
								->join('clients', 'users.id', '=', 'clients.user_id')
								//->leftJoin('requests', 'clients.id', '=', 'requests.client_id')
								->where('clients.trainer_id', '=', NULL)
								->where('users.email','=',$cemail)
								//->where('requests.trainer_id', '!=', $tid)
								->select('clients.id', 'users.first_name', 'users.last_name', 'users.email')->first();



		if (!empty($client->id)) {

			$req = DB::table('requests')
									->where('client_id', '=', $client->id)
									->where('trainer_id', '=', $tid)
									->select('id')->first();
			if (empty($req)) {
				DB::table('requests')->insert(
						array('client_id' => $client->id, 'trainer_id' => $tid)
					);
				$this->layout->content = View::make('trainer.requestSuccess')->with('title', 'Request Succesful')
																			 ->with('client', $client);
			} else {
				$this->layout->content = View::make('trainer.requestExists')->with('title', 'Request Error');
			}
		} else {
			$this->layout->content = View::make('trainer.invalidClient')->with('title', 'Request Error');
		}
	}

}