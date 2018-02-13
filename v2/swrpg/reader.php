<?php
//	ob_start();

	mt_srand((double)microtime()*1000000);
	$cqs = 0;
	$creas = '';

function getmicrotime() {
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

$start = getmicrotime();

function EncodeLtGt($str) {
	$str = html_entity_decode($str, ENT_NOQUOTES);
	$str = html_entity_decode($str, ENT_NOQUOTES);
	$str = str_replace('&', '&amp;', $str);
	$str = str_replace('<', '&lt;', $str);
	$str = str_replace('>', '&gt;', $str);
	return $str;
}

function GenerateXML($last=0, $info=0) {
	global $realpath, $handler, $maxlines, $ctitle, $nview;

	header('Content-Type: text/xml');

	$rez = count_mysql_query("SELECT UNIX_TIMESTAMP(MAX(posttime)) as stamp FROM uo_chat_log WHERE chat='$realpath'", $handler, "reader.php: GenerateXML() 1/1");
	$row = mysqli_fetch_assoc($rez);
	mysqli_free_result($rez);

	if (!empty($last) && $last >= intval($row['stamp'])) {
		header('HTTP/1.1 304 Not Modified');
		die();
	}

//	$last = 0;
	$last = intval(MMC_Get($realpath.'.xml.last'));
	if (empty($info) && !empty($last) && $last >= intval($row['stamp'])) {
		$output = MMC_Get($realpath.'.xml.output');
		if (!empty($output)) {
			echo $output;
			echo '<!-- cached version -->', "\n";
			die();
		}
	}

	MMC_Set($realpath.'.xml.last', intval($row['stamp']), 900);

	$tz = date('Z');
	$path = mb_substr($realpath, 4);

	$rez = @count_mysql_query("SELECT count(chat) FROM uo_chat_ulist WHERE chat='$realpath' AND utime>".(time()-300)."", $handler, "reader.php: GenerateXML() 1/1");
	$active = mysqli_fetch_row($rez);
	$active = intval($active[0]);
	mysqli_free_result($rez);

	$rez = @count_mysql_query("SELECT count(chat) FROM uo_chat_ulist WHERE chat='$realpath'", $handler, "reader.php: GenerateXML() 1/1");
	$chatters = mysqli_fetch_row($rez);
	$chatters = intval($chatters[0]);
	mysqli_free_result($rez);

	$output = '';
	$output .= '<'.'?xml version="1.0" encoding="UTF-8"?'.">\n";
	$xinfo = <<<XMLEND
<info>
	<chat>{$path}</chat>
	<title>{$ctitle}</title>
	<lastpost>{$row['stamp']}</lastpost>
	<timezone>{$tz}</timezone>
	<viewers>{$nview}</viewers>
	<chatters>{$chatters}</chatters>
	<active>{$active}</active>
</info>
XMLEND;

	if (!empty($info)) {
		echo $output;
		echo $xinfo;
		return;
	}

	$output .=  <<<XMLEND
<reader>
	$xinfo
	<lines>
XMLEND;
	$rez = @count_mysql_query("SELECT
		UNIX_TIMESTAMP(posttime) as stamp,ident,username,line,rawpost,
		xmlpost,color
		FROM uo_chat_log
		WHERE
		chat='$realpath'
		ORDER BY posttime DESC LIMIT $maxlines
		", $handler, "reader.php: GenerateXML() 1/1");

	for ($i=0 ; $row = mysqli_fetch_assoc($rez) ; $i++) {
		$row['line'] = EncodeLtGt($row['line']);
		$row['rawpost'] = EncodeLtGt($row['rawpost']);
		$row['xmlpost'] = EncodeLtGt($row['xmlpost']);
		$output .=  <<<XMLEND

		<line>
			<id>{$i}</id>
			<ident>{$row['ident']}</ident>
			<color>#{$row['color']}</color>
			<username>{$row['username']}</username>
			<posttime>{$row['stamp']}</posttime>
			<fullpost>{$row['line']}</fullpost>
			<rawpost>{$row['rawpost']}</rawpost>
			<post>{$row['xmlpost']}</post>
		</line>
XMLEND;
	}

	$output .=  <<<XMLEND

	</lines>
</reader>

XMLEND;

	echo $output;
	echo '<!-- generated fresh -->', "\n";
	MMC_Set($realpath.'.xml.output', $output, 900);
}

	$realpath = preg_replace('@.*/([^/]+)/reader.php$@', '\1', $_SERVER['PHP_SELF']);
	$chatpath = $realpath;

	echo "\n<!-- Before init: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
	require_once('../common/session.php');
	require_once('../../chatv3/_inc/mmcache.php');
	require_once('../mysql.php');
	echo "\n<!-- Mid init: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
	require_once('../setup.php');
	require_once('../'.$realpath.'/settings.php');
	require_once('../common/helpers.php');
	require_once('../std_uo.php');
	require_once('../'.$realpath.'/options.php');
	require_once('../common/banhelp.php');
	require_once('../common/ignore.php');
	require_once('../common/lastmsg.php');
	require_once('../common/proxy.php');

	require_once('../common/language.php');
	if (file_exists('../'.$realpath.'/language.php')) {
		require_once('../'.$realpath.'/language.php');
	}

	if (empty($dtcalc)) {
		$dtcalc = 'g:ia, F d (T)';
	}

	GetChatPrefs($realpath);
	$GLOBALS['biglog']['real_id'] = $GLOBALS['biglog']['chat_id'];
	GetChatPrefs($chatpath);

	$realpath = 'chat'.$realpath;
	$chatpath = $realpath;

	$inlinemsg = '<p><b>This chat has no valid Master Email contact. Please provide one in Settings.</b><p>';
	if (strpos($cadmin, '@') && strpos($cadmin, '.')) {
		$inlinemsg = '';
	}

	if (!$_REQUEST['cspeed']) {
		$_REQUEST['cspeed'] = 'default';
	}

	if ($_REQUEST['cspeed'] != 'default') {
		$respeed = $_REQUEST['cspeed'];
	}

	if (!empty($_SESSION[$realpath]['ident'])) {
		$ident = $_SESSION[$realpath]['ident'];
	}
	else {
		$ident = mb_substr(md5($_SERVER['REMOTE_ADDR'].$realpath), 0, $identlenght);
	}

	$bodytag = str_replace('<body ', '<body class="reader" ', $bodytag);
	$cbodytag = str_replace('<body ', '<body class="reader" ', $cbodytag);
	$ubodytag = str_replace('<body ', '<body class="reader" ', $ubodytag);

	echo "\n<!-- Before X-Forward: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
	$xcnt = count($banip);
	$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	for ($cc=0;$cc<$xcnt;$cc++) {
		if (
		    (strcmp($ident, $banip[$cc]) == 0)
		    || (strncmp($_SERVER['REMOTE_ADDR'], $banip[$cc], strlen($banip[$cc])) == 0)
		    || (strncmp($_SERVER['HTTP_X_FORWARDED_FOR'], $banip[$cc], strlen($banip[$cc])) == 0)
		    ) {
			echo '<html><head></head>', $cbodytag;
			$ox = $banguage[4];
			$ox = str_replace('{IDENT}', $ident, $ox);
			echo $ox;
			echo '</body></html>';
			exit();
		}
		else if (strpos($banip[$cc], '.') !== false || strpos($banip[$cc], '*') !== false) {
			$banip[$cc] = str_replace('\\*', '.*', preg_quote($banip[$cc]));
			if (preg_match('/^'.preg_quote($banip[$cc], '/').'$/is', $_SERVER['REMOTE_HOST'])) {
				echo '<html><head></head>', $cbodytag;
				$ox = $banguage[4];
				$ox = str_replace('{IDENT}', $ident, $ox);
				echo $ox;
				echo '</body></html>';
				exit();
			}
		}
	}
	echo "\n<!-- After X-Forward: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";

	if ($_REQUEST['p'] == 'colorpicker') {
		$cp = file_get_contents('../common/colorpick.html');
		$cp = str_replace('{TITLE}', $ctitle, $cp);
		if (!empty($csshead)) {
			$cp = str_replace('{CHATCSS}', '<style type="text/css">'.$csshead.'</style>', $cp);
		}
		else {
			$cp = str_replace('{CHATCSS}', '', $cp);
		}
		$cp = str_replace('{CHATTEXT}', $servcol, $cp);
		$cp = str_replace('{CHATLINK}', $s_link, $cp);
		$cp = str_replace('{CHATVLINK}', $s_visit, $cp);
		$cp = str_replace('{CHATALINK}', $s_active, $cp);
		$cp = str_replace('{CHATBGCOLOR}', $s_bgcol, $cp);
		$cp = str_replace('{CHATBACKGROUND}', $s_bgimg, $cp);
		echo $cp;
	}
	else if (!empty($_REQUEST['inspect']) || $_REQUEST['p'] == 'inspect') {
		echo "<html>\n";
		echo <<<HEADER
<!DOCTYPE html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<title>pJJ Session/Cookie Inspection</title>
</head>
<body>
<pre>
<b>Session:</b>
HEADER;
		print_r($_SESSION);
		echo "\n\n<b>Cookies:</b>\n";
		print_r($_COOKIE);
		echo "</pre>";
	}
	else if (!empty($_REQUEST['xml']) || $_REQUEST['p'] == 'xml') {
		$_REQUEST['handle'] = strtolower(ereg_replace($master_name_filter, '', $_REQUEST['handle']));
		$userlevel = ChatVerifyLogin($_REQUEST['handle'], $_REQUEST['password'], $chatpath);
		if ($memonly <= 1 || $userlevel > 0) {
			UpdateViewers($realpath);
			$nview = GetViewers($realpath);
			GenerateXML(intval($_REQUEST['lastpost']), $_REQUEST['xmlinfo']);
			die();
		}
		else {
			header('HTTP/1.1 403 Access Denied');
			die();
		}
//*
    }
	else if (!empty($_REQUEST['urls']) || $_REQUEST['p'] == 'urls') {
		header('X-Robots-Tag: noindex, nofollow, noarchive');
		echo <<<HEADER
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW, NOARCHIVE">
	<title>{$ctitle}</title>
	<style type='text/css'>
	$csshead
	</style>
</head>
{$bodytag}
<p><a href="reader.php" target="TextWindow">Back to chat</a></p>

HEADER;

		if (!isset($urlblock)) {
			$urlblock = $logblock;
		}
        $pass = 1;
        if ($urlblock == 1 && !CheckFlags('1', $_SESSION[$realpath]['flags'])) {
            $pass = 0;
		}
        if ($urlblock == 2 && !CheckFlags('MmZxX', $_SESSION[$realpath]['flags'])) {
            $pass = 0;
		}
        if ($urlblock == 3 && !CheckFlags('MmZX', $_SESSION[$realpath]['flags'])) {
            $pass = 0;
		}

        if ($pass == 0 && $urlblock == 3) {
            echo "<b>URLs are currently locked to anyone below administrator status.</b>";
        }
		else if ($pass == 0 && $urlblock == 2) {
            echo "<b>URLs are currently locked to anyone below moderators status.</b>";
        }
		else if ($pass == 0) {
            echo "<b>URLs are currently blocked for anyone not logged in to the chat.</b>";
        }
		else {
            $query = "SELECT url_href, url_poster FROM chatv2.seen_urls WHERE url_chat = {$GLOBALS['biglog']['chat_id']} ORDER BY url_id DESC LIMIT 200";
            $rez = $GLOBALS['sql']->query($query);
            $count = $GLOBALS['sql']->numRows($rez);
            if (!empty($count)) {
                echo <<<STUFF
                <center>
                <h2>Recently Posted URLs</h2>
                <table cellspacing="0" cellpadding="3" border="0" class="urltable">
                <tr valign="top">
                    <td><b>URL</b></td>
                    <td><b>Poster</b></td>
                </tr>
STUFF;
                for ($i=0;$i<$count;$i++) {
                    $row = $GLOBALS['sql']->fetchAssoc($rez, $i);
                    echo <<<STUFF
                    <tr valign="top">
                        <td><a href="{$row['url_href']}" target="_blank" rel="nofollow">{$row['url_href']}</a></td>
                        <td>{$row['url_poster']}</td>
                    </tr>
STUFF;
                }
                echo <<<STUFF
                </table>
                <p><a href="reader.php" target="TextWindow">Back to chat</a></p>
                </center>
STUFF;
            }
			else {
                echo <<<STUFF
                <b>No urls seen in this chat yet.</b>
                <p><a href="reader.php" target="TextWindow">Back to chat</a></p>
STUFF;
            }
        }
//*/
	}
	else if (!empty($_REQUEST['msgs']) || $_REQUEST['p'] == 'msgs') {
		echo '<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Messages for ', $_SESSION[$realpath]['handle'], '</title>
	<style type="text/css">
	.message .time {
		font-size: 75%;
	}

	', $csshead, '
	</style>
	<script type="text/javascript" src="https://pjj.cc/common/js/functions.js"></script>
	<base target="TextWindow">
</head>', $bodytag;

		echo '<a href="reader.php" target="TextWindow">Back to chat</a>', "\n";

		unset($_REQUEST['al']);
		if (file_exists(__DIR__.'/reader-backdoor.php')) {
			require_once __DIR__.'/reader-backdoor.php';
		}

		$msgs = array();
		$uids = array();
		$oldest = 2147483647;
		$_REQUEST['uid'] = intval($_REQUEST['uid']);
		if (empty($_REQUEST['uid'])) {
			$_REQUEST['uid'] = intval($_SESSION[$realpath]['user']['uid']);
		}
		$_REQUEST['offset'] = intval($_REQUEST['offset']);
		$_REQUEST['limit'] = intval($_REQUEST['limit']);
		if (empty($_REQUEST['limit']) || $_REQUEST['limit'] < 50) {
			$_REQUEST['limit'] = 50;
		}
		else if ($_REQUEST['limit'] > 5000 && empty($_REQUEST['al'])) {
			$_REQUEST['limit'] = 5000;
		}

		$order = 'DESC';
		if ($_REQUEST['order'] == 'asc') {
			$order = 'ASC';
		}

		if (empty($_SESSION['uids'])) {
			$_SESSION['uids'] = array();
		}
		if (!empty($_SESSION[$realpath]['user']['uid']) && empty($_SESSION['uids'][$_SESSION[$realpath]['user']['uid']])) {
			$_SESSION['uids'][$_SESSION[$realpath]['user']['uid']] = $_SESSION[$realpath]['user'];
		}

		if (empty($_SESSION['uids'][$_REQUEST['uid']]) && empty($_REQUEST['al'])) {
			echo '<p>', $banguage[5], '</p>'; // Unregistered user
		}
		else {
			if ($_REQUEST['cleanmsg'] == 1) {
				count_mysql_query("UPDATE uo_chat_message SET archived='yes' WHERE rcpt_uid=".$_REQUEST['uid']." AND unread='no'", $handler);
			}
			else {
				count_mysql_query("UPDATE uo_chat_message SET unread='no' WHERE rcpt_uid=".$_REQUEST['uid']." AND unread='yes'", $handler);
			}

			// Fetch all messages sent to me which are not archived
			$query = "SELECT message_id,msg,rcpt_uid,auth_uid,auth,UNIX_TIMESTAMP(msg_stamp) as utime FROM uo_chat_message
				WHERE rcpt_uid = ".$_REQUEST['uid']." AND archived='no' ORDER BY message_id DESC LIMIT ".$_REQUEST['limit'];
			if (!empty($_REQUEST['offset'])) {
				$query = "SELECT message_id,msg,rcpt_uid,auth_uid,auth,UNIX_TIMESTAMP(msg_stamp) as utime FROM uo_chat_message
					WHERE rcpt_uid = ".$_REQUEST['uid']." AND message_id < ".$_REQUEST['offset']." ORDER BY message_id DESC LIMIT ".$_REQUEST['limit'];
			}
			$rez = count_mysql_query($query, $handler);
			while ($row = mysqli_fetch_assoc($rez)) {
				$msgs[$row['message_id']] = $row;
				$uids[intval($row['rcpt_uid'])] = intval($row['rcpt_uid']);
				$uids[intval($row['auth_uid'])] = intval($row['auth_uid']);
				$oldest = min($oldest, $row['message_id']);
			}
			mysqli_free_result($rez);

			if (empty($msgs)) {
				echo '<p>You do not have any messages.';
				if (empty($_REQUEST['offset'])) {
					// Count all messages sent to me which are archived
					$count = 0;
					$query = "SELECT count(*) as cnt FROM uo_chat_message
						WHERE rcpt_uid = ".$_REQUEST['uid']." AND archived='yes'";
					$rez = count_mysql_query($query, $handler);
					while ($row = mysqli_fetch_assoc($rez)) {
						$count += $row['cnt'];
					}
					mysqli_free_result($rez);
					if (!empty($count)) {
						echo ' <a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;offset=2147483647">View Archive (', $count, ' messages)</a>';
					}
				}
				echo '</p>';
			}
			else {
				// Fetch all messages I've sent that are newer than the oldest non-archived message I've received
				$query = "SELECT message_id,msg,rcpt_uid,auth_uid,username,UNIX_TIMESTAMP(msg_stamp) as utime FROM uo_chat_message
					WHERE auth_uid = ".$_REQUEST['uid']." AND message_id >= $oldest ORDER BY message_id DESC";
				if (!empty($_REQUEST['offset'])) {
					$query = "SELECT message_id,msg,rcpt_uid,auth_uid,username,UNIX_TIMESTAMP(msg_stamp) as utime FROM uo_chat_message
						WHERE auth_uid = ".$_REQUEST['uid']." AND message_id < ".$_REQUEST['offset']." AND message_id >= $oldest ORDER BY message_id DESC";
				}
				$rez = count_mysql_query($query, $handler);
				while ($row = mysqli_fetch_assoc($rez)) {
					$msgs[$row['message_id']] = $row;
					$uids[intval($row['rcpt_uid'])] = intval($row['rcpt_uid']);
					$uids[intval($row['auth_uid'])] = intval($row['auth_uid']);
				}
				mysqli_free_result($rez);

				// Fetch one message I've sent that is older than the oldest non-archived message I've received
				$query = "SELECT message_id,msg,rcpt_uid,auth_uid,username,UNIX_TIMESTAMP(msg_stamp) as utime FROM uo_chat_message
					WHERE auth_uid = ".$_REQUEST['uid']." AND message_id < $oldest ORDER BY message_id DESC LIMIT 1";
				$rez = count_mysql_query($query, $handler);
				while ($row = mysqli_fetch_assoc($rez)) {
					$msgs[$row['message_id']] = $row;
					$uids[intval($row['rcpt_uid'])] = intval($row['rcpt_uid']);
					$uids[intval($row['auth_uid'])] = intval($row['auth_uid']);
				}
				mysqli_free_result($rez);

				// Fetch names for every UID seen so far
				sort($uids);
				$uids = array_unique($uids);
				$uids = '('.implode(',', $uids).')';
				$query = "SELECT uid,username,displayname,pcolor FROM uo_chat_database
					WHERE uid IN ".$uids;
				$rez = count_mysql_query($query, $handler);
				$uids = array();
				while ($row = mysqli_fetch_assoc($rez)) {
					if (empty($row['displayname'])) {
						$row['displayname'] = ucwords($row['username']);
					}
					if (empty($row['pcolor'])) {
						$row['pcolor'] = $servcol;
					}
					$row['pcolor'] = FixColor($row['pcolor']);
					$uids[$row['uid']] = $row;
				}
				mysqli_free_result($rez);

				krsort($msgs);

				echo '<p><form action="reader.php" method="post" target="TextWindow">
				<input type="submit" value="Archive Messages">
				<input type="hidden" name="p" value="msgs">
				<input type="hidden" name="cleanmsg" value="1">
				<input type="hidden" name="uid" value="', $_REQUEST['uid'], '">
				</form>', "\n";

				echo '<p>Navigate: <a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'">Beginning</a>,
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=50&amp;offset=', $oldest, '">Next 50</a>
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=250&amp;offset=', $oldest, '">Next 250</a>
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=500&amp;offset=', $oldest, '">Next 500</a>
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=2000&amp;offset=', $oldest, '">Next 2000</a>
				</p>', "\n";

				echo '<p><table class="message" cellspacing="0" cellpadding="2" border="1" style="width:100%;table-layout:fixed;">
				<tr valign="top">
					<th class="from">From</th>
					<th class="to">To</th>
					<th class="msg" width="70%">Message</th>
					<th class="time">Time</th>
				</tr>', "\n";

				if ($order == 'ASC') {
					$msgs = array_reverse($msgs, true);
				}

				foreach ($msgs as $id => $msg) {
					if (empty($msg['rcpt_uid'])) {
						echo '<tr valign="top" title="Message ID: ', $msg['message_id'],'">
						<td class="from">', htmlentities($uids[$msg['auth_uid']]['displayname']), '</a></td>
						<td class="to">', htmlentities($msg['username']), '</td>
						<td class="msg"><span style="color: #', htmlentities($uids[$msg['auth_uid']]['pcolor']),';">', $msg['msg'], '</span></td>
						<td class="time">', date($dtcalc, $msg['utime']), '</td>
						</tr>', "\n";
					}
					else if (empty($msg['auth_uid'])) {
						echo '<tr valign="top" title="Message ID: ', $msg['message_id'],'">
						<td class="from">', htmlentities($msg['auth']), '</a></td>
						<td class="to">', htmlentities($uids[$msg['rcpt_uid']]['username']), '</td>
						<td class="msg">', $msg['msg'], '</td>
						<td class="time">', date($dtcalc, $msg['utime']), '</td>
						</tr>', "\n";
					}
					else if ($msg['auth_uid'] == $_REQUEST['uid']) {
						echo '<tr valign="top" title="Message ID: ', $msg['message_id'],'">
						<td class="from">', htmlentities($uids[$msg['auth_uid']]['displayname']), '</td>
						<td class="to"><a href="#" onclick="setConsoleChannel(\'', htmlentities($uids[$msg['rcpt_uid']]['username']), '\');">', htmlentities($uids[$msg['rcpt_uid']]['displayname']), '</a></td>
						<td class="msg"><span style="color: #', htmlentities($uids[$msg['auth_uid']]['pcolor']),';">', $msg['msg'], '</span></td>
						<td class="time">', date($dtcalc, $msg['utime']), '</td>
						</tr>', "\n";
					}
					else {
						echo '<tr valign="top" title="Message ID: ', $msg['message_id'],'">
						<td class="from"><a href="#" onclick="setConsoleChannel(\'', htmlentities($uids[$msg['auth_uid']]['username']), '\');">', htmlentities($uids[$msg['auth_uid']]['displayname']), '</a></td>
						<td class="to">', htmlentities($uids[$msg['rcpt_uid']]['displayname']), '</td>
						<td class="msg"><span style="color: #', htmlentities($uids[$msg['auth_uid']]['pcolor']),';">', $msg['msg'], '</span></td>
						<td class="time">', date($dtcalc, $msg['utime']), '</td>
						</tr>', "\n";
					}
				}
				echo '</table>', "\n";
				echo '<p>Navigate: <a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'">Beginning</a>,
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=50&amp;offset=', $oldest, '">Next 50</a>
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=250&amp;offset=', $oldest, '">Next 250</a>
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=500&amp;offset=', $oldest, '">Next 500</a>
				<a href="reader.php?p=msgs&amp;uid=', $_REQUEST['uid'],'&amp;limit=2000&amp;offset=', $oldest, '">Next 2000</a>
				</p>', "\n";
			}
			echo '<p><a href="reader.php" target="TextWindow">Back to chat</a></p>', "\n";
		}
	}
	else {
		$random = mt_rand(1,2147483647);
		$down = (($_REQUEST['reverse'] == "on") ? ("#down") : (""));
		echo "\n<!-- Head: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
		$rpath = mb_substr($realpath, 4);
		$down = ($_REQUEST['reverse'] == 'on' ? '#down' : '');
		echo <<<HEADER
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	<META NAME="ROBOTS" CONTENT="NOFOLLOW, NOARCHIVE">
	<meta http-equiv="refresh" content="$respeed;URL=reader.php?cspeed={$_REQUEST['cspeed']}&reverse={$_REQUEST['reverse']}{$down}&random=$random$down">
	<title>{$ctitle}</title>
	<link href="https://pjj.cc/common/css/common.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="https://pjj.cc/common/js/functions.js"></script>
	<script type="text/javascript">
	var refreshID;
	function RefreshPage() {
		window.location = 'https://pjj.cc/$rpath/reader.php?cspeed={$_REQUEST['cspeed']}&reverse={$_REQUEST['reverse']}&random=$random$down';
	}
	function RefreshPageDelayed() {
		if (refreshID) {
			clearTimeout(refreshID);
			refreshID = null;
		}
		refreshID = setTimeout(RefreshPage, 500+Math.floor(Math.random()*1000+1));
	}
	refreshID = setTimeout(RefreshPage, (($respeed*1000)+1000));
	</script>
	<style type='text/css'>
	$csshead
	</style>
</head>
{$bodytag}
HEADER;

		$oldident = $ident;
		if (!empty($_COOKIE['pJJChat_Banned'])) {
			$ident = $_COOKIE['pJJChat_Banned'];
		}

		if (CheckBan($ident, $chatpath) == 0) {
			echo "\n<!-- After CheckBan: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
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

			echo "\n<!-- After Proxy: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
			UpdateViewers($realpath);
			$nview = GetViewers($realpath);

			if (($memonly <= 1) || CheckFlags('1', $_SESSION[$realpath]['flags'])) {
				echo "<base target='_blank'><center>";
				if (!empty($logofile)) {
					echo "<a href='$logolink' target=_blank><img border=0 src='$logofile'></a>";
				}
				if (file_exists('register/motd.dat')) {
					ParseMotD();
				}
				echo "</center><br>";

				if ($_COOKIE['pJJChat_NoColor'] == "on") {
					echo "<style> * {color: #{$servcol}}</style>";
				}

				readfile("../announce.dat");
				echo "<base target='_blank' href=''>";

				if ($_REQUEST['reverse'] == "on") {
					$lines = MMC_Get("$realpath.rache");
					if (empty($lines)) {
						CacheChatLines();
						$lines = MMC_Get("$realpath.rache");
					}
					if ($_COOKIE['pJJChat_NoColor'] == "on") {
						echo str_replace("color=", "", $lines);
						unset($lines);
					}
					else {
						echo $lines;
					}
					echo $inlinemsg;
				}
				else if (!CheckAnyIgnore($realpath, $ident)) {
					echo $inlinemsg;
					$lines = MMC_Get($realpath.'.cache');
					if (empty($lines)) {
						CacheChatLines();
						$lines = MMC_Get($realpath.'.cache');
					}
					if ($_COOKIE['pJJChat_NoColor'] == 'on') {
						echo str_replace("color=", "", $lines);
						unset($lines);
					}
					else {
						echo $lines;
					}
				}
				else {
					$result = count_mysql_query("SELECT ident,line,username FROM uo_chat_log WHERE chat='$realpath' ORDER BY posttime DESC", $handler);
					while($line = mysqli_fetch_assoc($result)) {
						$line['line'] = stripslashes($line['line']);
						if (!CheckIgnore($line['ident'])) {
							if ($pJJChat_NoColor == "on") {
								$line['line'] = str_replace("color=", "", $line['line']);
							}
							echo str_replace("", "'", $line['line']);
						}
					}
					mysqli_free_result($result);
				}

				if ($lastpos > 0) {
					echo "<p><font color='$servcol'>";
					echo $timer;
					echo date($dtcalc, GetLastMsg($realpath)+($tzone*3600));
					echo " ($nview)</font>";
				}
			}
			else {
				echo $language[0];
			}
			setcookie("pJJChat_Banned", "", 0);
			echo "<a name='down' id='down'></a>";

			if ($_REQUEST['reverse'] == 'on') {
				echo <<<XSCRIPT
<script type="text/javascript">
var down = document.getElementById('down');
if (down && down.scrollIntoView) {
	down.scrollIntoView();
}
</script>

XSCRIPT;
			}
		}
		else {
			setcookie("pJJChat_Banned", $ident, time()+604800);
			echo $language[12];
		}
	}

	echo "<br><br> Debug: ".round(getmicrotime()-$start, 2)." secs / $cqs queries";
//	echo "<!-- Debug: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->";
	echo "<!-- ";
	print_r($GLOBALS['querylog']);
//	echo "\n";
//	print_r($_SESSION);
	echo " -->";
	echo "</body></html>";
