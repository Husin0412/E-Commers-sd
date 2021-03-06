@extends('layouts.frontLayout.front_design')
@section('content')

    @if(Session::has('signup_error'))
        <div class="alert alert-dark alert-block" style="background-color:red; color:white; width:16%; margin-left:51%;">
            <button type="button" class="close" data-dismiss="alert">x</button>	
            <strong> {{Session::get('signup_error')}}</strong>
        </div>
        @endif  
        @if(Session::has('signup_success'))
        <div class="alert alert-dark alert-block" style="background-color:green; color:white; width:16%; margin-left:51%;">
            <button type="button" class="close" data-dismiss="alert">x</button>	
            <strong> {{Session::get('signup_success')}}</strong>
        </div>
    @endif
    @if(Session::has('flash_message_error'))
        <div class="alert alert-dark alert-block" style="background-color:red; color:white; width:19%; margin-left:20%;">
            <button type="button" class="close" data-dismiss="alert">x</button>	
            <strong> {{Session::get('flash_message_error')}}</strong>
        </div>
        @endif  
        @if(Session::has('flash_message_success'))
        <div class="alert alert-dark alert-block" style="background-color: cornflowerblue;color: white;width: 19%;margin-left: 46%;">
            <button type="button" class="close" data-dismiss="alert">x</button>	
            <strong> {{Session::get('flash_message_success')}}</strong>
        </div>
	@endif
	
	<div id="loading"></div>
    <section id="form" style="margin:20px 0px 5% 0;"><!--form-->
		<div class="container">
			<div class="row">
				<div class="col-sm-4">
					<div class="login-form"><!--login form-->
						<h2>Login to your account</h2>
						<form action="{{url('/user-login')}}" id="loginForm" name="loginForm" method="post"> {{csrf_field()}}
							<input type="email" name="email" placeholder="Email Address" required />
							<input class="" type="password" name="password" placeholder="Password" required />
							<!-- <span>
								<input type="checkbox" class="checkbox"> 
								Keep me signed in
							</span> -->
							 <button type="submit" class="btn btn-default">Login</button> <br>
							 <a style="position: relative;top: -6px;" href="{{url('forgot-password')}}">Forgot Password ?</a>
						</form>
					</div><!--/login form-->
				</div>
				<div class="col-sm-1">
					<h2 class="or">OR</h2>
				</div>
				<div class="col-sm-4">
					<div class="signup-form"><!--sign up form-->
						<h2>New User Signup!</h2>
						<form action="{{url('/user-register')}}" method="post" id="registerForm" name="registerForm"> {{csrf_field()}}
							<input name="name" type="text" placeholder="Name" />
							<input name="email" type="email" placeholder="Email Address" />
							<input name="password" type="password" placeholder="Password" id="myPassword" />
							<button type="submit" class="btn btn-default">Signup</button>
						</form>
					</div><!--/sign up form-->
				</div>
			</div>
		</div>
	</section><!--/form-->

@endsection

@section('script');
<script>
$().ready(function () {


});
</script>
@endsection