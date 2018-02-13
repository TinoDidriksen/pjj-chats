<?php
    ob_start();
    ignore_user_abort(true);

	require_once("../common/session.php");
	require_once("../mysql.php");
	require_once("../setup.php");
	require_once("../common/helpers.php");
	require_once("settings.php");
	require_once("options.php");

	$realpath = preg_replace('~.*/([^/]+)/gui_icon.php$~', 'chat\1', $_SERVER['PHP_SELF']);
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

function TrimArray($arr) {
	foreach($arr as $key => $value) {
		if (!is_array($value)) {
			$value = str_replace('"', "'", $value);
			$arr[$key] = trim(str_replace('$', '', $value));
		} else {
			$arr[$key] = TrimArray($arr[$key]);
		}
	}

	return $arr;
}

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Icon Editor</title>

<script type="text/javascript">
if (window != window.top) {
	top.location.href = location.href;
}
</script>
<script src="/common/js/jquery.js" type="text/javascript"></script>
<script src="/common/js/jquery-ui.js" type="text/javascript"></script>
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
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
<?php

	$_REQUEST = TrimArray($_REQUEST);
	extract($_REQUEST);

	if (!empty($altdata))
		$chatpath = $altdata;
	else {
		$chatpath = preg_replace("~.*/([^/]+)/gui_icon.php$~", "chat\\1", $_SERVER['PHP_SELF']);
		if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc'))
			$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
	}

	$fn = "iconlist.php";

	$flags = ChatVerifyLogin($_REQUEST['login'], $_REQUEST['password'], $chatpath);
	$bing = GetChatPrefs($chatpath);
	if ((strlen($message) > 5) && CheckFlags("iXZmM", $flags)) {
		@count_mysql_query("DELETE FROM uo_chat_adminlog WHERE stamp<DATE_SUB(now(), INTERVAL 28 DAY)", $handler);
		@count_mysql_query("INSERT INTO uo_chat_adminlog
			(page_id,chat_id,user_id,user_ip,stamp)
			VALUES (
			3,
			{$GLOBALS['biglog']['chat_id']},
			{$GLOBALS['biglog']['user_id']},
			'{$_SERVER['REMOTE_ADDR']}',
			now()
			)", $handler);

		$fi = fopen($fn, "w");
		fwrite($fi, "<?php\n");

		fwrite($fi, "\$icons = array (\n");
		for ($cc=0;$cc<count($_REQUEST['iname']);$cc++) {
			$_REQUEST['iname'][$cc] = trim($_REQUEST['iname'][$cc]);
			if (!empty($_REQUEST['iname'][$cc])) {
				fwrite($fi, "\"".trim($_REQUEST['iname'][$cc])."\" => \"".trim($_REQUEST['ifile'][$cc])."\",\n");
			}
		}
		fwrite($fi, ");\n");

		fwrite($fi, "\$picons = array (\n");
		for ($cc=0;$cc<count($_REQUEST['piname']);$cc++) {
			$_REQUEST['piname'][$cc] = trim($_REQUEST['piname'][$cc]);
			if (!empty($_REQUEST['piname'][$cc])) {
				fwrite($fi, "\"".trim($_REQUEST['piname'][$cc])."\" => \"".trim($_REQUEST['pifile'][$cc])."\",\n");
			}
		}
		fwrite($fi, ");\n");

		fclose($fi);
		@chmod($fn, 0666);
	}

	require_once("iconlist.php");
	$nicon = count($icons);
	reset($icons);
	if (!$picons) {
		$picons = array();
	}

	echo "Link 1: <a href=https://pjj.cc/common/icon/ target=_blank>Project JJ Common Icons</a> for usage by anyone.<br>";
	echo "Link 2: <a href=http://image.projectjj.com/ target=_blank>Project JJ Image Service</a> for uploading icons if you don't have another host.<p>";
	echo "<form action='gui_icon.php' method='post' target='_top'>\n<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000 id='reg_icons'>\n";
	echo "<thead>";
	echo "<tr bgcolor=#ffffff><td colspan=4><b>Registered User Icons</b> (drag'n'drop rows to rearrange)</td></tr>";
	echo "<tr bgcolor=#eeeeee><td><b>#</b></td><td><b>Name</b></td><td><b>URL</b></td><td><b>Img</b></td></tr>";
	echo "</thead>";
	echo "<tbody>";
	$cc=0;
	while (list($name, $file) = each($icons)) {
		echo "<tr bgcolor=#ffffff><td class='icon_num'>$cc</td><td><input name=\"iname[$cc]\" value=\"$name\" type=text size=30 class='icon_name'></td><td><input name=\"ifile[$cc]\" value=\"$file\" type=text size=30 class='icon_file'></td><td bgcolor=\"$s_bgcol\" align=center>";
		if (strstr($file, "tp://")) {
			echo "<img border=0 src=\"$file\">";
		}
		echo "</td></tr>";
		$cc++;
	}
	for ($aa=$cc;$aa<$cc+5;$aa++) {
		echo "<tr bgcolor=#ffffff><td class='icon_num'>$aa</td><td><input name=\"iname[$aa]\" value=\"\" type=text size=30 class='icon_name'></td><td><input name=\"ifile[$aa]\" value=\"\" type=text size=30 class='icon_file'></td><td></td></tr>";
	}

	echo "</tbody>";
	echo "</table><p><table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000 id='pub_icons'>\n";
	echo "<thead>";
	echo "<tr bgcolor=#ffffff><td colspan=4><b>Public User Icons</b> (drag'n'drop rows to rearrange)</td></tr>";
	echo "<tr bgcolor=#eeeeee><td><b>#</b></td><td><b>Name</b></td><td><b>URL</b></td><td><b>Img</b></td></tr>";
	echo "</thead>";
	echo "<tbody>";
	$cc=0;
	while (list($name, $file) = each($picons)) {
		echo "<tr bgcolor=#ffffff><td class='icon_num'>$cc</td><td><input name=\"piname[$cc]\" value=\"$name\" type=text size=30 class='icon_name'></td><td><input name=\"pifile[$cc]\" value=\"$file\" type=text size=30 class='icon_file'></td><td bgcolor=\"$s_bgcol\" align=center>";
		if (strstr($file, "tp://")) {
			echo "<img border=0 src=\"$file\">";
		}
		echo "</td></tr>";
		$cc++;
	}
	for ($aa=$cc;$aa<$cc+5;$aa++) {
		echo "<tr bgcolor=#ffffff><td class='icon_num'>$aa</td><td><input name=\"piname[$aa]\" value=\"\" type=text size=30 class='icon_name'></td><td><input name=\"pifile[$aa]\" value=\"\" type=text size=30 class='icon_file'></td><td></td></tr>";
	}
	echo "</tbody>";
	echo "</table>\n<p>\n<input type='submit' value='Save'> Login: <input type=text name=login value=\"{$_REQUEST['login']}\"> Password: <input type=password name=password value='{$_REQUEST['password']}'>\n";
	echo "<input type=hidden value=\"Submitted icons.\" name=message>\n";
	echo "</form>\n";

?>
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
</td>
	<td valign="top" align="right" height="100%" width=80> </td></tr>
	</table>
</td></tr>
<tr><td background="https://pjj.cc/gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>

<script type="text/javascript">
$('#reg_icons tbody').sortable({
	update: function(event, ui) {
		var i = 0;
		$('#reg_icons tbody tr').each(function(index, elem) {
			$(elem).find('.icon_num').text(i);
			$(elem).find('.icon_name').attr('name', 'iname['+i+']');
			$(elem).find('.icon_file').attr('name', 'ifile['+i+']');
			++i;
		});
	}
});
$('#pub_icons tbody').sortable({
	update: function(event, ui) {
		var i = 0;
		$('#pub_icons tbody tr').each(function(index, elem) {
			$(elem).find('.icon_num').text(i);
			$(elem).find('.icon_name').attr('name', 'piname['+i+']');
			$(elem).find('.icon_file').attr('name', 'pifile['+i+']');
			++i;
		});
	}
});
</script>
</body>
</html>
