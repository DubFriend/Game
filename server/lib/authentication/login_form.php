<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL);

require 'authenticate.php';
require 'registration.php';


$DB = new PDO(
	"mysql:host=localhost;dbname=login_test",
	'root',
	'P0l.ar-B3ar'
);

$Auth = new \authenticator\Authenticator(array(
	'database' => $DB,
	'MAX_SESSION_DURATION' => 2000,
	'MAX_FAILED_ATTEMPTS' => 15
));


$Reg = new \authenticator\Registration($DB);



$loginStatus = false;
$registrationStatus = array();

if(isset($_GET['act'])) {

	if($_GET['act'] == 'login') {
		$username = $_POST['username'];
		$password = $_POST['password'];

		$loginStatus = $Auth->login($username, $password);
	}
	else if($_GET['act'] == 'register') {
		$registrationStatus = $Reg->register(array(
			'username' => $_POST['username'],
			'password' => $_POST['password'],
			'accessLevel' => 2
		));
	}
}


$isAuthentic = $Auth->authenticate(1);
?>


<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Login</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="flat_ui/css/bootstrap.css" rel="stylesheet" />
	<link href="flat_ui/css/flat-ui.css" rel="stylesheet" />
	<link rel="shortcut icon" href="images/favicon.ico">

	<style>
		body {
			background: #2C3E50;
		}

		p {
			color: #1ABC9C;
		}

		.login-form {
			width: 312px;
			margin-left: auto;
			margin-right: auto;
			margin-top: 8em;
		}
	</style>

</head>
<body>

	<p class='black'>
		<?php
		
		$html = "

		<p>SESSION Status : " . session_status() . "</p>

		<p>SESSION : " . print_r($_SESSION, true) .  "

		<p>GET : " . print_r($_GET, true) . "</p>
		<p>POST : " . print_r($_POST, true) . "</p>
		<p>Is Authenticated : $isAuthentic</p>
		<p>Is Login Success : $loginStatus</p>
		<p>Registration Status : " . print_r($registrationStatus, true) . "</p>";


		echo $html;
		?>
	</p>

	<form class='login-form' action='login_form.php?act=login' method='POST'>
		<div class='control-group'>
			<input type='text' name='username' id='username' placeholder='Username' class='login-field' >
			<label class='login-field-icon fui-man-16' for='username'></label>
		</div>
		<div class='control-group'>
			<input type='password' name='password' id='password' placeholder='Password' class='login-field'>
			<label class='login-field-icon fui-lock-16' for='password'></label>
		</div>
		<input type='submit' class='btn btn-primary btn-large btn-block' value='Login' />
	</form>

	<form class='login-form' action='login_form.php?act=register' method='POST'>
		<div class='control-group'>
			<input type='text' name='username' id='username' placeholder='Username' class='login-field' >
			<label class='login-field-icon fui-man-16' for='username'></label>
		</div>
		<div class='control-group'>
			<input type='password' name='password' id='password' placeholder='Password' class='login-field'>
			<label class='login-field-icon fui-lock-16' for='password'></label>
		</div>
		<input type='submit' class='btn btn-primary btn-large btn-block' value='Register' />
	</form>

	<script src='flat_ui/js/jquery-1.8.2.min.js'></script>
	<script src='flat_ui/js/jquery-ui-1.10.0.custom.min.js'></script>
	<script src='flat_ui/js/jquery.dropkick-1.0.0.js'></script>
	<script src='flat_ui/js/custom_checkbox_and_radio.js'></script>
	<script src='flat_ui/js/custom_radio.js'></script>
	<script src='flat_ui/js/jquery.tagsinput.js'></script>
	<script src='flat_ui/js/bootstrap-tooltip.js'></script>
	<script src='flat_ui/js/jquery.placeholder.js'></script>
	<script src='flat_ui/js/application.js'></script>
	<!--[if lt IE 8]>
	<script src="js/icon-font-ie7.js"></script>
	<script src="js/icon-font-ie7-24.js"></script>
	<![endif]-->

</body>
</html>