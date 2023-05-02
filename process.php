<?php
	//standard start
	session_start();
	require_once("new_connection.php");
	date_default_timezone_set("Asia/Hong_Kong");

	//Perform depending on action token
	
		if(isset($_POST['action']) && $_POST['action'] == 'register'){
			register_user($_POST);
		}elseif(isset($_POST['action']) && $_POST['action'] == 'signin'){
			login_user($_POST);
		}elseif(isset($_POST['action']) && $_POST['action'] == 'reset_pass'){
			reset_pass_user($_POST);
		}elseif(isset($_POST['action']) && $_POST['action'] == 'Sign Out'){
			signout_user($_POST);
		}elseif(isset($_POST['action']) && $_POST['action'] == 'Create a message'){
			post_message($_POST);
		}elseif(isset($_POST['action']) && $_POST['action'] == 'Create a comment'){
			post_comment($_POST);
		}else{
			//malicious access to process.php or success.php
			session_destroy();
			header('Location: wall.php');
			exit();
		}
	

	//------------------------------Register function----------------------------//
	function register_user($post){
		//empty array everytime
		$_SESSION['logs'] = array();

		//----Validations----------------------------------------------------------------//
		//For first name.
		//Check if its empty
		$first_name = escape_this_string($post['first_name']);
		$all_alpha = ctype_alpha($first_name);
		$string_length = strlen($first_name);

		if(empty($post['first_name'])){
			$_SESSION['logs'][] = "<p> First Name is empty.</p>";
		}elseif($all_alpha == FALSE){
			$_SESSION['logs'][] = "<p> First name must not contain any numbers.</p>";
		}elseif($string_length < 2){
			$_SESSION['logs'][] = "<p> First name must be minimum 2 characters long.</p>";
		}

		//For last name.
		//Check if its empty.
		$last_name = escape_this_string($post['last_name']);
		$all_alpha = ctype_alpha($last_name);
		$string_length = strlen($last_name);
		if(empty($post['last_name'])){
			$_SESSION['logs'][] = "<p> Last Name is empty.</p>";
		}elseif($all_alpha == FALSE){
			$_SESSION['logs'][] = "<p> Last Name must not contain any numbers.</p>";
		}elseif($string_length < 2){
			$_SESSION['logs'][] = "<p> Last Name must be minimum 2 characters long.</p>";
		}

		//For email//
		//Check if email is empty and check if email is already existing in the database.
		$email = escape_this_string($post['email']);
		$valid_email = filter_var($email, FILTER_VALIDATE_EMAIL);
		$query = "SELECT * FROM users WHERE email = '$email'";
		$users = fetch_all($query);
		if(empty($post['email'])){
			$_SESSION['logs'][] = "<p> Email is empty.</p>";
		}elseif($valid_email == FALSE || !empty($users)){
			$_SESSION['logs'][] = "<p> Invalid email address.</p>";
		}

		//For Password
		//Check if password is empty
		$password = escape_this_string($post['password']);
		$confirm_password = escape_this_string($post['confirm_password']);
		$string_length = strlen($password);

		if(empty($post['password'])){
			$_SESSION['logs'][] = "<p> Password is empty.</p>";
		}elseif($string_length < 8){
			$_SESSION['logs'][] = "<p> Password should have atleast 8 characters.</p>";
		}elseif($password != $confirm_password){
			$_SESSION['logs'][] = "<p> Password confirmation does not match.</p>";
		}


		//----End of Validations----------------------------------------------------------//

		//If there are errors go back to register page and display
		if(!empty($_SESSION['logs'])){
			$_SESSION['log_state'] = "error on";
			header("Location: register.php");
			exit();
		}else{
			/*generate ecnrypted password and input into system*/
			$encrypted_password = md5($password);

			$query = "INSERT INTO users(first_name, last_name, email, password, created_at, updated_at) 
			VALUES('{$first_name}', '{$last_name}', '{$email}', '{$encrypted_password}',  NOW(), NOW())";
			run_mysql_query($query);

			$_SESSION['log_state'] = "success";
			$_SESSION['logs'][] = "<p> User successfully registered!</p>";
			header("Location: register.php");
			exit();
		}
	}

	//------------------------------End of Register function---------------------//
	
	//---------------------------------Signin function-------------------------------------//
	function login_user($post){
		//Empty logs
		$_SESSION['logs'] = array();

		$email = escape_this_string($_POST['email']);
		$query = "SELECT * FROM users WHERE email = '{$email}'";
		$row = fetch_record($query);
		$encrypted_password = md5($_POST['password']);
		//------Validations------------------------------------------------------//

		//Check if  email is empty then check if no recorded email
		if(empty($post['email'])){
			$_SESSION['logs'][] = "<p> Email is empty.</p>";
		}elseif(empty($post['password'])){
			$_SESSION['logs'][] = "<p> Password is empty.</p>";
		}elseif(empty($row)){
			$_SESSION['logs'][] = "<p> Email address cannot be found.</p>";
		}elseif($encrypted_password != $row['password']){
			$_SESSION['logs'][] = "<p> Incorrect password.</p>";
		}

		//If there are errors go back to signin page and display
		if(!empty($_SESSION['logs'])){
			$_SESSION['log_state'] = "error on";
			header("Location: signin.php");
			exit();
		}else{
			//successful login!
			$_SESSION['user_id'] = $row['id'];
			$_SESSION['logged_in'] = TRUE;
			$_SESSION['first_name'] = $row['first_name'];
			header("Location: wall.php");
			exit();
		}
		
	}
	//------------------------------End of signin function--------------------------//

	//--------------------------Reset Pass function--------------------------------//
	function reset_pass_user($post){
		//Empty logs
		$_SESSION['logs'] = array();

		$email = escape_this_string($_POST['email']);
		$query = "SELECT * FROM users WHERE email = '{$email}'";
		$row = fetch_record($query);

		//Check if email is empty
		if(empty($post['email'])){
			$_SESSION['logs'][] = "<p> email is empty.</p>";
		}elseif(empty($row)){
			$_SESSION['logs'][] = "<p>Invalid email. (Cannot be found)</p>";
		}

		//If any error logs are there, send them with the user to wall.php
		if(!empty($_SESSION['logs'])){
			$_SESSION['log_state'] = "error on";
			header("Location: reset_pass.php");
			exit();
		}else{

			$default_pass = 'village88';
			$encrypted_password = md5($default_pass);
			$query = "UPDATE users
					  SET password = '$encrypted_password', updated_at = NOW()
					  WHERE email = '{$email}'";
			run_mysql_query($query);

			$_SESSION['log_state'] = "success";
			$_SESSION['logs'][] = "<p> Successful Password Reset of " .  $_POST['email'] . " !</p>";
			header("Location: reset_pass.php");
			exit();
		}
	}
	//------------------------End of Reset Pass Function--------------------------//

	//--------------------------Logoff function--------------------------------//
		function signout_user($post){
		//Empty logs
			session_destroy();
			header("Location: wall.php");
			exit();
		}
	//------------------------End of Logoff Function--------------------------//

	//--------------------------Post message function--------------------------------//
	function post_message($post){
		$userid = $_SESSION['user_id'];
		$message = escape_this_string($post['message']);

		//Empty logs
		$_SESSION['logs'] = array();

		//check first if its empty
		if(empty($post['message'])){
			$_SESSION['logs'][] = "<p> Message area is empty.</p>";
		}

		//if there are errors log and send back to wall.php
		if(!empty($_SESSION['logs'])){
			$_SESSION['log_state'] = "error on";
			header("Location: wall.php");
			exit();
		}else{
			$query = "INSERT INTO messages(user_id, message, created_at, updated_at) 
			VALUES('{$userid}', '{$message}', NOW(), NOW())";
			run_mysql_query($query);
			$_SESSION['log_state'] = "error off";
			header("Location: wall.php");
			exit();
		}

	}
	//------------------------End of Post message function--------------------------//

	//--------------------------Post comment function--------------------------------//
	function post_comment($post){
		$comment = escape_this_string($post['comment']);
		//check first if its empty
		if(empty($post['comment'])){
			$_SESSION['logs'][] = "<p> Comment area is empty.</p>";
		}

		//if there are errors log and send back to wall.php
		if(!empty($_SESSION['logs'])){
			$_SESSION['log_state'] = "error on";
			header("Location: wall.php");
			exit();
		}else{
			//input into database
			$query =  "INSERT INTO comments(user_id, message_id, comment, created_at, updated_at) 
			VALUES('{$_SESSION['user_id']}', '{$post['message_id']}', '{$comment}', NOW(), NOW())";
			var_dump($query);
			var_dump($_SESSION);
			var_dump($_POST);
	
			run_mysql_query($query);
			$_SESSION['log_state'] = "error off";
			header("Location: wall.php");
			exit();
		}
	}

	//------------------------End of Post comment function--------------------------//
	
?>