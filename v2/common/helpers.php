<?php

if (!function_exists('count_mysql_query')) {
    function count_mysql_query($query, $hand, $reason="Unknown") {
    	global $cqs, $creas;

    	$cqs++;
    	$creas .= "<br>$reason\n";

		$rez = mysqli_query($hand, $query);
		if ($err = mysqli_errno($hand)) {
			echo "\n<!-- MySQL error {$err} for {$query} -->\n";
		}
		return $rez;
    }
}

function time_point($file, $line) {
	if (empty($GLOBALS['-x-time-point'])) {
		$GLOBALS['-x-time-point'] = microtime(true);
	}
	$step = microtime(true);
	echo "\n<!-- $file: $line: ", round($step - $GLOBALS['-x-time-point'], 4), " -->\n";
	$GLOBALS['-x-time-point'] = $step;
}

function FixColor($color='') {
	$color = trim($color);
	$color = strtolower($color);
	$color = str_replace('#', '', $color);

	if (empty($color) || strcmp($color, 'random') == 0) {
		return str_pad(dechex(mt_rand(0,255)), 2, '0', STR_PAD_LEFT).str_pad(dechex(mt_rand(0,255)), 2, '0', STR_PAD_LEFT).str_pad(dechex(mt_rand(0,255)), 2, '0', STR_PAD_LEFT);
	}

	$names = array(
		'aliceblue' => 'f0f8ff',
		'antiquewhite' => 'faebd7',
		'aqua' => '00ffff',
		'aquamarine' => '7fffd4',
		'azure' => 'f0ffff',
		'beige' => 'f5f5dc',
		'bisque' => 'ffe4c4',
		'black' => '000000',
		'blanchedalmond' => 'ffebcd',
		'blue' => '0000ff',
		'blueviolet' => '8a2be2',
		'brown' => 'a52a2a',
		'burlywood' => 'deb887',
		'cadetblue' => '5f9ea0',
		'chartreuse' => '7fff00',
		'chocolate' => 'd2691e',
		'coral' => 'ff7f50',
		'cornflowerblue' => '6495ed',
		'cornsilk' => 'fff8dc',
		'crimson' => 'dc143c',
		'cyan' => '00ffff',
		'darkblue' => '00008b',
		'darkcyan' => '008b8b',
		'darkgoldenrod' => 'b8860b',
		'darkgray' => 'a9a9a9',
		'darkgreen' => '006400',
		'darkkhaki' => 'bdb76b',
		'darkmagenta' => '8b008b',
		'darkolivegreen' => '556b2f',
		'darkorange' => 'ff8c00',
		'darkorchid' => '9932cc',
		'darkred' => '8b0000',
		'darksalmon' => 'e9967a',
		'darkseagreen' => '8fbc8f',
		'darkslateblue' => '483d8b',
		'darkslategray' => '2f4f4f',
		'darkturquoise' => '00ced1',
		'darkviolet' => '9400d3',
		'deeppink' => 'ff1493',
		'deepskyblue' => '00bfff',
		'dimgray' => '696969',
		'dodgerblue' => '1e90ff',
		'firebrick' => 'b22222',
		'floralwhite' => 'fffaf0',
		'forestgreen' => '228b22',
		'fuchsia' => 'ff00ff',
		'gainsboro' => 'dcdcdc',
		'ghostwhite' => 'f8f8ff',
		'gold' => 'ffd700',
		'goldenrod' => 'daa520',
		'gray' => '808080',
		'green' => '008000',
		'greenyellow' => 'adff2f',
		'honeydew' => 'f0fff0',
		'hotpink' => 'ff69b4',
		'indianred' => 'cd5c5c',
		'indigo' => '4b0082',
		'ivory' => 'fffff0',
		'khaki' => 'f0e68c',
		'lavender' => 'e6e6fa',
		'lavenderblush' => 'fff0f5',
		'lawngreen' => '7cfc00',
		'lemonchiffon' => 'fffacd',
		'lightblue' => 'add8e6',
		'lightcoral' => 'f08080',
		'lightcyan' => 'e0ffff',
		'lightgoldenrodyellow' => 'fafad2',
		'lightgrey' => 'd3d3d3',
		'lightgreen' => '90ee90',
		'lightpink' => 'ffb6c1',
		'lightsalmon' => 'ffa07a',
		'lightseagreen' => '20b2aa',
		'lightskyblue' => '87cefa',
		'lightslategray' => '778899',
		'lightsteelblue' => 'b0c4de',
		'lightyellow' => 'ffffe0',
		'lime' => '00ff00',
		'limegreen' => '32cd32',
		'linen' => 'faf0e6',
		'magenta' => 'ff00ff',
		'maroon' => '800000',
		'mediumaquamarine' => '66cdaa',
		'mediumblue' => '0000cd',
		'mediumorchid' => 'ba55d3',
		'mediumpurple' => '9370d8',
		'mediumseagreen' => '3cb371',
		'mediumslateblue' => '7b68ee',
		'mediumspringgreen' => '00fa9a',
		'mediumturquoise' => '48d1cc',
		'mediumvioletred' => 'c71585',
		'midnightblue' => '191970',
		'mintcream' => 'f5fffa',
		'mistyrose' => 'ffe4e1',
		'moccasin' => 'ffe4b5',
		'navajowhite' => 'ffdead',
		'navy' => '000080',
		'oldlace' => 'fdf5e6',
		'olive' => '808000',
		'olivedrab' => '6b8e23',
		'orange' => 'ffa500',
		'orangered' => 'ff4500',
		'orchid' => 'da70d6',
		'palegoldenrod' => 'eee8aa',
		'palegreen' => '98fb98',
		'paleturquoise' => 'afeeee',
		'palevioletred' => 'd87093',
		'papayawhip' => 'ffefd5',
		'peachpuff' => 'ffdab9',
		'peru' => 'cd853f',
		'pink' => 'ffc0cb',
		'plum' => 'dda0dd',
		'powderblue' => 'b0e0e6',
		'purple' => '800080',
		'red' => 'ff0000',
		'rosybrown' => 'bc8f8f',
		'royalblue' => '4169e1',
		'saddlebrown' => '8b4513',
		'salmon' => 'fa8072',
		'sandybrown' => 'f4a460',
		'seagreen' => '2e8b57',
		'seashell' => 'fff5ee',
		'sienna' => 'a0522d',
		'silver' => 'c0c0c0',
		'skyblue' => '87ceeb',
		'slateblue' => '6a5acd',
		'slategray' => '708090',
		'snow' => 'fffafa',
		'springgreen' => '00ff7f',
		'steelblue' => '4682b4',
		'tan' => 'd2b48c',
		'teal' => '008080',
		'thistle' => 'd8bfd8',
		'tomato' => 'ff6347',
		'turquoise' => '40e0d0',
		'violet' => 'ee82ee',
		'wheat' => 'f5deb3',
		'white' => 'ffffff',
		'whitesmoke' => 'f5f5f5',
		'yellow' => 'ffff00',
		'yellowgreen' => '9acd32'
	);

	if (!empty($names[$color])) {
		return $names[$color];
	}

	$color = preg_replace('@[^0-9a-f]@', '0', $color);
	if (strlen($color) < 6) {
		$color = str_pad($color, 6, '0');
	}
	else if (strlen($color) > 6) {
		$color = substr($color, 0, 6);
	}

	return $color;
}

function ChatVerifyLogin($username, $password, $chatpath) {
	global $handler, $master_name_filter;

	$username = strtolower($username);
	$username = preg_replace('~'.$master_name_filter.'~i', "", $username);

	/*
	if ($username == 'tino didriksen' && !preg_match('/^130\.22(5|6)/', $_SERVER['REMOTE_ADDR'])) {
		return -1;
	}
	//*/

	$result = @count_mysql_query("SELECT username,password,flags,uid,email FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler, "helpers.php: ChatVerifyLogin() 1/2");
	$cuser = mysqli_fetch_assoc($result);
	$GLOBALS['biglog']['user_name'] = $cuser['username'];
	$GLOBALS['biglog']['user_id'] = intval($cuser['uid']+0);
	mysqli_free_result($result);

	if (empty($cuser['username'])) {
		return 0;
    }

	if (empty($_REQUEST['xml']) && strlen($password) != 32) {
		$password = md5($password);
	}

	$_REQUEST['error']['pwds'] = $cuser['password']." : ".$password;
	if (strcmp($password, $cuser['password']) == 0) {
		if (!empty($cuser['email']) && strpos($cuser['email'], '@') !== false) {
			$result = @count_mysql_query(
				"SELECT GROUP_CONCAT(DISTINCT flags) as flags
				FROM uo_chat_database
				WHERE chat='$chatpath'
				AND email='".mysqli_real_escape_string($handler, $cuser['email'])."'
				AND password='".mysqli_real_escape_string($handler, $cuser['password'])."'
				AND dtime IS NULL
				GROUP BY chat, email, password, dtime", $handler, "helpers.php: ChatVerifyLogin() 2/2");
			$flags = mysqli_fetch_assoc($result);
			mysqli_free_result($result);
			return '1'.$flags['flags'];
		}
		if (!empty($cuser['flags'])) {
			return '1'.$cuser['flags'];
		}
		else {
			return '1';
		}
	}
	else {
		return -1;
	}

	return 0;
}

function ChatFetchChain($chatpath, $password, $email) {
	global $handler;

	if (!empty($chatpath) && !empty($email) && !empty($password)) {
		$chain = array();
		$uids = array();
		$chatpath = mysqli_real_escape_string($handler, $chatpath);
		$email    = mysqli_real_escape_string($handler, $email);
		$password = mysqli_real_escape_string($handler, $password);

		$query = "SELECT chat,username,displayname,uid,email,password FROM uo_chat_database WHERE chat LIKE 'chat%' AND password='{$password}' AND email='{$email}' AND dtime IS NULL";
		$result = count_mysql_query($query, $handler, "helpers.php: ChatFetchChain() 1/1");
		while($row = mysqli_fetch_assoc($result)) {
			if (empty($row['displayname'])) {
				$row['displayname'] = ucwords($row['username']);
			}
			if (strcmp($row['chat'], $chatpath) == 0) {
				$chain[$row['uid']] = $row['displayname'];
			}

			if (empty($_SESSION['uids'])) {
				$_SESSION['uids'] = array();
			}
			$_SESSION['uids'][$row['uid']] = $row;
		}
		mysqli_free_result($result);

		return array('chain'=>$chain);
	}

	return false;
}

function ChatVerifyLoginFetch($username, $password, $chatpath) {
	global $handler, $master_name_filter;

	$username = strtolower($username);
	$username = preg_replace('~'.$master_name_filter.'~i', "", $username);

	/*
	if ($username == 'tino didriksen' && !preg_match('/^130\.22(5|6)/', $_SERVER['REMOTE_ADDR'])) {
		return -1;
	}
	//*/

	$result = @count_mysql_query("SELECT username,password,flags,faction,prefs,icon,chain,picon,pimage,plink,pcolor,uid,email,skype,lastfm,displayname
	FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler, "helpers.php: ChatVerifyFetchLogin() 1/2");
	$cuser = mysqli_fetch_assoc($result);
	$GLOBALS['biglog']['user_name'] = $username;
	$GLOBALS['biglog']['user_id'] = intval($cuser['uid']+0);
	mysqli_free_result($result);

	$_REQUEST['error']['pwds'] = $cuser['password'].' : '.md5($password);
	if ($cuser['username'] == $username) {
		if (strcmp($cuser['password'], $password) == 0 || strcmp($cuser['password'], md5($password)) == 0) {
			if (empty($cuser['displayname'])) {
				$cuser['displayname'] = ucwords($cuser['username']);
			}
			$_REQUEST['error']['sts'] = 1;
			$cuser['status'] = 1;
			$cuser['flags'] .= '1';
			$chain = ChatFetchChain($chatpath, $cuser['password'], $cuser['email']);
			if (!empty($chain['chain']) && is_array($chain['chain'])) {
				$cuser['chain'] .= "\n".implode("\n", $chain['chain']);
			}
			$_SESSION['uids'][$cuser['uid']] = $cuser;

			$cuser['flags_user'] = $cuser['flags'];
			if (!empty($cuser['email']) && strpos($cuser['email'], '@') !== false) {
				$result = @count_mysql_query(
					"SELECT GROUP_CONCAT(DISTINCT flags) as flags
					FROM uo_chat_database
					WHERE chat='$chatpath'
					AND email='".mysqli_real_escape_string($handler, $cuser['email'])."'
					AND password='".mysqli_real_escape_string($handler, $cuser['password'])."'
					AND dtime IS NULL
					GROUP BY chat, email, password, dtime", $handler, "helpers.php: ChatVerifyFetchLogin() 2/2");
				$flags = mysqli_fetch_assoc($result);
				$cuser['flags'] = $flags['flags'].'1';
				mysqli_free_result($result);
			}

			return $cuser;
		}
		else {
			$_REQUEST['error']['sts'] = -1;
			return array('status' => -1);
		}
	}

	$_REQUEST['error']['sts'] = 0;
	return array('status' => 0);
}

function ChangeDisplayName($uid=0, $dname='') {
	global $handler;
	$dname = mq($dname);
	count_mysql_query("UPDATE uo_chat_database SET displayname=$dname WHERE uid=$uid", $handler, "helpers.php: ChangeDisplayName() 1/1");
}

function GetPrefs($username, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT prefs FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler, $handler, "helpers.php: GetPrefs() 1/1");
	$cuser = mysqli_fetch_row($result);
	mysqli_free_result($result);

	return $cuser[0];
}

function GetChatPrefs($chatpath) {
	global $handler;
	unset($GLOBALS['chat_path']);
	unset($GLOBALS['chat_id']);

	if (strpos($chatpath, 'chat') === 0 && strlen($chatpath) > 4) {
		$chatpath = substr($chatpath, 4);
	}

	$query = "SELECT prefs,chat_id FROM chatv2.chats WHERE chat='{$chatpath}'";
	$result = $GLOBALS['sql']->query($query);
	$cuser = $GLOBALS['sql']->fetchAssoc($result);
	$GLOBALS['sql']->freeResult($result);

	if (empty($cuser['chat_id'])) {
		return false;
	}
	$GLOBALS['biglog']['chat_path'] = $GLOBALS['chat_path'] = $chatpath;
	$GLOBALS['biglog']['chat_id'] = $GLOBALS['chat_id'] = intval($cuser['chat_id']);

	return $cuser['prefs'];
}

function GetFlags($username, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT flags FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler, "helpers.php: GetFlags() 1/1");
	$cuser = mysqli_fetch_row($result);
	mysqli_free_result($result);

	return $cuser[0];
}

function CheckFlags($aflag, $flags) {
	for ($cc=0;$cc<strlen($aflag);$cc++) {
		if (strchr($flags, $aflag[$cc])) {
			return 1;
		}
	}

	return 0;
}

function AddUser($new_name, $new_ident, $new_time, $new_link, $new_img, $chatpath) {
	global $handler;
	global $cpref;

	$new_link = str_replace("'","`",$new_link);
	$new_img = str_replace("'","`",$new_img);
	$new_name = str_replace("'","`",$new_name);

	$extra = '';
	if (CheckFlags('N', $cpref)) {
	    $extra = "username='$new_name' OR";
	}

	@count_mysql_query("DELETE FROM uo_chat_ulist WHERE chat='$chatpath' AND ($extra ident='$new_ident')", $handler, "helpers.php: AddUser() 1/2");
	@count_mysql_query("INSERT INTO uo_chat_ulist (chat,ident,username,link,image,utime) VALUES ('$chatpath','$new_ident','$new_name','$new_link','$new_img','$new_time')", $handler, "helpers.php: AddUser() 2/2");
}

function DeleteUser($new_ident, $chatpath) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_ulist WHERE chat='$chatpath' AND ident='$new_ident'", $handler, "helpers.php: DeleteUser() 1/1");
}

function FindUser($userident, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT username FROM uo_chat_ulist WHERE chat='$chatpath' AND ident='$userident'", $handler, "helpers.php: FindUser() 1/1");
	$usel = @mysqli_fetch_row($result);
	@mysqli_free_result($result);

	if (!empty($usel[0])) {
		return 1;
	}
	return 0;
}

function ShowList($chatpath) {
	global $handler, $timeout, $tzone, $rpath, $master_name_filter;

	@count_mysql_query("DELETE FROM uo_chat_ulist WHERE chat='$chatpath' AND utime<'".(time()-$timeout)."'", $handler, "helpers.php: ShowList() 1/4");

	//echo "<base href=''>";

	require_once("../common/image.php");
	$usels = array();
	$query = "";
	$result = @count_mysql_query("SELECT chat,ident,username,link,image,utime FROM uo_chat_ulist WHERE chat='$chatpath' ORDER BY utime DESC", $handler, "helpers.php: ShowList() 2/4");
	while ($usel = @mysqli_fetch_row($result)) {
		$usel[2] = str_replace('_', ' ', $usel[2]);
		$usels[] = $usel;
		$query .= "username='".(trim(preg_replace('~'.$master_name_filter.'~i', "", strtolower($usel[2]))))."' OR ";
	}
	@mysqli_free_result($result);
	if (count($usels) < 1) {
		return 1;
    }
	$cpref = GetChatPrefs($chatpath);

	$query = "(".preg_replace("~(.*) OR $~","\\1", $query).")";

	$rez = @count_mysql_query("SELECT username FROM uo_chat_database WHERE chat='$chatpath' AND $query AND profile!='' AND dtime IS NULL", $handler, "helpers.php: ShowList() 3/4");
	$profs = "";
	while ($usel = @mysqli_fetch_row($rez)) {
		$profs .= "$usel[0]|";
	}
	@mysqli_free_result($rez);

	$rez = @count_mysql_query("SELECT prefs,email,aim,icq,ym,msn,site,username,skype,lastfm,flickr,facebook,gplus,steam FROM uo_chat_database WHERE chat='$chatpath' AND $query AND dtime IS NULL", $handler, "helpers.php: ShowList() 4/4");
	$prefs = array();
	while ($usel = @mysqli_fetch_row($rez)) {
		$prefs[] = $usel;
	}
	@mysqli_free_result($rez);

	$cnts = count($usels);
    for($ix=0;$ix<$cnts;$ix++) {
		$usel = $usels[$ix];
		$fixhandle = trim(preg_replace('~'.$master_name_filter.'~i', "", strtolower($usel[2])));
		echo "<hr width='75%'>\n";

		$usel[3] = htmlentities(str_replace("`","'",$usel[3]));
		$usel[4] = trim(str_replace("`","'",$usel[4]));
		$usel[2] = htmlentities(str_replace("`","'",$usel[2]));
		$uluser = $usel[2];

        $imgid = $ix+1;

		unset($upref);
		for($ig=0;$ig<count($prefs);$ig++) {
			if (strcasecmp($prefs[$ig][7], $fixhandle) == 0) {
				$upref = $prefs[$ig];
			}
		}

		if (!empty($upref)) {
			echo '<a onclick="setConsoleChannel(\''.$fixhandle.'\');" class="setchannel">';
		}
		if (($usel[4]) && ($usel[3])) {
			if ($_REQUEST['noimg'] > 0) {
				echo "[<a href='$usel[4]' target='_blank'><i>Open Image</i></a>]<br><span title=\"$usel[3]\" class='handle withlink'>$uluser</span><br>\n";
			}
			else {
				echo "<span title=\"$usel[3]\" class='handle withlink'><img border='0' id='img$imgid' src=\"$usel[4]\" ".ChatImageSize($usel[4])."><br>$uluser</span><br>\n";
			}
		}
		else if ($usel[4]) {
			if ($_REQUEST['noimg'] > 0) {
				echo "[<a href='$usel[4]' target='_blank'><i>Open Image</i></a>]<br><span class='handle nolink'>$uluser</span><br>\n";
			}
			else {
				echo "<img border=0 id='img$imgid' src='$usel[4]' ".ChatImageSize($usel[4])."><br><span class='handle nolink'>$uluser</span><br>\n";
			}
		}
		else if ($usel[3]) {
			echo "<span title=\"$usel[3]\" class='handle withlink'>$uluser</span><br>\n";
		}
		else {
			echo "<span class='handle nolink'>$uluser</span><br>\n";
        }
		if (!empty($upref)) {
			echo '</a>';
		}

		if (!CheckFlags("m", $cpref)) {
			if (!empty($fixhandle) && stristr($profs, $fixhandle)) {
				echo "<a href='https://pjj.cc/$rpath/register/viewer.php?su=".urlencode($usel[2])."&fm=view' target='_blank'><img alt='Profile' src='https://pjj.cc/gfx/im/prof2.gif' border='0'></a>";
            }

			if (CheckFlags("M", $upref[0]) && !empty($upref[1])) {
				echo "<a href='mailto:$upref[1]'><img alt='Email' src='https://pjj.cc/gfx/im/email.gif' border=0></a>";
			}

			$upref[2] = str_replace(" ", "", $upref[2]);
			if (!empty($upref[2])) {
				echo "<a href='aim:GoIM?ScreenName=$upref[2]' title='AIM'>";
				echo "<img alt='AIM' src='https://pjj.cc/gfx/im/aimonline.gif' border='0'>";
				echo "</a>";
			}
			if (!empty($upref[3])) {
				echo "<a href='http://www.icq.com/$upref[3]' target='_blank' title='ICQ'>";
				echo "<img alt='ICQ' src='https://pjj.cc/gfx/im/icqonline.gif' border='0'>";
				echo "</a>";
			}
			if (!empty($upref[4])) {
				echo "<a href=\"http://profiles.yahoo.com/$upref[4]\" target='_blank' title='YM'>";
				echo "<img border='0' src=\"https://pjj.cc/gfx/im/yahooonline.gif\" alt='YM'>";
				echo "</a>";
			}
			if (!empty($upref[5])) {
				echo "<a href=\"http://members.msn.com/$upref[5]\" target='_blank' title='MSN'>";
				echo "<img border='0' src='https://pjj.cc/gfx/im/msnonline.gif' alt='MSN'>";
				echo "</a>";
			}
			if (!empty($upref[9])) {
				echo "<a href=\"http://www.last.fm/user/{$upref[9]}/\" target='_blank' title='Last.fm'>";
				echo "<img border='0' src='https://pjj.cc/gfx/im/lastfm.png' alt='Last.fm'>";
				echo "</a>";
			}
			if (!empty($upref[10])) {
				echo "<a href='http://flickr.com/photos/$upref[10]/' target='_blank' title='Flickr'>";
				echo "<img border='0' hspace='2' src='https://i.pjj.cc/c8b1694ee96e780aa35f426abc3e67d7.gif' alt='Flickr'>";
				echo "</a>";
			}
			if (!empty($upref[11])) {
				echo "<a href='http://www.facebook.com/$upref[11]' target='_blank' title='Facebook'>";
				echo "<img border='0' hspace='2' src='https://pjj.cc/gfx/im/facebook.png' alt='Facebook'>";
				echo "</a>";
			}
			if (!empty($upref[12])) {
				echo "<a href='http://plus.google.com/$upref[12]' target='_blank' title='Google Plus'>";
				echo "<img border='0' hspace='2' src='https://pjj.cc/gfx/im/googleplus.png' alt='Google Plus'>";
				echo "</a>";
			}
			if (!empty($upref[13])) {
				echo "<a href='http://steamcommunity.com/id/$upref[13]' target='_blank' title='Steam'>";
				echo "<img border='0' hspace='2' src='https://pjj.cc/gfx/im/steam.png' alt='Steam'>";
				echo "</a>";
			}
			if (!empty($upref[8])) {
				echo "<a href='callto:$upref[8]' title='Skype'>";
				echo "<img border='0' hspace='2' src='https://i.pjj.cc/3e818c5e24ac3e9e9aa2155129f55f10.gif' alt='Skype'>";
				echo "</a>";
			}
			if (!empty($upref[6])) {
				echo "<a href='$upref[6]' target='_blank'>";
				echo "<img alt='Homepage' src='https://pjj.cc/gfx/im/prof.gif' border='0'>";
				echo "</a>";
			}
		}

		echo "<br><font size=-1>$usel[1]</font><br>\n";

		$dtstring = date("g:i:sa", $usel[5]+($tzone*3600));
		echo "Posted: $dtstring\n";

		unset($usel);
	}
	return 1;
}

function _parseSpecial_helper($m) {
	$url = $m[1];
	$txts = preg_split('~(.{50})~us', $url, -1, PREG_SPLIT_DELIM_CAPTURE);
	$url = htmlspecialchars($url);
	return '<a href="'.$url.'" target="_blank" title="'.$url.'">'.htmlspecialchars(implode("\xc2\xad", $txts)).'</a>';
}

function ParseSpecial($mline) {
	$mline = ' '.$mline.' ';
	$mline = preg_replace('~ /([^/\s]+)/ ~u', ' <i>\1</i> ', $mline);
	$mline = preg_replace('~ _([^_\s]+)_ ~u', ' <u>\1</u> ', $mline);
	$mline = preg_replace('~ -([^-\s]+)- ~u', ' <b>\1</b> ', $mline);
	$mline = preg_replace_callback('~((f|ht)tps?://[^\s\r]*[^\s\r,.:!?)])~ui', '_parseSpecial_helper', $mline);
	$mline = preg_replace('~(irc://[^\s\r]*[^\s\r,.:!?)])~ui', '<a href="\1" title="IRC: \1">\1</a>', $mline);
	$mline = preg_replace('~(aim:[^\s\r]*[^\s\r,.:!?)])~ui', '<a href="\1" title="AIM: \1">\1</a>', $mline);
	$mline = preg_replace('~(callto:[^\s\r]*[^\s\r,.:!?)])~ui', '<a href="\1" title="Skype: \1">\1</a>', $mline);

	return trim($mline);
}

function ExtractUrls($line, $handle) {
	if (preg_match_all('@((f|ht)tps?://\S*[^\s,.:!?)])@isu', $line, $urls)) {
        $urls = $urls[1];
        foreach ($urls as $url) {
            $url = $GLOBALS['sql']->escapeString(trim($url));
            $handle = $GLOBALS['sql']->escapeString(trim($handle));
            $query = "INSERT INTO chatv2.seen_urls (url_poster, url_poster_id, url_time, url_href, url_chat)
            VALUES (
            '{$handle}',
            {$GLOBALS['biglog']['user_id']},
            now(),
            '{$url}',
            {$GLOBALS['biglog']['chat_id']}
            )";
            $GLOBALS['sql']->query($query);
        }
    }
}

function FilterWords($message) {
	global $banwords;
//	$banwords['geocities.com/sdarkfalls'] = '[blocked by TD]';

	while ($filter = each($banwords)) {
	    $message = str_ireplace($filter[0], $filter[1], $message);
	}

	return $message;
}

function GetFactionDetails($chatpath, $faction) {
	global $handler;

	$result = @count_mysql_query("SELECT chat,id,name,icon FROM uo_chat_faction WHERE chat='$chatpath' AND id='$faction'", $handler, "helpers.php: GetFactionDetails() 1/1");
	$cuser = mysqli_fetch_row($result);
	mysqli_free_result($result);

	return $cuser;
}

function ParseMotD($file = "register/motd.dat") {
	global $handler;
/*
	[C=chat]
	[V=chat]
	[CV=chat]
*/
	$fz = fopen($file, "rb");
	$motd = fread($fz, filesize($file));
	fclose($fz);

	if (strstr($motd, "]") && (stristr($motd, "[C=") || stristr($motd, "[A=") || stristr($motd, "[V=") || stristr($motd, "[CV="))) {
		$regs = array();

		if (preg_match_all("/\\[[CVA]+=([-[:alnum:]_]+)\\]/i", $motd, $regs) > 0) {
			$regs = array_unique($regs[1]);
			sort($regs);

			$chatters = array();
			$viewers = array();
			for ($cc=0;$cc<count($regs);$cc++) {
				$chatters[$cc] = 0;
				$viewers[$cc] = 0;
				$regs[$cc] = strtolower($regs[$cc]);
			}

			if (stristr($motd, "[C=") || stristr($motd, "[CV=")) {
				$query = "SELECT chat FROM uo_chat_ulist WHERE (";
				for ($cc=0;$cc<count($regs);$cc++) {
					$query .= "chat='chat{$regs[$cc]}' OR ";
				}
				$query = preg_replace('~ OR $~', ')', $query);

				$rows = array();
				$rez = @mysqli_query($handler, $query);
				echo mysqli_error($handler);
				while($row = @mysqli_fetch_row($rez)) {
					$rows[] = $row[0];
				}
				@mysqli_free_result($rez);

				sort($rows);
				for ($cc=0;$cc<count($rows);$cc++) {
					for ($cx=0;$cx<count($regs);$cx++) {
						if ($rows[$cc] == "chat{$regs[$cx]}")
							$chatters[$cx]++;
					}
				}

				for ($cc=0;$cc<count($regs);$cc++) {
					$motd = preg_replace("~\\[[Cc]={$regs[$cc]}\\]~", "{$chatters[$cc]}", $motd);
				}
			}

			if (stristr($motd, "[V=") || stristr($motd, "[CV=")) {
				$query = "SELECT DISTINCT chat,ip FROM uo_chat WHERE (";
				for ($cc=0;$cc<count($regs);$cc++) {
					$query .= "chat='chat{$regs[$cc]}' OR ";
				}
				$query = preg_replace('~ OR $~', ')', $query);

				$rows = array();
				$rez = @mysqli_query($handler, $query);
				echo mysqli_error($handler);
				while($row = @mysqli_fetch_row($rez)) {
					$rows[] = $row[0];
				}
				@mysqli_free_result($rez);

				sort($rows);
				for ($cc=0;$cc<count($rows);$cc++) {
					for ($cx=0;$cx<count($regs);$cx++) {
						if (strstr($rows[$cc], "chat{$regs[$cx]}"))
							$viewers[$cx]++;
					}
				}

				for ($cc=0;$cc<count($regs);$cc++) {
					$motd = preg_replace("~\\[[Vv]={$regs[$cc]}\\]~", "{$viewers[$cc]}", $motd);
				}
			}

			if (strstr($motd, "[CV=")) {
				for ($cc=0;$cc<count($regs);$cc++) {
					$motd = preg_replace("~\\[[Cc][Vv]={$regs[$cc]}\\]~", "{$chatters[$cc]}/{$viewers[$cc]}", $motd);
				}
			}

			$viewers = array();
			if (stristr($motd, "[A=")) {
				$time = time()-300;
				$query = "SELECT DISTINCT chat,ident FROM uo_chat_ulist WHERE utime>={$time} AND (";
				for ($cc=0;$cc<count($regs);$cc++) {
					$query .= "chat='chat{$regs[$cc]}' OR ";
				}
				$query = preg_replace('~ OR $~', ')', $query);

				$rows = array();
				$rez = @mysqli_query($handler, $query);
				echo mysqli_error($handler);
				while($row = @mysqli_fetch_row($rez)) {
					$rows[] = $row[0];
				}
				@mysqli_free_result($rez);

				sort($rows);
				for ($cc=0;$cc<count($rows);$cc++) {
					for ($cx=0;$cx<count($regs);$cx++) {
						if (strstr($rows[$cc], "chat{$regs[$cx]}"))
							$viewers[$cx]++;
					}
				}

				for ($cc=0;$cc<count($regs);$cc++) {
					$motd = preg_replace("~\\[[Aa]={$regs[$cc]}\\]~", "{$viewers[$cc]}", $motd);
				}
			}

		}
	}
	echo $motd;
}

function CacheChatLines() {
	global $realpath, $handler, $start, $maxlines;

	$result = @count_mysql_query("SELECT line, posttime FROM uo_chat_log WHERE chat='$realpath' ORDER BY posttime DESC LIMIT $maxlines", $handler, "helpers.php: CacheChatLines() 1/2");

	$oldest = null;
	$lines = array();
	while($line = @mysqli_fetch_assoc($result)) {
		$lines[] = $line['line'];
		$oldest = $line['posttime'];
	}

	for ($i=0;$i<count($lines);$i++) {
		$lines[$i] = str_replace("\\'", "'", $lines[$i]);
	}

	@mysqli_free_result($result);

	@count_mysql_query("DELETE FROM uo_chat_log WHERE chat='$realpath' AND posttime < '$oldest'", $handler, "helpers.php: CacheChatLines() 2/2");

	echo "\n<!-- $realpath.lines -->\n";
	MMC_Lock("$realpath.lines");

	$fcac = ''; // @fopen("../common/cache/$realpath.cache", "wb");
	for ($cc=0;$cc<count($lines);$cc++) {
		$fcac .= str_replace("`", "'", $lines[$cc])."\n";
	}
	MMC_Set("$realpath.cache", $fcac, 60);

	$fcac = ''; // @fopen("../common/cache/$realpath.rache", "wb");
	for ($cc=count($lines)-1;$cc>=0;$cc--) {
		$fcac .= str_replace("`", "'", $lines[$cc])."\n";
	}
	MMC_Set("$realpath.rache", $fcac, 60);

	if ($exit == 0) {
		echo "<!-- Debug: ".round(getmicrotime()-$start, 5)." secs / $cqs queries -->";
		echo "<!-- $creas -->";
	}
	MMC_Unlock("$realpath.lines");
}
