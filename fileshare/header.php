<?php
session_start();

?>

<!DOCTYPE html>
<html>
<head>
	<link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	
	<style type="text/css">

	body {
		font-family: "Verdana";
	}

	.codeicon {
		color: white;
	}
	</style>

	<script type="text/javascript" async>
	</script> 
</head>
<body>
<div class="w3-container">
	<div class="w3-bar w3-light-grey w3-border w3-large">
		<a href="frontpage.php" class="w3-bar-item w3-button w3-green"><i class="fa fa-home"></i></a>
		<a href="#fileshare.php" class="w3-bar-item w3-button w3-indigo"><i class="glyphicon glyphicon-file"></i></a>
		<a href="#chatzone.php" class="w3-bar-item w3-button w3-purple"><i class="glyphicon glyphicon-comment"></i></a>
		<a href="#code" class="w3-bar-item w3-button w3-blue"><i class="codeicon fa fa-code"></i></a>
		<?php if (!isset($_SESSION['username'])) { ?>
			<a class="w3-bar-item w3-button w3-right w3-green" onclick="document.getElementById('loginmodal').style.display='block'"><i class="fa fa-sign-in"></i></a>
		<?php } else { ?>
			<a class="w3-bar-item w3-button w3-right w3-red" href="logout.php"><i class="fa fa-sign-out"></i></a>
			<a class="w3-bar-item w3-button w3-right" style="text-decoration:none" href="profile.php"><?php echo $_SESSION['username']; ?></a>
		<?php } ?>
	</div>
</div>
	
</body>
<div id="loginmodal" class="w3-modal">
	<div class="w3-modal-content w3-animate-top" style="width: 40%;min-height: 200px">
		<div class="w3-container">
    		<a onclick="document.getElementById('loginmodal').style.display='none'" class="w3-button w3-display-topright w3-hover-red">&times;</a>
			<?php include('dialogues/logindialogue.php') ?>
		</div>
	</div>
</div>
<div id="registermodal" class="w3-modal">
	<div class="w3-modal-content w3-animate-top" style="width: 40%;min-height: 200px">
		<div class="w3-container">
			<a onclick="document.getElementById('registermodal').style.display='none'" class="w3-button w3-display-topright w3-hover-red">&times;</a>
			<?php include('dialogues/registerdialogue.php'); ?>
		</div>
	</div>
</div>
</html>