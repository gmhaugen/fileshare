<?php
session_start();
if (session_destroy()) {
	$_SESSION = array();
	header("Location: frontpage.php");
}
?>