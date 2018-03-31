<?php
session_start();
$defaultError = "Something went wrong. Try again later.";

if (isset($_POST['action']) && $_POST['action'] == "register") {
	if (empty($_POST['username']) || empty($_POST['password1']) || empty($_POST['password2']) || empty($_POST['email']) || empty($_POST['register-csrf-token']) || empty($_POST['action'])) {
		$error = "Username or Password is incorrect";
		if (empty($_POST['username'])) {
			reportError("register-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "username was missing");
			echo $defaultError."1";
			return;
		}
		if (empty($_POST['password1'])) {
			reportError("register-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "password was missing");
			echo $defaultError."2";
			return;
		}
		if (empty($_POST['password2'])) {
			reportError("register-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "password check was missing");
			echo $defaultError."3";
		}
		if (empty($_POST['register-csrf-token'])) {
			reportError("register-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "CSRF-token was missing");
			echo $defaultError."4";
			return;
		}
		if(empty($_POST['action'])) {
			reportError("register-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "action was missing");
			echo $defaultError."5";
			return;
		}
	}

	if ($_POST['action'] != "register") {
		reportError("register-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "invalid action (".$_POST['action'].")");
		echo $defaultError."6";
		return;
	}

	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$username_sanitized = sanitizeInput($_POST['username']);
	$password_sanitized = sanitizeInput($_POST['password1']);
	$password_check_sanitized = sanitizeInput($_POST['password2']);
	$email_sanitized = sanitizeInput($_POST['email']);

	if (!filter_var($email_sanitized, FILTER_VALIDATE_EMAIL)) {
		echo "Email address is not valid!";
		return;
	}

	if (strlen($_POST['username']) > 30 || strlen($username_sanitized) > 30 || strlen($_POST['password1']) > 30 || strlen($password_sanitized) > 30) {
		echo $defaultError."7";
		return;
	}

	if (strlen($_POST['email']) > 40 || strlen($email_sanitized) > 40) {
		echo $defaultError."8";
		return;
	}

	if (strlen($_POST['password1']) > 30 || strlen($password_sanitized) > 30) {
		echo $defaultError."9";
		return;
	}

	if (strlen($_POST['password2']) > 30 || strlen($password_check_sanitized) > 30) {
		echo $defaultError."10";
		return;
	}

	if ($password_check_sanitized != $password_sanitized) {
		echo "Passwords does not match";
		return;
	}

	//matbe also controll length of csrf-token
	if ($_POST['register-csrf-token'] != $_SESSION['register-csrf-token']) {
		reportError("register-error", $_POST['username'], "".$_SERVER['REMOTE_ADDR']."", "CSRF-token was incorrect");
		echo $defaultError."11";
		return;
	}

	if ($db->connect_errno > 0) {
		die('Unable to connect to database [' . $db->connect_error . ']');
		echo 'DB connection problems...';
	}

	if (!userExists($username_sanitized)) {
		$hashed_password = password_hash($password_sanitized, PASSWORD_DEFAULT);

		$registrationCode = makeRegistration($username_sanitized, $email_sanitized, $hashed_password);

		if ($registrationCode == '0') {
			echo $defaultError."123 =>".$registrationCode;
			return;
		} else {
			// Email should be sent.
			echo "Your registration is pending.\nPlease visit this link to\ncomplete your registration:\n<a href=\"http://localhost/registration/confirm.php?registrationcode=".$registrationCode."\"></a>";
		}

	} else {
		echo $defaultError."13";
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

function createRegistrationId() {
	$id = rand(30000, 1000000);
	while (checkRegistrationId($id) == false) {
		$id = rand(30000, 1000000);
	}
	return $id;
}

function checkRegistrationId($id) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$query = "SELECT * FROM registration WHERE id=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	$count = $result->num_rows;

	if ($count > 0) {
		return false;
	} else {
		return true;
	}
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

function userExists($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$query = "SELECT * FROM user WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $username);
	$stmt->execute();
	$result = $stmt->get_result();
	$count = $result->num_rows;

	if ($count > 0) {
		return true;
	} else {
		return false;
	}
}

function userIdIsInUse($id) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$query = "SELECT * FROM user WHERE id=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $id);
	$stmt->execute();
	$result = $stmt->get_result();
	$count = $result->num_rows;

	if ($count > 0) {
		return true;
	} else {
		return false;
	}
}

function makeRegistration($username, $email, $password) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");

	$id = createRegistrationId();
	$registered = date("Y-m-d H:i:s", time());
	$registrationCode = bin2hex(openssl_random_pseudo_bytes(24));
	$role = 'user';
	$query = "INSERT INTO registration(id,username,email,password,registered,role,registration_code) VALUES (?,?,?,?,?,?,?)";
	$stmt = $db->prepare($query);
	$stmt->bind_param("sssssss", $id, $username, $email, $password, $registered, $role, $registrationCode);

	$stmt->execute();
	$affectedRows = $stmt->affected_rows;

	if ($affectedRows > 0) {
		return $registrationCode;
	} else {
		return 0;
	}

}

?>