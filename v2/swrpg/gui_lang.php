<?php
    ob_start();
    ignore_user_abort(true);

	require_once '../common/session.php';
	require_once '../mysql.php';
	require_once '../setup.php';
	require_once '../common/helpers.php';
	require_once 'settings.php';
	require_once '../common/language.php';
	require '../common/fixup.php';

	$realpath = preg_replace('~.*/([^/]+)/gui_lang.php$~', 'chat\1', $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
		$realpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
	}

	if (!CheckFlags('1', $_SESSION[$realpath]['flags'])) {
		die('Only registered members of the chat may view this page, and you have to be logged in to the chat itself.');
	}

	if (empty($_REQUEST['password']) && !empty($_SESSION[$realpath]['user']['password'])) {
		$_REQUEST['password'] = $_SESSION[$realpath]['user']['password'];
	}
	else if (!empty($_REQUEST['password']) && strlen($_REQUEST['password']) != 32 && strcmp($_SESSION[$realpath]['user']['password'], md5($_REQUEST['password'])) == 0) {
		$_REQUEST['password'] = $_SESSION[$realpath]['user']['password'];
	}
	if (empty($_REQUEST['login'])) {
		if (!empty($_SESSION[$realpath]['user']['displayname'])) {
			$_REQUEST['login'] = $_SESSION[$realpath]['user']['displayname'];
		}
		else if (!empty($_SESSION[$realpath]['user']['login'])) {
			$_REQUEST['login'] = $_SESSION[$realpath]['user']['username'];
		}
	}

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Language Editor</title>

<script type="text/javascript">
if (window != window.top) {
	top.location.href = location.href;
}
</script>
<style type="text/css">
body { font-family: verdana, arial, sans-serif; }
</style>
</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="https://pjj.cc/gfx/up_tile.gif" valign="top" align="left" height="32"> </td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%" width=80> </td>
	<td valign="top" height="100%">
<center><img src="https://pjj.cc/gfx/null.gif" border="0"></center>
<?php

	if (!empty($altdata)) {
		$chatpath = $altdata;
	}
	else {
		$chatpath = preg_replace("~.*/([^/]+)/gui_lang.php$~", "chat\\1", $_SERVER['PHP_SELF']);
		if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
			$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
		}
	}

	$fn = 'language.php';

	$flags = ChatVerifyLogin($_REQUEST['login'], $_REQUEST['password'], $chatpath);
	$bing = GetChatPrefs($chatpath);
	if (!empty($_REQUEST['message']) && CheckFlags('XZmM', $flags)) {
		@count_mysql_query("DELETE FROM uo_chat_adminlog WHERE stamp<DATE_SUB(now(), INTERVAL 28 DAY)", $handler);
		@count_mysql_query("INSERT INTO uo_chat_adminlog
			(page_id,chat_id,user_id,user_ip,stamp)
			VALUES (
			4,
			{$GLOBALS['biglog']['chat_id']},
			{$GLOBALS['biglog']['user_id']},
			'{$_SERVER['REMOTE_ADDR']}',
			now()
			)", $handler);

		$fi = fopen($fn, 'wb');
		fwrite($fi, "<?php\n");

		foreach ($_REQUEST['language'] as $k => $v) {
			$v = trim(strval($v));
			if (!empty($GLOBALS['language'][$k]) && $GLOBALS['language'][$k] === $v) {
				continue;
			}
			fwrite($fi, "\$GLOBALS['language'][".$k."] = ".var_export($v, true).";\n");
		}
		foreach ($_REQUEST['banguage'] as $k => $v) {
			$v = trim(strval($v));
			if (!empty($GLOBALS['banguage'][$k]) && $GLOBALS['banguage'][$k] === $v) {
				continue;
			}
			fwrite($fi, "\$GLOBALS['banguage'][".$k."] = ".var_export($v, true).";\n");
		}
		foreach ($_REQUEST['xxanguage'] as $k => $v) {
			$v = trim(strval($v));
			if (!empty($GLOBALS['xxanguage'][$k]) && $GLOBALS['xxanguage'][$k] === $v) {
				continue;
			}
			fwrite($fi, "\$GLOBALS['xxanguage'][".$k."] = ".var_export($v, true).";\n");
		}

		sort($GLOBALS['m8ball']);
		foreach ($_REQUEST['m8ball'] as $k => $v) {
			$v = trim(strval($v));
			if (empty($v)) {
				unset($_REQUEST['m8ball'][$k]);
				continue;
			}
			$_REQUEST['m8ball'][$k] = $v;
		}
		sort($_REQUEST['m8ball']);
		if (!empty($_REQUEST['m8ball']) && $GLOBALS['m8ball'] != $_REQUEST['m8ball']) {
			fwrite($fi, "\$GLOBALS['m8ball'] = array();\n");
			foreach ($_REQUEST['m8ball'] as $k => $v) {
				fwrite($fi, "\$GLOBALS['m8ball'][".$k."] = ".var_export($v, true).";\n");
			}
		}

		fclose($fi);
		@chmod($fn, 0600);
	}

	if (file_exists('language.php')) {
		require_once 'language.php';
	}

	require '../common/fixup.php';

	echo "<form action='gui_lang.php' method='post' target='_top'>\n";

	echo '<h2>Language Editor</h2>';
	echo 'On the inline messages, the following fields are always valid: {TIMESTAMP}, {USERNAME}, {IDENT}, {COLOR}, {LINK}, {IMAGE}<br><br>';
	echo '<table border="0" cellspacing="0" cellpadding="2" width="100%">';
	echo '<tr><th>Purpose</th><th>Message</th><th>Valid Fields</th></tr>';

	echo '<tr><td>Blocked view</td><td><textarea name="language[0]" cols="60" rows="3">', htmlentities(stripslashes($language[0])), '</textarea></td><td></td></tr>';
	echo '<tr><td>Join</td><td><textarea name="language[1]" cols="60" rows="3">', htmlentities(stripslashes($language[1])), '</textarea></td><td>Inline</td></tr>';
	echo '<tr><td>Exit</td><td><textarea name="language[2]" cols="60" rows="3">', htmlentities(stripslashes($language[2])), '</textarea></td><td>Inline, {MESSAGE}</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td>Ban</td><td><textarea name="language[3]" cols="60" rows="3">', htmlentities(stripslashes($language[3])), '</textarea></td><td>Inline, {BANIDENT}, {BANDURATION}</td></tr>';
	echo '<tr><td>Unban</td><td><textarea name="language[6]" cols="60" rows="3">', htmlentities(stripslashes($language[6])), '</textarea></td><td>Inline, {BANIDENT}</td></tr>';
	echo '<tr><td>Banned msg</td><td><textarea name="language[12]" cols="60" rows="3">', htmlentities(stripslashes($language[12])), '</textarea></td><td></td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td>Gag</td><td><textarea name="language[15]" cols="60" rows="3">', htmlentities(stripslashes($language[15])), '</textarea></td><td>Inline, {GAGIDENT}, {GAGDURATION}</td></tr>';
	echo '<tr><td>Ungag</td><td><textarea name="language[16]" cols="60" rows="3">', htmlentities(stripslashes($language[16])), '</textarea></td><td>Inline, {GAGIDENT}</td></tr>';
	echo '<tr><td>Gagged msg</td><td><textarea name="language[17]" cols="60" rows="3">', htmlentities(stripslashes($language[17])), '</textarea></td><td></td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td>Ignore</td><td><textarea name="language[4]" cols="60" rows="3">', htmlentities(stripslashes($language[4])), '</textarea></td><td>Inline, {IGNOREIDENT}, {IGNOREDURATION}</td></tr>';
	echo '<tr><td>Unignore</td><td><textarea name="language[5]" cols="60" rows="3">', htmlentities(stripslashes($language[5])), '</textarea></td><td>Inline, {IGNOREIDENT}</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td>Nick</td><td><textarea name="language[7]" cols="60" rows="3">', htmlentities(stripslashes($language[7])), '</textarea></td><td>Inline, {NEWNAME}</td></tr>';
	echo '<tr><td>Whois</td><td><textarea name="language[9]" cols="60" rows="3">', htmlentities(stripslashes($language[9])), '</textarea></td><td>Inline, {WHOISNAME}, {WHOISDATE}</td></tr>';
	echo '<tr><td>Whois fail</td><td><textarea name="language[8]" cols="60" rows="3">', htmlentities(stripslashes($language[8])), '</textarea></td><td>Inline, {WHOISNAME}</td></tr>';
	echo '<tr><td>Clear</td><td><textarea name="language[10]" cols="60" rows="3">', htmlentities(stripslashes($language[10])), '</textarea></td><td>Inline</td></tr>';
	echo '<tr><td>You are..</td><td><textarea name="language[11]" cols="60" rows="3">', htmlentities(stripslashes($language[11])), '</textarea></td><td>Inline</td></tr>';
	echo '<tr><td>Action</td><td><textarea name="language[13]" cols="60" rows="3">', htmlentities(stripslashes($language[13])), '</textarea></td><td>Inline, {ICON}, {MESSAGE}</td></tr>';
	echo '<tr><td>Last Played</td><td><textarea name="language[14]" cols="60" rows="3">', htmlentities(stripslashes($language[14])), '</textarea></td><td>Inline, {LASTPLAYED}</td></tr>';
	echo '<tr><td colspan="3">&nbsp;</td></tr>';
	echo '<tr><td>Post (with icon)</td><td><textarea name="xxanguage[0]" cols="60" rows="3">', htmlentities(stripslashes($xxanguage[0])), '</textarea></td><td>Inline, {SYMBOL}, {CHATPATH}, {USERNAMEURL}, {ICON}, {MESSAGE}</td></tr>';
	echo '<tr><td>Post (no icon)</td><td><textarea name="banguage[1]" cols="60" rows="3">', htmlentities(stripslashes($banguage[1])), '</textarea></td><td>Inline, {SYMBOL}, {MESSAGE}</td></tr>';
	echo '<tr><td>Message</td><td><textarea name="banguage[2]" cols="60" rows="3">', htmlentities(stripslashes($banguage[2])), '</textarea></td><td>Inline, {RECIPIENT}</td></tr>';
	echo '<tr><td>Message (fail)</td><td><textarea name="banguage[3]" cols="60" rows="3">', htmlentities(stripslashes($banguage[3])), '</textarea></td><td>Inline, {RECIPIENT}</td></tr>';
	echo '<tr><td>Permban</td><td><textarea name="banguage[4]" cols="60" rows="3">', htmlentities(stripslashes($banguage[4])), '</textarea></td><td>{IDENT}</td></tr>';
	echo '<tr><td>Unregistered</td><td><textarea name="banguage[5]" cols="60" rows="3">', htmlentities(stripslashes($banguage[5])), '</textarea></td><td></td></tr>';
	echo '<tr><td>Dice</td><td><textarea name="banguage[6]" cols="60" rows="3">', htmlentities(stripslashes($banguage[6])), '</textarea></td><td>Inline, {DICERESULT}, {DICETYPE}</td></tr>';
	echo '<tr><td>Magic 8-Ball</td><td><textarea name="language[18]" cols="60" rows="3">', htmlentities(stripslashes($language[18])), '</textarea></td><td>Inline, {8BALLRESULT}</td></tr>';
	echo '</table>';

	echo '<br/>';
	echo '<br/>';

	echo '<table border="0" cellspacing="0" cellpadding="2">';
	echo '<tr><th>Possible Magic 8-Ball Messages</th></tr>';
	foreach ($GLOBALS['m8ball'] as $v) {
		echo '<tr><td><input type="text" name="m8ball[]" size="60" value="', htmlentities(stripslashes($v)), '" /></td></tr>';
	}
	for ($i=0 ; $i<5 ; $i++) {
		echo '<tr><td><input type="text" name="m8ball[]" size="60" value="" /></td></tr>';
	}
	echo '</table>';

	echo "<p>\n<input type='submit' value='Save'> Login: <input type=text name=login value=\"{$_REQUEST['login']}\"> Password: <input type=password name=password value='{$_REQUEST['password']}'>\n";
	echo "<input type=hidden value=\"Submitted language.\" name=message>\n";
	echo "</form><p>\n";

?>
<center><img src="https://pjj.cc/gfx/null.gif" border="0"></center>
</td>
	<td valign="top" align="right" height="100%" width="80"> </td></tr>
	</table>
</td></tr>
<tr><td background="https://pjj.cc/gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>
</body>
</html>
