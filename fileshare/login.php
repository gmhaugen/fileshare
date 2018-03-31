<?php
include('userhandler.php');
session_start();

if (isset($_POST['action']) && $_POST['action'] == "login") {
	if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['csrf-token']) || empty($_POST['action'])) {
		$error = "Username or Password is incorrect";
		if (empty($_POST['username'])) {
			reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "username was missing");
			echo "Something went wrong. Try again later.1";
			return;
		}
		if (empty($_POST['password'])) {
			reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "password was missing");
			echo "Something went wrong. Try again later.2";
			return;
		}
		if (empty($_POST['login-csrf-token'])) {
			reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "CSRF-token was missing");
			echo "Something went wrong. Try again later.3";
			return;
		}
		if(empty($_POST['action'])) {
			reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "action was missing");
			echo "Something went wrong. Try again later.4";
			return;
		}
	}

	if ($_POST['action'] != "login") {
		reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "invalid action (".$_POST['action'].")");
		echo "Something went wrong. Try again later.5";
		return;
	}

	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$username_sanitized = sanitizeInput($_POST['username']);
	$password_sanitized = sanitizeInput($_POST['password']);

	if (strlen($_POST['username']) > 30 || strlen($username_sanitized) > 30 || strlen($_POST['password']) > 30 || strlen($password_sanitized) > 30) {
		echo "Something went wrong. Try again later.6";
		return;
	}

	if ($_POST['login-csrf-token'] != $_SESSION['login-csrf-token']) {
		$error = "Something went wrong. Try again later.7";
		reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "CSRF-token was incorrect");
		echo $error.$_POST['login-csrf-token']." and ".$_SESSION['login-csrf-token'];
		return;
	}

	if ($db->connect_errno > 0) {
		die('Unable to connect to database [' . $db->connect_error . ']');
		echo 'DB connection problems...';
	}

	$query = "SELECT password FROM user WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $username_sanitized);
	$stmt->execute();
	$stmt->bind_result($stored_password);
	$stmt->fetch();

	if (isset($stored_password)) {
		if (password_verify($password_sanitized, $stored_password)) {
			if (!checkSuspended($username_sanitized)) {
				echo "Account is suspended";
				return;
			}
			$stmt->close();
			$time = date("Y-m-d H:i:s", time());
			$query = "UPDATE `user` SET lastlogin=? WHERE username=?";
			$secondstmt = $db->prepare($query);
			$secondstmt->bind_param("ss", $time, $username_sanitized);
			$status = $secondstmt->execute();
			if ($status) {
				$_SESSION['username'] = $username_sanitized;
				$_SESSION['csrf-token'] = bin2hex(openssl_random_pseudo_bytes(24));
				//header('forum.php');
				echo "ok";
			} else {
				//reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "Could not update user with \"last login\"");
				$error = "Something went wrong!8";
				echo $error;
			}
		} else {
			$error = "Username or password is incorrect";
			reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "Incorrect password");
			$error = $error."".evaluateAttempts($username_sanitized);
			echo $error;
		}
	} else {
		$error = "Username or password is incorrect";
		reportError("login-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "User not found");
		evaluateAttempts($username_sanitized);
		echo $error;
	}
}

function reportError($type, $username, $ipAddress, $errorComment) {
	$error_db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "errors");
	$query = "INSERT INTO `login_errors`(`id`,`type`,`at_time`,`username_tried`,`ip_address`,`error_comment`) VALUES (?,?,?,?,?,?)";

	$errorid = createId();
	$time = date("Y-m-d H:i:s", time());

	$stmt = $error_db->prepare($query);
	$stmt->bind_param("ssssss", $errorid, $type, $time, $username, $ipAddress, $errorComment);
	$stmt->execute();
	$stmt->close();
	$error_db->close();
}

function sanitizeInput($input) {
	$input = trim($input);
	$input = stripslashes($input);
	$input = htmlspecialchars($input);

	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$input = mysqli_real_escape_string($db, $input);
	$db->close();
	return $input;
}

function createId() {
	$id = rand(30000,10000000);
	while (checkErrorId($id) == false) {
		$id = rand(30000,10000000);
	}
	return $id;
}

function checkErrorId($id) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "errors");
	$sql = "SELECT * FROM login_errors WHERE id='$id'";
	$result = mysqli_query($db, $sql);

	if (mysqli_num_rows($result) > 0) {
		return false;
	} else {
		return true;
	}
}

function checkSuspended($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$query = "SELECT account_status from user WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$stmt->bind_result($accountStatus);

	if ($accountStatus == "suspended") {
		return false;
	} else {
		return true;
	}
}

function evaluateAttempts($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "errors");
	$error_comment = "Incorrect password";
	$totalAllowedAttempts = 5;
	$query = "SELECT * FROM login_errors WHERE username_tried=? AND error_comment=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("ss", $username, $error_comment);
	$stmt->execute();
	$result = $stmt->get_result();
	$count = $result->num_rows;

	if ($count >= $totalAllowedAttempts) {
		suspendUser($username);
	}

	return $count;
}

?>