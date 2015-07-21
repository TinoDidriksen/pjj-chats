<?php

if (!function_exists("count_mysql_query")) {
	function count_mysql_query($query, $hand, $reason="Unknown") {
		global $cqs;

		$cqs++;

		return mysql_query($query, $hand);
	}
}

function CheckFlags($aflag, $flags) {
	for ($cc=0;$cc<strlen($aflag);$cc++) {
		if (strchr($flags, $aflag[$cc]))
			return $cc+1;
	}
	return 0;
}

function VerifyLogin($username, $password, $chatpath) {
	global $handler, $master_name_filter;

	$username = strtolower($username);
	$username = eregi_replace($master_name_filter, "", $username);

	/*
	if ($username == 'tino didriksen' && !preg_match('/^130\.22(5|6)/', $_SERVER['REMOTE_ADDR'])) {
		return -1;
	}
	//*/

	$result = @count_mysql_query("SELECT username,password,flags,email FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
	$cuser = mysql_fetch_assoc($result);

	if (empty($cuser['username'])) {
		return 0;
	}

	if (strcmp($password, $cuser['password']) == 0 || strcmp(md5($password), $cuser['password']) == 0) {
		if (!empty($cuser['email']) && strpos($cuser['email'], '@') !== false) {
			$result = @count_mysql_query(
				"SELECT GROUP_CONCAT(DISTINCT flags) as flags
				FROM uo_chat_database
				WHERE chat='$chatpath'
				AND email='".mysql_real_escape_string($cuser['email'])."'
				AND password='".mysql_real_escape_string($cuser['password'])."'
				AND dtime IS NULL
				GROUP BY chat, email, password, dtime", $handler);
			$flags = mysql_fetch_assoc($result);
			mysql_free_result($result);
			return '1'.$flags['flags'];
		}
		if (!empty($cuser['flags'])) {
			return $cuser['flags'];
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

function RandomPass($length = 12) {
	$all = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

	for($i=0;$i<$length;$i++) {
		$randy = mt_rand(0, strlen($all)-1);
		$pass .= $all[$randy];
	}

	return $pass;
}

function UserExists($chat, $username) {
	global $handler;
	$result = @count_mysql_query("SELECT username FROM uo_chat_database WHERE chat='$chat' AND username='". trim($username). "' AND dtime IS NULL", $handler);
	if (mysql_num_rows($result) != 0) {
		return true;
	}
	mysql_free_result($result);
	return false;
}

function AddUser($ad_name, $ad_pass, $new_name, $new_pass, $new_faction, $new_mail, $new_level, $chatpath) {
	global $handler, $master_name_filter;

	$flags = VerifyLogin($ad_name, $ad_pass, $chatpath);
	if (CheckFlags("AXZmM", $flags)) {

		if (CheckFlags("M", $new_level))
			$new_level = str_replace("M", "", $new_level);
		if (CheckFlags("m", $new_level) && !CheckFlags("M", $flags))
			$new_level = str_replace("m", "", $new_level);
		if (CheckFlags("Z", $new_level) && !CheckFlags("mM", $flags))
			$new_level = str_replace("Z", "", $new_level);
		if (CheckFlags("f", $new_level) && !CheckFlags("ZmM", $flags))
			$new_level = str_replace("f", "", $new_level);
		if (CheckFlags("P", $new_level) && !CheckFlags("ZmM", $flags))
			$new_level = str_replace("P", "", $new_level);

		$new_name = strtolower($new_name);
		$new_name = eregi_replace($master_name_filter, "", $new_name);

		$result = @count_mysql_query("SELECT chat,username FROM uo_chat_database WHERE chat='$chatpath' AND username='$new_name' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);

		if ($cuser[1] != "") {
			echo "<p>The name '$new_name' is already in the database.<br>\n";
			return -3;
		}

		$result = @count_mysql_query("INSERT INTO uo_chat_database (chat,username,password,flags,email,faction) VALUES ('$chatpath','$new_name','".md5($new_pass)."','$new_level','$new_mail','$new_faction')", $handler);
		echo "<p>User '$new_name' was added with password '$new_pass', faction '$new_faction' and email '$new_mail'.<br>\n";

		$new_name = ucwords($new_name);
		$ad_name = ucwords($ad_name);
		$fc = fopen("wizard_locked/actionlog.log", "a");
		fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": User '$new_name' added as level '$new_level' - $ad_name\n"));
		fclose($fc);
		return 1;
	}
	return 0;
}

function ChangePass($username, $password, $chatpath) {
	global $handler;

	if (CheckFlags("M", GetFlags($username, $chatpath))) {
		echo "Nice try.";
		return 0;
	}

	@count_mysql_query("UPDATE uo_chat_database SET password='".md5($password)."' WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);

	$username = ucwords($username);
	$fc = fopen("wizard_locked/actionlog.log", "a");
	fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": User '$username' changed password.\n"));
	fclose($fc);
	return 1;
}

function ChangePrefs($username, $password, $newpref, $nmail, $chatpath, $icq, $aim, $ym, $msn, $site, $skype='', $lastfm='', $flickr='', $dname='', $facebook='', $gplus='', $steam='') {
	global $handler;

	if (VerifyLogin($username, $password, $chatpath)) {
		$newpref = mq($newpref);
		$nmail = mq($nmail);
		$icq = intval($icq);
		$aim = mq($aim);
		$ym = mq($ym);
		$msn = mq($msn);
		$site = mq($site);
		$skype = mq($skype);
		$lastfm = mq($lastfm);
		$flickr = mq($flickr);
		$dname = mq($dname);
		$facebook = mq($facebook);
		$gplus = mq($gplus);
		$steam = mq($steam);

		@count_mysql_query("UPDATE uo_chat_database SET prefs=$newpref, email=$nmail, icq=$icq, aim=$aim,
			ym=$ym, msn=$msn, site=$site, skype=$skype, lastfm=$lastfm, flickr=$flickr, displayname=$dname,
			facebook=$facebook, gplus=$gplus, steam=$steam
			WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);

		echo "<p>Preferences for '$username' set to $newpref, $nmail, $icq, $aim, $ym, $msn, $skype, $lastfm, $flickr, $facebook, $gplus, $steam, $dname.<br>";
		return 1;
	}
	return 0;
}

function ChangeChatPrefs($username, $password, $newpref, $chatpath='') {
	global $handler;

	if (CheckFlags("ZmM", VerifyLogin($username, $password, $chatpath))) {

		$query = "UPDATE chatv2.chats SET prefs='$newpref' WHERE chat_id=".$GLOBALS['biglog']['chat_id'];
		$GLOBALS['sql']->begin();
		if ($GLOBALS['sql']->query($query) === false) {
			$GLOBALS['sql']->rollback();
		}
		else {
			$GLOBALS['sql']->commit();
		}

		echo "<p>Preferences for the chat was set to '$newpref'..<br>";
		return 1;
	}
	return 0;
}

function GetPrefs($username, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT prefs,email,aim,ym,icq,msn,site,skype,lastfm,flickr,displayname,facebook,gplus,steam FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
	$cuser = mysql_fetch_array($result);
	@mysql_free_result($result);

	return $cuser;
}

function GetChatPrefs($chatpath) {
	global $handler;

	if (strpos($chatpath, 'chat') === 0 && strlen($chatpath) > 4) {
		$chatpath = mb_substr($chatpath, 4);
	}

	$query = "SELECT chat,prefs,chat_id FROM chatv2.chats WHERE chat='$chatpath'";
	$result = $GLOBALS['sql']->query($query);
	$cuser = $GLOBALS['sql']->fetchAssoc($result);
	$GLOBALS['biglog']['chat_id'] = intval($cuser['chat_id']);
	$GLOBALS['sql']->freeResult($result);

	return $cuser;
}

function GetFaction($username, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT faction FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
	$cuser = mysql_fetch_row($result);

	return $cuser[0];
}

function GetFactionDetails($chatpath, $faction) {
	global $handler;

	$result = @count_mysql_query("SELECT chat,id,name,icon FROM uo_chat_faction WHERE chat='$chatpath' AND id='$faction'", $handler);
	$cuser = mysql_fetch_row($result);
	@mysql_free_result($result);

	return $cuser;
}

function AlterFaction($faction, $ficon) {
	global $handler;

	@count_mysql_query("UPDATE uo_chat_faction SET icon='$ficon' WHERE id='$faction'", $handler);
	echo "<br>".ucwords($faction)." edited.</br>";
}

function GetEmail($username, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT email FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
	$cuser = mysql_fetch_row($result);

	return $cuser[0];
}

function GetFlags($username, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT flags FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
	$cuser = mysql_fetch_row($result);

	return $cuser[0];
}

function DeleteUser($ad_name, $ad_pass, $username, $chatpath) {
	global $handler;

	$flags = VerifyLogin($ad_name, $ad_pass, $chatpath);
	if (CheckFlags("DXZmM", $flags)) {

		$result = @count_mysql_query("SELECT username,flags,uid FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);

		if (CheckFlags("M", $cuser[1])) {
			echo "<p>System Administrators cannot be deleted.<br>\n";
			return -2;
		}
		else if (CheckFlags("m", $cuser[1]) && !CheckFlags("M", $flags)) {
			echo "<p>You cannot delete Chat Masters.<br>\n";
			return -2;
		}
		else if (CheckFlags("ZP", $cuser[1]) && !CheckFlags("mM", $flags)) {
			echo "<p>You cannot delete Masters or Protected users.<br>\n";
			return -2;
		}

		@count_mysql_query("UPDATE uo_chat_database SET dtime=now() WHERE chat='$chatpath' AND uid=".$cuser[2], $handler);
		echo "<p>User '".$cuser[0]."' (UID ".$cuser[2].") was deleted.<br>\n";

		$username = ucwords($username);
		$ad_name = ucwords($ad_name);
		$fc = fopen("wizard_locked/actionlog.log", "a");
		fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": User '$username' deleted - $ad_name\n"));
		fclose($fc);
		return 1;
	}
	return -1;
}

function DeleteSelf($ad_name, $ad_pass, $chatpath) {
	global $handler;
	$username = $ad_name;

	$flags = VerifyLogin($ad_name, $ad_pass, $chatpath);
	if ($flags) {
		$result = @count_mysql_query("SELECT username,flags,uid FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);

		if (CheckFlags("M", $cuser[1])) {
			echo "<p>System Administrators cannot be deleted.<br>\n";
			return -2;
		}
		else if (CheckFlags("m", $cuser[1]) && !CheckFlags("M", $flags)) {
			echo "<p>You cannot delete Chat Masters.<br>\n";
			return -2;
		}
		else if (CheckFlags("ZP", $cuser[1]) && !CheckFlags("mM", $flags)) {
			echo "<p>You cannot delete Masters or Protected users.<br>\n";
			return -2;
		}

		@count_mysql_query("UPDATE uo_chat_database SET dtime=now() WHERE chat='$chatpath' AND uid=".$cuser[2], $handler);
		echo "<p>User '".$cuser[0]."' (UID ".$cuser[2].") was deleted.<br>\n";

		$username = ucwords($username);
		$ad_name = ucwords($ad_name);
		$fc = fopen("wizard_locked/actionlog.log", "a");
		fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": User '$username' deleted - $ad_name\n"));
		fclose($fc);
		return 1;
	}
	return -1;
}

function ChangeUser($ad_name, $ad_pass, $username, $userlevel, $chatpath) {
	global $handler;

	$flags = VerifyLogin($ad_name, $ad_pass, $chatpath);
	if (!empty($username) && CheckFlags("fZmM", $flags)) {

		$result = @count_mysql_query("SELECT username,flags FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);

		if (CheckFlags("M", $cuser[1])) {
			echo "<p>System Administrators cannot be altered.<br>\n";
			return -2;
		}
		else if (CheckFlags("m", $cuser[1]) && !CheckFlags("M", $flags)) {
			echo "<p>You cannot alter Chat Masters.<br>\n";
			return -2;
		}
		else if (CheckFlags("ZP", $cuser[1]) && !CheckFlags("mM", $flags)) {
			echo "<p>You cannot alter Masters or Protected users.<br>\n";
			return -2;
		}

		if (CheckFlags("M", $userlevel))
			$userlevel = str_replace("M", "", $userlevel);
		if (CheckFlags("m", $userlevel) && !CheckFlags("M", $flags))
			$userlevel = str_replace("m", "", $userlevel);
		if (CheckFlags("Z", $userlevel) && !CheckFlags("mM", $flags))
			$userlevel = str_replace("Z", "", $userlevel);
		if (CheckFlags("f", $userlevel) && !CheckFlags("ZmM", $flags))
			$userlevel = str_replace("f", "", $userlevel);
		if (CheckFlags("P", $userlevel) && !CheckFlags("ZmM", $flags))
			$userlevel = str_replace("P", "", $userlevel);

		@count_mysql_query("UPDATE uo_chat_database SET flags='$userlevel' WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		echo "<p>User '$username' was given flags '$userlevel'.<br>\n";

		$username = ucwords($username);
		$ad_name = ucwords($ad_name);
		$fc = fopen("wizard_locked/actionlog.log", "a");
		fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": User '$username' made '$userlevel' - $ad_name\n"));
		fclose($fc);
		return 1;
	}
	return -1;
}

function ResetPass($username, $ad_name, $ad_pass, $chatpath) {
	global $handler;

	$flags = VerifyLogin($ad_name, $ad_pass, $chatpath);
	if (CheckFlags("pXZmM", $flags)) {


		$result = @count_mysql_query("SELECT username,flags FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);

		if (CheckFlags("M", $cuser[1])) {
			echo "<p>System Administrators know their passwords.<br>\n";
			return -2;
		}
		else if (CheckFlags("m", $cuser[1])) {
			echo "<p>You cannot reset Chat Masters.<br>\n";
			return -2;
		}
		else if (CheckFlags("ZP", $cuser[1]) && !CheckFlags("mM", $flags)) {
			echo "<p>You cannot reset Masters or Protected users.<br>\n";
			return -2;
		}

		@count_mysql_query("UPDATE uo_chat_database SET password='".md5("defpass")."' WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		echo "<p>Password for user '$username' was reset to 'defpass'.<br>\n";

		$username = ucwords($username);
		$ad_name = ucwords($ad_name);
		$fc = fopen("wizard_locked/actionlog.log", "a");
		fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": Reset password for user '$username' - $ad_name\n"));
		fclose($fc);
		return 1;
	}
	return -1;
}

function RenameUser($username, $ad_name, $ad_pass, $new_name, $chatpath) {
	global $handler, $master_name_filter;

	$flags = VerifyLogin($ad_name, $ad_pass, $chatpath);
	if (!empty($username) && !empty($new_name) && CheckFlags("RXZmM", $flags)) {

		$new_name = strtolower($new_name);
		$new_name = eregi_replace($master_name_filter, "", $new_name);

		$result = @count_mysql_query("SELECT flags FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);

		if (CheckFlags("M", $cuser[0])) {
			echo "<p>System Administrators only have 1 name.<br>\n";
			return -2;
		}
		else if (CheckFlags("m", $cuser[0]) && !CheckFlags("mM", $flags)) {
			echo "<p>You cannot rename Chat Masters.<br>\n";
			return -2;
		}
		else if (CheckFlags("ZP", $cuser[0]) && !CheckFlags("mM", $flags)) {
			echo "<p>You cannot rename Masters or Protected users.<br>\n";
			return -2;
		}

		$result = @count_mysql_query("SELECT username FROM uo_chat_database WHERE chat='$chatpath' AND username='$new_name' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);
		if ($cuser[0] != "") {
			echo "<p>User '$new_name' already exists.<br>\n";
			return 1;
		}

		@count_mysql_query("UPDATE uo_chat_database SET username='$new_name' WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		echo "<p>User '$username' renamed to '$new_name'.<br>\n";

		$username = ucwords($username);
		$ad_name = ucwords($ad_name);
		$fc = fopen("wizard_locked/actionlog.log", "a");
		fwrite($fc, stripslashes(date("F d, Y T - H:i:s").": '$username' renamed to '$new_name' - $ad_name\n"));
		fclose($fc);
		return 1;
	}
	return -1;
}

function ChangeFaction($username, $ad_name, $ad_pass, $new_name, $chatpath) {
	global $handler;

	$flags = VerifyLogin($ad_name, $ad_pass, $chatpath);
	if (CheckFlags("FXZmM", $flags)) {

		$new_name = strtolower($new_name);
		$new_name = trim($new_name);
		$new_name = str_replace("_", " ", $new_name);
		$new_name = str_replace(":", " ", $new_name);
		$new_name = str_replace("'", "", $new_name);
		$new_name = str_replace("ï¿½", "", $new_name);
		$new_name = str_replace("`", "", $new_name);
		$new_name = str_replace("\"", "", $new_name);

		$result = @count_mysql_query("SELECT flags FROM uo_chat_database WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		$cuser = mysql_fetch_row($result);

		if (CheckFlags("M", $cuser[0])) {
			echo "<p>System Administrators are not in factions.<br>\n";
			return -2;
		}
		else if (CheckFlags("P", $cuser[0]) && !CheckFlags("mM", $flags)) {
			echo "<p>You cannot change the faction of Protected users.<br>\n";
			return -2;
		}

		@count_mysql_query("UPDATE uo_chat_database SET faction='$new_name' WHERE chat='$chatpath' AND username='$username' AND dtime IS NULL", $handler);
		echo "<p>User '$username' is now part of '$new_name'.<br>\n";
	}
	return -1;
}

function ListUsers($sfact="", $sort="username") {
	global $handler;

	include '../settings.php';
	include '../options.php';
	include '../../common/faction_help.php';

	if (!empty($altdata)) {
		$chatpath = $altdata;
	}
	else {
		$chatpath = eregi_replace(".*/([^/]+)/register/biglist.php$", "chat\\1", $_SERVER['PHP_SELF']);
		if (strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
			$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
		}
	}

	$arr = GetFactionNames($chatpath);

	$sort = eregi_replace('[^,[:alnum:]]+', '', $sort);
	if (empty($sort))
		$sort = 'username';
	$sort = mysql_escape_string($sort);

	if (!empty($sfact)) {
	    $sfact = mysql_escape_string($sfact)+0;
		$result = @count_mysql_query("SELECT username,flags,faction,lastlogin,prefs,email,icq,aim,pcolor FROM uo_chat_database WHERE chat='$chatpath' AND faction='$sfact' AND BINARY flags!='M' AND dtime IS NULL ORDER BY $sort ASC", $handler);
	}
	else {
		$result = @count_mysql_query("SELECT username,flags,faction,lastlogin,prefs,email,icq,aim,pcolor FROM uo_chat_database WHERE chat='$chatpath' AND BINARY flags!='M' AND dtime IS NULL ORDER BY $sort ASC", $handler);
	}

	echo "<table BORDER=0 CELLSPACING=2 CELLPADDING=2>
	<tr>
		<td><u><a href=\"biglist.php?sort=username\">Username</a></u></td>
		<td><u><a href=\"biglist.php?sort=flags\">Flags</a></u></td>
		<td><u><a href=\"biglist.php?sort=faction\">Faction</a></u></td>
		<td align=right><u><a href=\"biglist.php?sort=lastlogin\">Last Seen</a></u></td>
		<td><a href=\"biglist.php?sort=email,prefs\">Contact (email,icq,aim)</a></td>
		<td>Text Color</td>
		</tr>";

	while ($cuser = mysql_fetch_array($result)) {
		$cuser['username'] = ucwords($cuser['username']);
		echo "<tr><td>{$cuser['username']}</td><td>{$cuser['flags']}</td>";
		$fname = $arr[$cuser['faction']+0];
		echo "<td><a href=biglist.php?sort=".urlencode($sort)."&faction=".urlencode($cuser['faction']).">$fname</a></td>";

		if ($cuser['lastlogin'] > 0)
			echo "<td align=right>".date("Y-m-d H:i", $cuser['lastlogin']+($tzone*3600))."</td>";
		else
			echo "<td align=right>Unknown</td>";

		echo "<td>";
		if (CheckFlags("M", $cuser['prefs'])) {
			echo "<a href=mailto:{$cuser['email']}>Email</a> ";
		}
		if (!empty($cuser['icq'])) {
			echo "ICQ: <b>{$cuser['icq']}</b> ";
		}
		if (!empty($cuser['aim'])) {
			echo "AIM: <b>{$cuser['aim']}</b> ";
		}
		echo "</td>";
		echo '<td bgcolor="'.$cuser['pcolor'].'">'.$cuser['pcolor'].'</td>';
		echo "</tr>";
	}
	@mysql_free_result($result);
	echo "</table>";
	return 1;
}

function ListUsersModify($userlevel, $chatpath) {
	global $handler;

	$arr = GetFactionNames($chatpath);

	echo "<table BORDER=0 CELLSPACING=2 CELLPADDING=2><tr align=center valign=center><td align=center valign=center>Action:
	<br><SELECT NAME='modaction'>";
	if (CheckFlags("FXZmM", $userlevel))
		echo "<OPTION value='faction'>Change Faction";
	if (CheckFlags("pXZmM", $userlevel))
		echo "<OPTION value='resetpass'>Reset Password";
	if (CheckFlags("RXZmM", $userlevel))
		echo "<OPTION value='rename'>Rename";
	if (CheckFlags("DXZmM", $userlevel))
		echo "<OPTION value='delete'>Delete";
	echo "</SELECT></td><td align=center valign=center>User:
	<br><SELECT NAME='selecteduser'>";

	$result = @count_mysql_query("SELECT username,flags,faction FROM uo_chat_database WHERE chat='$chatpath' AND BINARY flags!='M' AND dtime IS NULL ORDER BY username ASC", $handler);
	while ($cuser = mysql_fetch_row($result)) {
		if ($cuser[0] != "") {
			$fname = $arr[$cuser[2]+0];
			echo "<OPTION value='$cuser[0]'>$cuser[0] # $cuser[1] # $fname\n";
		}
	}

	echo "</SELECT></td><td>New Name/Faction:<br><input name='newname' value='' size=30></td></tr></table>";
	return 1;
}

function ListIconsModify($chatpath) {
	global $handler;

	echo "User:
	<br><SELECT NAME='selecteduser'>";

	$result = @count_mysql_query("SELECT username,flags FROM uo_chat_database WHERE chat='$chatpath' AND dtime IS NULL ORDER BY username ASC", $handler);
	while ($cuser = mysql_fetch_row($result)) {
		if ($cuser[0] != "")
			echo "<OPTION value='$cuser[0]'>$cuser[0] - $cuser[1]\n";
	}

	echo "</SELECT>";
	return 1;
}

function EnumerateIcons($user, $chatpath) {
	global $handler, $master_name_filter;

	$user = strtolower(eregi_replace($master_name_filter, "", $user));

	echo "<table border=0 cellspacing=1 cellpadding=2 bgcolor=#000000>";
	echo "<tr bgcolor=#eeeeee><td><b>#</b></td><td><b>Name</b></td><td><b>Url</b></td><td></td></tr>\n";
	$cc=0;
	$rez = @count_mysql_query("SELECT username,icon FROM uo_chat_database WHERE chat='$chatpath' AND username='$user' AND dtime IS NULL", $handler);
	$row = @mysql_fetch_row($rez);
	if (stristr($row[0], $user)) {
		$icon = explode("\n", $row[1]);
		for ($cc=0;$cc<count($icon);$cc++) {
			$ic = explode("ï¿½", $icon[$cc]);
			echo "<tr bgcolor=#ffffff>";
			echo "<td>$cc</td>";
			echo "<td><input name=\"name[$cc]\" value=\"$ic[0]\"></td>";
			echo "<td><input name=\"file[$cc]\" value=\"$ic[1]\"></td>";
			echo "<td><img border=0 src=\"$ic[1]\"></td>";
			echo "</tr>\n";
		}
	}

	for ($aa=$cc;$aa<$cc+5;$aa++) {
		echo "<tr bgcolor=#ffffff>";
		echo "<td>$aa</td>";
		echo "<td><input name=\"name[$aa]\" value=\"\"></td>";
		echo "<td><input name=\"file[$aa]\" value=\"\"></td>";
		echo "<td></td>";
		echo "</tr>\n";
	}

	echo "</table><input type=hidden name=iconedit value=1>";
}

function ShowDropdown($chatpath) {
	global $handler;

	echo "<form ACTION='viewer.php' METHOD='GET' target='view'><SELECT NAME='selecteduser'>";

	$result = @count_mysql_query("SELECT username,displayname FROM uo_chat_database WHERE chat='$chatpath' AND profile!='' AND dtime IS NULL ORDER BY username ASC", $handler);

	while ($cuser = mysql_fetch_assoc($result)) {
		$user = ucwords($cuser['username']);
		if (empty($cuser['displayname'])) {
			$cuser['displayname'] = $user;
		}
		echo "<OPTION value='$user'>".htmlentities($cuser['displayname'])."\n";
	}

	echo "</SELECT><input type=hidden name=frames value=view><INPUT TYPE='SUBMIT' Value='View profile'></form>";
}

function ShowProfile($selecteduser, $chatpath) {
	global $handler, $master_name_filter, $master_zlib;

	$selecteduser = trim(eregi_replace($master_name_filter, "", strtolower($selecteduser)));

	$rez = @count_mysql_query("SELECT profile FROM uo_chat_database WHERE chat='$chatpath' AND username='$selecteduser' AND profile!='' AND dtime IS NULL", $handler);

	if ($prof = @mysql_fetch_assoc($rez)) {
		if ($prof['profile'][0] != 'x') {
			echo stripslashes($prof['profile']);
		}
		else {
			echo stripslashes(gzuncompress($prof['profile']));
        }
	}
	else {
		echo "User $selecteduser doesn't have a profile.";
	}
	@mysql_free_result($rez);
}

function PrepDBData($value, $nl2br=true) { // Continuing the lovely mysql_ namegiving
	// Stripslashes
	if (get_magic_quotes_gpc()) {
		$value = stripslashes($value);
	}

	if (!is_numeric($value)) {
		$value = str_replace('\r', "", $value); // Remove ugly \r 's
		if ($nl2br)
			$value = nl2br($value);

		$value = mysql_real_escape_string($value);
	}
	return $value;
}
