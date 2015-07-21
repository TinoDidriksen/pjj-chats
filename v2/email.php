<?php
    die("Disabled due to spammers using it.");
    ob_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (mail@tinodidriksen.com)">
	<meta name="GENERATOR" content="Tino Didriksen (mail@tinodidriksen.com)">
	<title>Project JJ Chats - Contacting /<? echo $chat; ?></title>

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
<tr><td background="gfx/up_tile.gif" valign="top" align="left" height="32">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="32">
	<tr><td valign="top" align="left" height="32"><a href="?"><img src="gfx/projectjj.gif" border="0"></a></td>
	<td valign="top" align="right" height="32"><a href="rq.php" target=_blank><img src="gfx/phpchat.gif" border="0"></a></td></tr>
	</table>
</td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%"><img src="gfx/up_l.gif" border="0"></td>
	<td valign="top" height="100%">
<center><img src="gfx/null.gif" border=0><br>
<?php
	include("setup.php");

	if ((file_exists($_REQUEST['chat']."/sendmsg.php")) && (file_exists($_REQUEST['chat']."/settings.php"))) {
		include($_REQUEST['chat']."/settings.php");
	}
	else {
		die("No such chat /{$_REQUEST['chat']}<br>\n");
	}

	if ($_REQUEST['sent'] == "") {
		echo "Use this form to contact <b>$ctitle</b>.<p>\n";
		echo "<form action=email.php method='POST'>";
		echo "<input type=hidden name=chat value={$_REQUEST['chat']}><input type=hidden name=sent value=1>";
		echo "<table cellspacing=0 cellpadding=3 border=0>";
		echo "<tr><td>Your name:</td><td><input type=text name=name size=16></td></tr>";
		echo "<tr><td>Your email:</td><td><input type=text name=email size=16></td></tr>";
		echo "<tr><td>Subject:</td><td><input type=text name=subject size=30></td></tr>";
		echo "<tr><td valign=top>Text:</td><td><textarea name=body cols=30 rows=15></textarea></td></tr>";
		echo "<tr><td colspan=2 align=right><input type=submit value=\"Send to /$chat\"></td></tr>";
		echo "</table></form>";
	}
	else {
		echo "Sending email to <b>$ctitle</b> and a copy to <b>{$_REQUEST['email']}</b>...";
		flush();

		$_REQUEST['subject'] = 'Chat Mail: '.$_REQUEST['subject'];
		$_REQUEST['body'] = "Target chat: http://pjj.cc/{$_REQUEST['chat']}/\nSender IP: {$_SERVER['REMOTE_ADDR']}\n\n".$_REQUEST['body'];

		$res = mail($cadmin, $_REQUEST['subject'], $_REQUEST['body'], "Return-Path: <{$_REQUEST['email']}>\r\nFrom: \"{$_REQUEST['name']}\" <{$_REQUEST['email']}>\r\nReply-To: \"{$_REQUEST['name']}\" <{$_REQUEST['email']}>\r\nBcc: \"{$_REQUEST['name']}\" <{$_REQUEST['email']}>, \"$master_name\" <$master_email>\r\nX-pJJ-IP: {$_SERVER['REMOTE_ADDR']}\r\nX-pJJ-Chat: http://pjj.cc/{$_REQUEST['chat']}/\r\n");
		if ($res == 1)
			echo "<b>success</b><p>\n";
		else
			echo "<b>failure</b><p>\n";

		echo "<a href=\"/?\">Return to portal...</a>";
	}

?>
</td>
	<td valign="top" align="right" height="100%"><img src="gfx/up_r.gif" border="0"></td></tr>
	</table>
</td></tr>
<tr><td background="gfx/dn_tile.gif" align="center" valign="bottom" height="32"><center><a href="rq.php" target=_blank><img src="gfx/worlds.gif" border="0"></a></center></td></tr>
</table>
</body>
</html>
