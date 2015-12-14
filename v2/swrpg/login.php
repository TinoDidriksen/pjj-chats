<?php
	require_once("../common/session.php");
	require_once("../mysql.php");
	require_once("../setup.php");
	require("settings.php");
	require("options.php");

	$realpath = preg_replace('@.*/([^/]+)/login.php$@', '\1', $_SERVER['PHP_SELF']);
	$realpath = 'chat'.$realpath;
	$chatpath = $realpath;

    if (empty($numicons)) {
        $numicons = 1;
    }
    $icons = $_REQUEST['icons'];

	$newpath = mb_substr($realpath, 4);
	//$newpath = str_replace("/","",$newpath);

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

	if (!empty($_COOKIE['pJJChat']) && ($logout != 1)) {
        $cookiedata = unserialize($_COOKIE['pJJChat']);
        if (is_array($cookiedata)) {
            $cookiedata[0] = 'on';
            $cookiedata[1] = $cookiedata['reload'];
            $handle		= $cookiedata['handle'];
            $password	= $cookiedata['password'];
            $color		= $cookiedata['color'];
            $icons		= $cookiedata['icons'];
            $link		= $cookiedata['link'];
            $image		= $cookiedata['image'];
            $autolog	= $cookiedata['autologin'];
        }
	}

	if (($autolog == "on") && ($logout != 1)) {
		$somepath = eregi_replace("(.*)/login.php$", "\\1", $_SERVER['PHP_SELF']);
		header(
		"Location: $somepath/sendmsg.php?handle=".urlencode($handle).
		"&image=".urlencode($image).
		"&link=".urlencode($link).
		"&icons=".urlencode(serialize($icons)).
		"&color=".urlencode($color).
		"&iwantcookie=".urlencode($cookiedata[0]).
		"&reload=".urlencode($cookiedata[1]).
		"&motd=on".
		"&password=".urlencode($password).
		"&autolog=".urlencode($autolog)
		);
		die();
	}

	$c = count($images);
	for($i=0;$i<$c;$i++) {
		if (strstr($images[$i], '../master/'))
			$images[$i] = str_replace('../master/', 'https://master.pjj.cc/', $images[$i]);
		if (strstr($images[$i], '../'))
			$images[$i] = str_replace('../', 'https://pjj.cc/', $images[$i]);
	}

	echo "<!DOCTYPE html>
<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<META NAME='ROBOTS' CONTENT='NOARCHIVE'>
	<script type='text/javascript' src='https://pjj.cc/common/js/functions.js'></script>
	<style>
	$csshead
	</style>
</head>
$bodytag";
echo "
<table border=0 width='100%' height='100%'><tr><td align=center valign=top>

<form method=post action=\"sendmsg.php\" name=\"chat\">

<table border=0 cellspacing=0 cellpadding=0>
<tr valign=top>
	<td><b>Nickname</b>: </td>
	<td><input type=text name=\"handle\" maxlength=40 size=15 value=\"$handle\"></td>
	<td><b>Image URL</b>: </td>
	<td><input type=text name=\"image\" maxlenght=1024 size=15 value=\"$image\"></td>
	<td></td>
</tr>
<tr valign=top>
	<td><b>Color</b>: </td>
	<td><input type=text name=\"color\" maxlength=6 size=6 value=\"$color\"><a href=\"#\" onclick=\"openColorPicker('$realpath');\">@</a></td>
	<td><b>Link</b>: </td>
	<td valign=top><input type=text name=\"link\" maxlength=1024 size=15 value=\"$link\"></td>
	<td>Adv: <input class=boxes type=checkbox name=\"mode_advanced\"></td>
</tr>
<tr valign=top>
	<td><b>Password</b> (<a href=\"https://pjj.cc/common/password.php?chat=$newpath\" target=_blank style=\"font-size: 8pt;\">lost?</a>): </td>
	<td><input type=password name=\"password\" maxlenght=32 size=6 value=\"$password\"></td>
	<td><b>Icon</b>: </td>
	<td>";

	include("iconlist.php");
	if (!$picons) {
		$picons = array();
    }

	for ($i=0;$i<$numicons;$i++) {
        $icon = $icons[$i];
        echo "<select name='icons[$i]'>\n";
        echo "<option value=\"\">No Icon\n";
        foreach ($picons as $name => $file) {
            if ($icon == $file)
                echo "<option value='$file' selected>$name\n";
            else
                echo "<option value='$file'>$name\n";
        }
        foreach ($icons as $name => $file) {
            if ($icon == $file)
                echo "<option value='$file' selected>$name\n";
            else
                echo "<option value='$file'>$name\n";
        }
        echo "</select>";
    }

echo "</td><td>Sim: <input class=boxes type=checkbox name=\"mode_simple\"></td></tr>

<tr><td valign=top align=right>";
$button = "<input class='boxes' type='image' src='{$images[2]}' alt='Enter' border=0>";
if (!preg_match('@^(ht|f)tps?://@ui', $images[2])) {
	$button = "<button class='boxes' title='Enter'>{$images[2]}</button>";
}
echo $button;

$button = "<img alt='Reset' src='{$images[0]}' border=0>";
if (!preg_match('@^(ht|f)tps?://@ui', $images[0])) {
	$button = $images[0];
}
echo "<img src='$images[14]' border=0><a href=\"javascript:document.forms['login'].reset()\">$button</a>";
echo "<input type=hidden name=\"motd\" value=\"on\">
</td>
<td valign=top><img src='$images[14]' border=0>Reload: <input class=boxes type=checkbox name='reload' ".(($cookiedata[1] == "on") ? "checked":"").">|| Cookie: <input class=boxes type=checkbox name='iwantcookie' ".(($cookiedata[0] == "on") ? "checked":"").">
</td><td>Auto: <input class=boxes type=checkbox name='autolog' ".(($autolog == "on") ? "checked":"")."></td><td>";

if ($reglink != "") {
	$button = "<img alt='Register' src='{$images[11]}' border=0>";
	if (!preg_match('@^(ht|f)tps?://@ui', $images[11])) {
		$button = $images[11];
	}
	echo "<a href='$reglink' target=_blank>$button</a>";
}

$refresh = "<img alt='Refresh' src='{$images[10]}' border=0>";
if (!preg_match('@^(ht|f)tps?://@ui', $images[10])) {
	$refresh = $images[10];
}
$manual = "<img alt='Manual' src='{$images[4]}' border=0>";
if (!preg_match('@^(ht|f)tps?://@ui', $images[4])) {
	$manual = $images[4];
}
echo "</td><td></td></tr>
</table>
</form>
</td>
<td align=center valign=top>
<a href='reader.php?cspeed=22&motd=on' target='TextWindow'>$refresh</a><p>
<a href='manual.php' target='_blank'>$manual</a><p>
</td></tr></table>
<div align='right'><a href='https://pjj.cc/legal/' target='TextWindow'>Terms of Service</a></div>
</body>
</html>";
