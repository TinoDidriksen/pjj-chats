<?php
//ob_start("ob_gzhandler");
ob_start();
?>
<html>
<head>
    <META NAME="ROBOTS" CONTENT="NOARCHIVE">
	<META NAME="ROBOTS" CONTENT="NOINDEX">
	<META NAME="ROBOTS" CONTENT="NOFOLLOW">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Logs</title>

<script type="text/javascript">
if (window != window.top)
  top.location.href = location.href;
</script>

</head>

<body text="#FFFFFF" bgcolor="#000000" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<center><img src="http://pjj.cc/gfx/null.gif" border=0 height=16></center>
<b>These logs are no longer being updated. Use <a href="dblog.php">DB Logs</a> to see recent lines.</b><p>
<?php

function FindUser($userident, $chatpath) {
	global $handler;

	$result = @mysql_query("SELECT username FROM uo_chat_ulist WHERE chat='$chatpath' AND ident='$userident'", $handler);
	$usel = mysql_fetch_row($result);
	@mysql_free_result($result);

	if (!empty($usel[0])) {
		return 1;
	}
	return 0;
}

include("../../common/session.php");
include("../../mysql.php");
include("../../setup.php");
include("../settings.php");
include("../options.php");
include("../../common/tome_of_power.php");
include("../../common/zlib.php");

$pass = 1;
$realpath = ereg_replace(".*/([^/]+)/register/biglog.php$", "chat\\1", $_SERVER['PHP_SELF']);
if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
	$realpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
}

$pass = 1;
if ($logblock == 1 && !CheckFlags('1', $_SESSION[$realpath]['flags'])) {
	$pass = 0;
}
if ($logblock == 2 && !CheckFlags('MmZxX', $_SESSION[$realpath]['flags'])) {
	$pass = 0;
}
if ($logblock == 3 && !CheckFlags('MmZX', $_SESSION[$realpath]['flags'])) {
	$pass = 0;
}

if ($pass == 0 && $logblock == 3) {
	echo "<b>Logs are currently locked to anyone below administrator status.</b>";
}
else if ($pass == 0 && $logblock == 2) {
	echo "<b>Logs are currently locked to anyone below moderators status.</b>";
}
else if ($pass == 0) {
	echo "<b>Logs are currently blocked for anyone not logged into the chat.</b>";
}
else {
	if (($_REQUEST['action'] == "view") && ($_REQUEST['time'] != "")) {
		DisplayLog($_REQUEST['time']);
	}
	else {
		ListLogs();
	}
}

?>
<center><img src="http://pjj.cc/gfx/null.gif" border=0></center>
</body>
</html>
