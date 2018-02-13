<?php
	//ob_start("ob_gzhandler");
	ob_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Complete Userlist</title>

<script type="text/javascript">
if (window != window.top) {
	top.location.href = location.href;
}
</script>

</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="https://pjj.cc/gfx/up_tile.gif" valign="top" align="left" height="32"> </td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%" width=80> </td>
	<td valign="top" height="100%">
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
<?php
	require_once "../../common/session.php";
	require_once "../../mysql.php";
	require_once "../../setup.php";
	require_once "../../common/tome_of_power.php";

	$realpath = preg_replace('~.*/([^/]+)/register/biglist.php$~', 'chat\1', $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
		$realpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
	}

	if (!CheckFlags('1', $_SESSION[$realpath]['flags'])) {
		die('Only registered members of the chat may view this page, and you have to be logged in to the chat itself.');
	}

	ListUsers($_REQUEST['faction'], $_REQUEST['sort']);
?>
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
</td>
	<td valign="top" align="right" height="100%" width=80> </td></tr>
	</table>
</td></tr>
<tr><td background="https://pjj.cc/gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>
</body>
</html>
