<?php
	session_start();
	require_once("new_connection.php");

	//defaults
	date_default_timezone_set("Asia/Hong_Kong");

	//For the error log to show
	$log_state = 'error off';

	if(isset($_SESSION['log_state'])){
		$log_state = $_SESSION['log_state'];
	}

	switch ($log_state){
		case "error on":
			$log_state = "error";
			break;
		
		case "error off":
			$log_state = "error hidden";
			break;
	}

	//for navbar set switching
	if(isset($_SESSION['logged_in']) && ($_SESSION['logged_in']) == TRUE){
		//Switch navbar to set 2
		$set1 = "hidden";
		$set2 = "";
	}else{
		$set1 = "";
		$set2 = "hidden";
	}
	
?>
<html lang="en">
	<head>
		<title>Sign in</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--Google font-->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
		<!--My Stylesheet-->
		<link rel="stylesheet" type="text/css" href="stylesheet.css">
	</head>
	<body class="ext signin">
		<nav>
			<a href="wall.php"><h2 id="blog_title">Starry Night Blog</h2></a>
			<a class="<?=$set1?>" href="register.php" id="register_btn">Register</a>
			<a class="<?=$set1?>" href="reset_pass.php" id="reset_pass_btn">Reset Password</a>

			<p class="<?=$set2?>">Welcome<?=$_SESSION['first_name']?></p>
			<form class="<?=$set2?>" action="process.php" method="POST">
				<input class="<?=$set2?>" type="submit" name="action" value="Sign Out" id="signout_btn">
			</form>
		</nav>
<?php	if(isset($_SESSION['logs']) && !empty($_SESSION['logs'])){
?>		<section class="<?=$log_state?>">
<?php		foreach($_SESSION['logs'] as $logs){
?>			<?=$logs?>
<?php		}
			unset($_SESSION['logs']);
?>		</section>
<?php	}
?>		<h2>Sign in </h2>
		<form action="process.php" method="POST">
			<input type="hidden" name="action" value="signin">
			<label for="email">email:</label><input type="email" name="email" placeholder="juandelacruz@yahoo.com">
			<label for="password">Password:</label><input type="password" name="password" placeholder="password">
			<input type="submit" value="Sign In">
		</form>
	</body>
</html>