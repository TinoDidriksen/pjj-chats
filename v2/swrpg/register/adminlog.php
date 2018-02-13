<?php
	ob_start();

	/*
	if (!preg_match('/^130\.22(5|6)/', $_SERVER['REMOTE_ADDR'])) {
		die('Being worked on...');
	}
	//*/
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (chats@projectjj.com)">
	<meta name="GENERATOR" content="Tino Didriksen (chats@projectjj.com)">
	<title>Admin Logs</title>
</head>
<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>

<?php
	require_once("../../common/session.php");
	require_once("../../../chatv3/_inc/mmcache.php");
	require_once("../../mysql.php");
	require_once("../../setup.php");
	require_once("../settings.php");
	require_once("../options.php");
	require_once("../../common/tome_of_power.php");

	$realpath = ereg_replace(".*/([^/]+)/register/adminlog.php$", "chat\\1", $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc'))
		$realpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);

	if (!CheckFlags('MmZX', $_SESSION[$realpath]['flags'])) {
		die('Only administrators can view this page.');
	}

	$cpref = GetChatPrefs($realpath);

	$output = '';
	if ($_REQUEST['log'] === 'cpanel') {
		$output = '<pre>'.file_get_contents('wizard_locked/actionlog.log').'</pre>';
	}
	else {
		$rez = count_mysql_query(
			"SELECT
			alog.user_id, dtbs.username, alog.page_id, alog.stamp, alog.user_ip
			FROM uo_chat_adminlog as alog
			LEFT JOIN uo_chat_database as dtbs ON (alog.user_id=dtbs.uid)
			WHERE alog.chat_id={$GLOBALS['biglog']['chat_id']}
			ORDER BY alog.entry_id ASC
			", $handler);
		if (mysqli_num_rows($rez)) {
			$pages = array(
				1 => 'Settings',
				2 => 'Options',
				3 => 'Icons',
				4 => 'Language'
				);
			$output .= '<table cellspacing="0" cellpadding="3" border="1">';
			$output .= '<tr valign="top">
				<td><b>Page</b></td>
				<td><b>ID: Username</b></td>
				<td><b>IP</b></td>
				<td><b>Timestamp</b></td>
				</tr>';
			while($row = mysqli_fetch_assoc($rez)) {
				$row['page_id'] = $pages[$row['page_id']];
				$output .= <<<HTMLEND
				<tr valign="top">
				<td>{$row['page_id']}</td>
				<td>{$row['user_id']}: {$row['username']}</td>
				<td>{$row['user_ip']}</td>
				<td>{$row['stamp']}</td>
				</tr>
HTMLEND;
			}
			$output .= "</table>";
		}
		else {
			$output .= "<b>Admin log is empty...</b>";
		}
		mysqli_free_result($rez);
	}
?>

<?=$output;?>

<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
</body>
</html>
