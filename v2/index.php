<?php
	if (empty($_SERVER['HTTPS'])) {
		header('Location: https://pjj.cc/', true, 301);
		die();
	}
	header('Strict-Transport-Security: max-age=86400; includeSubDomains');

	if (strpos(strtolower($_SERVER['HTTP_HOST']), 'chat.projectjj.') !== false) {
		header('HTTP/1.1 301 Moved Permanently');
		header("Location: https://pjj.cc/");
		die();
	}

    ignore_user_abort();
	ob_start();
?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="Author" content="Tino Didriksen <mail@tinodidriksen.com>">
	<meta name="Generator" content="Tino Didriksen <mail@tinodidriksen.com>">
	<meta name="google-site-verification" content="dDCWHjXgRUCemnG36GYPWNtUIa0V_5FL2nC4J4jAB9g" />
	<title>Project JJ Chats - Let Worlds Unfold</title>
	<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
	<link rel="icon" type="image/x-icon" href="/favicon.ico" />

<script type="text/javascript">
if (window != window.top) {
	top.location.href = location.href;
}
</script>

<style type="text/css">
body {
    margin: 0;
    padding: 0;
    color: #000;
    background-color: #fff;
    font-family: verdana, arial, sans-serif;
    line-height: 140%;
}
a {
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
</style>

</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td valign="top" align="left" height="32" style="background-image: URL('//pjj.cc/gfx/up_tile.gif'); background-repeat: repeat-x;">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="32">
	<tr><td valign="top" align="left"width="151"><a href="https://tinodidriksen.com/"><img src="gfx/projectjj2.gif" border="0"></a></td>
	<td valign="top" align="right" width="151"><a href="https://tinodidriksen.com/" target=_blank><img src="gfx/phpchat2.gif" border="0"></a></td></tr>
	</table>
</td></tr>
</table>
<center><img src="gfx/null.gif" border="0" height="10"><br>
<table cellspacing="3" cellpadding="3" border="0">
<tr align="center" valign="top">
<td><a href="http://forum.tinodidriksen.com/"><b>Main Discussion Board</b></a><br>
</td>
<td><b>Want a chat?</b> (it's free)<br>
Use the <a href="rq.php"><b>Chat Request Form</b></a>
</td>
<td><a href="https://www.facebook.com/groups/pjj.chats/"><b>Facebook</b></a><br>
<a href="https://plus.google.com/communities/115521193128885558520"><b>Google+</b></a><br>
</td>
</tr>
<tr valign="top" align="center">
<td><a href="characters.php"><b>Character Manager</b></a><br>
Handles all your characters
</td>
<td><b>Getting rid of a chat?</b><br>
Use the <a href="delete.php"><b>Chat Deletion Form</b></a>
</td>
<td><b>Recover deleted chats?</b><br>
Use the <a href="recover.php"><b>Chat Recover Form</b></a>
</td>
</tr></table>
<center><?php readfile("announce.dat"); ?></center>
<br>
<?php
	require_once("mysql.php");
	require_once("std_uo.php");
	require_once("../chatv3/_inc/mmcache.php");

	function SortList($a, $b) {
		$ta = trim(strtolower(preg_replace('/[^A-Za-z]+/', '', $a[0])));
		$tb = trim(strtolower(preg_replace('/[^A-Za-z]+/', '', $b[0])));
		return strcmp($ta, $tb);
	}

	function SortListActive($a, $b) {
		if ($a[7] == $b[7]) return SortListView($a, $b);
		return ($a[7] > $b[7]) ? -1 : 1;
	}

	function SortListChat($a, $b) {
		if ($a[2] == $b[2]) return SortListActive($a, $b);
		return ($a[2] > $b[2]) ? -1 : 1;
	}

	function SortListView($a, $b) {
		if ($a[3] == $b[3]) return SortList($a, $b);
		return ($a[3] > $b[3]) ? -1 : 1;
	}

	function ExAlist($path) {
		global $alist;

		$cnt = 0;
		for ($cc=0;$cc<count($alist);$cc++) {
			if ($alist[$cc] == $path)
				$cnt++;
		}

		return $cnt;
	}

	function ExUlist($path) {
		global $ulist;

		$cnt = 0;
		for ($cc=0;$cc<count($ulist);$cc++) {
			if ($ulist[$cc] == $path)
				$cnt++;
		}

		return $cnt;
	}

	function ExView($path) {
		global $uview;

		$cnt = 0;
		for ($cc=0;$cc<count($uview);$cc++) {
			if ($uview[$cc] == $path)
				$cnt++;
		}

		return $cnt;
	}

	$dirx = array();
	$timez = array();
	$query = "SELECT chat, EXTRACT(EPOCH FROM utime) as utime, chat_id FROM chatv2.chats
		WHERE dtime IS NULL AND utime > now() - INTERVAL '6 month'";
	$rez = $GLOBALS['sql']->query($query);
	$num_chats = $GLOBALS['sql']->numRows($rez);
	for ($i=0;$i<$num_chats;$i++) {
		$row = $GLOBALS['sql']->fetchAssoc($rez, $i);
		$dirx[] = $row['chat'];
		$timez[$row['chat']] = $row['utime'];
	}
	$GLOBALS['sql']->freeResult($rez);
	$numchat = $num_chats;

	$nda = "#EEEEEE";
	$ccall = 0;
	$ccv = 0;

	$numvis = 0;
	$numact = 0;

	mysqli_query($handler, "DELETE FROM uo_chat WHERE utime<'".(time()-300)."'");

	$alist = array();
	$result = mysqli_query($handler, "SELECT chat FROM uo_chat_ulist WHERE utime>'".(time()-300)."' ORDER BY chat ASC");
	while($row = mysqli_fetch_row($result)) {
		$alist[] = $row[0];
		$numact++;
	}
	mysqli_free_result($result);

	$ulist = array();
	$result = mysqli_query($handler, "SELECT chat FROM uo_chat_ulist WHERE utime>'".(time()-86400)."' ORDER BY chat ASC");
	while($row = mysqli_fetch_row($result)) {
		$ulist[] = $row[0];
	}
	mysqli_free_result($result);

	$uview = array();
	$result = mysqli_query($handler, "SELECT DISTINCT chat,ip FROM uo_chat ORDER BY chat ASC");
	while($row = mysqli_fetch_row($result)) {
		$uview[] = $row[0];
	}
	mysqli_free_result($result);

    MMC_Lock('portal.lock');
    $clist = array();
    $lines = array();
    $portalcachetime = intval(MMC_Get('portal.stamp'));
	if ($portalcachetime >= time()-120) {
		$clist = MMC_Get('portal.clist');
		$lines = MMC_Get('portal.popular');
        echo "\n<!-- Cached portal from ".gmdate('r', $portalcachetime)." -->\n";
	}

	if (empty($clist)) {
        echo "\n<!-- Fresh portal -->\n";

		function sort_lines($a, $b) {
			if ($a == $b) {
				return 0;
			}
			return $a < $b;
		}

		$rez = $GLOBALS['sql']->query("SELECT chat_id, chat
			FROM chatv2.chats WHERE (ctime < now() - INTERVAL '1 month' OR ctime IS NULL)
			AND utime > now() - INTERVAL '1 month' AND dtime IS NULL
			ORDER BY chat_id ASC");
		$num = $GLOBALS['sql']->numRows($rez);
		if (empty($num)) {
			die('Empty.');
		}

		$chats = array();
		for ($i=0 ; $i<$num ; $i++) {
			$row = $GLOBALS['sql']->fetchAssoc($rez, $i);
			$chats[$row['chat']] = $row['chat_id'];
		}
		$GLOBALS['sql']->freeResult($rez);

		$lines = array();
		foreach ($chats as $chat => $id) {
			$rez = $GLOBALS['sql']->query("SELECT count(stamp) as cnt FROM chatv2logs.log_$id
				WHERE stamp >= now() - INTERVAL '1 month'");
			$num = $GLOBALS['sql']->numRows($rez);
			if ($num) {
				$row = $GLOBALS['sql']->fetchAssoc($rez, 0);
				$lines[$chat] = $row['cnt'];
			}
			$GLOBALS['sql']->freeResult($rez);
		}

		foreach ($lines as $chat => $n) {
			if ($n < 50) {
				unset($lines[$chat]);
			}
		}

		uasort($lines, 'sort_lines');

        for ($cc=0 ; $cc<count($dirx) ; $cc++) {
            if ((is_dir($dirx[$cc])) && (file_exists($dirx[$cc].'/settings.php'))) {
                continue;
            }
            else {
                unset($dirx[$cc]);
                unset($lines[$dirx[$cc]]);
            }
        }
        $dirx = array_unique($dirx);
        sort($dirx);

        $too_old = time()-2419200;

        for ($cc=0 ; $cc<count($dirx) ; $cc++) {
        	/*
            if ($timez[$dirx[$cc]] < $too_old) {
                continue;
            }
            //*/

            unset($crating);
            unset($memonly);
            unset($cpicture);
            unset($cpicturelink);
            unset($cpicturesize);
            unset($cdescript);
            unset($ctitle);
            unset($sportal);
            require_once $dirx[$cc].'/settings.php';
            $sportal = 0;
            require_once $dirx[$cc].'/options.php';

            if (empty($ctitle) || $ctitle == 'Unnamed') {
                $ctitle = 'Unnamed';
                $sportal = 1;
            }
			$ctitle = wordwrap($ctitle, 20, ' ', true);

            if (($_REQUEST['hidden'] == 1) && ($sportal == 0)) {
                unset($lines[$dirx[$cc]]);
                continue;
            }
            else if (($_REQUEST['hidden'] == 0) && ($sportal != 0)) {
                unset($lines[$dirx[$cc]]);
                continue;
            }

            if (!isset($crating)) {
                $crating = 3;
            }

            $clist[$numvis] = array();

            $clist[$numvis][0] = $ctitle;

            if ($cpicture) {
                if ($cpicturelink) {
                    $cdescript = "<a href=\"{$cpicturelink}\"><img src=\"{$cpicture}\" {$cpicturesize}></a>\n".$cdescript;
                }
                else {
                    $cdescript = "<img src=\"{$cpicture}\" {$cpicturesize}>\n".$cdescript;
                }
            }
            if (!$cdescript) {
                $cdescript = 'No Description';
            }
			$cdescript = wordwrap($cdescript, 20, ' ', true);
            $cdescript = preg_replace("/\n/", '<br />', trim($cdescript), 4);
            $clist[$numvis][1] = stripslashes($cdescript);
            if ($clean == 1) {
                $clist[$numvis][1] = strip_tags($clist[$numvis][1]);
            }

            $cview = ExUlist('chat'.$dirx[$cc]);
            $ccall += $cview;
            $clist[$numvis][2] = $cview;

            $cview = ExView('chat'.$dirx[$cc]);
            $ccv += $cview;
            $clist[$numvis][3] = $cview;

            if (isset($memonly)) {
                $clist[$numvis][4] = $memonly;
            }
            else {
                $clist[$numvis][4] = 0;
            }
            if (!empty($clist[$numvis][4])) {
                unset($lines[$dirx[$cc]]);
            }

            $clist[$numvis][5] = $dirx[$cc];
            $clist[$numvis][6] = $crating;

            $cview = ExAlist('chat'.$dirx[$cc]);
            $ccall += $cview;
            $clist[$numvis][7] = $cview;

            $numvis++;
        }

        MMC_Set('portal.stamp', time(), 900);
        MMC_Set('portal.clist', $clist, 900);
        MMC_Set('portal.popular', $lines, 900);
    }
    $numvis = count($clist);

    $ccall = 0;
    $ccv = 0;
    foreach ($clist as $c) {
        $ccall += $c[2];
        $ccv += $c[3];
    }

    MMC_Unlock('portal.lock');

	if ($_REQUEST['sort'] == 'chat') {
		usort($clist, 'SortListChat');
	}
	else if ($_REQUEST['sort'] == 'active') {
		usort($clist, 'SortListActive');
	}
	else if ($_REQUEST['sort'] == 'view') {
		usort($clist, 'SortListView');
	}
	else if ($_REQUEST['sort'] == 'title') {
		usort($clist, 'SortList');
	}
	else {
		$_REQUEST['sort'] = 'active';
		usort($clist, 'SortListActive');
	}

	$result = mysqli_query($handler, "SELECT DISTINCT ip FROM uo_chat");
	$numall = mysqli_num_rows($result);
	mysqli_free_result($result);

	echo "<table border=0 cellspacing=0 cellpadding=3 bgcolor=#FFFFFF width='90%' align='center' style='width: 90%;'>\n";
	echo "<tr><td align=left><a href=\"?sort=title\"><img src=\"gfx/icon_title.gif\" border=0 alt=\"Chat Title\"></a></td>";
	echo "<td align=center><a href=\"?sort=active\"><img src=\"gfx/icon_info.gif\" border=0 alt=\"Description\"></a></td>";
	echo "<td align=center width=40><a href=\"?sort=chat\"><img src=\"gfx/icon_chat.gif\" border=0 alt=\"Active Chatters\"></a></td>";
	echo "<td align=center width=40><a href=\"?sort=view\"><img src=\"gfx/icon_view.gif\" border=0 alt=\"Current Observers\"></a></td>";
	echo "<td align=right><img src=\"gfx/icon_stat.gif\" border=0 alt=\"Status\"></td></tr>\n";

	echo "<tr><td bgcolor=#000000 colspan=5 style='height:1px; padding: 0px;'> </td></tr>";
	echo "<tr><td align=left style='white-space: nowrap;'>$numvis of $numchat</td><td align=center>";
	if ($_REQUEST['st'] == 'no') {
		setcookie('pJJ-Portal-View', '1', time()+2419200);
		$_REQUEST['st'] = 1;
	}
	else if ($_REQUEST['st'] == 'yes') {
		setcookie('pJJ-Portal-View', '', time()-2419200);
		$_REQUEST['st'] = 0;
		$_COOKIE['pJJ-Portal-View'] = 0;
	}
	if (($_COOKIE['pJJ-Portal-View'] == 1) || ($_REQUEST['st'] == 1)) {
		echo '<a href="?st=yes">Clean Up Descriptions</a>';
		$_REQUEST['st'] = 1;
	}
	else {
		echo '<a href="?st=no">Show Full Descriptions</a>';
	}
	echo "</td><td align=center title='Total/Active'>$ccall/$numact</td><td align=center style='white-space: nowrap;' title='Total/Unique'>$ccv/$numall</td><td align=right>-</td></tr>\n";
	echo "<tr><td bgcolor=#000000 colspan=5 style='height:1px; padding: 0px;'> </td></tr>";

    if (!empty($_REQUEST['xml'])) {
        ob_end_clean();
        header('Content-Type: text/xml');
        $ratings = array('G', 'PG', 'PG13', 'R', 'NC17');
        $modes = array('open', 'moderated', 'locked');
        echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        echo '<chats>'."\n";
        foreach ($clist as $wc) {
            echo '<chat>'."\n";
            echo '<path>'.$wc[5].'</path>'."\n";
            $wc[0] = str_replace('&', '&amp;', $wc[0]);
            $wc[0] = str_replace('<', '&lt;', $wc[0]);
            $wc[0] = str_replace('>', '&gt;', $wc[0]);
            echo '<title>'.$wc[0].'</title>'."\n";
            echo '<rating>'.$ratings[$wc[6]].'</rating>'."\n";
            $wc[1] = str_replace('&', '&amp;', $wc[1]);
            $wc[1] = str_replace('<', '&lt;', $wc[1]);
            $wc[1] = str_replace('>', '&gt;', $wc[1]);
            echo '<description>'.$wc[1].'</description>'."\n";
            echo '<visitors>'.$wc[2].'</visitors>'."\n";
            echo '<chatters>'.$wc[7].'</chatters>'."\n";
            echo '<viewers>'.$wc[3].'</viewers>'."\n";
            echo '<moderation>'.$modes[$wc[4]].'</moderation>'."\n";
            echo '<lastpost>'.$timez[$wc[5]].'</lastpost>'."\n";
            echo '</chat>'."\n";
        }
        echo '</chats>'."\n";
        die();
    }


	while (count($lines) > 10) {
		array_pop($lines);
	}

    if (!empty($lines)) {
    	$map = array();
    	for ($cc=0 ; $cc<count($clist) ; $cc++) {
    		$wc = $clist[$cc];
    		$map[$wc[5]] = $cc;
    	}

    	$nlist = array();
    	foreach ($lines as $chat => $n) {
    		$nlist[] = $clist[$map[$chat]];
    		unset($clist[$map[$chat]]);
    	}
    	$clist = array_merge($nlist, $clist);
    }

	for ($cc=0 ; $cc<count($clist) ; $cc++) {
		$wc = $clist[$cc];
		$nda = ($nda == '#EEEEEE') ? '#FFFFFF' : '#EEEEEE';
        $fullname = htmlentities('"'.$wc[0].'"', ENT_QUOTES);
		if (($_REQUEST['st'] == 0) && (strlen($wc[0]) > 30)) {
			$wc[0] = trim(mb_substr($wc[0], 0, 30));
		}
		$rating = '';
		switch($wc[6]) {
		    case 0:
			$rating = "[<font color='#00FF00'>G</font>]";
			break;

		    case 1:
			$rating = "[<font color='#40C000'>PG</font>]";
			break;

		    case 2:
			$rating = "[<font color='#808000'>PG13</font>]";
			break;

		    case 3:
			$rating = "[<font color='#C04000'>R</font>]";
			break;

		    case 4:
			$rating = "[<font color='#FF0000'>NC17</font>]";
			break;
		}
		echo "<tr bgcolor='$nda' valign='top' align='center' title='$fullname'><td align=left><a href='$wc[5]/' title='Click to enter $fullname'>$wc[0]</a></td>";

		if (empty($_REQUEST['st'])) {
			$wc[1] = str_replace('<br>','[BR]', $wc[1]);
			$wc[1] = strip_tags(str_replace('<br />','[BR]', $wc[1]));
			$wc[1] = str_replace('[BR]',' ', $wc[1]);
			if (strlen($wc[1]) > 150) {
				$wc[1] = trim(mb_substr($wc[1], 0, 150));
			}
			echo "<td style='font-size: 75%;'>$wc[1] $rating</td>";
		}
		else {
			echo "<td><div style='max-height: 150px; overflow: hidden;'>$wc[1]</div> $rating</td>";
		}

		echo "<td>$wc[7]</td><td>$wc[3]</td><td align=right style='white-space: nowrap;'><a href='$wc[5]/' title='Click to enter $fullname'>";

		if ($wc[4] == 1) {
			echo "Mod";
		}
		else if ($wc[4] == 2) {
			echo "Lock";
		}
		else {
			echo "Open";
		}

		echo " </a></td></tr>\n";

		if ($cc == 9) {
			echo "<tr><td bgcolor=#000000 colspan=5 style='height:1px; padding: 0px;'> </td></tr>";
		}

        /*
		if (
		    (($clist[$cc+1][2] == 0) && ($clist[$cc][2] != 0) && ($_REQUEST['sort'] == "chat"))
		    || (($clist[$cc+1][3] == 0) && ($clist[$cc][3] != 0) && ($_REQUEST['sort'] == "view" || $_REQUEST['sort'] == "active"))
		    ) {
			echo "<tr><td bgcolor=#000000 colspan=5 style='height:1px; padding: 0px;'> </td></tr>";
		}
        //*/
	}

	echo "<tr><td bgcolor=#000000 colspan=5 style='height:1px; padding: 0px;'> </td></tr>";
	echo "<tr><td align=left>$numvis of $numchat</td><td align=center>-</td><td align=center>$ccall</td><td align=center>$ccv ($numall)</td><td align=right>-</td></tr>\n";
	echo "<tr><td bgcolor=#000000 colspan=5 style='height:1px; padding: 0px;'> </td></tr>";

	echo "<tr><td align=left><a href=\"?sort=title\"><img src=\"gfx/icon_title.gif\" border=0 alt=\"Chat Title\"></a></td>";
	echo "<td align=center><a href=\"?sort=active\"><img src=\"gfx/icon_info.gif\" border=0 alt=\"Description\"></a></td>";
	echo "<td align=center width=40><a href=\"?sort=chat\"><img src=\"gfx/icon_chat.gif\" border=0 alt=\"Chatters\"></a></td>";
	echo "<td align=center width=40><a href=\"?sort=view\"><img src=\"gfx/icon_view.gif\" border=0 alt=\"Observers\"></a></td>";
	echo "<td align=right><img src=\"gfx/icon_stat.gif\" border=0 alt=\"Status\"></td></tr>\n";

	echo "</table>";

?>
</center>
<table cellspacing="0" cellpadding="0" border="0" align="center"><tr><td>
<blockquote>
<font color="#0783FF">Chat Title:</font> Very simple, and exactly what the name says.<br>
<font color="#0783FF">Description:</font> The chat theme/setting as the admin writes it.<br>
<font color="#0783FF">Chatters:</font> Number of people in the chat.<br>
<font color="#0783FF">Observers:</font> Number of people looking on the chat. Includes chatters.<br>
<font color="#0783FF">Status:</font> Open, Moderated or Locked. Access ranges from View/Chat over View/NoChat to NoView/NoChat for unregistered users.
</blockquote>
</td></tr></table>
<br>
<div align="center">
    <a href="legal/">Terms of Service</a>
    |
    <a href="mailto:legal@projectjj.com">Copyright 1999-<?=date('Y');?> Project JJ</a>
</div>
<script src="https://www.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">_uacct = "UA-87771-3"; urchinTracker();</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="gfx/dn_tile.gif" align="center" valign="bottom" height="32"><center><a href="https://tinodidriksen.com/" target=_blank><img src="gfx/worlds.gif" border="0"></a></center></td></tr>
</table>
</body>
</html>
