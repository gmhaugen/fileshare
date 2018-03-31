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
	<title>Profile</title>
</head>
<div class="header" id="theheader"><?php include('header.php'); ?></div>
<body>
<div class="w3-padding-64" id="bodycontainer">
	<?php if (!isset($_SESSION['username'])) { echo "<h3>Nothing to see here!</h3><p>(You are not logged in)</p>"; } else { ?>
	<p>This is profile page.</p>
	<input type="text">
	<?php } ?>
</div>
</body>
</html>