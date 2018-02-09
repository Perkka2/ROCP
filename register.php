<?php
/*
Not your simple registration script, as this uses input validation using Javascript & PHP, anti-bot security codes,
as well as email/admin validation, if desired.
Credits to Invision Power Board for their security codes.
*/
require 'memory.php';
check_auth($_SERVER['PHP_SELF']); // checks for required access
if ($GET_rc != "" && $GET_p >= 0 ) {
	// Displays the image, based on the arguements in the image
	$query = sprintf(GET_CODE, $GET_rc);
	$result = execute_query($query, "register.php");
	$line = $result->FetchRow();
	$this_number = substr($line[0], $GET_p - 2, 1);
	$numbers = array( 
		0 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKsOnmqSPjtT1ZdnnjCUqBQAOw==',
		1 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUjAEWyMqoXIprRkjxtZJWrz3iCBQAOw==',
		2 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUDH5hiKubnpPzRQvoVbvyrDHiWAAAOw==',
		3 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKbaHgRyUZtmlPtlfnnMiGUFADs=',
		4 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjAN5mLDtjFJMRjpj1Rv6v1SHN0IFADs=',
		5 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhA+Bpxn/DITL1SRjnps63l1M9RQAOw==',
		6 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVjIEYyWwH3lNyrQTbnVh2Tl3N5wQFADs=',
		7 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIUhI9pwbztAAwP1napnFnzbYEYWAAAOw==',
		8 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDH5hiKubHgSPWXoxVUxC33FZZCkFADs=',
		9 => 'R0lGODlhCAANAJEAAAAAAP////4BAgAAACH5BAQUAP8ALAAAAAAIAA0AAAIVDA6hyJabnnISnsnybXdS73hcZlUFADs=',
	);
	//flush();
	header("Content-type: image/gif");
	echo base64_decode($numbers[$this_number]);
	exit();
}
require 'header.inc';

if (strlen($GET_auth) == 32 && strlen($GET_user) >= 4) {
	$query = sprintf(CONFIRM_AUTH, $GET_auth, $GET_user);
	$result = execute_query($query, "register.php");
	if ($result->RowCount() == 0) {
		redir("login.php", "Sorry, the auth code and/or username entered does not match the records in the database.");
	}
	else {
		$line = $result->FetchRow();
		add_account($line[2], $line[3], $line[4], $line[5],$CONFIG_default_level, true);
		$query = sprintf(DEL_PENDING, $GET_auth);
		$result = execute_query($query, "register.php", 0, 0, true);
		redir("login.php", "Account Verification successful! You can log into the CP, as well as the server now.");
	}
}
				
if (!$CONFIG_register && !$GET_auth) {
	redir("login.php", "Sorry, the Administrator has disabled registration.");
}

if ($POST_action == "Register Account!") {
	// Register clicked
	$register_user = $POST_register_user;		//obtains data
	$register_password = $POST_register_pass;
	$register_gender = $POST_gender;
	$register_code = $POST_code;
	$register_email = $POST_register_email;
	$register_level = $CONFIG_default_level;
	
	if ($CONFIG_secure_mode) {
		// Makes sure that security code has been entered
		if (!is_numeric($POST_code)) {
			display_error("You must enter a security code to register!");
		}
	}
	// checks lengths of username and password
	if (strlen($register_user) < 4 or strlen($register_user) > 24) {
		display_error("Account Name must be between 4 and 24 letters.");
	}
	elseif (strlen($register_password) < 4 or strlen($register_password) > 24) {
		display_error("Password has to be between 4 and 24 letters.");
	}
	elseif ($POST_register_pass != $POST_register_pass2) {
		display_error("Your two passwords do not match! Please try again.");
	}
	elseif (strlen($register_email) < 6 or strlen($register_email) > 60) {
		display_error("Your email must be between 6 and 60 letters.");
	}
	elseif ($CONFIG_sim_pass) {
		if (similar_text(strtolower($register_user),strtolower($register_password)) == strlen($register_password)) {
			display_error("Password is too similar to your username.");
		}
	}
	// checks if legal characters are in username/password
	if (!isalphanumeric($register_user) or !isalphanumeric($register_password)) {
		display_error("Illegal characters detected. Please use A-Z, a-z, 0-9 only.");
	}
	$query = sprintf(CHECK_DUPE_ACCOUNT, $register_user);	// searches if account already exists
	$result = execute_query($query, "register.php");
	if ($result->RowCount() > 0) {
		display_error("Account Already Exists, please choose another one.");
	}
	$query = sprintf(CHECK_DUPE_PENDING_ACCOUNT, $register_user);	// searches if account already exists
	$result = execute_query($query, "register.php");
	if ($result->RowCount() > 0) {
		if ($CONFIG_register_type == 1) {
			display_error("This account has already been requested! If it is yours, please validate it by email.");
		}
		else {
			display_error("This account has already been requested! If it is yours, please wait for a GM to accept you.");
		}
	}
	if ($CONFIG_secure_mode) {
		// Checks that security code entered is the same as code in DB
		$query = sprintf(CHECK_CODE, $POST_reg_id, $POST_code);
		$result = execute_query($query, "register.php");
		if ($result->RowCount() == 0) {
			display_error("You did not enter the correct security code!");
		}
		// There is a match, delete the entry from the DB
		$query = sprintf(DELETE_CODE, $POST_reg_id);
		$result = execute_query($query, "register.php", 0, 0, true);
	}
	// Next line added by Maldiablo
	$original_password = $register_password;
	if ($CONFIG_use_md5) {
		// MD5 Support upon registration.
		$register_password = md5($register_password);
	}
	
	$ip = ip2long($_SERVER['REMOTE_ADDR']);
	
	// Checks the limits of the server
	
	if ($CONFIG_max_accounts > 0) {
		// Checks that max accounts on server has not been exceeded
		$query = CHECK_MAX_ACCOUNTS;
		$result = execute_query($query, "register.php");
		if ($result->fields[0] >= $CONFIG_max_accounts) {
			display_error("The server account limit is full!");
		}
	}
	
	if ($CONFIG_max_per_ip > 0) {
		// Checks that max accounts per IP has not been exceeded
		$query = sprintf(CHECK_MAX_ACCOUNTS_IP, $ip);
		$result = execute_query($query, "register.php");
		if ($result->fields[0] >= $CONFIG_max_per_ip) {
			display_error("You cannot sign up for more accounts!");
		}
	}
	
	if ($CONFIG_max_per_email > 0) {
		// Checks that max accounts by email has not been exceeded
		$query = sprintf(CHECK_MAX_ACCOUNTS_EMAIL, $register_email);
		$result = execute_query($query, "register.php");
		if ($result->fields[0] >= $CONFIG_max_per_email) {
			display_error("You cannot have more than $CONFIG_max_per_email accounts on $register_email!");
		}
	}
	
	// Checks the limits in the pending table
	if ($CONFIG_register_type > 0) {
		if ($CONFIG_max_per_ip > 0) {
			// Checks that max accounts per IP in pending list has not been exceeded
			$query = sprintf(CHECK_MAX_PENDING_ACCOUNTS_IP, $ip);
			$result = execute_query($query, "register.php");
			$line = $result->FetchRow();
			if ($result->fields[0] >= $CONFIG_max_per_ip) {
				display_error("You cannot sign up for more accounts!");
			}
		}
		
		if ($CONFIG_max_per_email > 0) {
			// Checks that max accounts by email has not been exceeded
			$query = sprintf(CHECK_MAX_PENDING_ACCOUNTS_EMAIL, $register_email);
			$result = execute_query($query, "register.php");
			if ($result->fields[0] >= $CONFIG_max_per_email) {
				display_error("You cannot have more than $CONFIG_max_per_email accounts on $register_email in pending list!");
			}
		}
	}
	
	switch ($CONFIG_register_type) {
		case 0:
			// Inserts the registered user to login table
			add_account($register_user, $original_password, $register_gender, $register_email, $register_level);
			break;
		case 1: case 2:
			// Inserts the user to pending table
			$reg_id = md5(uniqid(microtime()) ); // generate a unique session id
			// Adds the account, with encrypted password to pending table
			$query = sprintf(ADD_PENDING, $reg_id, $register_user, $register_password, $register_gender, $register_email, $ip);
			$result = execute_query($query, "register.php", 0, 0, true);
			break;
	}
	if ($link->Affected_Rows() == 0) {
		display_error("Account Signup Failed, please contact one of the gamemasters, with any error messages if necessary.");
	}
	
	if ($CONFIG_register_type == 1) {
		// Only register_type 1 sends an email
		$message = sprintf($lang['confirmemail'], $CONFIG_server_name, $register_user, $original_password, $CONFIG_server_name, $CONFIG_cp_location, $reg_id, $register_user, $CONFIG_server_name);
		SendMail($register_user, $register_email, "$CONFIG_server_name Server Registration", $message);
	}
	
	// Constructs the final message
	
	switch ($CONFIG_register_type) {
		case 0:
			$msg = "
			Account Signup Sucessful!<br>
			Your email for character deletion is: $register_email
			";
			if ($forums_location != "") {
				$msg .= "<br>Forums are located at: <a href=\"$forums_location\">$forums_location</a>\n";
			}
			if ($patch_location != "") {
				$msg .= "<br>Patch is located at: <a href=\"$patch_location\">$patch_location</a>\n";
			}
			if ($irc != "") {
				$msg .= "<br>IRC Channel: $irc\n";
			}
			break;
		case 1:
			$msg = "
			An email has been sent to you ($POST_register_email).  Please click the link inside to activate your account.
			<br><br>
			If you do not get the email within 5 minutes, make make sure that you are using the correct email address. <br>
			Take note that hotmail accounts seem to take more time to process <br>
			or do not recieve the email at all.<br>";
			break;
		case 2:
			$msg = "
			Your request has been sent to the GMs, where they will review your request. Please check back later
			";
			break;
	}	
	redir("login.php", $msg);
}
else {
	$server_rules = nl2br(file_get_contents("rules.txt"));
	switch ($CONFIG_register_type) {
		case 0:
			$type_string = "Normal";
			break;
		case 1:
			$type_string = "Email Validation";
			break;
		case 2:
			$type_string = "Admin Validation";
			break;
	}
	echo "
<form action=\"register.php\"  name=\"register\" method=\"POST\" onSubmit=\"return checkData()\">
	<table align=\"center\" class=mytable width=80% border=\"0\">
		<tr class=mytitle>
			<td colspan=3>Registration for $CONFIG_server_name</td>
		</tr>
		<tr class=myheader>
			<td colspan=3>Note: Please click register ONLY ONCE!</td>
		</tr>
		<tr class=mycell>
			<td colspan=3>Registration Type: <b>$type_string</b></td>
		</tr>
		<tr class=mycell>
			<td>Account Name: (Only Alphanumeric, no spaces allowed)</td>
			<td><input type=\"text\" class=\"myctl\" name=\"register_user\"></td>
		</tr>

		<tr class=mycell>
			<td>Password: </td>
			<td><input type=\"password\" class=\"myctl\" name=\"register_pass\"></td>
		</tr>
		
		<tr class=mycell>
			<td>Enter Password again: </td>
			<td><input type=\"password\" class=\"myctl\" name=\"register_pass2\"></td>
		</tr>

		<tr class=mycell>
			<td>Gender: </td>
			<td>
				<select name=\"gender\" class=\"myctl\">
				<option value=M>Male
				<option value=F>Female
				</select>
			</td>
		</tr>
		<tr class=mycell>
			<td>Email: </td>
			<td><input type=\"text\" class=\"myctl\" name=\"register_email\"></td>
		</tr>
		<tr class=mytitle>
			<td colspan=2><b>By registering, you agree to the following rules:</b></td>
		</tr>
		<tr class=mycell>
			<td colspan=2><b>$server_rules</b></td>
		</tr>
	</table>
	";
	require 'terms.php';
	
	if ($CONFIG_secure_mode) {
		// Anti-bot registration
		
		// Clear out old register IDs older than 1 hour old.
		$query = sprintf(CLEAR_CODES, time() - (60*60));
		$result = execute_query($query, 'register.php', 0, 0, true);
		
		$reg_id = md5(uniqid(microtime()) ); // set a unique session id
		// Generates the random number
		mt_srand ((double) microtime() * 1000000);
		$reg_code = mt_rand(100000, 999999);
		
		// Stores the security code into the DB
		$query = sprintf(INSERT_CODE, $reg_id, $reg_code, time());
		$result = execute_query($query, "register.php", 0, 0, true);
		
		echo "<p>Please enter the numbers of the image below into the text box.<br>\n";
		echo "Note: They are all numbers, there are no letters.<p>\n";
		generate_random_number($reg_id);
		echo "<p>Security Code: <input type=\"text\" class=\"myctl\" name=\"code\"><p>";
	}
	echo "
	<p>
	<input type=\"submit\" class=\"myctl\" name=\"action\" value=\"Register Account!\">
	";
} ?>
<SCRIPT LANGUAGE="JavaScript">
<!--
function checkData () {
	// Validation functions obtained from:
	// http://www.devshed.com/c/a/JavaScript/Form-Validation-with-JavaScript/7/
	
	// check to see if input is numeric
	function isNumeric(val)
	{
		if (val.match(/^[0-9]+$/))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}
	// check to see if input is alphanumeric
	function isAlphaNumeric(val)
	{
		if (val.match(/^[a-zA-Z0-9]+$/))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}
	// check to see if input is a valid email address
	function isEmailAddress(val)
	{
		if (val.match(/^([a-zA-Z0-9])+([.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-]+)+/))
		{
			return true;
		}
		else
		{
			return false;
		}	
	}
	user = document.register.register_user.value;
	pass1 = document.register.register_pass.value;
	pass2 = document.register.register_pass2.value;
	email = document.register.register_email.value;
	if (!user || user.length < 4 || user.length > 24) {
		alert("You must enter a username between 4 and 24 characters.");
		return false;
	}
	if (!pass1 || pass1.length < 4 || pass1.length > 24) {
		alert("You must enter a password between 4 and 24 characters.");
		return false;
	}
<?php
	if ($CONFIG_sim_pass) {
		echo"
		if (user.toLowerCase() == pass1.toLowerCase()) {
			alert(\"Your password cannot be equal to your username!\");
			return false;
		}
		";
	}
?>
	if (!pass2 || pass2.length < 4 || pass2.length > 24) {
		alert("Your 2nd password must be between 4 and 24 characters.");
		return false;
	}
	if (pass1 != pass2) {
		alert("Your passwords do not match!");
		return false;
	}
	if (!email || email.length < 6 || email.length > 60) {
		alert("You must enter an email between 6 and 60 characters.");
		return false;
	}
<?php
	if ($CONFIG_secure_mode) {
		echo "
		code = document.register.code.value;
		if (!isNumeric(code) || code.length != 6) {
			alert(\"Your security code must be 6 characters and only numbers!\");
			return false;	
		}
		";
	}
?>
	if (!isAlphaNumeric(user)) {
		alert("Your username must be alphanumeric!");
		return false;
	}
	if (!isAlphaNumeric(pass1)) {
		alert("Your passwords must be alphanumeric!");
		return false;
	}
	if (!isEmailAddress(email)) {
		alert("You did not enter a valid email address!");
		return false;
	}
	return true;
}

// -->
</SCRIPT>
<?php
require 'footer.inc';

function generate_random_number ($reg_id) {
	echo "
	<img src='register.php?rc={$reg_id}&p=2' border='0' alt='Code Bit' />
	<img src='register.php?rc={$reg_id}&p=3' border='0' alt='Code Bit' />
	<img src='register.php?rc={$reg_id}&p=4' border='0' alt='Code Bit' />
	<img src='register.php?rc={$reg_id}&p=5' border='0' alt='Code Bit' />
	<img src='register.php?rc={$reg_id}&p=6' border='0' alt='Code Bit' />
	<img src='register.php?rc={$reg_id}&p=7' border='0' alt='Code Bit' />
	<input type=hidden  name=reg_id value=$reg_id>
	";
}
function display_error($error_message) {
	global $STORED_skin, $start_time, $STORED_level, $queries, $logged_in;
	require 'config.php';
	require 'extract.inc';
	redir("register.php", $error_message);
	require 'footer.inc';
	exit();
}
?>