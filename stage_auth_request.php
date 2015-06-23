<?php


include_once "include/Database.php";


// check that a auth_key is provided...
if (isset($_GET["auth_key"])) $auth_key = $_GET["auth_key"];
else die("Invalid validation request.  A valid <tt>auth_key</tt> must be provided.\n");

// ... and is in a valid auth_key format...
if (strlen($auth_key) != 32)
	die ("The <tt>auth_key</tt> must be 32 characters long.\n");
if (preg_match('/[^A-Za-z0-9]/', $auth_key))
	die ("The <tt>auth_key</tt> must be provided in a valid format.\n");

// ... and does not already exist.
Database::connect();
$_esc_auth_key = mysql_escape_string($_GET["auth_key"]);
$result = mysql_query("SELECT id FROM auth_requests WHERE `auth_key`='$_esc_auth_key'");
if (!$result) die(mysql_error());
if (mysql_num_rows($result) != 0)
	die("This auth_key is not available. Try another auth_key.\n");


// let's say the staged autorization process will only be active for 3 minutes
$time_limit_in_minutes = 3;


// set the execution timeout of this script so it doesn't prematurely end
// (plus a few seconds just in case)
set_time_limit($time_limit_in_minutes * 60 + 15);


// get the values from the request (mysql_* functions are depreciated, should be modernized)
$auth_key = mysql_escape_string($_GET["auth_key"]);
$notify_url = isset($_GET["notify_url"]) ? mysql_escape_string($_GET["notify_url"]) : "";
$requested_by_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? mysql_escape_string($_SERVER['HTTP_USER_AGENT']) : "";
$requested_by_ip = mysql_escape_string($_SERVER['REMOTE_ADDR']);


// add a staging record to the database
$query = "INSERT INTO `auth_requests` (`id`, `datetime_created`, `auth_key`, `requested_by_ip`, `requested_by_user_agent`, `notify_url`, `datetime_validated`) VALUES (NULL, CURRENT_TIMESTAMP, '$auth_key', '$requested_by_ip', '$requested_by_user_agent', '$notify_url', '0');";
mysql_query($query) or die ("there was an error staging the request. " . mysql_error());


// grab the id of the last inserted record (for later)
$auth_request_id = mysql_insert_id();


// output the magic phrase to the client, signaling that a login has been staged; then flush.
print("Authorization request staged. Awaiting user login...");
// uncomment the line below if you expect to stage auth messages on a browser.
// The output messages won't display on firefox or IE unless the flushed data
// is of a certain length, hence this str_pad below. It's not necessairy when using CURL.
// echo str_pad('',4096)."\n";
ob_flush();
flush();


// await notification that a user has validated an id with this auth_key.
// for this technique, I'm just going to poll the database every n seconds an
// see if a the record has been validated.
$authorization_successful = false;
$countdown = $time_limit_in_minutes * 60;
$query = "SELECT `shared_id_key`, `notify_url`, `validated_by_user_id` FROM `auth_requests` WHERE `id`=$auth_request_id AND `shared_id_key` != ''";
$validated_by_user_id = 0;
$shared_id_key = "";
$notify_url = "";

while (true)
{
	// use the query on the database to check if the record is updated
	$result = mysql_query($query);
	if (mysql_num_rows($result) == 1)
	{
		$authorization_successful = true;
		$validated_by_user_id = mysql_result($result, 0, "validated_by_user_id");
		$shared_id_key = mysql_result($result, 0, "shared_id_key");
		$notify_url = mysql_result($result, 0, "notify_url");
		break;
	}

	// print and flush the countdown clock
	print("." . $countdown);
	ob_flush();
	flush();

	// decrement the countdown clock
	sleep(2);
	$countdown -= 2;
	if ($countdown < 0) break;
}


// Either the authorization was successful or not.  Output the result.
if ($authorization_successful)
{
	$result = mysql_query("SELECT * FROM users");
	print("\nThe authentication was successful!\nThe shared_id_key is [[$shared_id_key]] and the allegro_member_id is (($validated_by_user_id))\n");
	// TODO: POST to the notify_url here
	// TODO: POST to the notify_url here
	// TODO: POST to the notify_url here
}
else
{
	print("The authroization window has timed out.");
}

