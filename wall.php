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

	//to automatically display messages
	function display_messages(){
		$query = "SELECT users.first_name, users.last_name, messages.id AS message_id, messages.message, DATE_FORMAT(messages.created_at, '%m-%d-%Y %h:%i%p') AS created_at
				  FROM users
				  LEFT JOIN messages ON users.id = messages.user_id
				  WHERE messages.message IS NOT NULL
				  ORDER BY created_at";
		return fetch_all($query);
	}
	$messages = display_messages();
	
?>
<html lang="en">
	<head>
		<title>Starry Nights</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<!--Google font-->
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Nunito&display=swap" rel="stylesheet">
		<!--My Stylesheet-->
		<link rel="stylesheet" type="text/css" href="stylesheet.css">
	</head>
	<body class="index">
		<nav>
			<a href="wall.php"><h2 id="blog_title">Starry Night Blog</h2></a>
			<a  class="<?=$set1?>" href="signin.php" id="signin_btn">Sign in</a>
			<a  class="<?=$set1?>" href="register.php" id="register_btn">Register</a>

<?php		if(isset($_SESSION['first_name'])){
?>			<p class="<?=$set2?>">Welcome <?=$_SESSION['first_name']?></p>
<?php		}
?>			<form class="<?=$set2?>" action="process.php" method="POST">
				<input class="<?=$set2?>" type="submit" name="action" value="Sign Out" id="signout_btn">
			</form>
		</nav>
		<h1>Welcome to my wall!🌌</h1>
		<!-- message Error Log -->
		<?php	if(isset($_SESSION['logs']) && !empty($_SESSION['logs'])){
?>		<section class="<?=$log_state?>">
<?php		foreach($_SESSION['logs'] as $logs){
?>			<?=$logs?>
<?php		}
			unset($_SESSION['logs']);
?>		</section>
<?php	}
?><form class="message <?=$set2?>"action="process.php" method="POST">
			<h3>Post a message</h3>
			<textarea name="message" row="4" placeholder="Leave something nice to say!"></textarea>
			<input id="post_message_btn" type="submit" name="action" value="Create a message">
		</form>
		<!-- PHP Display message -->
<?php	if(isset($messages) && !empty($messages)){

			foreach($messages as $message){
				//display the content, save the message id
?>		<ul>
			<li style='font-weight:bold;'><?=$message['first_name']?> <?=$message['last_name']?> (<?=$message['created_at']?>)</li>
			<li><?=$message['message']?></li>
		</ul>
<?php			$comments = fetch_all("SELECT users.first_name, users.last_name, comments.comment AS comment, DATE_FORMAT(comments.created_at, '%m-%d-%Y %h:%i%p') AS comments_created_at
									  FROM comments
									  LEFT JOIN users ON comments.user_id = users.id
									  WHERE message_id = '{$message['message_id']}'
									  ORDER BY comments_created_at DESC");

				//display comments with the same message id
				if(!empty($comments)){
					foreach($comments as $comment){
?>		<ul class="comment">
			<li style='font-weight:bold;'><?=$comment['first_name']?> <?=$comment['last_name']?> (<?=$comment['comments_created_at']?>)</li>
			<li><?=$comment['comment']?></li>
		</ul>
<?php				}
				}
?>
		<form class="comment <?=$set2?>"action="process.php" method="POST">
			<input type="hidden" name="message_id" value="<?=$message['message_id']?>">
			<textarea name="comment" row="4" placeholder="Leave a comment!"></textarea>
			<input id="comment_btn" type="submit" name="action" value="Create a comment">
		</form>
<?php		}
		}
?>	</body>
</html>