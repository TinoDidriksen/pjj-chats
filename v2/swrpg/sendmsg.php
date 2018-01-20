<?php
	$GLOBALS['-x-time-point'] = microtime(true);
//	ob_start();
	ignore_user_abort(true);

	mt_srand((double)microtime()*1000000);

	$cqs = 0;
	$creas = '';

function getmicrotime() {
	return microtime(true);
}

	$start = getmicrotime();

	$realpath = preg_replace('@.*/([^/]+)/sendmsg.php$@', '\1', $_SERVER['PHP_SELF']);
	$chatpath = $realpath;

	require_once('../common/helpers.php');
	require_once('../common/session.php');
	require_once('../../chatv3/_inc/mmcache.php');
	require_once('../'.$realpath.'/settings.php');
	require_once('../'.$realpath.'/options.php');
	require_once('../mysql.php');
	require_once('../setup.php');

	require_once('../common/language.php');
	if (file_exists('../'.$realpath.'/language.php')) {
		require_once('../'.$realpath.'/language.php');
	}

	require_once('../common/fixup.php');

	$realpath = 'chat'.$realpath;
	$chatpath = $realpath;

	$newpath = mb_substr($chatpath, 4);
	if ($_SERVER['HTTP_HOST']) {
		$cpath = 'https://'.$_SERVER['HTTP_HOST'].ereg_replace('(.*)/sendmsg.php', '\1', $_SERVER['PHP_SELF']);
	}
	else {
		$cpath = 'https://'.$_SERVER['SERVER_NAME'].ereg_replace('(.*)/sendmsg.php', '\1', $_SERVER['PHP_SELF']);
	}

	if (!empty($_SESSION[$realpath]['ident'])) {
		$ident = $_SESSION[$realpath]['ident'];
	}
	else {
		$ident = mb_substr(md5($_SERVER['REMOTE_ADDR'].$realpath), 0, $identlenght);
		$_SESSION[$realpath]['ident'] = $ident;
	}
	$undoable = true;

	if (empty($dtcalc)) {
		$dtcalc = 'g:ia, F d (T)';
	}
    if (empty($numicons)) {
        $numicons = 1;
    }

	$fiximages = array(
		'../gfx/buttons/clear.gif',
		'../gfx/buttons/cpjj.gif',
		'../gfx/buttons/enter.gif',
		'../gfx/buttons/exit.gif',
		'../gfx/buttons/manual.gif',
		'../gfx/buttons/members.gif',
		'../gfx/buttons/messages.gif',
		'../gfx/buttons/music.gif',
		'../gfx/buttons/post.gif',
		'../gfx/buttons/profiles.gif',
		'../gfx/buttons/refresh.gif',
		'../gfx/buttons/register.gif',
		'../gfx/buttons/undo.gif',
		'../gfx/buttons/userlist.gif',
		'../gfx/buttons/null.gif'
	);
	foreach ($images as $k => $v) {
		if (empty($images[$k])) {
			$images[$k] = $fiximages[$k];
		}
	}

	$bodytag = str_replace('<body ', '<body class="console" ', $bodytag);
	$cbodytag = str_replace('<body ', '<body class="console" ', $cbodytag);
	$ubodytag = str_replace('<body ', '<body class="console" ', $ubodytag);

	$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$xcnt = count($banip);
	for ($cc=0;$cc<$xcnt;$cc++) {
		if (
		    (strcmp($ident, $banip[$cc]) == 0)
		    || (strncmp($_SERVER['REMOTE_ADDR'], $banip[$cc], strlen($banip[$cc])) == 0)
		    || (strncmp($_SERVER['HTTP_X_FORWARDED_FOR'], $banip[$cc], strlen($banip[$cc])) == 0)
		    ) {
			echo '<!DOCTYPE html>', "\n", '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>', "\n", $cbodytag;
			$ox = $banguage[4];
			$ox = str_replace('{IDENT}', $ident, $ox);
			echo $ox;
			echo '</body></html>';
			exit();
		}
		else if (strpos($banip[$cc], '.') !== false || strpos($banip[$cc], '*') !== false) {
			$banip[$cc] = str_replace('\\*', '.*', preg_quote($banip[$cc]));
			if (preg_match('/^'.preg_quote($banip[$cc], '/').'$/is', $host)) {
				echo '<!DOCTYPE html>', "\n", '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>', "\n", $cbodytag;
				$ox = $banguage[4];
				$ox = str_replace('{IDENT}', $ident, $ox);
				echo $ox;
				echo '</body></html>';
				exit();
			}
		}
	}

	//*
	require_once('../common/proxy.php');
	if ($proxyblock == 1 && empty($_SESSION[$realpath]['user']['uid'])) {
		$bl = Proxy_IsProxy($_SERVER['REMOTE_ADDR']);
		if ($bl !== false) {
			echo '<!DOCTYPE html>', "\n", '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>';
			echo 'This chat blocks open proxies, and you are using one. You have been banned for 8 hours.';
			echo '<br>The list that caught you is: <a href="', $bl, '">', $bl, '</a>';
			echo '</body></html>';
			AddBan($ident, time()+28800, '[proxy]', $chatpath);
			setcookie('pJJChat_Banned', $ident, time()+604800);
			die();
		}
	}
	time_point(__FILE__, __LINE__);
	//*/

	if (!empty($_COOKIE['pJJChat_Banned'])) {
		$ident = $_COOKIE['pJJChat_Banned'];
	}

	$ooc_r = str_replace("\"", "`", $_REQUEST['ooc_r']);
	$ooc_r = str_replace("\'", "`", $ooc_r);
	$ooc_l = str_replace("\"", "`", $_REQUEST['ooc_l']);
	$ooc_l = str_replace("\'", "`", $ooc_l);

	if (empty($ooc_l)) {
		$ooc_l = '((';
	}
	if (empty($ooc_r)) {
		$ooc_r = '))';
	}

	$write_me = '';

	require_once('../common/banhelp.php');
	require_once('../common/ignore.php');
	require_once('../common/faction_help.php');


	GetChatPrefs($realpath);
	$GLOBALS['biglog']['real_id'] = $GLOBALS['biglog']['chat_id'];
	$cpref = GetChatPrefs($chatpath);

	$color = FixColor($_REQUEST['color']);
	$_SESSION[$realpath]['color'] = $color;

	if (!$_REQUEST['cspeed']) {
		$_REQUEST['cspeed'] = $respeed;
	}
	else if ($_REQUEST['cspeed'] < 7) {
		$_REQUEST['cspeed'] = 7;
	}
	else if ($_REQUEST['cspeed'] > 600) {
		$_REQUEST['cspeed'] = 600;
	}
	$cspeed = $_REQUEST['cspeed'];
	$_SESSION[$realpath]['cspeed'] = $cspeed;

	if ($max_nick < 10) {
		$max_nick = 40;
	}
	else if ($max_nick > 63) {
		$max_nick = 63;
	}

	if (empty($_REQUEST['password']) && !empty($_SESSION[$realpath]['user']['password'])) {
		$_REQUEST['password'] = $_SESSION[$realpath]['user']['password'];
	}
	else if (strlen($_REQUEST['password']) != 32 && strcmp($_SESSION[$realpath]['user']['password'], md5($_REQUEST['password'])) == 0) {
		$_REQUEST['password'] = $_SESSION[$realpath]['user']['password'];
	}
	if (empty($_REQUEST['handle']) && !empty($_SESSION[$realpath]['user']['displayname'])) {
		$_REQUEST['handle'] = $_SESSION[$realpath]['user']['displayname'];
	}
	else if (empty($_REQUEST['handle']) && !empty($_SESSION[$realpath]['user']['username'])) {
		$_REQUEST['handle'] = $_SESSION[$realpath]['user']['username'];
	}

	$handle = stripslashes(trim(mb_substr($_REQUEST['handle'], 0, $max_nick)));
	$handle = str_replace('_', ' ', $handle);
	$handle = trim($handle);
	if (CheckFlags('L', $cpref)) {
		$_REQUEST['link'] = '';
	}
	else {
		$_REQUEST['link'] = stripslashes(trim(mb_substr($_REQUEST['link'], 0, 256)));
	}
	$link = $_REQUEST['link'];

	if (CheckFlags('i', $cpref)) {
		$_REQUEST['image'] = '';
	}
	else {
		$_REQUEST['image'] = stripslashes(trim(mb_substr($_REQUEST['image'], 0, 256)));
	}
	$image = trim($_REQUEST['image']);

	if (empty($handle)) {
		$handle = $noname;
    }

	$link = str_replace('\'', '', $link);
	$image = str_replace('\'', '', $image);
	$link = str_replace('\\', '', $link);
	$image = str_replace('\\', '', $image);
	$_SESSION[$realpath]['link'] = $link;
	$_SESSION[$realpath]['image'] = $image;

	if ($handle === '(random)') {
		$fixhandle = strtolower(ereg_replace($master_name_filter, '', $_REQUEST['oldhandle']));
		$client = ChatVerifyLoginFetch($fixhandle, $_REQUEST['password'], $chatpath);

		$client['chain'] = trim($client['chain']);
		if (!empty($client['chain']) && strcasecmp($client['chain'], $_REQUEST['oldhandle']) != 0 && !CheckFlags('C', $client['prefs'])) {
			$lines = array();
			if (strpos($client['chain'], "\n") === false) {
				$lines = array(0 => $client['chain']);
			}
			else {
				$lines = explode("\n", $client['chain']);
			}
			sort($lines);
			$lines = array_unique($lines);
			sort($lines);
			$client['chain'] = $lines;
		}
		$handle = $client['chain'][ array_rand($client['chain']) ];
	}

	$fixhandle = strtolower(ereg_replace($master_name_filter, '', $handle));

	$client = ChatVerifyLoginFetch($fixhandle, $_REQUEST['password'], $chatpath);

	$client['chain'] = trim($client['chain']);
	if (!empty($client['chain']) && strcasecmp($client['chain'], $handle) != 0 && !CheckFlags('C', $client['prefs'])) {
		$lines = array();
		if (strpos($client['chain'], "\n") === false) {
			$lines = array(0 => $client['chain']);
		}
		else {
			$lines = explode("\n", $client['chain']);
		}
		sort($lines);
		$lines = array_unique($lines);
		sort($lines);
		$client['chain'] = $lines;
	}

	$_SESSION[$realpath]['user'] = $client;

	$falselog = '';
	if ($client['status'] <= -1) {
		$falselog = $handle;
		$handle = $noname;
		$client = array('status' => 0);
		$fixhandle = strtolower(ereg_replace($master_name_filter, '', $handle));
	}
	if (!empty($client['uid'])) {
		if (strcmp($client['displayname'], $handle)) {
			ChangeDisplayName($client['uid'], $handle);
		}
		$ident = mb_substr(md5($client['uid'].$realpath), 0, $identlenght);
		$_SESSION[$realpath]['ident'] = $ident;
	}
	if (empty($handle)) {
		$handle = 'Null Name';
    }
	$_REQUEST['oldhandle'] = $handle;
	$_SESSION[$realpath]['flags'] = $client['flags'];
	$_SESSION[$realpath]['flags_user'] = $client['flags_user'];
	$_SESSION[$realpath]['handle'] = $handle;
	$_SESSION[$realpath]['fixhandle'] = $fixhandle;

	ChatSessionSuspend();

	if ((empty($client['username'])) && (CheckFlags('n', $cpref))) {
		$image = '';
	}

	require_once('iconlist.php');
	if (empty($picons)) {
		$picons = array();
	}

	$facticon = '';
	if (!empty($client['faction'])) {
		$facticon = GetFactionIcon($chatpath, $client['faction']);
	}

	if (!empty($_REQUEST['icons']) && !is_array($_REQUEST['icons'])) {
        $_REQUEST['icons'] = unserialize($_REQUEST['icons']);
    }

    if (CheckFlags('C', $cpref)) {
		$_REQUEST['icons'] = array();
	}
	else if (is_array($_REQUEST['icons'])) {
        $contents = implode(';', $picons).$facticon;
        if (($client['password']) && (!CheckFlags('z', $client['flags_user']))) {
            $contents .= implode(';', $icons);
            $contents .= $client['icon'];
        }
        foreach ($_REQUEST['icons'] as $key => $icon) {
            if (empty($icon) || stristr($contents, $icon) === false) {
                unset($_REQUEST['icons'][$key]);
            }
        }
        $_REQUEST['icons'] = array_values($_REQUEST['icons']);
	}

	if (($memonly >= 1) && ($client['status'] <= 0) && !CheckFlags('MmZXxzpADRPFfsoiIOCBbralVL1', $client['flags'])) {
		echo '<!DOCTYPE html>', "\n", '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head>', $cbodytag;
		echo $language[0];
		echo '</body></html>';
		exit();
	}

	$message = $_REQUEST['message'];
	if (CheckFlags('aXZmM', $client['flags']) && (mb_substr($message, 0, 5) == '/raw ')) {
		$write_me .= mb_substr($message, 5);
		unset($message);
	}
	$message = mb_substr($message, 0, 10240);
	if ($_REQUEST['channel'] == '*') {
		unset($_REQUEST['channel']);
	}
	if (!empty($_REQUEST['channel']) && !empty($message)) {
	    $message = '/msg '.str_replace(' ', '_', $_REQUEST['channel']).' '.$message;
	}

	$xml = array();
	$xml['private'] = false;
	$xml['rawpost'] = trim($message);

	$GLOBALS['delete-existing'] = false;

	time_point(__FILE__, __LINE__);
	if ($handle) {
		setcookie('pJJChat_NoColor', $_REQUEST['mode_nocol'],time()+2592000);

		if ($_REQUEST['firstmsg'] != 1) {
			$checkmsg = 1;
			$ident = $_SESSION[$realpath]['ident'];
			if ((CheckFlags('lZmM', $client['flags_user']) == 0) && (!CheckFlags('J', $cpref)) && (CheckBan($ident, $chatpath) == 0) && (CheckGag($ident, $chatpath) == 0)) {
				$ox = $language[1];
				$ox = str_replace('{USERNAME}', $handle, $ox);
				$write_me .= $ox;
				AddUser($handle, $ident, time(), $link, $image, $realpath);
			}

			$_REQUEST['firstmsg'] = 1;

			if ($_REQUEST['autolog'] == 'on') {
				$_REQUEST['iwantcookie'] = 'on';
			}

			$newcookie = array();
			$newcookie['iwantcookie']	= $_REQUEST['iwantcookie'];
			$newcookie['reload']		= $_REQUEST['reload'];
			$newcookie['handle']		= $handle;
			$newcookie['password']		= $_REQUEST['password'];
			$newcookie['color']			= $color;
			$newcookie['icons']			= serialize($_REQUEST['icons']);
			$newcookie['link']			= $link;
			$newcookie['image']			= $image;
			$newcookie['autologin']		= $_REQUEST['autolog'];
			setcookie('pJJChat', serialize($newcookie), time()+2592000);
		}

		$motd = $_REQUEST['motd'];
		if (($handle != $noname) && ($_REQUEST['reload'] == 'on')) {
			$link = $client['plink'];
			$image = $client['pimage'];
			$color = FixColor($client['pcolor']);
			$_REQUEST['icons'] = unserialize($client['picon']);

			if (CheckFlags('A', $client['prefs']))
				$_REQUEST['mode_advanced'] = 'on';
			if (CheckFlags('S', $client['prefs']))
				$_REQUEST['mode_simple'] = 'on';
			if (CheckFlags('O', $client['prefs']))
				$motd = '';
			if (CheckFlags('b', $client['prefs']))
				$_REQUEST['st_bold'] = 'on';
			if (CheckFlags('u', $client['prefs']))
				$_REQUEST['st_ulined'] = 'on';
			if (CheckFlags('i', $client['prefs']))
				$_REQUEST['st_italic'] = 'on';
			if (CheckFlags('t', $client['prefs']))
				$_REQUEST['st_tt'] = 'on';
		}

		if (CheckFlags('R', $client['prefs'])) {
			if ($_REQUEST['st_rev'] == 'on') {
				$_REQUEST['st_rev'] = '';
			}
			else {
				$_REQUEST['st_rev'] = 'on';
			}
		}

		$xml['username'] = $handle;
		$handle = str_replace('<', '&lt;', $handle);
		$handle = str_replace('>', '&gt;', $handle);

		$message = trim($message);
		if (!empty($message)) {
            MMC_Unset($realpath.'.xml.last');
            MMC_Unset($realpath.'.xml.output');
			$message = str_replace('<', '&lt;', $message);
			$message = str_replace('>', '&gt;', $message);

			$message = eregi_replace("\\[i\\]([^[]*[^/]*[^i]*[^]]*)\\[/i\\]", "<i>\\1</i>", $message);
			$message = eregi_replace("\\[u\\]([^[]*[^/]*[^u]*[^]]*)\\[/u\\]", "<u>\\1</u>", $message);
			$message = eregi_replace("\\[b\\]([^[]*[^/]*[^b]*[^]]*)\\[/b\\]", "<b>\\1</b>", $message);
			$message = eregi_replace("\\[s\\]([^[]*[^/]*[^s]*[^]]*)\\[/s\\]", "<s>\\1</s>", $message);
			$message = eregi_replace("\\[t\\]([^[]*[^/]*[^t]*[^]]*)\\[/t\\]", "<tt>\\1</tt>", $message);
			if (CheckFlags('c', $cpref) == 0) {
				$message = eregi_replace("\\[c([[:alnum:]]*)\\]([^[]*[^/]*[^c]*[^]]*)\\[/c\\]", "<font color='#\\1'>\\2</font>", $message);
			}

			if (CheckFlags('s', $cpref)) {
				$message = preg_replace('@[\n\r]+@s', ' ', $message);
			}
			else if (CheckFlags('m', $client['prefs']) || CheckFlags('l', $cpref) || !empty($_REQUEST['multiline'])) {
				$message = nl2br($message);
			}

			time_point(__FILE__, __LINE__);
			$exit = 0;
			$addok = 1;
			if (((stristr(mb_substr($message, 0, 5), '/exit')) || (stristr(mb_substr($message, 0, 5), '/quit'))) && (CheckBan($ident, $chatpath) == 0) && (CheckGag($ident, $chatpath) == 0)) {
				$addok = 0;
				$ident = $_SESSION[$realpath]['ident'];
				if ((!CheckFlags('lZmM', $client['flags_user']) && !CheckFlags('J', $cpref)) || (stristr(mb_substr($message, 0, 5), '/quit'))) {
					$finalword = mb_substr($message, 6);
					$finalword = FilterWords($finalword);
					$xml['post'] = $finalword;
					$ox = $language[2];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$ox = str_replace('{MESSAGE}', $finalword, $ox);
					$write_me .= $ox;
				}
				$exit = 1;
			}
			else if ((stristr(mb_substr($message, 0, 3), '/me')) && !CheckBan($ident, $chatpath) && !CheckGag($ident, $chatpath)) {
				$ident = $_SESSION[$realpath]['ident'];
				$outputme = '';
				if (!empty($_REQUEST['icons'])) {
                    $outicon = '';
                    foreach ($_REQUEST['icons'] as $icon) {
                        $outicon .= "<img src='$icon' border='0'>";
                    }
					if (($client['password']) && (!CheckFlags('z', $client['flags_user']))) {
						$outputme = '<a href="'.$cpath.'/register/viewer.php?su='.urlencode($handle).'&fm=view">'.$outicon.'</a>';
					}
					else {
						$outputme = $outicon;
					}
				}
				$message = FilterWords($message);
				$xml['post'] = $message;
				$ox = $language[13];
				$ox = str_replace('{USERNAME}', $handle, $ox);
				$ox = str_replace('{COLOR}', $color, $ox);
				$ox = str_replace('{ICON}', $outputme, $ox);
				$ox = str_replace('{MESSAGE}', mb_substr($message, 4), $ox);
				$write_me .= $ox;
			}
			else if ((stristr(mb_substr($message, 0, 3), "/lp")) && !CheckBan($ident, $chatpath) && !CheckGag($ident, $chatpath)) {
                require_once('../common/lastfm.php');
                $oname = strtolower(trim(mb_substr($message, 4)));
                $lastplayed = '';
                if (!empty($oname)) {
                    $lastplayed = LastFM_GetLastTune($oname);
                }
				else {
                    $lastplayed = LastFM_GetLastTune($client['lastfm']);
                }

                if (!empty($lastplayed)) {
                    $ident = $_SESSION[$realpath]['ident'];
                    $outputme = '';
                    if (!empty($_REQUEST['icons'])) {
                        $outicon = '';
                        foreach ($_REQUEST['icons'] as $icon) {
                            $outicon .= "<img src='$icon' border='0'>";
                        }
                        if (($client['password']) && (!CheckFlags('z', $client['flags_user']))) {
							$outputme = '<a href="'.$cpath.'/register/viewer.php?su='.urlencode($handle).'&fm=view">'.$outicon.'</a>';
						}
                        else {
                            $outputme = $outicon;
						}
                    }
					$ox = $language[14];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$ox = str_replace('{COLOR}', $color, $ox);
					$ox = str_replace('{LASTPLAYED}', $lastplayed, $ox);
                    $write_me .= $ox;
                }
				else {
                    $addok = 0;
                    $write_me = '';
                }
			}
			else if ((stristr(mb_substr($message, 0, 5), "/undo")) && !CheckFlags("U", $cpref)) {
				$addok = 0;
				$ident = $_SESSION[$realpath]['ident'];
				$result = count_mysql_query("SELECT posttime FROM uo_chat_log WHERE chat='$realpath' AND ident='$ident' AND ip='{$_SERVER['REMOTE_ADDR']}' ORDER BY posttime DESC", $handler, "sendmsg.php: /undo 1/2");
				$row = mysql_fetch_row($result);
				mysql_free_result($result);
				count_mysql_query("DELETE FROM uo_chat_log WHERE chat='$realpath' AND ident='$ident' AND ip='{$_SERVER['REMOTE_ADDR']}' AND posttime='$row[0]'", $handler, "sendmsg.php: /undo 2/2");
                MMC_Unset($realpath.'.xml.last');
                MMC_Unset($realpath.'.xml.output');
            }
			else if (stristr(mb_substr($message, 0, 7), "/strike")) {
				$reminf = str_replace("'", "`", mb_substr($message, 8));
				$result = count_mysql_query("SELECT line FROM uo_chat_log WHERE chat='$realpath' AND ident='$ident'", $handler, "sendmsg.php: /strike 1/2");
				while($row = mysql_fetch_row($result)) {
					$eline = addslashes($row[0]);
					$row[0] = stripslashes($row[0]);
					if (!empty($reminf) && (stristr($row[0], $reminf))) {
						$row[0] = addslashes("<s>$row[0]</s>");
						count_mysql_query("UPDATE uo_chat_log SET line='$row[0]' WHERE chat='$realpath' AND line='$eline'", $handler, "sendmsg.php: /strike 2/2");
					}
				}
				mysql_free_result($result);

				unset($eline);
				unset($row);
				unset($reminf);
				unset($result);
				unset($rez);
				unset($message);
                MMC_Unset($realpath.'.xml.last');
                MMC_Unset($realpath.'.xml.output');
			}
			else if (stristr(mb_substr($message, 0, 4), "/rem")) {
				$addok = 0;
				if (CheckFlags("rxXZmM", $client['flags'])) {
					$reminf = str_replace("'", "`", mb_substr($message, 5));
					$result = count_mysql_query("SELECT line FROM uo_chat_log WHERE chat='$realpath'", $handler, "sendmsg.php: /rem 1/2");
					while($row = mysql_fetch_row($result)) {
						$eline = addslashes($row[0]);
						$row[0] = stripslashes($row[0]);
						if (stristr($row[0], $reminf)) {
							count_mysql_query("DELETE FROM uo_chat_log WHERE chat='$realpath' AND line='$eline'", $handler, "sendmsg.php: /rem 2/2");
						}
					}
					mysql_free_result($result);
					unset($eline);
                    MMC_Unset($realpath.'.xml.last');
                    MMC_Unset($realpath.'.xml.output');
				}
			}
			else if ((stristr(mb_substr($message, 0, 4), "/ban")) && !CheckFlags("B", $cpref)) {
				if (CheckFlags("BxXZmM", $client['flags'])) {
					$banid = mb_substr($message, 5, $identlenght);
					$bantime = mb_substr($message, 5+$identlenght+1);
					if (($bantime+0) > 604800) {
						$bantime = 604800;
					}
					AddBan($banid, $bantime+time(), $fixhandle, $chatpath);
					$xml['post'] = $message;
					$ox = $language[3];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$ox = str_replace('{COLOR}', $color, $ox);
					$ox = str_replace('{BANIDENT}', $banid, $ox);
					$ox = str_replace('{BANDURATION}', $bantime, $ox);
					$write_me .= $ox;
				}
			}
			else if ((stristr(mb_substr($message, 0, 6), "/unban")) && !CheckFlags("B", $cpref)) {
				if (CheckFlags("BxXZmM", $client['flags'])) {
					$banid = mb_substr($message, 7, $identlenght);
					DeleteBan($banid, $chatpath);
					$xml['post'] = $message;
					$ox = $language[6];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$ox = str_replace('{COLOR}', $color, $ox);
					$ox = str_replace('{BANIDENT}', $banid, $ox);
					$write_me .= $ox;
				}
			}
			else if ((stristr(mb_substr($message, 0, 4), "/gag")) && !CheckFlags("B", $cpref)) {
				if (CheckFlags("BxXZmM", $client['flags'])) {
					$banid = mb_substr($message, 5, $identlenght);
					$bantime = mb_substr($message, 5+$identlenght+1);
					if (($bantime+0) > 604800) {
						$bantime = 604800;
					}
					AddGag($banid, $bantime+time(), $fixhandle, $chatpath);
					$xml['post'] = $message;
					$ox = $language[15];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$ox = str_replace('{COLOR}', $color, $ox);
					$ox = str_replace('{GAGIDENT}', $banid, $ox);
					$ox = str_replace('{GAGDURATION}', $bantime, $ox);
					$write_me .= $ox;
				}
			}
			else if ((stristr(mb_substr($message, 0, 6), "/ungag")) && !CheckFlags("B", $cpref)) {
				if (CheckFlags("BxXZmM", $client['flags'])) {
					$banid = mb_substr($message, 7, $identlenght);
					DeleteGag($banid, $chatpath);
					$xml['post'] = $message;
					$ox = $language[16];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$ox = str_replace('{COLOR}', $color, $ox);
					$ox = str_replace('{GAGIDENT}', $banid, $ox);
					$write_me .= $ox;
				}
			}
			else if ((stristr(mb_substr($message, 0, 7), "/ignore")) && !CheckFlags("I", $cpref)) {
				$banid = mb_substr($message, 8, $identlenght);
				$bantime = mb_substr($message, 8+$identlenght+1);
				if (($bantime+0) > 172800)
					$bantime = 172800;
				AddIgnore($banid, $bantime+time(), $ident, $chatpath);
				$xml['post'] = $message;
				$ox = $language[4];
				$ox = str_replace('{USERNAME}', $handle, $ox);
				$ox = str_replace('{COLOR}', $color, $ox);
				$ox = str_replace('{IGNOREIDENT}', $banid, $ox);
				$ox = str_replace('{IGNOREDURATION}', $bantime, $ox);
				$write_me .= $ox;
			}
			else if ((stristr(mb_substr($message, 0, 9), "/unignore")) && !CheckFlags("I", $cpref)) {
				$banid = mb_substr($message, 10, $identlenght);
				DeleteIgnore($banid, $chatpath, $ident);
				$xml['post'] = $message;
				$ox = $language[5];
				$ox = str_replace('{USERNAME}', $handle, $ox);
				$ox = str_replace('{COLOR}', $color, $ox);
				$ox = str_replace('{IGNOREIDENT}', $banid, $ox);
				$write_me .= $ox;
			}
			else if (stristr(mb_substr($message, 0, 5), "/sban")) {
				$addok = 0;
				if (CheckFlags("mM", $client['flags'])) {
					$banid = mb_substr($message, 6, $identlenght);
					$bantime = mb_substr($message, 6+$identlenght+1);
					if (($bantime+0) > 604800)
						$bantime = 604800;
					AddBan($banid, $bantime+time(), $fixhandle, $chatpath);
				}
			}
			else if (((stristr(mb_substr($message, 0, 5), "/nick")) || (stristr(mb_substr($message, 0, 5), "/name"))) && !CheckBan($ident, $chatpath) && !CheckGag($ident, $chatpath)) {
				$ident = $_SESSION[$realpath]['ident'];
				$message = str_replace("_", " ", $message);
				$message = str_replace(":", " ", $message);
				$message = str_replace("'", "'", $message);
				$message = str_replace("\"", "'", $message);

				$newhandle = mb_substr($message, 6, 40);
				$xml['post'] = $message;
				$ox = $language[7];
				$ox = str_replace('{USERNAME}', $handle, $ox);
				$ox = str_replace('{NEWNAME}', $newhandle, $ox);
				$write_me .= $ox;
				$handle = $newhandle;
			}
			else if (stristr(mb_substr($message, 0, 5), "/link")) {
				$addok = 0;
				$ident = $_SESSION[$realpath]['ident'];
				$link = mb_substr($message, 6);
				$link = str_replace("'", "'", $link);
			}
			else if ((stristr(mb_substr($message, 0, 6), '/8ball') || stristr(mb_substr($message, 0, 6), '/xball')) && !CheckBan($ident, $chatpath) && !CheckGag($ident, $chatpath)) {
				$rnd = array_rand($GLOBALS['m8ball']);
				$rnd = $GLOBALS['m8ball'][$rnd];
				$xml['post'] = $message;
				$ox = $language[18];
				$ox = str_replace('{8BALLRESULT}', $rnd, $ox);
				$write_me .= $ox;
			}
			else if ((stristr(mb_substr($message, 0, 5), '/dice')) && !CheckBan($ident, $chatpath) && !CheckGag($ident, $chatpath)) {
				$undoable = false;
				$ident = $_SESSION[$realpath]['ident'];

				$dice = trim(mb_substr($message, 6));
				$val = array(1, 6, 0);
				if (preg_match('@^(\d+)d(\d+)\s*([-+]?)(\d*)$@', $dice, $m)) {
					$val[0] = intval($m[1]);
					$val[1] = intval($m[2]);
					$val[2] = intval($m[4]);
					if ($m[3] === '-') {
						$val[2] *= -1;
					}
				}

				if ($val[0] > 100) {
					$val[0] = 100;
				}
				else if (empty($val[0]) || $val[0] < 1) {
					$val[0] = 1;
				}
				if ($val[1] > 92233720368547758) {
					$val[1] = 92233720368547758;
				}
				else if (empty($val[1]) || $val[1] < 1) {
					$val[1] = 6;
				}

				$results = array();
				$rest = $val[2];
				for ($dc=0;$dc<$val[0];$dc++) {
					$resta = mt_rand(1, $val[1]);
					$rest += $resta;
					$results[] = $resta;
				}
				$results = implode(', ', $results);
				$results .= ' = '.$rest;

				$xml['post'] = $message;
				$ox = $banguage[6];
				$ox = str_replace('{DICERESULT}', $results, $ox);
				$ox = str_replace('{DICETYPE}', sprintf('%ud%u %+d', $val[0], $val[1], $val[2]), $ox);
				$write_me .= $ox;
			}
			else if (stristr(mb_substr($message, 0, 6), "/image")) {
				$addok = 0;
				$ident = $_SESSION[$realpath]['ident'];
				$image = mb_substr($message, 7);
			}
			else if ((stristr(mb_substr($message, 0, 6), "/muban")) && !CheckFlags("B", $cpref)) {
				$addok = 0;
				if (CheckFlags("bXZmM", $client['flags'])) {
					@count_mysql_query("DELETE FROM uo_chat_ban WHERE chat='$chatpath'", $handler, "sendmsg.php: /muban 1/1");
				}
			}
			else if ((stristr(mb_substr($message, 0, 6), "/mugag")) && !CheckFlags("B", $cpref)) {
				$addok = 0;
				if (CheckFlags("bXZmM", $client['flags'])) {
					@count_mysql_query("DELETE FROM uo_chat_gag WHERE chat='$chatpath'", $handler, "sendmsg.php: /mugag 1/1");
				}
			}
			else if ((stristr(mb_substr($message, 0, 4), '/msg')) && !CheckBan($ident, $chatpath) && !CheckGag($ident, $chatpath) && !CheckFlags('P', $cpref)) {
				$ident = $_SESSION[$realpath]['ident'];
				$recipient = eregi_replace("^/msg[[:space:]]*([^[:space:]]+)[[:space:]]*.*$", "\\1", $message);
				$message = eregi_replace("^/msg[[:space:]]*[^[:space:]]+[[:space:]]*(.*)$", "\\1", $message);
				$message = FilterWords($message);

				$xml['private'] = true;
				$xml['rawpost'] = '[Private Message from "'.$handle.'" to "'.$recipient.'"]';

				$recipient = strtolower($recipient);
				$recipient = trim(str_replace('_', ' ', $recipient));
				$recipient = ereg_replace($master_name_filter, '', $recipient);

				$auth = mq($handle);
				$auth_uid = 'null';
				if (!empty($_SESSION[$realpath]['user']['uid'])) {
					$auth = 'null';
					$auth_uid = $_SESSION[$realpath]['user']['uid'];
				}

				$result = @count_mysql_query("SELECT username,displayname,uid FROM uo_chat_database
					WHERE username='$recipient' AND chat='$chatpath' AND dtime IS NULL", $handler, "sendmsg.php: /msg 1/4");
				$row = @mysql_fetch_assoc($result);
				@mysql_free_result($result);
				if (!empty($row['username'])) {
					$recipient = ucwords($row['username']);
					if (!empty($row['displayname'])) {
						$recipient = $row['displayname'];
					}
					$rcpt_uid = $row['uid'];
					$time = time();

					$message = mysql_real_escape_string(stripslashes($message));
					@count_mysql_query("INSERT INTO uo_chat_message (msg,auth,unread,rcpt_uid,auth_uid,msg_stamp)
						VALUES ('$message', $auth, 'yes', $rcpt_uid, $auth_uid, now())", $handler, "sendmsg.php: 2/4");

					$ox = $banguage[2];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$ox = str_replace('{RECIPIENT}', $recipient, $ox);
					$output = $ox;
				}
				else {
					$result = @count_mysql_query("SELECT username,displayname,uid FROM uo_chat_database
						WHERE username LIKE '%$recipient%' AND chat='$chatpath' AND dtime IS NULL", $handler, "sendmsg.php: 3/4");
					$row = @mysql_fetch_assoc($result);
					@mysql_free_result($result);
					if (!empty($row['username'])) {
						$recipient = ucwords($row['username']);
						if (!empty($row['displayname'])) {
							$recipient = $row['displayname'];
						}
						$rcpt_uid = $row['uid'];
						$time = time();

						$message = mysql_real_escape_string(stripslashes($message));
						@count_mysql_query("INSERT INTO uo_chat_message (msg,auth,unread,rcpt_uid,auth_uid,msg_stamp)
							VALUES ('$message', $auth, 'yes', $rcpt_uid, $auth_uid, now())", $handler, "sendmsg.php: 4/4");

						$ox = $banguage[2];
						$ox = str_replace('{USERNAME}', $handle, $ox);
						$ox = str_replace('{RECIPIENT}', $recipient, $ox);
						$output = $ox;
					}
					else {
						$ox = $banguage[3];
						$ox = str_replace('{USERNAME}', $handle, $ox);
						$ox = str_replace('{RECIPIENT}', ucwords($recipient), $ox);
						$output = $ox;
					}
				}

				$GLOBALS['delete-existing'] = true;
				$xml['post'] = $output;
				$write_me .= $output;
			}
			else if ((stristr(mb_substr($message, 0, 6), "/whois")) && !CheckBan($ident, $chatpath) && !CheckGag($ident, $chatpath) && !CheckFlags("W", $cpref)) {
				$ident = $_SESSION[$realpath]['ident'];
				$recipient = trim(mb_substr($message, 7));
				unset($message);

				$recipient = str_replace("_", " ", strtolower($recipient));
				$recipient = ereg_replace($master_name_filter, '', $recipient);

				$result = @count_mysql_query("SELECT lastlogin FROM uo_chat_database WHERE chat='$chatpath' AND username='$recipient' AND dtime IS NULL", $handler, "sendmsg.php: /whois 1/1");
				$lastlogin = @mysql_fetch_row($result);
				@mysql_free_result($result);

				if ($lastlogin[0] > 0) {
					$ox = $language[9];
					$ox = str_replace('{WHOISNAME}', ucwords($recipient), $ox);
					$ox = str_replace('{WHOISDATE}', date($dtcalc, $lastlogin[0]+($tzone*3600)), $ox);
					$output = $ox;
				}
				else {
					$ox = $language[8];
					$ox = str_replace('{WHOISNAME}', ucwords($recipient), $ox);
					$output = $ox;
				}

				$xml['post'] = $output;
				$write_me .= $output;
			}
			else if (stristr(mb_substr($message, 0, 6), "/clear")) {
				$addok = 0;
				if (CheckFlags("CXZmM", $client['flags'])) {

					@count_mysql_query("DELETE FROM uo_chat_ulist WHERE chat='$realpath'", $handler, "sendmsg.php: /clear 1/2");
					@count_mysql_query("DELETE FROM uo_chat_log WHERE chat='$realpath'", $handler, "sendmsg.php: /clear 2/2");

					$ox = $language[10];
					$ox = str_replace('{USERNAME}', $handle, $ox);
					$write_me .= $ox;
					$cleared = 1;
				}
			}
			else if (CheckBan($ident, $chatpath) == 0 && CheckGag($ident, $chatpath) == 0) {
				time_point(__FILE__, __LINE__);
				$ident = $_SESSION[$realpath]['ident'];
				if (CheckFlags("M", $client['flags_user'])) {
					$tag = '';
				}
				else if (CheckFlags("ADpRfXZm", $client['flags_user'])) {
					$tag = $adminident;
				}
				else if (CheckFlags("Brx", $client['flags_user'])) {
					$tag = $modident;
				}
				else if (CheckFlags('z', $client['flags_user'])) {
					$tag = $oocident;
				}
				else if ($client['password']) {
					$tag = $regident;
				}
				else {
					$tag = '';
				}

				if (stripos($tag, 'http://') === 0 || stripos($tag, 'https://') === 0) {
					$tag = '<img src="'.htmlentities($tag).'" border="0">&nbsp;';
				}

				ExtractUrls($message, $handle);
                $message = ParseSpecial($message);
				$message = FilterWords($message);

				if ($_REQUEST['st_ooc'] == 'on') {
					$message = $ooc_l.$message.$ooc_r;
				}
				if ($_REQUEST['st_bold'] == 'on') {
					$message = "<b>".$message."</b>";
				}
				if ($_REQUEST['st_italic'] == 'on') {
					$message = "<i>".$message."</i>";
				}
				if ($_REQUEST['st_ulined'] == 'on') {
					$message = "<u>".$message."</u>";
				}
				if ($_REQUEST['st_tt'] == 'on') {
					$message = "<tt>".$message."</tt>";
				}

				$postname = htmlentities($handle);
				/*
				if (!empty($link)) {
					$postname = '<span class="handle withlink">'.$handle.'</span>';
				}
				else {
					$postname = '<span class="handle nolink">'.$handle.'</span>';
				}
				//*/
				if (!empty($link)) {
					if (strpos($link, 'tp://') !== false) {
						$postname = '<a href="'.htmlentities($link).'" title="'.htmlentities($link).'">'.$postname.'</a>';
					}
					else {
						$postname = '<a title="'.htmlentities($link).'">'.$postname.'</a>';
					}
				}
				/*
				else if (!empty($client['password'])) {
					if (!empty($tag)) {
						$tag = '<a onclick="setConsoleChannel(\''.$fixhandle.'\');" class="setchannel">'.$tag.'</a>';
					}
					$postname = '<a onclick="setConsoleChannel(\''.$fixhandle.'\');" class="setchannel">'.$postname.'</a>';
				}
				//*/

				$xml['post'] = $message;
				if (!empty($_REQUEST['icons'])) {
                    $outicon = '';
                    foreach ($_REQUEST['icons'] as $icon) {
                        $outicon .= '<img src="'.htmlentities($icon).'" border="0">';
                    }
					$ox = $xxanguage[0];
					$ox = str_replace('{IDENT}', $ident, $ox);
					$ox = str_replace('{SYMBOL}', $tag, $ox);
					$ox = str_replace('{USERNAME}', $postname, $ox);
					$ox = str_replace('{CHATPATH}', $cpath, $ox);
					$ox = str_replace('{USERNAMEURL}', urlencode($handle), $ox);
					$ox = str_replace('{ICON}', $outicon, $ox);
					$ox = str_replace('{COLOR}', $color, $ox);
					$ox = str_replace('{MESSAGE}', $message, $ox);
					$output = $ox;
				}
				else {
					$ox = $banguage[1];
					$ox = str_replace('{IDENT}', $ident, $ox);
					$ox = str_replace('{SYMBOL}', $tag, $ox);
					$ox = str_replace('{USERNAME}', $postname, $ox);
					$ox = str_replace('{COLOR}', $color, $ox);
					$ox = str_replace('{MESSAGE}', $message, $ox);
					$output = $ox;
				}

				$write_me .= $output;
				time_point(__FILE__, __LINE__);
			}

			if ($image) {
			}

			if (($addok == 1) && ($cleared != 1)) {
				AddUser($handle, $ident, time(), $link, $image, $realpath);
				time_point(__FILE__, __LINE__);
			}

			if ($addok == 1) {
				require_once("../common/lastmsg.php");
				SetLastMsg($realpath);

				$currentday = date('Y-m-d');
				$currenthour = date('H');
				$currenthour = "h{$currenthour}=h{$currenthour}+1";
				count_mysql_query("UPDATE uo_chat_stats SET {$currenthour} WHERE chat='$realpath' AND date='{$currentday}'", $handler, "sendmsg.php: Updated 'stats' 1/3");
				if (mysql_affected_rows($handler) < 1) {
					if (mt_rand(1,100) == 50) {
						count_mysql_query("DELETE FROM uo_chat_stats WHERE date<=DATE_SUB(NOW(), INTERVAL 28 DAY)", $handler, "sendmsg.php: Updated 'stats' 2/3");
					}
					count_mysql_query("INSERT INTO uo_chat_stats SET chat='$realpath', date='{$currentday}', {$currenthour}", $handler, "sendmsg.php: Updated 'stats' 3/3");
                }
				time_point(__FILE__, __LINE__);
			}

			if ($client['password']) {
				@count_mysql_query("UPDATE uo_chat_database SET lastlogin='".(time())."',plink='".(mysql_real_escape_string($link))."',pimage='".mysql_real_escape_string($image)."',picon='".mysql_real_escape_string(serialize($_REQUEST['icons']))."',pcolor='$color' WHERE chat='$chatpath' AND username='$fixhandle' AND dtime IS NULL", $handler, "sendmsg.php: Updated 'reload' 1/1");
				time_point(__FILE__, __LINE__);
			}
		}

	$ident = $_SESSION[$realpath]['ident'];

	if ($maxlines > 256) {
		$maxlines = 256;
    }

	time_point(__FILE__, __LINE__);
	if (!empty($write_me)) {
		$write_me = str_replace('{TIMESTAMP}', date($dtcalc, time()+($tzone*3600)), $write_me);
		$write_me = str_replace('{USERNAME}', $handle, $write_me);
		$write_me = str_replace('{IDENT}', $ident, $write_me);
		$write_me = str_replace('{COLOR}', $color, $write_me);
		$write_me = str_replace('{LINK}', $link, $write_me);
		$write_me = str_replace('{IMAGE}', $image, $write_me);
		$write_me = str_replace('{ICON}', $outicon, $write_me);

		$tpack = mysql_real_escape_string($write_me);

		if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		    $proxyip = 'NULL';
		}
		else {
		    $proxyip = "'".mysql_real_escape_string(preg_replace('@([0-9.]+)(.*?)@', '\1', $_SERVER['HTTP_X_FORWARDED_FOR']))."'";
        }

		$uid = 'null';
		if (!empty($_SESSION[$realpath]['user']['uid'])) {
			$uid = intval($_SESSION[$realpath]['user']['uid']);
		}

		$oldident = $_SESSION[$realpath]['ident'];
  		if (!$undoable) {
			$ident = 'U'.$ident;
		}
		if ($GLOBALS['delete-existing'] == true) {
			count_mysql_query("DELETE FROM uo_chat_log WHERE chat = '$realpath' AND ident = '$ident' AND line = '$tpack'", $handler, "sendmsg.php: Post");
		}
		count_mysql_query("INSERT INTO uo_chat_log
			(chat,ident,line,username,ip,proxyip,ip_n,posttime,
			rawpost,xmlpost,color,uid
			)
			VALUES
			('$realpath','$ident','$tpack','".mysql_real_escape_string($handle)."','{$_SERVER['REMOTE_ADDR']}',
			{$proxyip},INET_ATON('{$_SERVER['REMOTE_ADDR']}'), now(),
			'".mysql_real_escape_string($xml['rawpost'])."',
			'".mysql_real_escape_string($xml['post'])."',
			'".mysql_real_escape_string($color)."', ".$uid."
			)", $handler, "sendmsg.php: Post 2/3");
		time_point(__FILE__, __LINE__);

        //*
        $enc = mb_detect_encoding($write_me, 'UTF-8, ISO-8859-1');
        $write_me_pgsql = iconv($enc, 'UTF-8//TRANSLIT', $write_me);
        if (empty($write_me_pgsql)) {
        	$write_me_pgsql = '(null)<br/>';
        }
        $enc = mb_detect_encoding($GLOBALS['biglog']['user_name'], 'UTF-8, ISO-8859-1');
        $GLOBALS['biglog']['user_name'] = iconv($enc, 'UTF-8//TRANSLIT', $GLOBALS['biglog']['user_name']);
        if (empty($GLOBALS['biglog']['user_name'])) {
        	$GLOBALS['biglog']['user_name'] = '(null)';
        }

        $mid = $GLOBALS['sql']->nextID('chatv2logs.log_'.$GLOBALS['biglog']['chat_id'].'_message_id_seq');
		time_point(__FILE__, __LINE__);
		$query = "INSERT INTO chatv2logs.log_{$GLOBALS['biglog']['chat_id']}
		(message_id, message, user_id, user_ident, user_name, stamp, stamp_year, stamp_week)
		VALUES ($mid,
		".$GLOBALS['sql']->escapeOrNullString($write_me_pgsql).",
		{$GLOBALS['biglog']['user_id']},
		'{$oldident}',
		".$GLOBALS['sql']->escapeOrNullString($GLOBALS['biglog']['user_name']).",
		now(),
        to_char(now(), 'IYYY')::int4,
        to_char(now(), 'IW')::int4
		)";
        $GLOBALS['sql']->begin();
        if ($GLOBALS['sql']->query($query) === false) {
            $GLOBALS['sql']->rollback();
        }
		else {
            $GLOBALS['sql']->commit();
        }
        //*/

		$ident = $_SESSION[$realpath]['ident'];
	}
	time_point(__FILE__, __LINE__);

	$c = count($images);
	for($i=0;$i<$c;$i++) {
		if (strstr($images[$i], '../master/')) {
			$images[$i] = str_replace('../master/', 'https://pjj.cc/master/', $images[$i]);
		}
		if (strstr($images[$i], '../')) {
			$images[$i] = str_replace('../', 'https://pjj.cc/', $images[$i]);
		}
	}

	if ($exit == 0) {
	$_REQUEST['oldhandle'] = htmlentities($_REQUEST['oldhandle']);
	$handle = str_replace('<', '&lt;', $handle);
	$handle = str_replace('>', '&gt;', $handle);
	echo '<!DOCTYPE html>
<html>
<head>
	<meta name="robots" content="noarchive">
	<meta name="robots" content="nofollow">
	<meta http-equiv="content-type" content="text/html;charset=utf-8">
	<title>', $ctitle, '</title>
	<script type="text/javascript" src="https://pjj.cc/common/js/functions.js"></script>
	<style type="text/css">', $csshead, '</style>
</head>', $cbodytag;

if (!empty($falselog)) {
	echo 'Wrong password for ', $falselog, '(<a href="https://pjj.cc/common/password.php?chat=', $newpath, '" target=_blank style="font-size: 8pt;">Have you lost it? Click here.</a>).<br>';
}
if (CheckGag($ident,$chatpath)) {
	echo $language[17];
}

	echo '<table border=0 cellspacing=0 cellpadding=1 width="100%">
<tr><td align=left valign=top>
<form method="post" action="sendmsg.php" name="chat">
<input type=hidden name="oldhandle" value="', $_REQUEST["oldhandle"], '">

<table border=0 width="100%">
<tr><td valign=top width="100%" style="white-space: nowrap;"> ';

if (CheckFlags("s", $cpref)) {
	echo '<input type=text name="message" size=60 style="width: 70%;" spellcheck="true"> ';
}
else if (CheckFlags("m", $client['prefs']) || CheckFlags("l", $cpref) || !empty($_REQUEST['multiline'])) {
	echo '<textarea name="message" style="width: 70%;" cols="30" rows="3" spellcheck="true"></textarea> ';
}
else {
	echo '<input type=text name="message" size=60 style="width: 70%;" spellcheck="true"> ';
}

//*
//if ($client['password']) {
    $rez = count_mysql_query("SELECT username,displayname FROM uo_chat_database WHERE chat='{$realpath}' AND username!='{$fixhandle}' AND lastlogin>UNIX_TIMESTAMP()-2592000 AND dtime IS NULL ORDER BY username ASC", $handler, 'sendmsg.php: Msglist 1/1');
    if (mysql_num_rows($rez) > 0) {
		echo '<span title="/msg ...">';
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false) {
			echo '<select class="select-channel" name="channel" style="width: 15%; font-size: 70%;">', "\n";
		}
		else {
			echo '<select class="select-channel" name="channel" style="width: 15%;">', "\n";
		}
		echo '<option value="*" selected>(public)</option>', "\n";
		while($row = mysql_fetch_assoc($rez)) {
			if (empty($row['displayname'])) {
				$row['displayname'] = ucwords($row['username']);
			}
			echo '<option value="', htmlentities($row['username']), '"';
			//if ($row['username'] == $_REQUEST['channel'])
			//echo ' selected';
			echo '>', htmlentities($row['displayname']), '</option>', "\n";
		}
		echo '</select></span> ';
    }
    mysql_free_result($rez);
//}
//*/
//echo '</td><td style="white-space: nowrap;">';
echo '<span style="width: 15%;">';

if ($_REQUEST['mode_simple'] != 'on') {
	echo '<span title="Advanced Layout">A<input class=boxes type=checkbox name="mode_advanced"';
	if ($_REQUEST['mode_advanced'] == 'on') {
		echo ' CHECKED';
	}
	echo '></span>';
}
if ($_REQUEST['mode_advanced'] != 'on') {
	echo '<span title="Simple Layout">S<input class=boxes type=checkbox name="mode_simple"';
	if ($_REQUEST['mode_simple'] == 'on') {
		echo ' CHECKED';
	}
	echo '></span>';
}
echo '<span title="Multi-line Input">M<input class=boxes type=checkbox name="multiline"';
if ($_REQUEST['multiline'] == 'on') {
	echo ' CHECKED';
}
echo '></span>';
echo '</span>';

echo '</td></tr>

<tr><td valign=top>';

if ($_REQUEST['color'] == 'random') {
	$color = 'random';
}

time_point(__FILE__, __LINE__);
if ($_REQUEST['mode_simple'] != 'on') {
	if (!CheckFlags("L", $client['prefs'])) {
		echo '<input type="text" name="color" size="6" value="', $color, '">';
		echo '<a href="#" onclick="openColorPicker(\'', $realpath, '\');">@</a>';
	}
	else {
		echo '<input type="hidden" name="color" value="$color">';
	}

	//if ($client['password']) {
        for ($i=0;$i<$numicons;$i++) {
            $icon = $_REQUEST['icons'][$i];
            if (!CheckFlags("c", $client['prefs'])) {
                echo '<select class="select-icons" name="icons[', $i, ']">', "\n";
                echo '<option value="">No Icon</option>';
                if (!empty($facticon)) {
                    if ($icon == $facticon) {
                        echo '<option value="', $facticon, '" selected>Faction Icon</option>', "\n";
					}
                    else {
                        echo '<option value="', $facticon, '">Faction Icon</option>', "\n";
					}
                }
                foreach ($picons as $name => $file) {
                    if ($icon == $file) {
                        echo '<option value="', $file, '" selected>', $name, '</option>', "\n";
					}
                    else {
                        echo '<option value="', $file, '">', $name, '</option>', "\n";
					}
                }
                if (($client['password']) && (!CheckFlags('z', $client['flags_user']))) {
                    foreach ($icons as $name => $file) {
                        if ($icon == $file) {
                            echo '<option value="', $file, '" selected>', $name, '</option>', "\n";
						}
                        else {
                            echo '<option value="', $file, '">', $name, '</option>', "\n";
						}
                    }

                    if (!empty($client['icon'])) {
                        $lines = explode("\n", $client['icon']);
                        for($count=0; $count < count($lines); $count++) {
                            list ($name,$file) = explode('¥',$lines[$count]);
                            if (!empty($file)) {
                                if ($icon == $file) {
                                    echo '<option value="', $file, '" selected>', $name, '</option>', "\n";
								}
                                else {
                                    echo '<option value="', $file, '">', $name, '</option>', "\n";
								}
                            }
                        }
                    }
                }
                echo '</select>';
            }
			else if (!empty($icon)) {
                echo '<input type="hidden" name="icons[', $i, ']" value="', $icon, '">';
            }
        }
	//}

	if (!CheckFlags('T', $client['prefs'])) {
		echo "&nbsp;<span title='Bold Text'><b>B</b><input class=boxes type=checkbox name='st_bold'";
		if ($_REQUEST['st_bold'] == 'on') {
			echo " CHECKED";
		}
		echo "></span>";
		echo " <span title='Italic Text'><i>I</i><input class=boxes type=checkbox name='st_italic'";
		if ($_REQUEST['st_italic'] == 'on') {
			echo " CHECKED";
		}
		echo "></span>";
		echo " <span title='Underlined Text'><u>U</u><input class=boxes type=checkbox name='st_ulined'";
		if ($_REQUEST['st_ulined'] == 'on') {
			echo " CHECKED";
		}
		echo "></span>";
		echo " <span title='Fixed Width Text'><tt>T</tt><input class=boxes type=checkbox name='st_tt'";
		if ($_REQUEST['st_tt'] == 'on') {
			echo " CHECKED";
		}
		echo "></span>";
		echo " <span title='Out of Character'>O<input class=boxes type=checkbox name='st_ooc'";
		if ($_REQUEST['st_ooc'] == 'on') {
			echo " CHECKED";
		}
		echo "></span>";
		echo " <span title='Reversed View'>R<input class=boxes type=checkbox name='st_rev'";
		if ($_REQUEST['st_rev'] == 'on') {
			echo ' CHECKED';
		}
		echo '></span>';
	}
	else {
echo "<input type=hidden name=st_bold value='{$_REQUEST['st_bold']}'>
<input type=hidden name=st_italic value='{$_REQUEST['st_italic']}'>
<input type=hidden name=st_ulined value='{$_REQUEST['st_ulined']}'>
<input type=hidden name=st_ooc value='{$_REQUEST['st_ooc']}'>
<input type=hidden name=st_tt value='{$_REQUEST['st_tt']}'>
<input type=hidden name=st_rev value='{$_REQUEST['st_rev']}'>";
	}

//*
	echo <<<OUTPUT
<script type="text/javascript" language="javascript">
function JJEmoticon(emots) {
	if (emots.selectedIndex > 0 && emots.options[emots.selectedIndex].value != '') {
		document.forms['chat'].elements['message'].value += emots.options[emots.selectedIndex].value;
		emots.selectedIndex = 0;
	}
}
</script>

<style type="text/css">
option.jjemotimages {
	min-width: 25ex;
	height: 20px;
	padding: 1px;
	background-repeat: no-repeat;
	background-position: top left;
	text-align: right;
}
</style>

<select class="select-emoticons" name="emoticons" onchange="JJEmoticon(this); return 1;" style="width: 12ex;">

OUTPUT;

	if (!empty($banwords)) {
		echo '<option value="" selected>(inserts)</option>', "\n";
	}

	foreach ($banwords as $key => $val) {
		$key = htmlentities($key);
		if (stripos($val, '<img') === 0) {
			$src = trim(preg_replace('@^.*<img.+src=[\'"]?([^\'"]+)[\'"]?.*>.*$@is', '\1', $val));
			if (!empty($src)) {
				echo <<<OUTPUT
				<option value="$key" class="jjemotimages" style="background-image: url($src);">$key</option>

OUTPUT;
			}
		}
		else {
            $val = trim(strip_tags($val));
            if (strlen($val) > 35) {
                $val = mb_substr($val, 0, 32).'...';
            }
			$val = htmlentities($val);
			if (!empty($val)) {
				echo <<<OUTPUT
				<option value="$key">$val</option>

OUTPUT;
			}
		}
	}
	echo <<<OUTPUT
	<option value="">(commands)</option>
	<option value="/me jumps around like a frog on drugs.">Action: /me</option>
	<option value="/nick Guess Who!">Change handle: /nick</option>
	<option value="/link http://example.com/">Change link: /link</option>
	<option value="/image https://i.pjj.cc/08e14eb330404bf7ef95f81e0be4b0d8.gif">Change image: /image</option>
	<option value="/dice 5d20 +5">Roll dice: /dice</option>
	<option value="/xball">Magic 8-Ball</option>
	<option value="/whois Tino Didriksen">Get information: /whois</option>
	<option value="/strike put a word here and all your lines with that word will be lined out">Strike lines: /strike</option>

OUTPUT;
	echo "</select>";
//*/

}
else {
	echo "<input type=hidden name=color value='$color'>
<input type=hidden name=icons value='".htmlentities(serialize($_REQUEST['icons']))."'>
<input type=hidden name=st_bold value='{$_REQUEST['st_bold']}'>
<input type=hidden name=st_italic value='{$_REQUEST['st_italic']}'>
<input type=hidden name=st_ulined value='{$_REQUEST['st_ulined']}'>
<input type=hidden name=st_tt value='{$_REQUEST['st_tt']}'>
<input type=hidden name=st_rev value='{$_REQUEST['st_rev']}'>";
}
echo '</td></tr><tr><td valign="bottom">';

time_point(__FILE__, __LINE__);
if (!CheckFlags('X', $client['prefs'])) {
	if (preg_match('@^(ht|f)tps?://@ui', $images[8])) {
		echo '<input type="image" src="', htmlentities($images[8]), '" alt="Post" border="0" class="boxes">';
	}
	else {
		echo '<button alt="Post" class="boxes">', $images[8], '</button>';
	}
	echo '<img src="', htmlentities($images[14]), '" border="0">';
	if (preg_match('@^(ht|f)tps?://@ui', $images[12])) {
		echo '<a href="javascript:document.forms[\'undo\'].submit()"><img alt="Undo" src="', htmlentities($images[12]), '" border="0"></a>';
	}
	else {
		echo '<a href="javascript:document.forms[\'undo\'].submit()" title="Undo">', $images[12], '</a>';
	}
	echo '<img src="', htmlentities($images[14]), '" border="0">';
	if (preg_match('@^(ht|f)tps?://@ui', $images[10])) {
		echo '<a href="reader.php?cspeed=', $cspeed, '&amp;motd=', $motd, '&amp;random=', rand(), '&amp;reverse=', $_REQUEST['st_rev'], (($_REQUEST['st_rev'] == 'on') ? ('#down') : ('')), '" target="TextWindow"><img alt="Refresh" src="', htmlentities($images[10]), '" border="0"></a>';
	}
	else {
		echo '<a href="reader.php?cspeed=', $cspeed, '&amp;motd=', $motd, '&amp;random=', rand(), '&amp;reverse=', $_REQUEST['st_rev'], (($_REQUEST['st_rev'] == 'on') ? ('#down') : ('')), '" target="TextWindow" title="Refresh">', $images[10], '</a>';
	}
	echo '<img src="', htmlentities($images[14]), '" border="0">';
	if (preg_match('@^(ht|f)tps?://@ui', $images[13])) {
		echo '<a href="userlist.php" target="Userlist"><img alt="Userlist" src="', htmlentities($images[13]), '" border="0"></a>';
	}
	else {
		echo '<a href="userlist.php" target="Userlist" title="Userlist">', $images[13], '</a>';
	}

	if ($client['password']) {
		echo '<img src="', htmlentities($images[14]), '" border="0">';
		if (preg_match('@^(ht|f)tps?://@ui', $images[5])) {
			echo '<a href="register/login.php" target="_blank"><img alt="Members" src="', htmlentities($images[5]), '" border="0"></a>';
		}
		else {
			echo '<a href="register/login.php" target="_blank" title="Members">', $images[5], '</a>';
		}
	}

	echo '</td></tr>';
}
echo '</table>';

	if ($_REQUEST['mode_advanced'] == 'on') {
		echo "<input type=text name='handle' value=\"$handle\" size=10>
		<input type=text name='link' value='$link' size=10>
		<input type=text name='image' value='$image' size=10>
		<input type=hidden name='firstmsg' value='{$_REQUEST['firstmsg']}'>
		<input type=password name='password' value='{$_REQUEST['password']}' size=10>
		<input type=text name='cspeed' value='$cspeed' maxlenght=3 size=2>
		<input type=text name='ooc_l' value='$ooc_l' maxlenght=5 size=2>
		<input type=text name='ooc_r' value='$ooc_r' maxlenght=5 size=2>
		<input class=boxes type=checkbox name='motd'";
		if ($motd == 'on') {
			echo " CHECKED";
		}
		echo "></form>";
	}
	else {
		echo "<input type=hidden name='handle' value=\"$handle\">
<input type=hidden name='firstmsg' value='{$_REQUEST['firstmsg']}'>
<input type=hidden name='link' value='$link'>
<input type=hidden name='cspeed' value='$cspeed'>
<input type=hidden name='image' value='$image'>
<input type=hidden name='password' value='{$_REQUEST['password']}'>
<input type=hidden name='motd' value='$motd'></form>
<table border=0 cellspacing=0 cellpadding=0><tr valign=top align=center>";
		if (!CheckFlags('N', $client['prefs'])) {
			$ox = $language[11];
			$ox = str_replace('{USERNAME}', $handle, $ox);
			echo '<td>', $ox, '</td><td width="5"> </td>';
		}
		echo '<td>';

	if (!empty($client['chain']) && is_array($client['chain'])) {
		echo '<form action="sendmsg.php" method="post" name="changer">
		<select class="select-handle" name="handle" onChange="form.submit()">', "\n";

		echo '<option value="', htmlentities($handle), '">Chain</option>', "\n";
		foreach ($client['chain'] as $ch) {
			if (!empty($ch)) {
				$ch = htmlentities($ch);
				echo '<option value="', $ch, '">', $ch, '</option>', "\n";
			}
		}
		echo '<option value="(random)">(random)</option>', "\n";

		echo '</select>
<input type="hidden" name="oldhandle" value="', htmlentities($_REQUEST['oldhandle']), '">
<input type="hidden" name="password" value="', htmlentities($_REQUEST['password']), '">
<input type="hidden" name="cspeed" value="', $cspeed, '">
<input type="hidden" name="reload" value="on">
<input type="hidden" name="motd" value="', $motd, '">
<input type="hidden" name="firstmsg" value="', htmlentities($_REQUEST['firstmsg']), '">
</form>';
	}

echo '</td><td width="5"> </td><td align="right">';

if (($_REQUEST['mode_simple'] != 'on') && !CheckFlags("C", $client['prefs'])) {
	$button = "<img alt='Refresh' src='{$images[10]}' border=0>";
	if (!preg_match('@^(ht|f)tps?://@ui', $images[10])) {
		$button = $images[10];
	}
	echo "<form action='sendmsg.php' method=post name='refresh'>
<input type=text maxlenght=3 size=2 name='cspeed' value='$cspeed'>
<a href='javascript:document.forms[\"refresh\"].submit()'>$button</a>
<input type=hidden name='oldhandle' value=\"{$_REQUEST['oldhandle']}\">
<input type=hidden name='handle' value=\"$handle\">
<input type=hidden name='firstmsg' value='{$_REQUEST['firstmsg']}'>
<input type=hidden name='link' value='$link'>
<input type=hidden name='image' value='$image'>
<input type=hidden name=icons value='".htmlentities(serialize($_REQUEST['icons']))."'>
<input type=hidden name='motd' value='$motd'>
<input type=hidden name='color' value='$color'>
<input type=hidden name='password' value='{$_REQUEST['password']}'>
<input type=hidden name=st_bold value='{$_REQUEST['st_bold']}'>
<input type=hidden name=st_italic value='{$_REQUEST['st_italic']}'>
<input type=hidden name=st_ulined value='{$_REQUEST['st_ulined']}'>
<input type=hidden name=st_ooc value='{$_REQUEST['st_ooc']}'>
<input type=hidden name=st_tt value='{$_REQUEST['st_tt']}'>
<input type=hidden name=st_rev value='{$_REQUEST['st_rev']}'>
</form>";
}

echo "<form action='sendmsg.php' method=post name='undo'>
<input type=hidden name='oldhandle' value=\"{$_REQUEST['oldhandle']}\">
<input type=hidden name='handle' value=\"$handle\">
<input type=hidden name='firstmsg' value='{$_REQUEST['firstmsg']}'>
<input type=hidden name='link' value='$link'>
<input type=hidden name='cspeed' value='$cspeed'>
<input type=hidden name='image' value='$image'>
<input type=hidden name=icons value='".htmlentities(serialize($_REQUEST['icons']))."'>
<input type=hidden name='motd' value='$motd'>
<input type=hidden name='color' value='$color'>
<input type=hidden name='message' value='/undo'>
<input type=hidden name='mode_simple' value='{$_REQUEST['mode_simple']}'>
<input type=hidden name='password' value='{$_REQUEST['password']}'>
<input type=hidden name=st_bold value='{$_REQUEST['st_bold']}'>
<input type=hidden name=st_italic value='{$_REQUEST['st_italic']}'>
<input type=hidden name=st_ulined value='{$_REQUEST['st_ulined']}'>
<input type=hidden name=st_ooc value='{$_REQUEST['st_ooc']}'>
<input type=hidden name=st_tt value='{$_REQUEST['st_tt']}'>
<input type=hidden name=st_rev value='{$_REQUEST['st_rev']}'>
</form>
</td></tr></table>";
}
echo "</td>

<td valign=top align=right>
<!-- <a href='register/viewer.php' target=_blank><img alt='Profiles' src='$images[9]' border=0></a><br> -->
<img src='$images[14]' border=0><br>";

	//*
	if (!empty($_SESSION[$realpath]['user']['uid'])) {
		$result = @count_mysql_query("SELECT count(*) as cnt FROM uo_chat_message WHERE rcpt_uid=".$_SESSION[$realpath]['user']['uid']." AND archived='no'", $handler, "sendmsg.php: Check 'private msg' 1/1");
		$nmsg = mysql_fetch_assoc($result);
		$rmsg = intval($nmsg['cnt']);
		mysql_free_result($result);

		$result = @count_mysql_query("SELECT count(*) as cnt FROM uo_chat_message WHERE rcpt_uid=".$_SESSION[$realpath]['user']['uid']." AND unread='yes'", $handler, "sendmsg.php: Check 'private msg' 1/1");
		$nmsg = mysql_fetch_assoc($result);
		$umsg = intval($nmsg['cnt']);
		mysql_free_result($result);

		if (!empty($umsg)) {
			echo '[<span style="color: #ff0000;">', $umsg, '</span>] ';
		}

		$result = @count_mysql_query("SELECT count(*) as cnt FROM uo_chat_message WHERE rcpt_uid=".$_SESSION[$realpath]['user']['uid']." AND archived='yes'", $handler, "sendmsg.php: Check 'private msg' 1/1");
		$nmsg = mysql_fetch_assoc($result);
		$amsg = intval($nmsg['cnt']);
		mysql_free_result($result);

		$button = "<img alt='$umsg/$rmsg/$amsg  unread/read/archived messages' src='{$images[6]}' border=0>";
		if (!preg_match('@^(ht|f)tps?://@ui', $images[6])) {
			$button = $images[6];
		}
		echo "<a href='reader.php?p=msgs&uid=", $_SESSION[$realpath]['user']['uid'], "' target='TextWindow' title='", $umsg, "/", $rmsg, "/", $amsg, " unread/read/archived messages'>$button</a>";
		echo "<br><img src='", $images[14], "' border=0><br>";
	}
	//*/

	if (!empty($musiclink)) {
		$button = "<img alt='Misc' src='{$images[7]}' border=0>";
		if (!preg_match('@^(ht|f)tps?://@ui', $images[7])) {
			$button = $images[7];
		}
		echo "<a href='$musiclink' target=_blank>$button</a><br>
<img src='{$images[14]}' border=0><br>";
	}

$button = "<img alt='Exit' src='{$images[3]}' border=0>";
if (!preg_match('@^(ht|f)tps?://@ui', $images[3])) {
	$button = $images[3];
}
echo "<a href='javascript:document.forms[\"exit\"].submit()'>$button</a>
<form method=post action='sendmsg.php' name='exit'>
<input type=hidden name='oldhandle' value=\"{$_REQUEST['oldhandle']}\">
<input type=hidden name='message' value='/exit {TIMESTAMP}'>
<input type=hidden name='handle' value=\"$handle\">
<input type=hidden name='color' value='$color'>
<input type=hidden name='firstmsg' value='{$_REQUEST['firstmsg']}'>
<input type=hidden name='password' value='{$_REQUEST['password']}'>
<input type=hidden name='link' value='$link'>
<input type=hidden name='image' value='$image'>
<input type=hidden name=icons value='".htmlentities(serialize($_REQUEST['icons']))."'>
</form>";

echo "</td></tr></table>";
time_point(__FILE__, __LINE__);
		CacheChatLines();
time_point(__FILE__, __LINE__);
		if (!empty($write_me)) {
			echo "<script type='text/javascript'>
	if (window.parent.frames['XMLSocket'] && window.parent.frames['XMLSocket'].socket && window.parent.frames['XMLSocket'].socket.send) {
		window.parent.frames['XMLSocket'].socket.send('POST {$GLOBALS['biglog']['real_id']}');
	}
</script>";
		}
		echo "<script type='text/javascript'>
	parent.frames['TextWindow'].location = 'reader.php?cspeed=$cspeed&motd=$motd&reverse={$_REQUEST['st_rev']}".(($_REQUEST['st_rev'] == 'on') ? ("#down") : (""))."';
	document.forms['chat'].elements['message'].focus();
</script>
</body></html>";
	}
	else {
		CacheChatLines();
		DeleteUser($ident, $realpath);
		unset($color);
		unset($handle);
		unset($ident);
		$logout = 1;
		require_once("login.php");
	}
}
