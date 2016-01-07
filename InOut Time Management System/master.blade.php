<!DOCTYPE html>
<html>
	<head>
		<title>@yield('sub-title') - ASU InOut Systems</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		@section('css')
			<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
			{{ HTML::style('css/toggles-modern.css') }}
			{{ HTML::style('css/jquery-ui-1.10.3.custom.min.css') }}
			{{ HTML::style('css/jquery-ui-timepicker-addon.min.css') }}
			{{ HTML::style('css/global.min.css') }}
		@show
	</head>
	<body>
		<div class="container">
			<header>
				<div id="dateBox">
					<div class="row">
						<div class="col-lg-3">
							<h6><strong>Arkansas State University</strong></h6>
						</div>
						<div class="col-lg-2 col-lg-offset-7 text-right">
							<h6><strong>{{ date('Y-m-d H:i:s') }} | <a href="assets/inout.pdf">HELP</a></strong></h6>
						</div>
					</div>
				</div>
				<div id="redBox">
					<div class="row">
						<div class="col-lg-2">
							{{ HTML::image('img/logo.png') }}
						</div>
						<div class="col-lg-5 col-lg-offset-5 text-right">
							<h4>InOut Time Management System</h4>
						</div> 
					</div>
				</div>
				<nav class="navbar navbar-inverse" role="navigation">
					<div class="navbar-header">
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex8-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
							<span class="icon-bar"></span>
						</button>
			        </div>

					<div class="collapse navbar-collapse navbar-ex8-collapse">
						<ul class="nav navbar-nav">
							@section ('menubar-home')
								<li><a href="{{ URL::to('/') }}"><span class="glyphicon glyphicon-home"></span> Home</a></li>
							@show
							@section ('menubar')

							@show
						</ul>

						@section('menubar-logout')
							@if (Auth::user())
								<ul class="nav navbar-nav navbar-right">
									<li><a href="{{ URL::to('logout') }}"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
								</ul>
							@else
								<ul class="nav navbar-nav navbar-right">
									<li><a href="{{ URL::to('login') }}"><span class="glyphicon glyphicon-user"></span> Login</a></li>
								</ul>
							@endif
						@show
					</div><!-- /.navbar-collapse -->
				</nav>
			</header>
			<div id="content">
				@yield('content')
			</div>
			<footer>
				<div class="row">
					<div class="col-lg-4">
						<h5><strong>Information and Technology Services</strong></h5>
						<h6>Arkansas State University</h6>
						<h6>Jonesboro, State University 72401<br><span class="text-info">24/7 Helpdesk: (870) 972-3933</span></h6>
					</div>
					<div class="col-lg-4 col-lg-offset-4 text-right">
						<h5><strong>Interactive Teaching and Technology Center</strong></h5>
						<h6>Arkansas State University</h6>
						<h6>Jonesboro, State University 72401</h6>
					</div>
				</div>
			</footer>
		</div>
		@section('js')
			{{ HTML::script('js/jquery.js') }}
			<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
			{{ HTML::script('js/toggles.min.js') }}
			{{ HTML::script('js/jquery-ui-1.10.3.custom.min.js') }}
			{{ HTML::script('js/jquery-ui-timepicker-addon.min.js') }}
			{{ HTML::script('js/global.min.js') }}
		@show
	</body>
</html>