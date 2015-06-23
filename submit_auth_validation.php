<?php


include_once "include/Database.php";


// validate posted login data with the corresponding auth_key

if ($_SERVER['REQUEST_METHOD'] != 'POST') die("You must vaildate authorization with a POST request");


// check that the correct arguments have been posted
if (!isset($_POST["auth_key"]) || !isset($_POST["username"]) || !isset($_POST["password"]))
	die ("There was an error posting the form.  A <tt>username</tt>, <tt>password</tt>, and <tt>auth_key</tt> must be posted.");


// connect to the database
Database::connect();


// grab and sanitize the posted values
$auth_key = mysql_escape_string($_POST["auth_key"]);
$username = mysql_escape_string($_POST["username"]);
$password = mysql_escape_string($_POST["password"]);
$ip = mysql_escape_string($_SERVER["REMOTE_ADDR"]);


// check that the key has not already been used
$result = mysql_query("SELECT EXISTS(SELECT 1 FROM auth_requests WHERE `auth_key`='$auth_key') as my_check");
if (!$result) die ("There was an error validating if the auth_key exists or not.");
if (mysql_result($result, 0, "my_check") == 1);


// validate the username/password
// For the purposes of this example, I have created a trivial 'users' table with id/username/password/acc_user_id
// In production, this block of code should be modified so that the appropriate user database
// is queried, validating the username/password combination and returning the user id.
$result = mysql_query("SELECT id, acc_user_id FROM users WHERE `username`='$username' AND `password` = '$password'");
if (!$result) die ("There was an error retrieving the username/password on the database.");
else if (mysql_num_rows($result) != 1) die ("That username/password is invalid. Please try again.");
$acc_user_id = mysql_result($result, 0, "acc_user_id");


// generate a shared_id_key to be known to the client and posted to the notify_url
$shared_id_key = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 16)), 0, 16);


// write into the database that the user has successfully logged in.
// The IPs of the requestor and the validator must match.
$result = mysql_query("UPDATE auth_requests
						SET `datetime_validated` = NOW()
							, `shared_id_key` = '$shared_id_key'
							, `validated_by_user_id` = '$acc_user_id'
						WHERE `auth_key`='$auth_key'
							AND `shared_id_key` = ''
							AND `requested_by_ip` = '$ip'");
if (!$result) die ("There was an error updating the record in the DB: " . mysql_error());
if (mysql_affected_rows() == 0)
	die("Unable to validate the auth request record.  It's possible that the authorization window has expired or that you are trying to login from a different IP.  If so, please resend the authorization and use the same device that was used to make the request.");


?><!DOCTYPE html>
<head>
	<link rel="stylesheet" href="css/acc_auth.css">
</head>
<body>


<div class="centered_cell">
	<img src="images/alex_auth-03.png" /><br>
	<h2>Congratulations!</h2>
	<p>You have successfully authorized! You can now close this window and return to your game.</p>
</div>


</body>
</html>