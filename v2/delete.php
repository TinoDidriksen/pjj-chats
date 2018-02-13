<?php
// This file is part of the Project JJ PHP Chat distribution.
// Created and maintained by Tino Didriksen <mail@tinodidriksen.com>
// The contents of this file is subject to a license.
// Read license.txt and readme.txt for more information.
?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (mail@tinodidriksen.com)">
	<meta name="GENERATOR" content="Tino Didriksen (mail@tinodidriksen.com)">
	<title>Chat Deletion Form</title>

<script type="text/javascript">
if (window != window.top)
  top.location.href = location.href;
</script>
</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="gfx/up_tile.gif" valign="top" align="left" height="32"> </td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%" width=80> </td>
	<td valign="top" height="100%">
<center><img src="gfx/null.gif" border=0></center>
<?php
	require_once('mysql.php');
	require_once('setup.php');
    require_once(__DIR__.'/common/pgsql/sql.php');

	function RmDirR($xdir) {
		$d = dir($xdir);
		while($entry = $d->read()) {
			if ($entry != "." && $entry != "..") {
				if (Is_Dir($xdir."/".$entry)) {
					RmDirR($xdir."/".$entry);
				} else {
					echo "Deleting $xdir/$entry<br>\n";
					UnLink($xdir."/".$entry);
				}
			}
		}
		$d->close();
		RmDir($xdir);
	}

	$cpath = strtolower(preg_replace("~([^-[:alnum:]_]+)~i", "", $_REQUEST['cpath']));
	if ($cpath == $master_chat) {
		echo "$master_chat is protected.<br>\n";
		exit();
	}
	else if ($cpath === 'sfw') {
	    die();
	}

/*
	$ok = false;
	if ((strtolower($_REQUEST['user']) != 'tino didriksen')
		|| ($_SERVER['REMOTE_ADDR'] == '217.157.173.123')
		|| ($_SERVER['REMOTE_ADDR'] == '127.0.0.1')
		|| ($_SERVER['REMOTE_ADDR'] == '72.51.46.112')
		|| ($_SERVER['REMOTE_ADDR'] == '72.51.46.46')
		) {
		$ok = true;
    }
    if ($ok == false) {
		die('You are not authorized.');
    }
//*/

	$email = strtolower($_REQUEST['email']);
	if ((!empty($_REQUEST['user'])) && (!empty($_REQUEST['pass'])) && (!empty($_REQUEST['email'])) && (!empty($_REQUEST['verify'])) && (!empty($cpath)) && (file_exists($cpath."/sendmsg.php")) && (file_exists($cpath."/settings.php"))) {
		$user = strtolower($_REQUEST['user']);
		$user = preg_replace('~'.$master_name_filter.'~i', "", $user);

		$epass = $_REQUEST['pass'];
		$pass = md5($_REQUEST['pass']);
		$result = mysqli_query($handler, "SELECT chat,username,password FROM uo_chat_database WHERE chat='chat$cpath' AND username='$user' AND password='$pass' AND ( flags='m' OR flags='M' )");
		$cuser = mysqli_fetch_row($result);
		@mysqli_free_result($result);

		if (($cuser[0] == "chat".$cpath) && (strtolower($cuser[1]) == $user) && ($cuser[2] == $pass)) {

            $GLOBALS['sql']->begin();
            $newname = mb_substr(sha1(uniqid(rand()).time()), 0, 12);
            $query = "UPDATE chatv2.chats SET chat='$newname',dtime=now() WHERE chat='$cpath'";
            if ($GLOBALS['sql']->query($query) === null) {
                $GLOBALS['sql']->rollback();
                die ("Boom in update.");
            }
            $GLOBALS['sql']->commit();

		// Deleting dirs and files
			if (!empty($cpath) && is_dir($cpath)) {
				//echo exec("rmdir /s /q ".$cpath).$cpath."<br>\n";
				echo "Copying to holding area:<br>\n".nl2br(shell_exec("cp -arv $cpath ../v2_backup/$newname"))."<br>\n";
				echo "Deleting from active area:<br>\n".nl2br(shell_exec("rm -rfv $cpath"))."<br>\n";
                //echo rename($cpath, '../v2_backup/'.$newname);
				//echo RmDirR($cpath)."<br>\n";
			}

			// Updating database entries
			$tables = array(
				"uo_chat",
				"uo_chat_stats",
				"uo_chat_ban",
				"uo_chat_boards",
				"uo_chat_database",
				"uo_chat_faction",
				"uo_chat_ignore",
				"uo_chat_last",
				"uo_chat_message",
				"uo_chat_regapps",
				"uo_chat_poll",
				"uo_chat_threads",
				"uo_chat_ulist",
				"uo_chat_vote"
				);

			$killme = "chat".$cpath;
			echo "Deleting $killme...<br>\n";
			for ($q=0;$q<count($tables);$q++) {
				$query = "UPDATE ".$tables[$q]." SET chat='$newname' WHERE chat='$killme'";
				echo "...".$query."<br>\n";
				$query = mysqli_query($handler, $query);
			}

			// Deleting database entries
			$tables = array(
				"uo_chat_log"
				);

			$killme = "chat".$cpath;
			echo "Deleting $killme...<br>\n";
			for ($q=0;$q<count($tables);$q++) {
				$query = "DELETE FROM ".$tables[$q]." WHERE chat='$killme'";
				echo "...".$query."<br>\n";
				$query = mysqli_query($handler, $query);
			}

			$subject = "Project JJ: Chat Deleted: $cpath";
			$body =  "Time: ".date("g:ia, F d (T)", time(0))."\nUnix time: ".time(0)."\n";
			$body .= "IP: {$_SERVER['REMOTE_ADDR']}\n";
			$body .= "Username: $user\n";
			$body .= "Password: $pass\n";
			$body .= "Chat: $cpath\n";
			$body .= "Email: $email\n\n";
			$body .= "Deletion successful. Backup kept as $newname\n";
			$addrs = "$master_email,$email";
			mail($addrs, $subject, $body, "From: $master_email\n");
		}
		else {
			echo "False login or path.<br>\n";
			$subject = "Project JJ: Deletion Attempt: $cpath";
			$body =  "Time: ".date("g:ia, F d (T)", time(0))."\nUnix time: ".time(0)."\n";
			$body .= "IP: {$_SERVER['REMOTE_ADDR']}\n";
			$body .= "Username: $user\n";
			$body .= "Password: $pass\n";
			$body .= "Chat: $cpath\n";
			$body .= "Email: $email\n\n";
			$body .= "Deletion not successful.\n";
			$addrs = "$master_email,$email";
			mail($addrs, $subject, $body, "From: $master_email\n");
		}
	}
	else {
?>
This form will in every respect delete a chat.<br>
Only the <i>Chat Master</i> can do this.<br>
Once submitted, the chat will be deleted. <b>This is not reversible.</b><p>

<form action="delete.php" method="post">
<table cellspacing=0 cellpadding=3 border=0>
<tr><td><b>Username</b></td>
<td><input name=user type=text size=12></td></tr>
<tr><td><b>Password</b></td>
<td><input name=pass type=text size=12></td></tr>
<tr><td><b>Chat path (acronym)</b></td>
<td><input name=cpath type=text size=4></td></tr>
<tr><td><b>Email</b></td>
<td><input name=email type=text></td></tr>
<tr><td><b>Verification</b></td>
<td><input name=verify type=checkbox> (check to verify)</td></tr>
<tr><td></td>
<td><input type=submit value="Delete Chat"></td></tr>
</table>
</form>
<?php
	}

?>
<center><img src="gfx/null.gif" border=0></center>
</td>
	<td valign="top" align="right" height="100%" width=80> </td></tr>
	</table>
</td></tr>
<tr><td background="gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>
</body>
</html>
