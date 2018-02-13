<?php
    ob_start();
    ignore_user_abort(true);

	require_once("../common/session.php");
	require_once("../mysql.php");
	require_once("../setup.php");
	require_once("../common/helpers.php");
	require_once("../common/image.php");
	require_once("settings.php");

	$realpath = preg_replace('~.*/([^/]+)/gui_opt.php$~', 'chat\1', $_SERVER['PHP_SELF']);
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
	<title>Option Editor</title>

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

	if (!isset($urlblock)) {
		$urlblock = $logblock;
	}

	if (!empty($altdata)) {
		$chatpath = $altdata;
	}
	else {
		$chatpath = preg_replace("~.*/([^/]+)/gui_opt.php$~", "chat\\1", $_SERVER['PHP_SELF']);
		if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
			$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
		}
	}

	$fn = "options.php";

	$flags = ChatVerifyLogin($_REQUEST['login'], $_REQUEST['password'], $chatpath);
	$bing = GetChatPrefs($chatpath);
	if (!empty($message) && CheckFlags("oXZmM", $flags)) {
		@count_mysql_query("DELETE FROM uo_chat_adminlog WHERE stamp<DATE_SUB(now(), INTERVAL 28 DAY)", $handler);
		@count_mysql_query("INSERT INTO uo_chat_adminlog
			(page_id,chat_id,user_id,user_ip,stamp)
			VALUES (
			2,
			{$GLOBALS['biglog']['chat_id']},
			{$GLOBALS['biglog']['user_id']},
			'{$_SERVER['REMOTE_ADDR']}',
			now()
			)", $handler);

		$fi = fopen($fn, "w");
		if (!$fi) {
			die("An error occured trying to write to the file.");
		}
		fwrite($fi, "<?php\n\$banwords = array (\n");
		for ($cc=0;$cc<count($_REQUEST['iname']);$cc++) {
			if (trim($_REQUEST['iname'][$cc]) != "")
				fwrite($fi, var_export(stripslashes(trim($_REQUEST['iname'][$cc])), true)." => ".var_export(stripslashes(trim($_REQUEST['ifile'][$cc])), true).",\n");
		}
		fwrite($fi, ");\n\$images = array(\n");

		for ($cc=0;$cc<count($_REQUEST['images']);$cc++) {
			fwrite($fi, "\"".trim($_REQUEST['images'][$cc])."\",\n");
		}
		fwrite($fi, ");\n\$jbbc = array(\n");

		for ($cc=0;$cc<count($_REQUEST['jbbc']);$cc++) {
			fwrite($fi, "\"".trim($_REQUEST['jbbc'][$cc])."\",\n");
		}
		fwrite($fi, ");\n\$banip = array(\n");

		for ($cc=0;$cc<count($_REQUEST['banips']);$cc++) {
			if (trim($_REQUEST['banips'][$cc]) != "")
				fwrite($fi, "\"".trim($_REQUEST['banips'][$cc])."\",\n");
		}
		fwrite($fi, ");\n\$reglink = \"{$_REQUEST['reglink']}\";\n");

		if ($_REQUEST['cpicture'] && !getimagesize($_REQUEST['cpicture'])) {
			$_REQUEST['cpicture'] = '';
			echo "<font color='#FF0000'>Portal Picture not a valid picture!</font><br>";
		}

		$pimgx = 200;
		$pimgy = 100;
		$newh = ChatImageSize($_REQUEST['cpicture']);
		fwrite($fi, "\$cpicture = \"".str_replace("\"","'", strip_tags($_REQUEST['cpicture']))."\";\n");
		fwrite($fi, "\$cpicturelink = \"".str_replace("\"","'", strip_tags($_REQUEST['cpicturelink']))."\";\n");
		fwrite($fi, "\$cpicturesize = \"".str_replace("\"","'", $newh)."\";\n");

		$cdescript = strip_tags($_REQUEST['cdescript'], "<font><a><b><u><i><tt>");
		$find = array(
			"/\"/s",
			'/ style=/is',
			'/ on[^=\s]+=/is'
			);
		$repl = array(
			"'",
			' bad=',
			' bad='
			);
		$cdescript = preg_replace($find, $repl, $cdescript);
		$cdescript = mb_substr($cdescript, 0, 1024);
		fwrite($fi, "\$cdescript = \"{$cdescript}\";\n");

		fwrite($fi, "\$altdata = \"{$_REQUEST['altdata']}\";\n");
		fwrite($fi, "\$memonly = {$_REQUEST['memonly']};\n");
		fwrite($fi, "\$numicons = {$_REQUEST['numicons']};\n");
		fwrite($fi, "\$crating = {$_REQUEST['crating']};\n");
		fwrite($fi, "\$xml_anon = {$_REQUEST['xml_anon']};\n");
		fwrite($fi, "\$sportal = {$_REQUEST['sportal']};\n");
		fwrite($fi, "\$proxyblock = {$_REQUEST['proxyblock']};\n");
		fwrite($fi, "\$jbblock = {$_REQUEST['jbblock']};\n");
		fwrite($fi, "\$logblock = {$_REQUEST['logblock']};\n");
		fwrite($fi, "\$urlblock = {$_REQUEST['urlblock']};\n");
		fclose($fi);
		@chmod($fn, 0600);
	}

	require_once("options.php");

	$fiximages = array(
		"../gfx/buttons/clear.gif",
		"../gfx/buttons/cpjj.gif",
		"../gfx/buttons/enter.gif",
		"../gfx/buttons/exit.gif",
		"../gfx/buttons/manual.gif",
		"../gfx/buttons/members.gif",
		"../gfx/buttons/messages.gif",
		"../gfx/buttons/music.gif",
		"../gfx/buttons/post.gif",
		"../gfx/buttons/profiles.gif",
		"../gfx/buttons/refresh.gif",
		"../gfx/buttons/register.gif",
		"../gfx/buttons/undo.gif",
		"../gfx/buttons/userlist.gif",
		"../gfx/buttons/null.gif"
	);
	foreach ($images as $k => $v) {
		if (empty($images[$k])) {
			$images[$k] = $fiximages[$k];
		}
	}

	$nicon = count($banwords);
	reset($banwords);
	echo "<form action='gui_opt.php' method='post' target='_top'>\n";
	echo "<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000 id='filters'>\n";
	echo "<thead>";
	echo "<tr bgcolor=#ffffff><td colspan=4><b>Automatic Moderation</b> (drag'n'drop rows to rearrange)</td></tr>";
	echo "<tr bgcolor=#eeeeee><td><b>#</b></td><td><b>Filtered Word</b></td><td><b>Replacement</b></td><td><b>Example</b></td></tr>";
	echo "</thead>";
	echo "<tbody>";
	$cc=0;
	while (list($name, $file) = each($banwords)) {
		$old = $file;
		$name = htmlentities($name);
		$file = htmlentities($file);
		echo "<tr bgcolor=#ffffff><td class='filter_num'>$cc</td><td><input name=iname[$cc] value=\"$name\" type=text size=30 class='filter_find'></td><td><input name=ifile[$cc] value=\"$file\" type=text size=30 class='filter_repl'></td><td align=center>$old</td></tr>";
		$cc++;
	}
	for ($aa=$cc;$aa<$cc+10;$aa++) {
		echo "<tr bgcolor=#ffffff><td class='filter_num'>$aa</td><td><input name=iname[$aa] value=\"\" type=text size=30 class='filter_find'></td><td><input name=ifile[$aa] value=\"\" type=text size=30 class='filter_repl'></td><td></td></tr>";
	}

	echo "</tbody>";
	echo "</table>\n<p>\n";

	echo "<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td>";
	echo "<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>\n";
	echo "<tr bgcolor=#ffffff><td colspan=2><b>Permanent Ban</b></td></tr>";
	echo "<tr bgcolor=#eeeeee><td><b>#</b></td><td><b>Banned Ident</b></td></tr>";
	for ($cc=0;$cc<count($banip);$cc++) {
		echo "<tr bgcolor=#ffffff><td>$cc</td><td><input name=banips[$cc] value=\"$banip[$cc]\" type=text size=10></td></tr>";
	}
	for ($aa=$cc;$aa<$cc+5;$aa++)
		echo "<tr bgcolor=#ffffff><td>$aa</td><td><input name=banips[$aa] type=text size=10></td></tr>";

	if (empty($jbbc[0]))
		$jbbc[0] = "000000";
	if (empty($jbbc[1]))
		$jbbc[1] = "0783FF";
	if (empty($jbbc[2]))
		$jbbc[2] = "0682FE";
	if (empty($jbbc[3]))
		$jbbc[3] = "0783FF";
	if (empty($jbbc[4]))
		$jbbc[4] = "FFFFFF";
	if (empty($jbbc[5]))
		$jbbc[5] = "/gfx/modbar.gif";
	if (empty($jbbc[6]))
		$jbbc[6] = "/gfx/up_tile.gif";
	if (empty($jbbc[7]))
		$jbbc[7] = "/gfx/up_tile.gif";
	if (empty($jbbc[8]))
		$jbbc[8] = "/gfx/dn_tile.gif";
	if (empty($jbbc[9]))
		$jbbc[9] = "/gfx/newthread.gif";
	if (empty($jbbc[10]))
		$jbbc[10] = "FFFFFF";
	if (empty($jbbc[11]))
		$jbbc[11] = "e0e0e0";
	if (empty($jbbc[12]))
		$jbbc[12] = "f0f0f0";
	if (empty($jbbc[13]))
		$jbbc[13] = "000000";
	if (empty($jbbc[14]))
		$jbbc[14] = "utime";
	if (empty($jbbc[15]))
		$jbbc[15] = "DESC";

	echo "</table></td><td width=10><img width=10 src='https://pjj.cc/gfx/null.gif' border=0></td><td><table cellspacing=1 cellpadding=3 border=0 bgcolor=#000000>";
	echo "<tr bgcolor=#ffffff><td colspan=2><b>Customize Board</b></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Text</td>			<td><input type=text name='jbbc[0]' value='$jbbc[0]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Link</td>			<td><input type=text name='jbbc[1]' value='$jbbc[1]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Active Link</td>	<td><input type=text name='jbbc[2]' value='$jbbc[2]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Visited Link</td>	<td><input type=text name='jbbc[3]' value='$jbbc[3]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Bgcolor</td>	<td><input type=text name='jbbc[4]' value='$jbbc[4]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Control Bar</td>	<td><input type=text name='jbbc[5]' value='$jbbc[5]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Control Back</td>	<td><input type=text name='jbbc[6]' value='$jbbc[6]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Top Bar</td>		<td><input type=text name='jbbc[7]' value='$jbbc[7]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Bottom Bar</td>	<td><input type=text name='jbbc[8]' value='$jbbc[8]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Index Bar</td>	<td><input type=text name='jbbc[9]' value='$jbbc[9]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>View bgcolor</td>	<td><input type=text name='jbbc[10]' value='$jbbc[10]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Info bgcolor</td>	<td><input type=text name='jbbc[11]' value='$jbbc[11]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Topic bgcolor</td><td><input type=text name='jbbc[12]' value='$jbbc[12]'></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Table bgcolor</td><td><input type=text name='jbbc[13]' value='$jbbc[13]'></td></tr>";
	echo "</table></td></tr></table>";

	echo "<p>Sort the threads on the board by <select name='jbbc[14]'>";
	echo "<option value=utime ".(($jbbc[14] == "utime") ? "SELECTED":"").">Last Reply";
	echo "<option value=ctime ".(($jbbc[14] == "ctime") ? "SELECTED":"").">Creation";
	echo "</select> ";
	echo "<select name='jbbc[15]'>";
	echo "<option value=DESC ".(($jbbc[15] == "DESC") ? "SELECTED":"").">Descending";
	echo "<option value=ASC ".(($jbbc[15] == "ASC") ? "SELECTED":"").">Ascending";
	echo "</select> ";

	echo "<p><table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>\n";
	echo "<tr bgcolor=#ffffff><td colspan=2><b>Interface Images</b></td></tr>";
	echo "<tr bgcolor=#eeeeee><td><b>Name</b></td><td><b>URL</b></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Clear</td><td><input name=images[0] value=\"$images[0]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Copyright</td><td><input name=images[1] value=\"$images[1]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Enter</td><td><input name=images[2] value=\"$images[2]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Exit</td><td><input name=images[3] value=\"$images[3]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Manual</td><td><input name=images[4] value=\"$images[4]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Members</td><td><input name=images[5] value=\"$images[5]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Messages</td><td><input name=images[6] value=\"$images[6]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Misc</td><td><input name=images[7] value=\"$images[7]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Post</td><td><input name=images[8] value=\"$images[8]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Profile</td><td><input name=images[9] value=\"$images[9]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Refresh</td><td><input name=images[10] value=\"$images[10]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Register</td><td><input name=images[11] value=\"$images[11]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Undo</td><td><input name=images[12] value=\"$images[12]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Userlist</td><td><input name=images[13] value=\"$images[13]\" type=text size=50></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Null (space)</td><td><input name=images[14] value=\"$images[14]\" type=text size=50></td></tr></table>\n";

	echo "<p><table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>\n";
	echo "<tr bgcolor=#ffffff><td colspan=2><b>Miscellaneous</b></td></tr>";
	echo "<tr bgcolor=#eeeeee><td><b>Description</b></td><td><b>Value</b></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Registration link</td><td><input name=reglink value=\"$reglink\" type=text size=30></td></tr>";
	if ($memonly == 1)
		$ch = " selected";
	else
		$ch = "";
	if ($memonly == 2)
		$cp = " selected";
	else
		$cp = "";
	echo "<tr bgcolor=#ffffff><td>Restricted area?</td><td><select name=memonly><option value=0>Open for all<option value=1$ch>All may view, but only members can chat<option value=2$cp>Only registered users allowed</select></td></tr>";

	if ($proxyblock == 1)
		$ch = " selected";
	else
		$ch = "";
	echo "<tr bgcolor=#ffffff><td>Block open proxies?</td><td><select name=proxyblock><option value=0>No<option value=1$ch>Yes</select></td></tr>";

	if (!isset($crating))
	    $crating = 3;
	$cl = " selected";
	if ($crating == 0)
		$ch = " selected";
	else
		$ch = "";
	if ($crating == 1)
		$cj = " selected";
	else
		$cj = "";
	if ($crating == 2)
		$ck = " selected";
	else
		$ck = "";
	if ($crating == 3)
		$cl = " selected";
	else
		$cl = "";
	if ($crating == 4)
		$cp = " selected";
	else
		$cp = "";
	echo <<<PHPEND
    <tr bgcolor=#ffffff>
    <td><a href="http://mpaa.org/movieratings/">Chat age rating</a>?</td>
    <td>
	<select name=crating>
	<option value=0$ch>Everyone (G)
	<option value=1$cj>Children (PG)
	<option value=2$ck>Teens (PG-13)
	<option value=3$cl>Mature (R)
	<option value=4$cp>Adults (NC-17)
	</select>
    </td>
    </tr>
PHPEND;

	if ($sportal == 1) {
			$ch = " selected";
	}
	else {
			$ch = "";
	}
	echo "<tr bgcolor=#ffffff><td>Visible on <a href='../' target=_blank>portal</a>?</td><td><select name=sportal><option value=0>Visible<option value=1$ch>Invisible</select></td></tr>";

	if ($xml_anon == 1) {
		$ch = " selected";
	}
	else {
		$ch = "";
	}
	echo "<tr bgcolor=#ffffff><td>Allow anonymous to stream XML?</td><td><select name=xml_anon><option value=0>No, only registered<option value=1$ch>Yes, if chat is unlocked</select></td></tr>";

	if ($jbblock == 1) {
		$ch = " selected";
	}
	else {
		$ch = "";
	}
	echo "<tr bgcolor=#ffffff><td>Who can post on the <a href=\"jbb/\" target=_blank>board</a>?</td><td><select name=jbblock><option value=0>Everyone<option value=1$ch>Members Only</select></td></tr>";

	if ($logblock == 3) {
		$cb = " selected";
	}
	else {
		$cb = "";
	}
	if ($logblock == 2) {
		$cx = " selected";
	}
	else {
		$cx = "";
	}
	if ($logblock == 1) {
		$ch = " selected";
	}
	else {
		$ch = "";
	}
	echo "<tr bgcolor=#ffffff><td>Who can view <a href=\"register/biglog.php\" target=_blank>the logs</a>?</td><td><select name=logblock><option value=0>Everyone<option value=1$ch>Registered<option value=2$cx>Moderators<option value=3$cb>Admins</select></td></tr>";

	if ($urlblock == 3) {
		$cb = " selected";
	}
	else {
		$cb = "";
	}
	if ($urlblock == 2) {
		$cx = " selected";
	}
	else {
		$cx = "";
	}
	if ($urlblock == 1) {
		$ch = " selected";
	}
	else {
		$ch = "";
	}
	echo "<tr bgcolor=#ffffff><td>Who can view <a href=\"reader.php?urls=1\" target=_blank>the URLs</a>?</td><td><select name=urlblock><option value=0>Everyone<option value=1$ch>Registered<option value=2$cx>Moderators<option value=3$cb>Admins</select></td></tr>";

    if (!isset($numicons)) {
        $numicons = 1;
    }
	echo "<tr bgcolor=#ffffff><td>Number of icons per chatter</td><td><select name=numicons>";
    for ($i=1;$i<=5;$i++) {
        echo "<option value='$i'";
        if ($numicons == $i) {
            echo " selected";
        }
        echo ">$i";
    }
    echo "</select></td></tr>";

	echo "<tr bgcolor=#ffffff><td>Portal Picture<br><i>Max 200x100.</i></td><td><input name=cpicture value=\"".htmlentities(stripslashes($cpicture))."\"></td></tr>";
	echo "<tr bgcolor=#ffffff><td>Picture Link<br></td><td><input name=cpicturelink value=\"".htmlentities(stripslashes($cpicturelink))."\"></td></tr>";
	echo "<tr bgcolor=#ffffff valign=top><td>Chat Description<br><i>Max 3 lines, or 1kb.<br>Allowed tags: font,a,b,i,u,tt</i></td><td><textarea name=cdescript cols=50 rows=10>".htmlentities(stripslashes($cdescript))."</textarea></td></tr>";
	echo "<!-- <tr bgcolor=#ffffff><td>Database (<font color=ff0000>Warning</font>: May mess up everything.)</td><td><input name=altdata value=\"$altdata\" type=text size=12></td></tr> -->";
	echo "</table>";

	echo "<p>\n<input type='submit' value='Save'> Login: <input type=text name=login value=\"{$_REQUEST['login']}\"> Password: <input type=password name=password value='{$_REQUEST['password']}'>\n";
	echo "<input type=hidden value=\"Submitted options.\" name=message>\n";
	echo "</form><p>\n";

?>
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
</td>
	<td valign="top" align="right" height="100%" width=80> </td></tr>
	</table>
</td></tr>
<tr><td background="https://pjj.cc/gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>

<script type="text/javascript">
$('#filters tbody').sortable({
	update: function(event, ui) {
		var i = 0;
		$('#filters tbody tr').each(function(index, elem) {
			$(elem).find('.filter_num').text(i);
			$(elem).find('.filter_find').attr('name', 'iname['+i+']');
			$(elem).find('.filter_repl').attr('name', 'ifile['+i+']');
			++i;
		});
	}
});
</script>
</body>
</html>
