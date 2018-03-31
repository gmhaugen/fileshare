<?php

if (!PHP_SESSION_ACTIVE) {
	session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
	<style>
		.header {
			position:relative;
		}
	</style>
	<script type="text/javascript" async>
		
	</script> 
	<title>Home</title>
</head>
<div class="header" id="theheader"><?php include('header.php'); ?></div>
<body>
<div class="w3-padding-64" id="bodycontainer">
	<p>This is test.</p>
	<input type="text">
</div>
</body>
</html>