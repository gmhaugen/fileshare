<?php
$loginCSRFToken = bin2hex(openssl_random_pseudo_bytes(24));
$_SESSION['login-csrf-token'] = $loginCSRFToken;

$error = "";

?>

<!DOCTYPE html>
<html>
<head>
	<style>

	body {
		font-family: "Verdana";
	}

	div.login {
		max-width: 300px;
		margin: auto;
	}
	
	.codeicon {
		color: white;
	}

	.login-button {
		width: 150px;
		margin: auto;
	}

	.register-button {
		width: 150px;
	}

	.login-error {
		color: red;
	}
	</style>

	<script src="js/jquery-3.3.1.js"></script>
	<script async>
		function logIn() {
			var loginCSRFToken = document.getElementById('login-csrf-token').value;
			var username = document.getElementById('username').value;
			var username = document.getElementById('password').value;
			console.log(document.getElementById('username').value.length);
			if (evaluateUsername(document.getElementById('username').value) && evaluatePassword(document.getElementById('password').value)) {
				console.log('valid');
				$.ajax({
					type: "post",
					url: "login.php",
					data: $('#loginform').serialize(),
					dataType: "json",
					beforeSend: function() {
						$("#loginformcontainer").hide();
						$("#loginloading").show();
					},
					success: function(data) {
						sleep(500);
						console.log(data);
						if (data.responseText == "ok") {
							$("#loginformcontainer").show();
							$("#loginloading").hide();
							$("#theheader").load('header.php');
						} else {
							document.getElementById('error').innerHTML = '<p class="login-error">' + data.responseText + '</p>';
							$("#loginformcontainer").show();
							$("#loginloading").hide();
						}
					},
					error: function(data) {
						sleep(500);
						console.log(data);
						if (data.responseText == "ok") {
							$("#loginformcontainer").show();
							$("#loginloading").hide();
							$("#theheader").load('header.php');
						} else {
							document.getElementById('error').innerHTML = '<p class="login-error">' + data.responseText + '</p>';
							$("#loginformcontainer").show();
							$("#loginloading").hide();
						}
					},
					complete: function() {
					}
				
				});
				return false;
			} else {
				document.getElementById('error').innerHTML = '<p class="login-error">Something went wrong. Try again later.</p>';
				return false;
			}

			
		}

		$('loginform').submit(function() {
			logIn();
		});

		function evaluateUsername(username) {
			var usernameLength = username.length;
			if (usernameLength > 30) {
				return false;
			}
			return true;
		}
		function evaluatePassword(password) {
			passwordLength = password.length;
			if (passwordLength > 30) {
				return false;
			}
			return true;
		}


		$(document).ready(function () {

		});

		function sleep(milliseconds) {
  			var start = new Date().getTime();
  			for (var i = 0; i < 1e7; i++) {
    			if ((new Date().getTime() - start) > milliseconds){
      				break;
    			}
  			}
		}

		function loadRegisterForm() {
			document.getElementById('loginmodal').style.display='none';
			document.getElementById('registermodal').style.display='block';
		}
	</script> 
</head>
<body>
<div class="login w3-container w3-center" id="login">
	<div class="w3-center" id="loginformcontainer">
		<h1>Log in</h1>
		<br>
		<form class="w3-container" id="loginform" method="post" onsubmit="return logIn();">
			<input type="hidden" name="login-csrf-token" id="login-csrf-token" value="<?php echo $loginCSRFToken; ?>">
			<input type="hidden" name="action" id="action" value="login">
			<input class="w3-input" id="username" type="text" name="username" placeholder="username" autocomplete="off" required>
			<br>
			<input class="w3-input" id="password" type="password" name="password" placeholder="*********" autocomplete="off" required>
			<br>
			<div class="loginerror" id="error">
			</div>
			<input class="login-button w3-button w3-block w3-indigo" type="submit" name="submit" id="submitbutton" value="Log in">
			or
			<br>
			<a class="register-button w3-button w3-indigo" onclick="loadRegisterForm();">Register</a>
			<br>
		</form>
	</div>
	<br>
	<br>
	<br>
	<br>
	<div class="" id="loginloading" style="display:none;">
		<i id="load-progress" class="fa fa-circle-o-notch fa-spin" style="color:blue;font-size:50px"></i>
	</div>
</div>
	
</body>
</html>