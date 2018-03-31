<?php

// This file should confirm a registration.
if (isset($_GET['registrationcode'])) {
	$registrationcode = sanitizeInput($_GET['registrationcode']);

	if (registrationCodeExists($registrationcode)) {
		register($registrationCode);
		echo "You are now registered";
	} else {
		echo "Registration failed";
	}
}

function register($registrationCode) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");

	$query = "SELECT * FROM registration WHERE registration_code=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $registrationCode);
	$stmt->execute();
	$stmt->bind_result($registrationId, $registrationUsername, $registrationEmail, $registrationPassword, $registrationRegistered, $registrationRole);
	$affectedRows = $stmt->affected_rows;

	if ($affectedRows > 0) {
		$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
		$id = rand(30000, 1000000);
		while (userIdIsInUse($id)) {
			$id = rand(30000, 1000000);
		}

		$accountStatus = "active";
		$role = "user";
		$defaultAvatar = "default_avatar";
		$registered = date("Y-m-d H:i:s", time());
		$lastLogin = $registered;


		$query = "INSERT INTO user(id,username,email,password,registered,lastlogin,role,avatar,account_status) VALUES (?,?,?,?,?,?,?,?,?)";
		$stmt = $db->prepare($query);
		$stmt->bind_param("sssssssss", $id, $registrationUsername, $registrationEmail, $registrationPassword, $time, $lastlogin, $registrationRole, $defaultAvatar, $accountStatus);
		$stmt->execute();
		$affectedRows = $stmt->affected_rows;

		if ($affectedRows == 1) {
				//user is registered
			
			echo "Registration is complete. You can now log in.";
		} else {
			echo $defaultError."321";
		}
	} else {
		echo "Something went wrong";
	}
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

function registrationCodeExists($registrationCode) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$query = "SELECT * FROM registration WHERE registration_code=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $registrationCode);
	$stmt->execute();
	$affectedRows = $stmt->affected_rows;
	echo "__________".$affectedRows."____________";
	return;

	if ($affectedRows > 0) {
		return true;
	} else {
		return false;
	}
}

?>