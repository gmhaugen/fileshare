<?php

$delerror = "";
$changepwerror = "";
$changeemailerror = "";
$changeavatarerror = "";
$suspendusererror = "";

if (isset($_POST['unsuspenduser'])) {
	if (!isset($_POST['csrf-token'])) {
		$suspendusererror = "Invalid token";
		return;
	}

	if (!isset($_POST['selecteduser']) || $_POST['selecteduser'] == "") {
		$suspendusererror = "No user is selected";
		return;
	}

	if (!isset($_POST['adminpassword'])) {
		$suspendusererror = "Invalid password!";
		return;
	}

	unsuspenduser($_POST['selecteduser']);
}

if (isset($_POST['suspenduser'])) {

	suspenduser($_POST['usertosuspend']);
}

if (isset($_POST['userkeyword'])) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$query = "SELECT * FROM user WHERE username LIKE CONCAT('%',?,'%')";

	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $_POST['userkeyword']);
	$stmt->execute();

	if ($stmt) {
		$stmt->bind_result($id, $username, $email, $password, $registered, $lastlogin, $role, $avatar, $account_status);
		$posts = array();

		while ($stmt->fetch()) {
			$posts[] = array('username' => $username, 'account_status' => $account_status);
		}
		echo json_encode($posts);
	}
}

if (isset($_POST['deleteuser'])) {
	if (isset($_POST['confirmdelete'])) {

	} else {
		$delerror = "Checkbox is unchecked, user is not deleted";
		return;
	}

	if (checkIsAdmin($_POST['adminpassword']) && passwordIsValid($_POST['adminpassword'])) {
		$username = $_POST['selecteduser'];
		deleteUser($username);
	} else {
		$delerror = "Wrong password!";
		return;
	}
}

if (isset($_POST['dochangeavatar'])) {
	if (isset($_POST['avatarname'])) {
		changeAvatar($_SESSION['username'], $_POST['avatarname']);

	} else {
		$changeavatarerror = "No avatar have been selected";
				header('Location: /forum/forum.php');
		return;
	}
}

if (isset($_POST['changeuserpw'])) {
	if (!isset($_POST['selecteduser'])) {
		$changepwerror = "User is not selected!";
		return;
	}

	if (!isset($_POST['adminpassword'])) {
		$changepwerror = "Admin password required!";
		return;
	}

	if (!isset($_POST['newpassword1']) || !isset($_POST['newpassword2'])) {
		$changepwerror = "New password is needed";
		return;
	}

	if ($_POST['newpassword1' != $_POST['newpassword2']]) {
		$changepwerror = "Passwords does not match!";
		return;
	}
	if (checkIsAdmin($_POST['adminpassword'])) {
		$id = $_POST['selecteduser'];
		$newuserpw = $_POST['newpassword1'];
		changeUserPw($id, $newuserpw);
	} else {
		$changepwerror = "Wrong password!";
		return;
	}
}

if (isset($_POST['changeuseremail'])) {
	//if true, then check if user exists
	if (!isset($_POST['selecteduser'])) {
		$changeemailerror = "User is not selected!";
		return;
	}

	if (!isset($_POST['adminpassword'])) {
		$changeemailerror = "Admin password required!";
		return;
	}

	if (!isset($_POST['newemail1']) || !isset($_POST['newemail2'])) {
		$changeemailerror = "New email address missing!";
		return;
	}

	if ($_POST['newemail1'] != $_POST['newemail2']) {
		$changeemailerror = "Emails does not match!";
		return;
	}

	$adminpassword = $_POST['adminpassword'];
	$email = $_POST['newemail1'];

	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$changeemailerror = "Invalid email address!";
		return;
	}

	if (checkIsAdmin($_POST['adminpassword'])) {
		$username = $_POST['selecteduser'];
		$newemail = $_POST['newemail1'];
		changeUserEmail($username, $newemail);
	} else {
		$changeemailerror = "Wrong password!";
		return;
	}
}

function deleteUser($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");

	$username_sanitized = mysqli_real_escape_string($db, $username);

	$query = "DELETE FROM user WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $username_sanitized);
	$stmt->execute();

	if (mysqli_affected_rows($db) > 0) {
		//perform error-handling?
	}
	$stmt->close();
}

function changeUserPw($username, $newpw) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");

	$username_sanitized = mysqli_real_escape_string($db, $username);
	$newpw_sanitized = mysqli_real_escape_string($db, $newpw);
	$hashedpassword = password_hash($newpw_sanitized, PASSWORD_DEFAULT);

	$query = "UPDATE user SET password=? WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("ss", $hashedpassword, $username_sanitized);
	$stmt->execute();

	if (mysqli_affected_rows($db) > 0) {
		//perform error-handling?
	}
	$stmt->close();
}

function changeAvatar($username, $avatarname) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");

	$username_sanitized = mysqli_real_escape_string($db, $username);
	$avatarname_sanitized = mysqli_real_escape_string($db, $avatarname);

	$query = "UPDATE user SET avatar=? WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("ss", $avatarname_sanitized, $username_sanitized);
	$stmt->execute();

	if (mysqli_affected_rows($db) > 0) {
		//perform error-handling?
	}
	$stmt->close();
}

function changeUserEmail($username, $newemail) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");

	$username_sanitized = mysqli_real_escape_string($db, $username);
	$newemail_sanitized = mysqli_real_escape_string($db, $newemail);
	
	$query = "UPDATE user SET email=? WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("ss", $newemail_sanitized, $username_sanitized);
	$stmt->execute();

	if (mysqli_affected_rows($db) > 0) {
		//perform error-handling?
	}
	$stmt->close();
}

function unsuspendUser($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$username_sanitized = mysqli_real_escape_string($db, $username);
	$newstatus = "active";

	$query = "UPDATE user SET account_status=? WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("ss", $newstatus, $username);
	$stmt->execute();

	if (mysqli_affected_rows($db) > 0) {
		// perform error-handling?
	}
	$stmt->close();
}

function suspendUser($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$username_sanitized = mysqli_real_escape_string($db, $username);
	$newstatus = "suspended";

	$query = "UPDATE user SET account_status=? WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("ss", $newstatus, $username);
	$stmt->execute();

	if (mysqli_affected_rows($db) > 0) {
		// perform error-handling?
	}
	$stmt->close();
}

function getUserAvatar($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$username_sanitized = mysqli_real_escape_string($db, $username);
	$query = "SELECT `avatar` FROM `user` WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $username_sanitized);
	$stmt->execute();
	if ($stmt) {
		$stmt->bind_result($avatar);
		$stmt->fetch();
		$stmt->close();
		return $avatar;
	}
}

function getUser($username) {
	
}

function userExists($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");

	$username_sanitized = mysqli_real_escape_string($db, $username);

	$query = "SELECT * FROM user where username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $username_sanitized);
	$stmt->execute();
	if ($stmt) {
		return true;
	} else {
		return false;
	}
}

function passwordIsValid($password) {
	$regex = "";

	if (preg_match($regex, $password)) {
		return true;
	} else {
		return false;
	}
}

function getUserRole($username) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$query = "SELECT role FROM user WHERE username=? ";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $username);
	$stmt->execute();
	if ($stmt) {
		$stmt->bind_result($userRole);
		$stmt->fetch();
		return $userRole;
	}
}

function checkIsAdmin($password) {
	$db = new mysqli("localhost", "root", "SuperSecretPassword123456789", "fileindex");
	$adminusername = "admin";

	$query = "SELECT password FROM user WHERE username=?";
	$stmt = $db->prepare($query);
	$stmt->bind_param("s", $adminusername);
	$stmt->execute();
	if ($stmt) {
		$stmt->bind_result($stored_password);
		$stmt->fetch();
		if (password_verify($password, $stored_password)) {
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

?>