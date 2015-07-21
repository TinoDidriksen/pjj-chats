<?php
    ob_start();
    ignore_user_abort(true);

	require_once("../common/session.php");
	require_once("../mysql.php");
	require_once("../setup.php");
	require_once("../common/helpers.php");
	require_once("options.php");

	$realpath = ereg_replace('.*/([^/]+)/gui_set.php$', 'chat\1', $_SERVER['PHP_SELF']);
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
		}
		else {
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
	<title>Settings Editor</title>

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
<tr><td background="http://pjj.cc/gfx/up_tile.gif" valign="top" align="left" height="32"> </td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%"><img src='http://pjj.cc/gfx/null.gif' border=0></td>
	<td valign="top" height="100%">
<center><img src='http://pjj.cc/gfx/null.gif' border=0></center>
<?php
	function CheckImage($id) {
		if (strstr($id, '<img')) {
			return $id;
		}
		else if (strstr($id, 'tp://')) {
			return "<img src=\"$id\" border=0>";
		}
		return $id;
	}

	function CheckBgImage($id) {
		if (strstr($id, "tp://")) {
			return " background=\"$id\"";
		}
		return '';
	}

	$nums = "0123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789";

	$_REQUEST = TrimArray($_REQUEST);
	extract($_REQUEST);

	if (!empty($altdata)) {
		$chatpath = $altdata;
	}
	else {
		$chatpath = ereg_replace(".*/([^/]+)/gui_set.php$", "chat\\1", $_SERVER['PHP_SELF']);
		if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
			$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
        }
	}

	if ($_REQUEST['max_nick'] < 20) {
		$_REQUEST['max_nick'] = 20;
    }
	if ($_REQUEST['identlenght'] < 3) {
		$_REQUEST['identlenght'] = 3;
    }
	if ($_REQUEST['timeout'] > 1209600) {
		$_REQUEST['timeout'] = 1209600;
    }

	$suglog = "settings.php";

	$sfooter = "\$dbodytag = \"<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' text='#\".\$servcol.\"' link='#\".\$s_link.\"' vlink='#\".\$s_visit.\"' alink='#\".\$s_active.\"' bgcolor='#\".\$s_bgcol.\"'\";
\$bodytag = \$dbodytag;
\$ubodytag = \$dbodytag;
\$cbodytag = \$dbodytag;
if (\$s_bgimg != \"\")
	\$bodytag .= \" background='\".\$s_bgimg.\"'\";
\$bodytag .= \">\n\";
if (\$u_bgimg != \"\")
	\$ubodytag .= \" background='\".\$u_bgimg.\"'\";
\$ubodytag .= \">\n\";
if (\$c_bgimg != \"\")
	\$cbodytag .= \" background='\".\$c_bgimg.\"'\";
\$cbodytag .= \">\n\";";

	$flags = ChatVerifyLogin($_REQUEST['login'], $_REQUEST['password'], $chatpath);
	$bing = GetChatPrefs($chatpath);
	$check = CheckFlags("sXZmM", $flags);
	if (($s_bgcol == "000000") || ($s_bgcol == "0")) {
		$s_bgcol = "010101";
	}
	$len = strlen($_REQUEST['message']);
	if (($len > 5) && ($check > 0)) {
		@count_mysql_query("DELETE FROM uo_chat_adminlog WHERE stamp<DATE_SUB(now(), INTERVAL 28 DAY)", $handler);
		@count_mysql_query("INSERT INTO uo_chat_adminlog
			(page_id,chat_id,user_id,user_ip,stamp)
			VALUES (
			1,
			{$GLOBALS['biglog']['chat_id']},
			{$GLOBALS['biglog']['user_id']},
			'{$_SERVER['REMOTE_ADDR']}',
			now()
			)", $handler);

		$fm = fopen($suglog, "w");
		fwrite($fm, "<?php\n");
		$ctitle = str_replace("\"", "'", str_replace('$', '', stripslashes(strip_tags($_REQUEST['ctitle']))));
		$ctitle = trim($ctitle);
		if (empty($ctitle)) {
			$ctitle = $chatpath;
        }
        if ($_REQUEST['userlistspeed'] > 300) {
		    $_REQUEST['userlistspeed'] = 300;
        }
		else if ($_REQUEST['userlistspeed'] < 60) {
		    $_REQUEST['userlistspeed'] = 60;
        }
		fwrite($fm, '$ctitle = '.var_export($ctitle, true).";\n");
		fwrite($fm, '$cadmin = '.var_export($_REQUEST['cadmin'], true).";\n");
		fwrite($fm, '$respeed = '.intval($_REQUEST['respeed']).";\n");
		fwrite($fm, '$servcol = '.var_export(FixColor($_REQUEST['servcol']), true).";\n");
		fwrite($fm, '$identlenght = '.intval($_REQUEST['identlenght']).";\n");
		fwrite($fm, '$logsize = '.var_export($_REQUEST['logsize'], true).";\n");
		fwrite($fm, '$maxlines = '.intval($_REQUEST['maxlines']).";\n");
		fwrite($fm, '$timeout = '.intval($_REQUEST['timeout']).";\n");
		fwrite($fm, '$userlistspeed = '.intval($_REQUEST['userlistspeed']).";\n");
		fwrite($fm, '$logofile = '.var_export($_REQUEST['logofile'], true).";\n");
		fwrite($fm, '$logolink = '.var_export($_REQUEST['logolink'], true).";\n");
		fwrite($fm, '$musiclink = '.var_export($_REQUEST['musiclink'], true).";\n");
		fwrite($fm, '$pimgx = '.intval($_REQUEST['pimgx']).";\n");
		fwrite($fm, '$pimgy = '.intval($_REQUEST['pimgy']).";\n");
		fwrite($fm, '$noname = '.var_export($_REQUEST['noname'], true).";\n");
		fwrite($fm, '$lastpos = '.intval($_REQUEST['lastpos']).";\n");
		fwrite($fm, '$identxtsize = '.intval($_REQUEST['identxtsize']).";\n");
		fwrite($fm, '$regident = '.var_export($_REQUEST['regident'], true).";\n");
		fwrite($fm, '$oocident = '.var_export($_REQUEST['oocident'], true).";\n");
		fwrite($fm, '$modident = '.var_export($_REQUEST['modident'], true).";\n");
		fwrite($fm, '$adminident = '.var_export($_REQUEST['adminident'], true).";\n");
		fwrite($fm, '$subchat = '.var_export($_REQUEST['subchat'], true).";\n");
		fwrite($fm, '$s_link = '.var_export($_REQUEST['s_link'], true).";\n");
		fwrite($fm, '$s_active = '.var_export($_REQUEST['s_active'], true).";\n");
		fwrite($fm, '$s_visit = '.var_export($_REQUEST['s_visit'], true).";\n");
		fwrite($fm, '$s_bgcol = '.var_export(FixColor($_REQUEST['s_bgcol']), true).";\n");
		fwrite($fm, '$s_bgimg = '.var_export($_REQUEST['s_bgimg'], true).";\n");
		fwrite($fm, '$c_bgimg = '.var_export($_REQUEST['c_bgimg'], true).";\n");
		fwrite($fm, '$u_bgimg = '.var_export($_REQUEST['u_bgimg'], true).";\n");
		fwrite($fm, '$max_nick = '.intval($_REQUEST['max_nick']).";\n");
		fwrite($fm, '$max_link = '.intval($_REQUEST['max_link']).";\n");
		fwrite($fm, '$max_image = '.intval($_REQUEST['max_image']).";\n");
		fwrite($fm, '$timer = '.var_export($_REQUEST['timer'], true).";\n");
		fwrite($fm, '$tzone = '.intval($_REQUEST['tzone']).";\n");
		fwrite($fm, '$dtcalc = '.var_export($_REQUEST['dtcalc'], true).";\n");
		fwrite($fm, '$initlink = '.var_export($_REQUEST['initlink'], true).";\n");
		fwrite($fm, '$csshead = '.var_export($_REQUEST['csshead'], true).";\n");
		fwrite($fm, '$altdata = '.var_export($_REQUEST['altdata'], true).";\n");
		fwrite($fm, $sfooter);
		fwrite($fm, "\n");
		fclose($fm);
	}
	require_once("settings.php");

	if (!$s_bgcol || ($s_bgcol == '000000')) {
		$s_bgcol = '010101';
    }
	if (empty($timer)) {
		$timer = "Last Post: ";
    }
	if (empty($dtcalc)) {
		$dtcalc = "g:ia, F d (T)";
    }
	if (empty($initlink)) {
		$initlink = "manual.php";
    }

	echo "
<form action=\"gui_set.php\" method=\"post\" target=\"_top\">
<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
	<tr bgcolor=#eeeeee>
		<td colspan=3><b>Chat</b></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Title</td>
		<td><input name=\"ctitle\" value=\"$ctitle\" size=20></td>
		<td>$ctitle</td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Master Email</td>
		<td><input name=\"cadmin\" value=\"$cadmin\" size=20></td>
		<td><a href=\"mailto:$cadmin\" target=_blank>$cadmin</a></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Welcome Page</td>
		<td><input name=\"initlink\" value=\"$initlink\" size=20></td>
		<td><a href=\"$initlink\" target=_blank>Test URL</a></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Extra Link</td>
		<td><input name=\"musiclink\" value=\"$musiclink\" size=20></td>
		<td><a href=\"$musiclink\" target=_blank>Test URL</a></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Default Handle</td>
		<td><input name=\"noname\" value=\"$noname\" size=20></td>
		<td>$noname</td>
	</tr>
<!--
	<tr bgcolor=#ffffff>
		<td>Database</td>
		<td><input name=\"altdata\" value=\"$altdata\" size=16></td>
		<td><font color=#ff0000>Warning: Altering this field may cause you to lose control over this chat.</font></td>
	</tr>
-->
</table>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10>

<table border=0 cellspacing=0 cellpadding=0><tr valign=top><td>
	<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
		<tr bgcolor=#eeeeee>
			<td colspan=3><b>Colors</b></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Text</td>
			<td><input name=\"servcol\" value=\"$servcol\" size=6></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$servcol\">Example</font></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Link</td>
			<td><input name=\"s_link\" value=\"$s_link\" size=6></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$s_link\"><u>Example</u></font></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Active Link</td>
			<td><input name=\"s_active\" value=\"$s_active\" size=6></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$s_active\"><u>Example</u></font></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Visited Link</td>
			<td><input name=\"s_visit\" value=\"$s_visit\" size=6></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$s_visit\"><u>Example</u></font></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Background</td>
			<td><input name=\"s_bgcol\" value=\"$s_bgcol\" size=6></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$servcol\">Example</font></td>
		</tr>
	</table>
</td><td>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10>
</td><td>
	<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
		<tr bgcolor=#eeeeee>
			<td colspan=3><b>Symbols</b></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>OOC</td>
			<td><input name=\"oocident\" value=\"".htmlentities($oocident)."\" size=20></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$servcol\">".CheckImage($oocident)."</font></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Registered</td>
			<td><input name=\"regident\" value=\"".htmlentities($regident)."\" size=20></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$servcol\">".CheckImage($regident)."</font></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Moderator</td>
			<td><input name=\"modident\" value=\"".htmlentities($modident)."\" size=20></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$servcol\">".CheckImage($modident)."</font></td>
		</tr>
		<tr bgcolor=#ffffff>
			<td>Administrator</td>
			<td><input name=\"adminident\" value=\"".htmlentities($adminident)."\" size=20></td>
			<td bgcolor=\"$s_bgcol\"><font color=\"$servcol\">".CheckImage($adminident)."</font></td>
		</tr>
	</table>
</td><td>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10>
</td>
</tr></table>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10>
<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
	<tr bgcolor=#eeeeee>
		<td colspan=1><b>Cascading Style Sheet (CSS)</b></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>&lt;style type=\"text/css\"&gt;<br><textarea name=\"csshead\" cols=60 rows=15>".htmlentities($csshead)."</textarea><br>&lt;/style&gt;</td>
	</tr>
</table>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10>

<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
	<tr bgcolor=#eeeeee>
		<td colspan=3><b>Images</b></td>
	</tr>
	<tr bgcolor=#ffffff valign=top>
		<td>Logo</td>
		<td><input name=\"logofile\" value=\"$logofile\" size=20></td>
		<td bgcolor=\"$s_bgcol\" rowspan=2><a href=\"$logolink\" target=_blank>".CheckImage($logofile)."</a></td>
	</tr>
	<tr bgcolor=#ffffff valign=top>
		<td>Logo URL</td>
		<td><input name=\"logolink\" value=\"$logolink\" size=20></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Main</td>
		<td><input name=\"s_bgimg\" value=\"$s_bgimg\" size=20></td>
		<td bgcolor=\"$s_bgcol\"".CheckBgImage($s_bgimg)."><font color=\"$servcol\">Example</font></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Console</td>
		<td><input name=\"c_bgimg\" value=\"$c_bgimg\" size=20></td>
		<td bgcolor=\"$s_bgcol\"".CheckBgImage($c_bgimg)."><font color=\"$servcol\">Example</font></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Userlist</td>
		<td><input name=\"u_bgimg\" value=\"$u_bgimg\" size=20></td>
		<td bgcolor=\"$s_bgcol\"".CheckBgImage($u_bgimg)."><font color=\"$servcol\">Example</font></td>
	</tr>
	<tr bgcolor=#ffffff valign=top>
		<td>User Image Size</td>
		<td><input name=\"pimgx\" value=\"$pimgx\" size=3>x<input name=\"pimgy\" value=\"$pimgy\" size=3> pixels</td>
		<td bgcolor=\"$s_bgcol\"><img src=\"http://pjj.cc/gfx/scanback.gif\" width=$pimgx height=$pimgy border=1></td>
	</tr>
</table>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10>

<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
	<tr bgcolor=#eeeeee>
		<td colspan=3><b>Maximums and Numbers</b></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Lines In Chat</td>
		<td><input name=\"maxlines\" value=\"$maxlines\" size=4></td>
		<td></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Handle Length</td>
		<td><input name=\"max_nick\" value=\"$max_nick\" size=4></td>
		<td>".substr($nums, 0, $max_nick)."</td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Link Length</td>
		<td><input name=\"max_link\" value=\"$max_link\" size=4></td>
		<td><a href=\"".substr($nums, 0, $max_link)."\" target=_blank>Test URL</a></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Image URL Length</td>
		<td><input name=\"max_image\" value=\"$max_image\" size=4></td>
		<td><a href=\"".substr($nums, 0, $max_image)."\" target=_blank>Test URL</a></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Idletime</td>
		<td><input name=\"timeout\" value=\"$timeout\" size=4></td>
		<td>$timeout seconds</td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Chat Refresh</td>
		<td><input name=\"respeed\" value=\"$respeed\" size=4></td>
		<td>$respeed seconds</td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Userlist Refresh</td>
		<td><input name=\"userlistspeed\" value=\"$userlistspeed\" size=4></td>
		<td>$userlistspeed seconds</td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Ident Length</td>
		<td><input name=\"identlenght\" value=\"$identlenght\" size=4></td>
		<td rowspan=2><font size=\"$identxtsize\">".substr($nums, 0, $identlenght)."</font></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Ident Size</td>
		<td><input name=\"identxtsize\" value=\"$identxtsize\" size=4></td>
	</tr>
</table>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10>

<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
	<tr bgcolor=#eeeeee>
		<td colspan=3><b>Date &amp; Time</b></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Show \"Last Post\"</td>
		<td><input name=\"lastpos\" value=\"$lastpos\" size=1></td>
		<td>".(($lastpos>0) ? ("Enabled (1)") : ("Disabled (2)"))."</td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Timestamp Text</td>
		<td><input name=\"timer\" value=\"$timer\" size=15></td>
		<td rowspan=3><i>Example:</i><br>$timer ".(date($dtcalc, time(0)+($tzone*3600)))."</td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Format (<a href=\"http://www.php.net/date\" target=_blank>help</a>)</td>
		<td><input name=\"dtcalc\" value=\"".htmlentities(addslashes($dtcalc))."\" size=10></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Timezone (from server)</td>
		<td><input name=\"tzone\" value=\"$tzone\" size=3></td>
	</tr>
</table>
<img src=\"http://pjj.cc/gfx/null.gif\" border=0 width=10 height=10><br>

<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>
	<tr bgcolor=#eeeeee>
		<td colspan=2><b>Authorization</b></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Username</td>
		<td><input type=text name=login value=\"{$_REQUEST['login']}\" size=15></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td>Password</td>
		<td><input type=password name=password value=\"{$_REQUEST['password']}\" size=15></td>
	</tr>
	<tr bgcolor=#ffffff>
		<td colspan=2 align=right><input type=\"submit\" value=\"Save Changes\"></td>
	</tr>
</table>
<input type=hidden value=\"Submitted settings.\" name=message>
</form>
";
?>
<center><img src="http://pjj.cc/gfx/null.gif" border=0></center>
</td>
	<td valign="top" align="right" height="100%"><img src='http://pjj.cc/gfx/null.gif' border=0></td></tr>
	</table>
</td></tr>
<tr><td background="http://pjj.cc/gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>
</body>
</html>
