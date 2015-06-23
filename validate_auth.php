<?php


include_once "include/Database.php";


// validate that a auth_key is provided
if (isset($_GET["auth_key"])) $auth_key = $_GET["auth_key"];
else die("Invalid validation request.  A valid <tt>auth_key</tt> must be provided.");

// check that the auth_key is staged and ready to be authorized
Database::connect();
$validating_by_ip = mysql_escape_string($_SERVER["REMOTE_ADDR"]);
$validating_with_auth_key = mysql_escape_string($auth_key);
$_esc_auth_key = mysql_escape_string($_GET["auth_key"]);
$result = mysql_query("SELECT id FROM auth_requests WHERE `auth_key`='$_esc_auth_key' AND `shared_id_key`=''");
if (!$result) die(mysql_error());
if (mysql_num_rows($result) != 1)
	die("This auth_key you provided is not active. Please resend the authorization request and try again.\n");


?><!DOCTYPE html>
<head>
	<link rel="stylesheet" href="css/acc_auth.css">
</head>
<body>


<div class="centered_cell">
<img src="images/alex_auth-03.png" /><p>Authorize using your allegro.cc identity!</p>
<form method="post" action="submit_auth_validation.php" />
<table>
	<tr>
		<td class="l">username</td>
		<td><input class="text_input" type="text" name="username" /></td>
	</tr>
	<tr>
		<td class="l">password</td>
		<td><input class="text_input" type="password" name="password" /></td>
	</tr>
	<tr>
		<td colspan=2><input class="submit_button" type="submit" value="Authorize" /></td>
	</tr>
</table>
<input type="hidden" name="auth_key" value="<?php echo $auth_key ?>" />
</form>
</div>


</body>
</html>