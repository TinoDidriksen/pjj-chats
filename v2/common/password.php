<?php
    ob_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (mail@tinodidriksen.com)">
	<meta name="GENERATOR" content="Tino Didriksen (mail@tinodidriksen.com)">
	<title>Project JJ Chats - Let Worlds Unfold</title>

<script type="text/javascript">
if (window != window.top)
  top.location.href = location.href;
</script>

<style>
	table,tr,td,font,body,a { font-size: 10pt; font-family: Times New Roman;}
</style>
</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="../gfx/up_tile.gif" valign="top" align="left" height="32">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="32">
	<tr><td valign="top" align="left" height="32"><a href="?"><img src="../gfx/projectjj.gif" border="0"></a></td>
	<td valign="top" align="right" height="32"><a href="mailto:chats@projectjj.com"><img src="../gfx/phpchat.gif" border="0"></a></td></tr>
	</table>
</td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%"><img src="../gfx/up_l.gif" border="0"></td>
	<td valign="top" height="100%">
<?php
	echo "<center><img src='../gfx/null.gif' border=0><br>\n";
	require_once '../mysql.php';
	require_once '../setup.php';
	require_once 'tome_of_power.php';

function NewPass($uid, $key, $stamp) {
	global $handler, $master_name_filter, $master_name, $master_email;

	mysqli_query($handler, "DELETE FROM uo_chat_newpass WHERE pass_stamp < DATE_SUB(now(), INTERVAL 15 MINUTE)");

	if (!preg_match('@^\w+$@', $key)) {
		echo "<p>Invalid key '$key'.<br>\n";
		return -1;
	}
	$uid = intval($uid);
	$stamp = date('Y-m-d H:i:s', strtotime($stamp));

	$result = mysqli_query($handler, "SELECT pass_uid, pass_key, pass_stamp
		FROM uo_chat_newpass
		WHERE pass_uid=$uid AND pass_key='".$key."' AND pass_stamp='".$stamp."'");
	$verify = mysqli_fetch_assoc($result);
	mysqli_free_result($result);

	if (empty($verify['pass_uid']) || $verify['pass_uid'] != $uid) {
		echo "<p>Invalid or expired reset link.<br>\n";
		return -1;
	}
	if (empty($verify['pass_key']) || $verify['pass_key'] != $key) {
		echo "<p>Invalid or expired reset link.<br>\n";
		return -1;
	}
	if (empty($verify['pass_stamp']) || $verify['pass_stamp'] != $stamp) {
		echo "<p>Invalid or expired reset link.<br>\n";
		return -1;
	}

	$result = mysqli_query($handler, "SELECT chat, uid, username, email
		FROM uo_chat_database
		WHERE uid=$uid");
	$cuser = mysqli_fetch_assoc($result);
	mysqli_free_result($result);

	if (empty($cuser['username']) || $cuser['uid'] != $uid) {
		echo "<p>User $uid was not found in the database.<br>\n";
		return -1;
	}
	if (strlen($cuser['email']) < 5 || strpos($cuser['email'], '@') === false) {
		echo "<p>User $uid doesn't have an email. Impossible to send new password.<br>\n";
		return -1;
	}
	$chat = trim(substr($cuser['chat'], 4));
	$username = trim($cuser['username']);
	$email = trim($cuser['email']);

	$newpass = RandomPass();
	$md5pass = md5($newpass);

	mysqli_query($handler, "UPDATE uo_chat_database SET password='".$md5pass."' WHERE uid=$uid");
	mysqli_query($handler, "DELETE FROM uo_chat_newpass WHERE pass_uid=$uid");

	$username = ucwords($username);

	$headers = '';
	$headers .= "From: $master_name <$master_email>\n";
	$headers .= "Reply-To: $master_name <$master_email>\n";
	$headers .= "Bcc: $master_name <$master_email>\n";

	$subject = "pJJ: New password for '$username' of /$chat";

	$message = <<<XBODY
New password for user {$username} (uid:{$cuser['uid']}):
$newpass

-- pJJ Chats

XBODY;

	mail("$username <$email>", $subject, $message, $headers);

	$fc = fopen("../$chat/register/wizard_locked/actionlog.log", "ab");
	fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": New password generated for user '$username' - $ip\n"));
	fclose($fc);

	echo "<p>New password generated for user '$username', and emailed to $email.<br>\n";
}

function RequestPass($chat, $username) {
	global $handler, $master_name_filter, $master_name, $master_email;

	if (!preg_match('@^\w+$@', $chat)) {
		echo "<p>Invalid chat path '$chat'.<br>\n";
		return -1;
	}

	$username = strtolower($username);
	$username = preg_replace('~'.$master_name_filter.'~i', '', $username);

	$chatpath = "chat".$chat;
	$result = mysqli_query($handler, "SELECT chat, uid, username, flags, email
		FROM uo_chat_database
		WHERE chat='$chatpath' AND username='$username'");
	$cuser = mysqli_fetch_assoc($result);
	mysqli_free_result($result);

	if (empty($cuser['username']) || $cuser['username'] != $username) {
		echo "<p>User '$username' was not found in the database.<br>\n";
		return -1;
	}
	if (strlen($cuser['email']) < 5 || strpos($cuser['email'], '@') === false) {
		echo "<p>User '$username' doesn't have an email. Impossible to send password reset link.<br>\n";
		return -1;
	}
	$email = trim($cuser['email']);

	if (CheckFlags("M", $cuser['flags'])) {
		echo "<p>System Administrators know their passwords.<br>\n";
		return -2;
	}

	$key = sha1(shell_exec('head -n2 /dev/urandom'));
	$stamp = date('Y-m-d H:i:s');

	mysqli_query($handler, "INSERT INTO uo_chat_newpass (pass_uid, pass_key, pass_stamp)
		VALUES (".$cuser['uid'].", '".$key."', '".$stamp."')
		ON DUPLICATE KEY UPDATE pass_key='".$key."', pass_stamp='".$stamp."'
		");

	$username = ucwords($username);
	$urlstamp = urlencode($stamp);

	$headers = '';
	$headers .= "From: $master_name <$master_email>\n";
	$headers .= "Reply-To: $master_name <$master_email>\n";
	$headers .= "Bcc: $master_name <$master_email>\n";

	$subject = "pJJ: Password request for '$username' of /$chat";

	$message = <<<XBODY
On {$stamp} someone from IP {$_SERVER['REMOTE_ADDR']} requested a new password for user {$username} (uid:{$cuser['uid']}).

To generate a new password, click:
https://pjj.cc/common/password.php?uid={$cuser['uid']}&key={$key}&stamp={$urlstamp}

The link will remain valid for 15 minutes or until someone successfully logs in to the account.

-- pJJ Chats

XBODY;

	mail("$username <$email>", $subject, $message, $headers);

	$fc = fopen("../$chat/register/wizard_locked/actionlog.log", "ab");
	fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": New password requested for user '$username' - $ip\n"));
	fclose($fc);

	echo "<p>New password requested for user '$username', and emailed to $email.<br>\n";
}

	if (!empty($_REQUEST['uid']) && !empty($_REQUEST['key']) && !empty($_REQUEST['stamp'])) {
		NewPass($_REQUEST['uid'], $_REQUEST['key'], $_REQUEST['stamp']);
	}
	else if (empty($_REQUEST['chat'])) {
		echo "<p><b>You must do this from a chat.</b><br>";
	}
	else if (empty($_REQUEST['username'])) {
		echo "<form action=password.php method=post><table cellspacing=1 cellpadding=3 border=0 bgcolor=#000000>";
		echo "<tr bgcolor=#eeeeee><td colspan=2><b>Request New Password</b></td></tr>";
		echo "<tr bgcolor=#ffffff><td>Username</td><td><input type=text size=32 name=username></td></tr>";
		echo "<tr bgcolor=#ffffff align=right><td colspan=2><input type=submit value=\"Request New Password\"></td></tr>";
		echo "</table><input type=hidden name=chat value=\"{$_REQUEST['chat']}\"></form>";
	}
	else {
		RequestPass($_REQUEST['chat'], $_REQUEST['username']);
	}
	echo "<img src='../gfx/null.gif' border=0></center>\n";
?>
</td>
	<td valign="top" align="right" height="100%"><img src="../gfx/up_r.gif" border="0"></td></tr>
	</table>
</td></tr>
<tr><td background="../gfx/dn_tile.gif" align="center" valign="bottom" height="32"><center><a href="mailto:chats@projectjj.com"><img src="../gfx/worlds.gif" border="0"></a></center></td></tr>
</table>
</body>
</html>
