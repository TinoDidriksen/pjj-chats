<?php
    ob_start();
?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Chat Request Form</title>

<script type="text/javascript">
if (window != window.top)
  top.location.href = location.href;
</script>
</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="gfx/up_tile.gif" valign="top" align="left" height="32"> </td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%" width=80> </td>
	<td valign="top" height="100%">
<center><img src="gfx/null.gif" border=0></center>
<b style="font-size: 150%;">Chat Request Form</b>
<p>It is recommended that you <a href="/" target=_blank>check</a> if there is another chat of nearly the same theme
as the chat you want to request before doing so.<br>
Please check with the Chat Master of any such chat if you perhaps could incorperate
your ideas in their chat instead of starting a whole new one.<p>
<?php
	require_once("setup.php");

	if ($_SERVER['HTTP_HOST']) {
		$cpath = "https://".$_SERVER['HTTP_HOST'].preg_replace("~(.*)/rq.php~", "\\1", $_SERVER['PHP_SELF']);
	}
	else {
		$cpath = "https://".$_SERVER['SERVER_NAME'].preg_replace("~(.*)/rq.php~", "\\1", $_SERVER['PHP_SELF']);
	}

	$path		= strtolower(trim($_REQUEST['path']));
	$path		= preg_replace("~([^-[:alnum:]_]+)~i", "", $path);
	$pwd		= trim($_REQUEST['pwd']);
	$mname		= trim(str_replace("_", " ", $_REQUEST['mname']));
	$mname		= strtolower(preg_replace('~'.$master_name_filter.'~i', "", $mname));
	$mail		= trim($_REQUEST['mail']);
	$comment	= trim($_REQUEST['comment']);
	$comment	= str_replace("\"", "", $comment);

	if (
	$path == 'www'
	|| $path == '_new'
	|| $path == '_clean'
	|| $path == 'legal'
	|| $path == 'common'
	) {
		echo "<b>Error:</b> '{$path}' is an invalid acronym.<br><br>";
		unset($path);
	}

	if ($path && $pwd && $mname && $mail && $_REQUEST['verify'] && $_REQUEST['terms']) {
		$path = urlencode($path);
		$pwd = urlencode($pwd);
		$mname = urlencode($mname);
		$mail = urlencode($mail);
		$a_name = urlencode($master_name);
		$a_pass = urlencode($master_password);
		@system("wget -q --no-check-certificate --output-document=/dev/stdout \"https://pjj.cc/newchat.php?nchat={$path}&password={$pwd}&username={$mname}&email={$mail}&login=$a_name&pass=$a_pass\"");
		echo "<br>Should be done now. Check your email...";
/*
		$header = "Chat Request: $path, $mname";
//		$body = "$cpath/newchat.php?username=".urlencode($mname);
		$body = "https://pjj.cc/newchat.php?username=".urlencode($mname);
		$body .= "&password=".urlencode($pwd);
		$body .= "&email=".urlencode($mail);
		$body .= "\n\n";
		$body .= "Path: $path\n";
		$body .= "Username: $mname\n";
		$body .= "Password: $pwd\n";
		$body .= "Email: $mail\n";
		$body .= "Comments: ".stripslashes($comment)."\n";
		$email = mail($master_email, $header, $body, "From: $mail\nReply-To: $mail\nX-pJJ-IP: {$_SERVER['REMOTE_ADDR']}\n");
		if ($email >= 1) {
			echo "Chat request successfully sent.<br>It should be processed within 24 hours.<p>";
		}
		else {
			echo "An error occurred processing the request.<br>Please try again.<p>";
		}
*/
	}
	else if ($path && $pwd && $mname && $mail) {
		if (strlen($path) < 2) {
			echo "Chat acronym must be at least <b>2</b> letters long.<p>";
			unset($path);
		}

		if (strlen($path) > 8) {
			echo "Chat acronym must be at most <b>8</b> letters long.<p>";
			unset($path);
		}

		if ((file_exists($path."/sendmsg.php")) || (file_exists($path."/settings.php"))) {
			echo "A chat with acronym $path already exists at <a href=\"$cpath/$path/\" target=_blank>$cpath/$path/</a><p>";
			unset($path);
		}

		if (!$_REQUEST['terms']) {
			echo "You must accept the <a href='legal/' target='_blank'>terms</a>.<p>";
			unset($path);
		}

		if ($path) {
			echo "<form method=post action=rq.php>
	<table cellspacing=1 cellpadding=3 border=0 bgcolor=#000000>
		<tr valign=top bgcolor=#eeeeee>
			<td><b>Name</b></td><td><b>Field</b></td><td><b>Example/Help</b></td>
		</tr>
		<tr valign=top bgcolor=#FFFFFF>
			<td>Chat acronym</td><td><b>$path</b></td><td><i>SWNA</i> (<b>S</b>tar <b>W</b>ars <b>N</b>ew <b>A</b>ge)</td>
		</tr>
		<tr valign=top bgcolor=#eeeeee>
			<td>Master nickname</td><td><b>$mname</b></td><td><i>Han Solo</i></td>
		</tr>
		<tr valign=top bgcolor=#FFFFFF>
			<td>Master password</td><td><b>$pwd</b></td><td><i>d7g3sg6j</i></td>
		</tr>
		<tr valign=top bgcolor=#eeeeee>
			<td>Master email</td><td><b>$mail</b></td><td><i>peter@yahoo.com</i></td>
		</tr>
<!--
		<tr valign=top bgcolor=#FFFFFF>
			<td>Remarks</td><td><b>".nl2br(stripslashes($comment))."</b></td><td><i>Any comment/extra info</i></td>
		</tr>
-->
	</table>
	<input type=submit value='Send'>
	<input type=hidden value='verify' name=verify>
	<input type=hidden value='terms' name=terms>
	<input type=hidden value=\"$path\" name=path>
	<input type=hidden value=\"$mname\" name=mname>
	<input type=hidden value=\"$mail\" name=mail>
	<input type=hidden value=\"$pwd\" name=pwd>
	<input type=hidden value=\"".stripslashes($comment)."\" name=comment>
</form>
";
		}
	}

	if (($path == "") || ($mname == "") || ($pwd == "") || ($mail == "")) {
		echo '<b>You must read and agree to these rules:</b><br><div style="border: 1px dashed black; padding: 5px;"><span>';
		readfile('legal/legal.html');
		echo '</span></div>';
?><form method=post action=rq.php>
	<table cellspacing=1 cellpadding=3 border=0 bgcolor=#000000>
		<tr valign=top bgcolor=#eeeeee>
			<td><b>Name</b></td><td><b>Field</b></td><td><b>Example/Help</b></td>
		</tr>
		<tr valign=top bgcolor=#FFFFFF>
			<td>Chat acronym</td><td><input type=text size=8 maxlength=8 name=path value=<?=$path?>></td><td><i>SWNA</i> (<b>S</b>tar <b>W</b>ars <b>N</b>ew <b>A</b>ge)</td>
		</tr>
		<tr valign=top bgcolor=#eeeeee>
			<td>Master nickname</td><td><input type=text size=16 maxlength=32 name=mname value="<?=$mname?>"></td><td><i>Han Solo</i></td>
		</tr>
		<tr valign=top bgcolor=#FFFFFF>
			<td>Master password</td><td><input type=text size=10 maxlength=16 name=pwd value=<?=$pwd?>></td><td><i>d7g3sg6j</i></td>
		</tr>
		<tr valign=top bgcolor=#eeeeee>
			<td>Master email</td><td><input type=text size=16 maxlength=128 name=mail value=<?=$mail?>></td><td><i>peter@yahoo.com</i></td>
		</tr>
<!--
		<tr valign=top bgcolor=#FFFFFF>
			<td>Remarks</td><td><textarea rows=5 cols=24 name=comment><?=$comment?></textarea></td><td><i>Any comment/extra info.<br>
			This will only be seen by pJJ.<br>
			It won't appear as description.</i></td>
		</tr>
-->
		<tr valign=top bgcolor=#eeeeee>
			<td>Terms</td><td><input type=checkbox size=16 maxlength=128 name=terms></td><td>Tick if you have read and accepted the <a href="legal/" target="_blank">Terms of Service</a>.</td>
		</tr>
	</table>
	<input type=submit value='Proceed'>
</form><?
	}

?>
<center><img src="gfx/null.gif" border=0></center>
</td>
	<td valign="top" align="right" height="100%" width=80> </td></tr>
	</table>
</td></tr>
<tr><td background="gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>
</body>
</html>
