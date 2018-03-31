<?php
$registerCSRFToken = bin2hex(openssl_random_pseudo_bytes(24));
$_SESSION['register-csrf-token'] = $registerCSRFToken;

$error = "";

?>

<!DOCTYPE html>
<html>
<head>
	<style>

	body {
		font-family: "Verdana";
	}

	div.register {
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

	.register-error {
		color: red;
	}

	.register-success {
		color: green;
	}
	</style>

	<script src="js/jquery-3.3.1.js"></script>
	<script async>
		function register() {
			if (evaluateUsername(document.getElementById('username').value) && evaluatePassword(document.getElementById('password').value)) {
				$.ajax({
					type: "post",
					url: "registration/register.php",
					data: $('#registerform').serialize(),
					dataType: "json",
					beforeSend: function() {
						$("#registerformcontainer").hide();
						$("#registerloading").show();
					},
					success: function(data) {
						sleep(500);
						console.log(data);
						if (data.responseText == "ok") {
							$("#registerformcontainer").show();
							$("#registerloading").hide();
							document.getElementById('registersuccess').innerHTML = '<p class="register-success">You have registered successfully!</p>';
							// register successfull, show login button
						} else {
							document.getElementById('registererror').innerHTML = '<p class="register-error">' + data.responseText + '</p>';
							$("#registerformcontainer").show();
							$("#registerloading").hide();
						}
					},
					error: function(data) {
						if (data.responseText == "ok") {
							$("#registerformcontainer").show();
							$("#registerloading").hide();
							console.log(data);
							document.getElementById('registersuccess').innerHTML = '<p class="register-success">You have registered successfully!</p>';
						} else {
							sleep(500);
							console.log(data);
							document.getElementById('registererror').innerHTML = '<p class="register-error">Something went wrong. Try again later.JS1</p>';
							$("#registerformcontainer").show();
							$("#registerloading").hide();
						}
					},
					complete: function() {

					}
				});
				return false;
			} else {
				document.getElementById('registererror').innerHTML = '<p class="login-error">Something went wrong. Try again later.JS2</p>';
			}
		}

		$('registerform').submit(function() {
			register();
		});


		function usernameCheck() {

		}
		function emailCheck() {

		}
		function pwCheck() {
			
		}
		function pwConfCheck() {

		}

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

		function sleep(milliseconds) {
  			var start = new Date().getTime();
  			for (var i = 0; i < 1e7; i++) {
    			if ((new Date().getTime() - start) > milliseconds){
      				break;
    			}
  			}
		}

		function loadLoginForm() {
			document.getElementById('registermodal').style.display='none';
			document.getElementById('loginmodal').style.display='block';
		}
	</script> 
</head>
<body>
<div class="register w3-container w3-center" id="register">
	<div class="w3-center" id="registerformcontainer">
		<h1>Fill in form to register</h1>
		<br>
		<form class="w3-container" id="registerform" method="post" onsubmit="return register();">
			<input type="hidden" name="register-csrf-token" id="register-csrf-token" value="<?php echo $registerCSRFToken; ?>">
			<input type="hidden" name="action" id="action" value="register">

			<input class="w3-input" id="username" type="text" name="username" onkeyup="usernameCheck();" placeholder="username" autocomplete="off" required>
			<br>
			<input class="w3-input" id="email" type="email" name="email" onkeyup="emailCheck();" placeholder="example@example.com" autocomplete="off" required>
			<br>
			<input class="w3-input" id="password1" type="password" name="password1" onkeyup="pwCheck();" placeholder="password" autocomplete="off" required>
			<br>
			<input class="w3-input" id="password2" type="password" name="password2" onkeyup="pwConfCheck();" placeholder="repeat password" autocomplete="off" required>
			<div class="register-error" id="registererror">
			</div>
			<div class="register-success" id="registersuccess">
			</div>
			<br>
			<input class="login-button w3-button w3-block w3-indigo" type="submit" name="submit" id="submitbutton" value="Register">
			or
			<br>
			<a class="register-button w3-button w3-indigo" onclick="loadLoginForm();">Log in</a>
			<br>
		</form>
	</div>
	<br>
	<br>
	<br>
	<br>
	<div class="" id="registerloading" style="display:none;">
		<i id="load-progress" class="fa fa-circle-o-notch fa-spin" style="color:blue;font-size:50px"></i>
	</div>
</div>
	
</body>
</html>