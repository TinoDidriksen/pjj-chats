<?php

	$die = false;

    if (stripos($_SERVER['HTTP_USER_AGENT'], 'Slurp') !== false
    || stripos($_SERVER['HTTP_USER_AGENT'], 'MJ12bot') !== false
    || stripos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') !== false
    || stripos($_SERVER['HTTP_USER_AGENT'], 'bingbot') !== false
    || stripos($_SERVER['HTTP_USER_AGENT'], 'adidxbot') !== false
    || stripos($_SERVER['HTTP_USER_AGENT'], 'msnbot') !== false) {
        header('Crawling Denied', true, 403);
        $die = true;
    }

    ini_set('default_charset', 'UTF-8');
    mb_internal_encoding('UTF-8');
    mb_http_output('UTF-8');
	ob_start();
    ignore_user_abort();

	/*
	if (!preg_match('/^62.198.254.119$/', $_SERVER['REMOTE_ADDR'])) {
		die('Being worked on...');
	}
	//*/
?>
<html>
<head>
    <META NAME="ROBOTS" CONTENT="NOINDEX, NOFOLLOW, NOARCHIVE">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Logs</title>

<script type="text/javascript">
if (window != window.top)
  top.location.href = location.href;
</script>

</head>

<body text="#FFFFFF" bgcolor="#000000" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<center><img src="http://pjj.cc/gfx/null.gif" border=0></center>

<?php
    if ($die) {
        die('</body></html>');
    }

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

	require_once '../../common/pgsql/sql.php';
	require_once '../../common/session.php';
	require_once '../../../chatv3/_inc/mmcache.php';
	require_once '../../mysql.php';
	require_once '../../setup.php';
	require_once '../settings.php';
	require_once '../options.php';
	require_once '../../common/tome_of_power.php';
	require_once '../../common/log.php';

	$pass = 1;
	$realpath = preg_replace('@.*/([^/]+)/register/dblog.php$@is', 'chat\1', $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
		$realpath = preg_replace('@(.*?)\.pjj\.cc@is', 'chat\1', $_SERVER['HTTP_HOST']);
	}

	$cpref = GetChatPrefs($realpath);

	$output = '';

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

	if (file_exists(__DIR__.'/dblog-backdoor.php')) {
		require_once __DIR__.'/dblog-backdoor.php';
	}

	ChatSessionSuspend();

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
        MMC_Lock('chats.global.log.lock');
        $GLOBALS['sql']->begin();
        $GLOBALS['sql']->query('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
		if (empty($_REQUEST['a'])) {
            $query = "
                SELECT count(stamp) as cnt, stamp_year as dyear, stamp_week as dweek
                FROM chatv2logs.log_{$GLOBALS['biglog']['chat_id']}
                GROUP BY stamp_year, stamp_week
                ORDER BY stamp_year, stamp_week
                ";
			$rez = $GLOBALS['sql']->query($query);
			if ($numrows = $GLOBALS['sql']->numRows($rez)) {
				$output .= "<br>Browse:";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400))."\">Last 24 hours</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*3))."\">Last 72 hours</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*3))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*7))."\">Last week</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*7))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*14))."\">Last fortnight</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*14))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*31))."\">Last month</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*31))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*92))."\">Last season</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*92))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25))."\">Last year</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*2))."\">Last 2 years</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*2))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*3))."\">Last 3 years</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*3))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*4))."\">Last 4 years</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*4))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*5))."\">Last 5 years</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*5))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*10))."\">Last 10 years</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=".htmlentities(date('Y-m-d H:i:s', time()-86400*365.25*10))."\">download</a>)";
				$output .= "<br> - <a href=\"dblog.php?a=1&amp;from=0001-01-01%2001:01:01\">Last aeon</a> (<a href=\"dblog.php?a=1&amp;download=1&amp;from=0001-01-01%2001:01:01\">download</a>)";
				$output .= "<br><br>Or browse individual weeks. Format is: Year/Week: Lines<div>";
				for ($i=0;$i<$numrows;$i++) {
                    $row = $GLOBALS['sql']->fetchAssoc($rez, $i);
					$output .= "<div style='float: left;'>&nbsp;<a href='dblog.php?year={$row['dyear']}&amp;week={$row['dweek']}&amp;a=1'>{$row['dyear']}/{$row['dweek']}</a>: {$row['cnt']}&nbsp;</div>\n";
				}
				$output .= "</div>";
			}
			else {
				$output .= "<b>Logs are empty...</b>";
			}
			$GLOBALS['sql']->freeResult($rez);
		}
		else {
			$output .= "<a href='dblog.php'>Return to overview</a>...<br>\n";

			// Initialization
			$count = "SELECT count(stamp) as cnt FROM chatv2logs.log_{$GLOBALS['biglog']['chat_id']} WHERE true=true ";
			$fetch =
				"SELECT message_id,
				message,
				user_id,
				user_name,
				to_char(stamp, 'YYMMDD HH24:MI') as shortstamp,
				to_char((stamp - '15 min'::interval)::timestamp, 'YYYY-MM-DD HH24:MI:SS') as contextstamp
				FROM chatv2logs.log_{$GLOBALS['biglog']['chat_id']}
				WHERE true=true ";
			$cond = "";
			$pages = "";

			// Input sanitation
			$_REQUEST['year'] = abs(intval($_REQUEST['year']));
			$_REQUEST['week'] = abs(intval($_REQUEST['week']));
			$_REQUEST['limit'] = abs(intval($_REQUEST['limit']));
			$_REQUEST['offset'] = abs(intval($_REQUEST['offset']));
			$_REQUEST['from'] = $GLOBALS['sql']->escapeString($_REQUEST['from']);

			if (!empty($_REQUEST['keywords'])) {
				if (!is_array($_REQUEST['keywords'])) {
					if (strpos($_REQUEST['keywords'], ' ') === false) {
						$_REQUEST['keywords'] = array($_REQUEST['keywords']);
					}
					else {
						$_REQUEST['keywords'] = preg_replace("@[ \t\r\n]+@s", ' ', $_REQUEST['keywords']);
						$_REQUEST['keywords'] = explode(' ', $_REQUEST['keywords']);
					}
				}
				$keyw = array();
				foreach($_REQUEST['keywords'] as $val) {
					$val = trim($val);
					if (!empty($val)) {
                        $enc = mb_detect_encoding($val, 'UTF-8, ISO-8859-1');
                        //$val = utf8_decode($val);
						$val = iconv($enc, 'UTF-8//IGNORE', $val);
                        //$val = mb_convert_encoding($val, 'UTF-8', $enc);
                        $keyw[] = $val;
					}
				}
				unset($_REQUEST['keywords']);
				$keyw = array_unique($keyw);
				sort($keyw);
				$_REQUEST['keywords'] = $keyw;
			}
			else {
				unset($_REQUEST['keywords']);
			}

			$entry = 0;
			if ($_REQUEST['entry'] == 'last') {
				$entry = 1;
			}
			else if ($_REQUEST['entry'] == 'first') {
				$entry = 2;
			}
			unset($_REQUEST['entry']);

			// Input fixup
			if (empty($_REQUEST['limit'])) {
				$_REQUEST['limit'] = 500;
			}
			else if ($_REQUEST['limit'] > 20000) {
				$_REQUEST['limit'] = 20000;
            }

            // Input forwarding
			$limit = $_REQUEST['limit'];
			$offset = $_REQUEST['offset'];
			unset($_REQUEST['limit']);
			unset($_REQUEST['offset']);
			$qid = http_build_query($_REQUEST, '', '&amp;');
			$_REQUEST['limit'] = $limit;
			$_REQUEST['offset'] = $offset;

			// Query building
			if (!empty($_REQUEST['year'])) {
				$cond .= " AND stamp_year={$_REQUEST['year']}";
			}
			if (!empty($_REQUEST['week'])) {
				$cond .= " AND stamp_week={$_REQUEST['week']}";
			}
			if (!empty($_REQUEST['from'])) {
				$cond .= " AND stamp>='{$_REQUEST['from']}'::timestamp";
			}
			if (!empty($_REQUEST['keywords']) && is_array($_REQUEST['keywords'])) {
				foreach($_REQUEST['keywords'] as $val) {
					if ($val[0] == '!') {
						$val = mb_substr($val, 1);
						$cond .= " AND message NOT ILIKE '%".$GLOBALS['sql']->escapeString($val)."%'";
					}
					else {
						$cond .= " AND message ILIKE '%".$GLOBALS['sql']->escapeString($val)."%'";
					}
				}
			}

			if (!empty($_REQUEST['download'])) {
				while (ob_end_clean()) {
					// no body...
				}

				$load = floatval(file_get_contents('/proc/loadavg'));
				if ($load >= 10.0) {
					echo "Load too high ($load, where limit is 10.0), try again later...\n";
					die();
				}

				chdir('/tmp/');
				$uniq = 'pjjchatlog_'.$GLOBALS['biglog']['chat_id'].'_'.substr(sha1('waffles'.uniqid().rand()), 0, 8);
				$fz = fopen($uniq.'.html', 'wb');

				$html = <<<XOUT
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>$uniq</title>
</head>
{$bodytag}

XOUT;

				fwrite($fz, $html);

				set_time_limit(300);
				$cond .= " ORDER BY message_id ASC";
				$rez = $GLOBALS['sql']->query($fetch.$cond);
				if ($numfetched = $GLOBALS['sql']->numRows($rez)) {
					set_time_limit(300);
					for ($i=0;$i<$numfetched;$i++) {
						$row = $GLOBALS['sql']->fetchAssoc($rez, $i);
						$row['message'] = log_SanitizeLine($row['message']);
						$output = "<div>";
						$output .= "[{$row['shortstamp']}]&nbsp;";
						$output .= "<span title='Line ID {$row['message_id']}; Author ".htmlspecialchars($row['user_name'], ENT_QUOTES)." (UID {$row['user_id']})'>{$row['message']}</span>";
						$output .= "</div>\n";
						fwrite($fz, $output);
					}
				}
				$GLOBALS['sql']->freeResult($rez);

				fwrite($fz, "</body>\n</html>\n");

				fclose($fz);
				shell_exec('nice -n20 zip -9r '.$uniq.'.zip '.$uniq.'.html');

				header('Cache-Control: public');
				header('Content-Description: pJJ Log Download');
				header('Content-Disposition: attachment; filename='.$uniq.'.zip');
				header('Content-Type: application/zip');
				header('Content-Length: '.filesize($uniq.'.zip'));
				header('Content-Transfer-Encoding: binary');
				readfile($uniq.'.zip');

				shell_exec('rm -f '.$uniq.'*');

				$GLOBALS['sql']->commit();
				MMC_Unlock('chats.global.log.lock');
				die();
			}

			// Fetch number of rows for pagination
            //echo htmlentities($count.$cond).'<br>';
			$rez = $GLOBALS['sql']->query($count.$cond);
			$numrows = $GLOBALS['sql']->fetchAssoc($rez);
			$numrows = intval($numrows['cnt']);
			$GLOBALS['sql']->freeResult($rez);

			$cond .= " ORDER BY message_id ASC";

			// Create the pagination, if applicable
			if (!empty($_REQUEST['limit']) && $_REQUEST['limit'] < $numrows) {
				if ($entry == 1) {
					$_REQUEST['offset'] = max(0, ($numrows-$_REQUEST['limit']));
				}
				else if ($entry == 2) {
					$_REQUEST['offset'] = 0;
				}
				$numpages = ceil($numrows/$_REQUEST['limit']);
				$curpage = floor($_REQUEST['offset']/$_REQUEST['limit'])+1;
				$range = range(1, $numpages);
				$offset = ($curpage-2)*$limit;
				$pages .= "\n<br>Page: ";
				$pages .= "<a href='dblog.php?{$qid}&amp;limit={$_REQUEST['limit']}&amp;offset={$offset}'>&lt;&lt; previous</a>, ";
				foreach($range as $page) {
					if ($page == $curpage) {
						$pages .= $page.", ";
					}
					else {
						$offset = ($page-1)*$limit;
						$pages .= "<a href='dblog.php?{$qid}&amp;limit={$_REQUEST['limit']}&amp;offset={$offset}'>".$page."</a>, ";
					}
				}
				$offset = ($curpage)*$limit;
				$pages .= "<a href='dblog.php?{$qid}&amp;limit={$_REQUEST['limit']}&amp;offset={$offset}'>next &gt;&gt;</a>";
				$pages .= "<br>\n";
				$cond .= " OFFSET {$_REQUEST['offset']} LIMIT {$_REQUEST['limit']}";
			}

            //echo htmlentities($fetch.$cond).'<br>';
			$rez = $GLOBALS['sql']->query($fetch.$cond);
			if ($numfetched = $GLOBALS['sql']->numRows($rez)) {
				$output .= "<b>{$numrows} lines found</b><br>\n";
				if (!empty($_REQUEST['keywords']) && is_array($_REQUEST['keywords'])) {
					$output .= "You can click the line's timestamp to get 15 minutes context of it.<br><br>";
				}
				$output .= $pages;
				for ($i=0;$i<$numfetched;$i++) {
                    $row = $GLOBALS['sql']->fetchAssoc($rez, $i);
                    $row['message'] = log_SanitizeLine($row['message']);
					$output .= "<div>";
					$output .= "<span title='Get Context'>[<a href='dblog.php?a=1&amp;from={$row['contextstamp']}'>{$row['shortstamp']}</a>]</span>&nbsp;";
					$output .= "<span title='Line ID {$row['message_id']}; Author ".htmlspecialchars($row['user_name'], ENT_QUOTES)." (UID {$row['user_id']})'>{$row['message']}</span>";
					$output .= "</div>\n";
				}
				$output .= $pages;
			}
			else {
				$output .= "<b>No rows matching that query...</b>";
			}
			$GLOBALS['sql']->freeResult($rez);
		}
        $GLOBALS['sql']->commit();
        MMC_Unlock('chats.global.log.lock');
	}

?>

<div>Keywords are inclusive, so the more keywords you put in, the less lines you'll find.
<br>You can specifically ask for a keyword to not exist by prefixing it with !.
<br>For example, searching for "mouse !cheese milk" would find lines that contain both mouse and milk but not cheese.</div>

<p><form method="get" accept-charset="UTF-8">
<table border="0" cellspacing="0" cellpadding="3" align="center">
<tr valign="top">
	<td>Keywords:</td>
	<td colspan="3"><input type="text" name="keywords" style="width: 100%;"></td>
</tr>
<!--
<tr valign="top">
	<td>Query ID:</td>
	<td colspan="3"><input type="text" name="qid" style="width: 100%;" value="<?=htmlentities($_REQUEST['qid']);?>"></td>
</tr>
<tr valign="top">
	<td>From</td>
	<td style="white-space: nowrap;" colspan="3">
		<input type="text" name="date_from[y]" value="<?=htmlentities($_REQUEST['date_from']['y']);?>" title="Year" size="4">
		<input type="text" name="date_from[m]" value="<?=htmlentities($_REQUEST['date_from']['m']);?>" title="Month" size="2">
		<input type="text" name="date_from[w]" value="<?=htmlentities($_REQUEST['date_from']['w']);?>" title="Week" size="2">
		<input type="text" name="date_from[d]" value="<?=htmlentities($_REQUEST['date_from']['d']);?>" title="Day of Month" size="2">
		<input type="text" name="date_from[h]" value="<?=htmlentities($_REQUEST['date_from']['h']);?>" title="Hour" size="2">
		<input type="text" name="date_from[i]" value="<?=htmlentities($_REQUEST['date_from']['i']);?>" title="Minute" size="2">
		<input type="text" name="date_from[s]" value="<?=htmlentities($_REQUEST['date_from']['s']);?>" title="Second" size="2">
	</td>
</tr>
<tr valign="top">
	<td>To</td>
	<td style="white-space: nowrap;" colspan="3">
		<input type="text" name="date_to[y]" value="<?=htmlentities($_REQUEST['date_to']['y']);?>" title="Year" size="4">
		<input type="text" name="date_to[m]" value="<?=htmlentities($_REQUEST['date_to']['m']);?>" title="Month" size="2">
		<input type="text" name="date_to[w]" value="<?=htmlentities($_REQUEST['date_to']['w']);?>" title="Week" size="2">
		<input type="text" name="date_to[d]" value="<?=htmlentities($_REQUEST['date_to']['d']);?>" title="Day of Month" size="2">
		<input type="text" name="date_to[h]" value="<?=htmlentities($_REQUEST['date_to']['h']);?>" title="Hour" size="2">
		<input type="text" name="date_to[i]" value="<?=htmlentities($_REQUEST['date_to']['i']);?>" title="Minute" size="2">
		<input type="text" name="date_to[s]" value="<?=htmlentities($_REQUEST['date_to']['s']);?>" title="Second" size="2">
	</td>
</tr>
-->
<tr valign="top">
	<td>Page limit</td>
	<td><input type="text" name="limit" value="<?=htmlentities($_REQUEST['limit']);?>" size="10"> lines</td>
	<td>Entry Page</td>
	<td><select name="entry"><option value="first">First<option value="last">Last</select></td>
</tr>
<tr valign="top">
	<td colspan="4"><div align="right"><input type="submit" value="Search"></div></td>
</tr>
</table>
<input type="hidden" name="a" value="1">
</form>

<?=$output;?>

<center><img src="http://pjj.cc/gfx/null.gif" border=0></center>
</body>
</html>
