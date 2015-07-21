<?php
	ob_start();
	
/*
DROP TABLE IF EXISTS tmpa ;
CREATE TABLE tmpa AS SELECT T.* FROM uo_chat_threads as T LEFT JOIN uo_chat_boards as B ON (T.id=B.uid) WHERE flags IS NOT NULL ;
TRUNCATE uo_chat_threads ;
INSERT INTO uo_chat_threads SELECT * FROM tmpa ;
//*/

	/*
	if ($_SERVER['REMOTE_ADDR'] != '62.198.254.119') {
		header('HTTP/1.0 403 Temporarily Disabled');
	    die ('Temporarily disabled due to spam overload.');
	}
	//*/
	
	if (!empty($_REQUEST) && !empty($_REQUEST['post'])) {
		require_once('../../../chatv3/_inc/mmcache.php');
		$last = intval(MMC_Get('jbb.'.$_SERVER['REMOTE_ADDR']));
		MMC_Set('jbb.'.$_SERVER['REMOTE_ADDR'], max(time(), $last) + 10);
		if (time() < $last) {
			header('HTTP/1.0 403 Too Soon');
			die ('You cannot post again that fast.');
		}
		sleep(2);
	}

	set_magic_quotes_runtime(0);
	if (get_magic_quotes_gpc()) {
		foreach($_REQUEST as $key => $value) {
			$_REQUEST[$key] = stripslashes($value);
		}
		foreach($_COOKIE as $key => $value) {
			$_COOKIE[$key] = stripslashes($value);
		}
	}

	require_once("../../common/session.php");
	require_once("../settings.php");
	require_once("../options.php");

	require_once("../../common/language.php");
	if (file_exists("../language.php")) {
		require_once("../language.php");
	}

	$realpath = ereg_replace(".*/([^/]+)/jbb/.*$", 'chat\1', $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
		$realpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
	}

	if (!empty($altdata)) {
		$chatpath = $altdata;
	}
	else {
		$chatpath = $realpath;
	}

	require_once("../../common/proxy.php");
	require_once("../../common/banhelp.php");
	require_once("../../mysql.php");
	require_once("../../setup.php");

	if (empty($_REQUEST['password']) && !empty($_SESSION[$realpath]['user']['password'])) {
		$_REQUEST['password'] = $_SESSION[$realpath]['user']['password'];
	}
	else if (!empty($_REQUEST['password']) && strlen($_REQUEST['password']) != 32 && strcmp($_SESSION[$realpath]['user']['password'], md5($_REQUEST['password'])) == 0) {
		$_REQUEST['password'] = $_SESSION[$realpath]['user']['password'];
	}
	if (empty($_REQUEST['username'])) {
		if (!empty($_SESSION[$realpath]['user']['displayname'])) {
			$_REQUEST['username'] = $_SESSION[$realpath]['user']['displayname'];
		}
		else if (!empty($_SESSION[$realpath]['user']['username'])) {
			$_REQUEST['username'] = $_SESSION[$realpath]['user']['username'];
		}
	}

	if (!empty($_SESSION[$realpath]['ident'])) {
		$ident = $_SESSION[$realpath]['ident'];
	}
	else {
		$ident = mb_substr(md5($_SERVER['REMOTE_ADDR'].$realpath), 0, $identlenght);
	}

	
	if (!empty($_REQUEST) && !empty($_REQUEST['post'])) {
		require_once '../../common/helpers.php';
		$userlevel = ChatVerifyLogin($_REQUEST['username'], $_REQUEST['password'], $chatpath);
		if ($userlevel == -1 || empty($userlevel)) {
			$words = array(
				'viagra',
				'cialis',
				'xanax',
				'kamagra',
				'zithromax',
				'nolvadex',
				'levitra',
				'tramadol',
				'ultram',
				'alprazolam',
				);
			foreach ($_REQUEST as $k => $v) {
				foreach ($words as $w) {
					if (preg_match('@\b'.$w.'\b@u', $v) || preg_match('@<a.*?\[url=.*?\[link=@us', $v)) {
						header('HTTP/1.0 403 Spam');
						header('Content-Type: text/plain');
						echo "We think your post is spam:\n\n";
						echo var_export($_REQUEST, true), "\n";
						exit(0);
					}
				}
			}

			require_once '../../common/akismet.php';
			$GLOBALS['akismet_home'] = str_replace('{PATH}', substr($realpath, 4), $GLOBALS['akismet_home']);
			if (akismet_check($_REQUEST)) {
				header('HTTP/1.0 403 Spam');
				header('Content-Type: text/plain');
				echo "Akismet thinks your post is spam:\n\n";
				echo var_export($_REQUEST, true), "\n";
				exit(0);
			}
		}
	}

	ChatSessionSuspend();

	$xcnt = count($banip);
	$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	for ($cc=0;$cc<$xcnt;$cc++) {
		if (
		    ($ident == $banip[$cc])
		    || (strncmp($_SERVER['REMOTE_ADDR'], $banip[$cc], strlen($banip[$cc])) == 0)
		    || (strncmp($_SERVER['HTTP_X_FORWARDED_FOR'], $banip[$cc], strlen($banip[$cc])) == 0)
		    ) {
			echo "<html><head></head>$cbodytag";
			$ox = $banguage[4];
			$ox = str_replace('{IDENT}', $ident, $ox);
			echo $ox;
			echo "</body></html>";
			exit();
		}
		else if (strpos($banip[$cc], '.') !== false || strpos($banip[$cc], '*') !== false) {
			$banip[$cc] = str_replace('\\*', '.*', preg_quote($banip[$cc]));
			if (preg_match('/^'.$banip[$cc].'$/is', $_SERVER['REMOTE_HOST'])) {
				echo "<html><head></head>\n$cbodytag";
				$ox = $banguage[4];
				$ox = str_replace('{IDENT}', $ident, $ox);
				echo $ox;
				echo "</body></html>";
				exit();
			}
		}
	}

	$oldident = $ident;
	if (!empty($_COOKIE['pJJChat_Banned'])) {
		$ident = $_COOKIE['pJJChat_Banned'];
	}

	if (CheckBan($ident, $chatpath) == 0) {
		$ident = $oldident;

		if ($proxyblock == 1 && empty($_SESSION[$realpath]['user']['uid'])) {
			$bl = Proxy_IsProxy($_SERVER['REMOTE_ADDR']);
			if ($bl !== false) {
				echo "This chat blocks open proxies, and you are using one. You have been banned for 8 hours.";
				echo "<br>The list that caught you is: <a href='$bl'>$bl</a>";
				echo "</body></html>";
				AddBan($ident, time()+28800, '[proxy]', $chatpath);
				setcookie("pJJChat_Banned", $ident, time()+604800);
				die();
			}
		}
	}
	else {
		setcookie("pJJChat_Banned", $ident, time()+604800);
		echo "<html><head></head>\n$cbodytag";
		echo $language[12];
		echo "</body></html>";
		exit();
	}

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
		$jbbc[5] = "http://pjj.cc/gfx/modbar.gif";
	if (empty($jbbc[6]))
		$jbbc[6] = "http://pjj.cc/gfx/up_tile.gif";
	if (empty($jbbc[7]))
		$jbbc[7] = "http://pjj.cc/gfx/up_tile.gif";
	if (empty($jbbc[8]))
		$jbbc[8] = "http://pjj.cc/gfx/dn_tile.gif";
	if (empty($jbbc[9]))
		$jbbc[9] = "http://pjj.cc/gfx/newthread.gif";
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

	$c = count($jbbc);
	for($i=0;$i<$c;$i++) {
		if (strstr($jbbc[$i], '../../')) {
			$jbbc[$i] = str_replace('../../', 'http://pjj.cc/', $jbbc[$i]);
		}
	}
?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Board for <? echo $ctitle; ?> (Project JJ)</title>
</head>

<body text="#<? echo $jbbc[0]; ?>" bgcolor=#<? echo $jbbc[4]; ?> link="#<? echo $jbbc[1]; ?>" vlink="#<? echo $jbbc[3]; ?>" alink="#<? echo $jbbc[2]; ?>" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="<? echo $jbbc[7]; ?>" valign="top" align="left"><img src="<? echo $jbbc[7]; ?>" border=0></td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%" width=32><img src="http://pjj.cc/gfx/null.gif" width=32 border=0></td>
	<td valign="top" height="100%">
<img src="http://pjj.cc/gfx/null.gif" height=32 border=0><br>
<?php

function ShowThreads($chatpath) {
	global $handler, $ctitle, $jbbc;

	require_once '../../common/helpers.php';

	$buttons = "<a href='?act=new' class='boardbtn btnnewthread'>New Thread</a> | <a href='?act=src' class='boardbtn btnsearch'>Search</a>";

	echo "<b>Board for <i>$ctitle</i></b><p>\n";

	echo "<table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
	echo "<tr bgcolor=#$jbbc[10]><td colspan=6 background=\"$jbbc[6]\">$buttons</td></tr>";
	echo "<tr bgcolor=#$jbbc[11]><td>OC</td><td>Topic</td><td>Author</td><td>Posts</td><td>Last Post (".date("T").")</td><td>Hits</td></tr>\n";

	$result = @mysql_query("SELECT uid,flags,topic,username,utime,hits FROM uo_chat_boards WHERE chat='$chatpath' AND flags LIKE '%S%' AND flags NOT LIKE '%D%' ORDER BY topic ASC", $handler);
	while($thread = mysql_fetch_row($result)) {
		$posts = @mysql_query("SELECT id FROM uo_chat_threads WHERE chat='$chatpath' AND id='$thread[0]'", $handler);
		$posts = mysql_num_rows($posts);
		echo "<tr bgcolor=#$jbbc[10]><td>$thread[1]</td><td>&nbsp;<a href=\"?pid=$thread[0]\">".htmlentities($thread[2])."</a></td><td><a href=\"../register/viewer.php?frames=view&selecteduser=".(urlencode($thread[3]))."\" target=_blank>".htmlentities(ucwords($thread[3]))."</a></td><td>$posts</td><td>".(date("g:ia, F d, Y", $thread[4]))."</td><td>".$thread[5]."</td></tr>\n";
	}
	@mysql_free_result($result);

	$result = @mysql_query("SELECT uid,flags,topic,username,utime,hits FROM uo_chat_boards WHERE chat='$chatpath' AND flags NOT LIKE '%S%' AND flags NOT LIKE '%D%' ORDER BY $jbbc[14] $jbbc[15]", $handler);
	while($thread = mysql_fetch_row($result)) {
		$posts = @mysql_query("SELECT id FROM uo_chat_threads WHERE chat='$chatpath' AND id='$thread[0]'", $handler);
		$posts = mysql_num_rows($posts);
		echo "<tr bgcolor=#$jbbc[10]><td>$thread[1]</td><td>&nbsp;<a href=\"?pid=$thread[0]\">".htmlentities($thread[2])."</a></td><td><a href=\"../register/viewer.php?frames=view&selecteduser=".(urlencode($thread[3]))."\" target=_blank>".htmlentities(ucwords($thread[3]))."</a></td><td>$posts</td><td>".(date("g:ia, F d, Y", $thread[4]))."</td><td>".$thread[5]."</td></tr>\n";
	}
	@mysql_free_result($result);

	echo "<tr bgcolor=#$jbbc[10]><td colspan=6 background=\"$jbbc[6]\">$buttons</td></tr>";
	echo "</table>\n";
}

function ShowPosts($chatpath, $pid) {
	global $handler, $jbbc;

	$result = @mysql_query("SELECT topic,flags FROM uo_chat_boards WHERE chat='$chatpath' AND uid='$pid' AND flags NOT LIKE '%D%'", $handler);
	$thread = mysql_fetch_array($result);
	@mysql_free_result($result);

	$buttons = '';
	$buttons .= "<a href='?' class='boardbtn btnindex'>Index</a> | <a href='?act=new' class='boardbtn btnnewthread'>New Thread</a>";
	if (strpos($thread['flags'], 'C') !== false) {
		$buttons .= " | <a href='?act=opn&pid=$pid' class='boardbtn btnopen'>Open</a>";
	}
	else {
		$buttons .= " | <a href='?act=rep&pid=$pid' class='boardbtn btnreply'>Reply</a> | <a href='?act=cls&pid=$pid' class='boardbtn btnclose'>Close</a>";
	}
	if (strpos($thread['flags'], 'S') !== false) {
		$buttons .= " | <a href='?act=unstick&pid=$pid' class='boardbtn btnunstick'>Unstick</a>";
	}
	else {
		$buttons .= " | <a href='?act=stick&pid=$pid' class='boardbtn btnstick'>Stick</a>";
	}
	$buttons .= " | <a href='?act=del&pid=$pid' class='boardbtn btndelete'>Delete</a>";

	echo "<b>Thread for <i>".htmlentities($thread[0])."</i></b><p>";

	echo "<table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
	echo "<tr bgcolor=#$jbbc[10]><td colspan=2 background=\"$jbbc[6]\">$buttons</td></tr>";

	$result = @mysql_query("SELECT chat,id,utime,topic,post,username FROM uo_chat_threads WHERE chat='$chatpath' AND id='$pid' AND dtime IS NULL ORDER BY utime ASC", $handler);
	while($post = mysql_fetch_row($result)) {
		echo "<tr bgcolor=#$jbbc[11]><td colspan=2><b>&nbsp;".htmlentities($post[3])."</b></td></tr>\n";
		$post[4] = preg_replace('@<tr><br />@i', '<tr>', $post[4]);
		$post[4] = preg_replace('@</tr><br />@i', '</tr>', $post[4]);
		$post[4] = preg_replace('@</tr>.?.?.?<br />.?.?.?<tr@is', '</tr><tr', $post[4]);
		$post[4] = preg_replace('@</td><br />@i', '</td>', $post[4]);
		echo "<tr valign=top><td bgcolor=#$jbbc[12]><a href=\"../register/viewer.php?frames=view&selecteduser=".(urlencode($post[5]))."\" target=_blank>".htmlentities(ucwords($post[5]))."</a><br>".(date("g:ia, F d, Y", $post[2]))."<br>[<a href=\"?pid=$pid&utime=$post[2]&act=mod\">Edit</a>]<br>[<a href=\"?pid=$pid&eid=$post[2]&act=pdel\">Delete</a>]</td><td bgcolor=#$jbbc[10]>".(str_replace("ï¿½","'",$post[4]))."</td></tr>\n";
	}
	@mysql_free_result($result);

	echo "<tr bgcolor=#$jbbc[10]><td colspan=2 background=\"$jbbc[6]\">$buttons</td></tr>";
	echo "</table>\n";

	@mysql_query("UPDATE uo_chat_boards SET hits=hits+1 WHERE chat='$chatpath' AND uid='$pid'", $handler);
}

function ShowReplyCreateBox($chatpath, $pid, $act, $but, $show=0, $username, $password) {
	global $handler, $jbblock, $ctitle, $jbbc;

	if ($jbblock > 0) {
		echo "<i>Note: Only members of <b>$ctitle</b> can post or reply on this board.</i><p>";
	}
	echo "<form action=index.php method=post>\n";
	echo "<table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
	echo "<tr bgcolor=#$jbbc[12]><td>Username</td><td><input type=text size=20 name=username value=\"".htmlentities($username)."\"></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[10]><td>Password</td><td><input type=password size=10 name=password value=\"$password\"></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[12]><td>Topic</td><td><input type=text name=topic size=40></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[10]><td colspan=2><textarea name=post cols=70 rows=17></textarea></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[11]><td colspan=2 align=right><input type=submit value=\"$but\"></td></tr>";
	echo "</table>\n";
	echo "<input type=hidden name=pid value=$pid><input type=hidden name=act value=$act></form>\n";
	echo "<i>Any HTML, JavaScript, CSS, and such tags can be used on the board.</i><p>";

	if ($show >= 1) {
		ShowPosts($chatpath, $pid, $handler);
	}
}

function ShowEditBox($chatpath, $pid, $username, $password, $timer) {
	global $handler, $jbbc;

	$result = mysql_query("SELECT chat,id,utime,topic,post,username FROM uo_chat_threads WHERE chat='$chatpath' AND id='$pid' AND utime='$timer'", $handler);
	$thread = mysql_fetch_row($result);
	@mysql_free_result($result);

	$thread[4] = str_replace("<br>","",$thread[4]);
	$thread[4] = str_replace("<br />","",$thread[4]);
	echo "<form action=index.php method=post>\n";
	echo "<table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
	echo "<tr bgcolor=#$jbbc[12]><td>Username</td><td><input type=text size=20 name=username value=\"".htmlentities($thread[5])."\"></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[10]><td>Password</td><td><input type=password size=10 name=password></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[12]><td>Topic</td><td><input type=text name=topic size=40 value=\"".htmlentities($thread[3])."\"></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[10]><td colspan=2><textarea name=post cols=50 rows=12>".htmlentities($thread[4])."</textarea></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[11]><td colspan=2 align=right><input type=submit value=\"Edit $pid\"></td></tr>";
	echo "</table>\n";
	echo "<input type=hidden name=utime value=$timer><input type=hidden name=pid value=$pid><input type=hidden name=act value=xmod></form>\n";

	ShowPosts($chatpath, $pid, $handler);
}

function ShowModBox($chatpath, $pid, $act, $but, $username, $password, $eid="") {
	global $handler, $jbbc;

	echo "<form action=index.php method=post>\n";
	echo "<table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
	echo "<tr bgcolor=#$jbbc[12]><td>Action</td><td>$but thread $pid</td></tr>\n";
	echo "<tr bgcolor=#$jbbc[12]><td>Username</td><td><input type=text size=20 name=username value=\"".htmlentities($username)."\"></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[10]><td>Password</td><td><input type=password size=10 name=password value=\"$password\"></td></tr>\n";
	echo "</table><input type=submit value=\"$but $pid\">\n";
	echo "<input type=hidden name=pid value=$pid><input type=hidden name=eid value=$eid><input type=hidden name=act value=$act></form>\n";
}

function ShowSearchBox($chatpath) {
	global $handler, $jbbc;

	echo "<form action=index.php method=post>\n";
	echo "<table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
	echo "<tr bgcolor=#$jbbc[12] valign=top><td>Phrase</td><td><input type=text size=40 name=phrase></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[10] valign=top><td><select name=maxfind><option value=10>10\n<option value=20>20\n<option value=30>30\n<option value=40>40\n<option value=50>50\n<option value=75>75\n<option value=100>100\n<option value=150>150\n<option value=99999>All\n</select></td><td align=right>\nTopics:<input type=checkbox name=src_topic checked><br>\n";
	echo "Bodies: <input type=checkbox name=src_body checked><br>\n";
	echo "Authors: <input type=checkbox name=src_auth></td></tr>\n";
	echo "<tr bgcolor=#$jbbc[11] align=right><td colspan=2><input type=submit value=\"Search\"></td></tr></table>\n";
	echo "<input type=hidden name=act value=xsrc></form>\n";
}

function DoReply($chatpath, $pid, $topic, $post, $username, $password) {
	global $handler, $jbblock, $jbbc;

	require_once '../../common/helpers.php';
	$userlevel = ChatVerifyLogin($username, $password, $chatpath);
	if ($userlevel == -1) {
		$username = "Impersonating $username";
		$userlevel = 0;
	}

	if (($jbblock > 0) && (!$userlevel)) {
		echo "<center>This board can only be used by members of the chat.<br>";
		echo "...<a href=\"?pid=$pid\">Return</a>...</center>";
		return 0;
	}

	$result = @mysql_query("SELECT flags FROM uo_chat_boards WHERE chat='$chatpath' AND uid='$pid'", $handler);
	$thrd = mysql_fetch_row($result);
	@mysql_free_result($result);

	if (strpos($thrd[0], 'O') !== false) {
		$topic = strip_tags($topic);
		$post = nl2br($post);

		@mysql_query("INSERT INTO uo_chat_threads SET chat='$chatpath',id='$pid',utime='".(time())."',topic='".
			mysql_escape_string($topic)."',post='".
			mysql_escape_string($post)."',post_org='".
			mysql_escape_string($post)."',username='".
			mysql_escape_string($username)."'", $handler);
		@mysql_query("UPDATE uo_chat_boards SET utime='".(time())."' WHERE chat='$chatpath' AND uid='$pid'", $handler);
	}
	else {
		echo "<center>Thread Closed</center>";
	}

	echo "<center>...<a href=\"?pid=$pid\">Return</a>...</center>";
}

function DoEdit($chatpath, $pid, $topic, $post, $username, $password, $timer) {
	global $handler, $jbbc;

	require_once '../../common/helpers.php';

	$result = @mysql_query("SELECT username FROM uo_chat_threads WHERE chat='$chatpath' AND id='$pid' AND utime='$timer'", $handler);
	$thrd = mysql_fetch_row($result);
	@mysql_free_result($result);

	if ($thrd[0] == $username) {
		$etype = ", author.";
		$userflags = ChatVerifyLogin($thrd[0], $password, $chatpath);
	}
	else {
		$userflags = ChatVerifyLogin($username, $password, $chatpath);
		if (CheckFlags("xXZmM", $userflags)) {
			$etype = ", moderative.";
		}
		else {
			$userflags = -1;
		}
	}

	if ($userflags != -1) {
		$result = @mysql_query("SELECT flags FROM uo_chat_boards WHERE chat='$chatpath' AND uid='$pid'", $handler);
		$thrd = mysql_fetch_row($result);
		@mysql_free_result($result);

		if ((strpos($thrd[0], 'O') !== false) || (CheckFlags("xXZmM", $userflags))) {
			$topic = strip_tags($topic);

			$post = explode("\n", $post);
			$last = array_pop($post);
			if (!strstr($last, "<i>Edited ")) {
				array_push($post, $last);
			}
			else {
				array_pop($post);
			}
			$post = implode("\n", $post);

			$post .= "\n\n<font size=-2 style=\"font-size: 7pt;\"><i>Edited ".(date("g:ia, F d, Y", time()))." by ".(htmlentities(ucwords($username)))."<!-- {$_SERVER['REMOTE_ADDR']} -->$etype</i></font>";
			$post = nl2br($post);

			@mysql_query("UPDATE uo_chat_threads SET post='".
				mysql_escape_string($post)."',topic='".
				mysql_escape_string($topic)."' WHERE chat='$chatpath' AND id='$pid' AND utime='$timer'", $handler);
			//@mysql_query("UPDATE uo_chat_boards SET utime='".(time())."' WHERE chat='$chatpath' AND id='$pid'", $handler);
		}
		else {
			echo "<center>Thread Closed</center>";
		}
	}
	else {
		echo "<center>False Login</center>";
	}

	echo "<center>...<a href=\"?pid=$pid\">Return</a>...</center>";
}

function DoMod($chatpath, $pid, $username, $password, $act) {
	global $handler, $jbbc;

	require_once '../../common/helpers.php';
	$userflag = ChatVerifyLogin($username, $password, $chatpath);
	if (CheckFlags("xXZmM",$userflag)) {
		if ($act == 's') {
			@mysql_query("UPDATE uo_chat_boards SET flags=REPLACE(flags,'S','') WHERE chat='$chatpath' AND uid='$pid'", $handler);
		}
		else if ($act == 'S') {
			@mysql_query("UPDATE uo_chat_boards SET flags=CONCAT(flags,'S') WHERE chat='$chatpath' AND uid='$pid'", $handler);
		}
		else if ($act == 'O') {
			@mysql_query("UPDATE uo_chat_boards SET flags=REPLACE(flags,'C','O') WHERE chat='$chatpath' AND uid='$pid'", $handler);
		}
		else if ($act == 'C') {
			@mysql_query("UPDATE uo_chat_boards SET flags=REPLACE(flags,'O','C') WHERE chat='$chatpath' AND uid='$pid'", $handler);
		}
		echo "<center>...<a href=\"?\">Return</a>...</center>";
	}
	else {
		echo "<center>False Login</center>";
	}
}

function DoDelete($chatpath, $pid, $username, $password) {
	global $handler, $jbbc;

	require_once '../../common/helpers.php';
	$userflag = ChatVerifyLogin($username, $password, $chatpath);
	if (CheckFlags("xXZmM",$userflag)) {
		@mysql_query("UPDATE uo_chat_boards SET flags=CONCAT(flags,'D') WHERE chat='$chatpath' AND uid='$pid'", $handler);

		echo "<center>...<a href=\"?\">Return</a>...</center>";
	}
	else {
		echo "<center>False Login</center>";
	}
}

function DoDeletePost($chatpath, $pid, $eid, $username, $password) {
	global $handler, $jbbc;

	require_once '../../common/helpers.php';
	$userflag = ChatVerifyLogin($username, $password, $chatpath);
	if (CheckFlags("xXZmM",$userflag)) {
		@mysql_query("UPDATE uo_chat_threads SET dtime=now() WHERE chat='$chatpath' AND id='$pid' AND utime='$eid'", $handler);

		echo "<center>...<a href=\"?pid=$pid\">Return</a>...</center>";
	}
	else {
		echo "<center>False Login</center>";
	}
}

function DoCreate($chatpath, $topic, $post, $username, $password) {
	global $handler, $jbblock, $jbbc;

	require_once '../../common/helpers.php';
	$userlevel = ChatVerifyLogin($username, $password, $chatpath);
	if ($userlevel == -1) {
		$username = "Impersonating $username";
		$userlevel = 0;
	}

	if (($jbblock > 0) && (!$userlevel)) {
		echo "<center>This board can only be used by members of the chat.<br>";
		echo "...<a href=\"?pid=$pid\">Return</a>...</center>";
		//echo '<pre>', var_export($_REQUEST, true), '</pre>';
		return 0;
	}

	$topic = strip_tags($topic);
	if (empty($topic))
		$topic = "No Topic";

	$post = nl2br($post);
	if (empty($post))
		$post = "No Post";

	@mysql_query("INSERT INTO uo_chat_boards SET chat='$chatpath',flags='O',utime='".(time())."',topic='".
		mysql_escape_string($topic)."',username='".
		mysql_escape_string($username)."',ctime='".(time())."'", $handler);

	$pid = @mysql_insert_id($handler);

	@mysql_query("INSERT INTO uo_chat_threads SET chat='$chatpath',id='$pid',utime='".(time())."',topic='".
		mysql_escape_string($topic)."',post='".
		mysql_escape_string($post)."',post_org='".
		mysql_escape_string($post)."',username='".
		mysql_escape_string($username)."'", $handler);

	echo "<center>Thread <b>".htmlentities($topic)."</b> created with ID <b>$pid</b>.<p>\n...<a href=\"?\">Return</a>...</center>";
}

function DoSearch($chatpath, $phrase, $src_topic, $src_body, $src_auth, $maxfind) {
	global $handler, $jbbc;

	echo "...$chatpath, $phrase, $src_topic, $src_body, $src_auth, $maxfind<p>";

	if (!empty($phrase)) {
		$phrase = mysql_escape_string($phrase);

		if ($src_topic == "on") {
			echo "<b>Topics</b><br><table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
			echo "<tr bgcolor=#$jbbc[11]><td>Topic</td><td>Author</td><td>Posts</td><td>Last Post (".date("T").")</td></tr>\n";
			$found=0;

			$result = mysql_query("SELECT uid,topic,username,utime FROM uo_chat_boards WHERE chat='$chatpath' AND topic LIKE '%$phrase%' ORDER BY utime DESC", $handler);
			while(($thread = mysql_fetch_row($result)) && ($found < $maxfind)) {
				$found++;
				$post = mysql_query("SELECT count(id) FROM uo_chat_threads WHERE chat='$chatpath' AND id='$thread[0]'", $handler);
				$posts = mysql_fetch_row($post);
				$posts = $posts[0];
				@mysql_free_result($post);

				echo "<tr bgcolor=#$jbbc[10]><td><a href=\"?pid=$thread[0]\">".(empty($thread[1]) ? ("No Topic") : ($thread[1]))."</a></td><td>".htmlentities(ucwords($thread[2]))."</td><td>$posts</td><td>".(date("g:ia, F d, Y", $thread[3]))."</td></tr>\n";
			}
			@mysql_free_result($result);
			echo "</table><p>\n";
		}
		if ($src_body == "on") {
			echo "<b>Bodies</b><br><table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
			echo "<tr bgcolor=#$jbbc[11]><td>Topic</td><td>Author</td><td>Posts</td><td>Last Post (".date("T").")</td></tr>\n";
			$found=0;

			$result = mysql_query("SELECT id,topic,username,utime FROM uo_chat_threads WHERE chat='$chatpath' AND post LIKE '%$phrase%' ORDER BY utime DESC", $handler);
			while(($thread = mysql_fetch_row($result)) && ($found < $maxfind)) {
				$found++;
				$post = mysql_query("SELECT count(id) FROM uo_chat_threads WHERE chat='$chatpath' AND id='$thread[0]'", $handler);
				$posts = mysql_fetch_row($post);
				$posts = $posts[0];
				@mysql_free_result($post);

				echo "<tr bgcolor=#$jbbc[10]><td><a href=\"?pid=$thread[0]\">".(empty($thread[1]) ? ("No Topic") : ($thread[1]))."</a></td><td>".htmlentities(ucwords($thread[2]))."</td><td>$posts</td><td>".(date("g:ia, F d, Y", $thread[3]))."</td></tr>\n";
			}
			@mysql_free_result($result);
			echo "</table><p>\n";
		}
		if ($src_auth == "on") {
			echo "<b>Authors</b><br><table cellspacing=1 cellpadding=2 border=0 bgcolor=#$jbbc[13]>\n";
			echo "<tr bgcolor=#$jbbc[11]><td>Topic</td><td>Author</td><td>Posts</td><td>Last Post (".date("T").")</td></tr>\n";
			$found=0;

			$result = mysql_query("SELECT id,topic,username,utime FROM uo_chat_threads WHERE chat='$chatpath' AND username LIKE '%$phrase%' ORDER BY utime DESC", $handler);
			while(($thread = mysql_fetch_row($result)) && ($found < $maxfind)) {
				$found++;
				$post = mysql_query("SELECT count(id) FROM uo_chat_threads WHERE chat='$chatpath' AND id='$thread[0]'", $handler);
				$posts = mysql_fetch_row($post);
				$posts = $posts[0];
				@mysql_free_result($post);

				echo "<tr bgcolor=#$jbbc[10]><td><a href=\"?pid=$thread[0]\">".(empty($thread[1]) ? ("No Topic") : ($thread[1]))."</a></td><td>".htmlentities(ucwords($thread[2]))."</td><td>$posts</td><td>".(date("g:ia, F d, Y", $thread[3]))."</td></tr>\n";
			}
			@mysql_free_result($result);
			echo "</table>\n";
		}
	}
	else {
		echo "Search phrase empty.";
	}
}

	$username = trim($_REQUEST['username']);
	if (empty($username)) {
		$username = "Anonymous";
	}

	if ($_REQUEST['act'] == "xmod") {
		DoEdit($chatpath, $_REQUEST['pid'], $_REQUEST['topic'], $_REQUEST['post'], $username, $_REQUEST['password'], $_REQUEST['utime']);
	}
	else if ($_REQUEST['act'] == "mod") {
		ShowEditBox($chatpath, $_REQUEST['pid'], $username, $_REQUEST['password'], $_REQUEST['utime']);
	}
	else if ($_REQUEST['act'] == "xsrc") {
		DoSearch($chatpath, $_REQUEST['phrase'], $_REQUEST['src_topic'], $_REQUEST['src_body'], $_REQUEST['src_auth'], $_REQUEST['maxfind']);
	}
	else if ($_REQUEST['act'] == "src") {
		ShowSearchBox($chatpath);
	}

	else if ($_REQUEST['act'] == "xpdel") {
		DoDeletePost($chatpath, $_REQUEST['pid'], $_REQUEST['eid'], $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['act'] == "pdel") {
		ShowModBox($chatpath, $_REQUEST['pid'], "xpdel", "Delete Post", $username, $_REQUEST['password'], $_REQUEST['eid']);
	}
	else if ($_REQUEST['act'] == "xdel") {
		DoDelete($chatpath, $_REQUEST['pid'], $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['act'] == "del") {
		ShowModBox($chatpath, $_REQUEST['pid'], "xdel", "Delete", $username, $_REQUEST['password']);
	}

	else if ($_REQUEST['act'] == "xopn") {
		DoMod($chatpath, $_REQUEST['pid'], $username, $_REQUEST['password'], 'O');
	}
	else if ($_REQUEST['act'] == "opn") {
		ShowModBox($chatpath, $_REQUEST['pid'], "xopn", "Open", $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['act'] == "xcls") {
		DoMod($chatpath, $_REQUEST['pid'], $username, $_REQUEST['password'], 'C');
	}
	else if ($_REQUEST['act'] == "cls") {
		ShowModBox($chatpath, $_REQUEST['pid'], "xcls", "Close", $username, $_REQUEST['password']);
	}

	else if ($_REQUEST['act'] == "xstick") {
		DoMod($chatpath, $_REQUEST['pid'], $username, $_REQUEST['password'], 'S');
	}
	else if ($_REQUEST['act'] == "stick") {
		ShowModBox($chatpath, $_REQUEST['pid'], "xstick", "Stick", $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['act'] == "xunstick") {
		DoMod($chatpath, $_REQUEST['pid'], $username, $_REQUEST['password'], 's');
	}
	else if ($_REQUEST['act'] == "unstick") {
		ShowModBox($chatpath, $_REQUEST['pid'], "xunstick", "Unstick", $username, $_REQUEST['password']);
	}

	else if ($_REQUEST['act'] == "xnew") {
		DoCreate($chatpath, $_REQUEST['topic'], $_REQUEST['post'], $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['act'] == "xrep") {
		DoReply($chatpath, $_REQUEST['pid'], $_REQUEST['topic'], $_REQUEST['post'], $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['act'] == "rep") {
		ShowReplyCreateBox($chatpath, $_REQUEST['pid'], "xrep", "Reply", 1, $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['act'] == "new") {
		ShowReplyCreateBox($chatpath, $_REQUEST['pid'], "xnew", "Create", 0, $username, $_REQUEST['password']);
	}
	else if ($_REQUEST['pid']) {
		ShowPosts($chatpath, $_REQUEST['pid']);
	}
	else {
		ShowThreads($chatpath);
	}

?>
<center><img src="http://pjj.cc/gfx/null.gif" border=0 height=32></center>
</td>
	<td valign="top" align="right" height="100%" width=32><img src="http://pjj.cc/gfx/null.gif" width=32 border=0></td></tr>
	</table>
</td></tr>
<tr><td background="<? echo $jbbc[8]; ?>" align="center" valign="bottom"><img src="<? echo $jbbc[8]; ?>" border=0></td></tr>
</table>
</body>
</html>
