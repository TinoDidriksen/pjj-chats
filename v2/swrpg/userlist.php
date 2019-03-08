<?php
//	ob_start();

	mt_srand((double)microtime()*1000000);
	$cqs = 0;
	$creas = "";

function getmicrotime() {
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}

	$start = getmicrotime();

	$realpath = preg_replace('@.*/([^/]+)/userlist.php$@', '\1', $_SERVER['PHP_SELF']);
	$chatpath = $realpath;

	require_once("../common/session.php");
	require_once("../mysql.php");
	require_once("../common/helpers.php");
	require_once("../common/banhelp.php");
	require_once("../$realpath/options.php");
	require_once("../$realpath/settings.php");
	require_once("../setup.php");

	$rpath = $realpath;
	$realpath = 'chat'.$realpath;
	$chatpath = $realpath;

	$bodytag = str_replace('<body ', '<body class="userlist" ', $bodytag);
	$cbodytag = str_replace('<body ', '<body class="userlist" ', $cbodytag);
	$ubodytag = str_replace('<body ', '<body class="userlist" ', $ubodytag);

	echo '<!DOCTYPE html>', "\n", '<html><head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
	echo '<META NAME="ROBOTS" CONTENT="NOFOLLOW">';
	echo '<META NAME="ROBOTS" CONTENT="NOARCHIVE">';
	echo "\n<!-- Head: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
	echo "<meta http-equiv='refresh' content='$userlistspeed;URL=userlist.php?noimg={$_REQUEST['noimg']}'>
	<title>$ctitle</title>
	<link href='https://pjj.cc/common/css/common.css' rel='stylesheet' type='text/css'>
	<script type='text/javascript' src='https://pjj.cc/common/js/functions.js'></script>
	<style type='text/css'>
	$csshead
	</style>
	<script>
	function RefreshPage() {
		location.search = '?noimg={$_REQUEST['noimg']}';
	}
	setTimeout(RefreshPage, (($userlistspeed*1000)-500));
	</script>
</head>
$ubodytag";
?>
<center>
<!--[if lte IE 6]>
<div class="avatarfirefox" style="text-align: left;">
<script type="text/javascript">
if (document.documentMode && document.documentMode == 7) {
	document.write("You are using IE8 in IE7 mode - turn off Compatibility View for pJJ.");
}
</script>
<br>
Your browser is outdated. Please upgrade to one of these:
<br><a href="http://www.mozilla.com/firefox/" target="_top">Mozilla Firefox</a>
<br><a href="http://www.opera.com/download/" target="_top">Opera</a>
<br><a href="http://www.apple.com/safari/" target="_top">Apple Safari</a>
<br><a href="http://www.google.com/chrome/" target="_top">Google Chrome</a>
<br><a href="http://www.microsoft.com/ie" target="_top">Internet Explorer</a>
</div>
<![endif]-->
<?php

	echo "<p><a href='{$_SERVER['REQUEST_URI']}'>Refresh</a><br>";
	if ($_REQUEST['noimg']) {
		echo "<a href='{$_SERVER['PHP_SELF']}?noimg=0'>Show Images</a><p>";
	}
	else {
		echo "<a href='{$_SERVER['PHP_SELF']}?noimg=1'>Hide Images</a><p>";
	}

	require_once("../common/language.php");
	// ToDo: $realpath must be wrong here...
	if (file_exists("../$rpath/language.php")) {
		require_once("../$rpath/language.php");
    }

	$_SERVER['REMOTE_HOST'] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	if (!empty($_SESSION[$realpath]['ident'])) {
		$ident = $_SESSION[$realpath]['ident'];
	}
	else {
		$ident = mb_substr(md5($_SERVER['REMOTE_ADDR'].$realpath), 0, $identlenght);
	}
	$oldident = $ident;
	if (!empty($_COOKIE['pJJChat_Banned'])) {
		$ident = $_COOKIE['pJJChat_Banned'];
	}

	ChatSessionSuspend();
	GetChatPrefs($realpath);

	if (CheckBan($ident, $realpath) == 0) {
		$ident = $oldident;
		$xcnt = count($banip);
		for ($cc=0;$cc<$xcnt;$cc++) {
			if (
				($ident == $banip[$cc])
				|| (strncmp($_SERVER['REMOTE_ADDR'], $banip[$cc], strlen($banip[$cc])) == 0)
				|| (strncmp($_SERVER['HTTP_X_FORWARDED_FOR'], $banip[$cc], strlen($banip[$cc])) == 0)
				) {
				$ox = $banguage[4];
				$ox = str_replace('{IDENT}', $ident, $ox);
				echo $ox;
				echo '</body></html>';
				exit();
			}
			else if (strpos($banip[$cc], '.') !== false || strpos($banip[$cc], '*') !== false) {
				$banip[$cc] = str_replace('\\*', '.*', preg_quote($banip[$cc]));
				if (preg_match('/^'.preg_quote($banip[$cc], '/').'$/is', $_SERVER['REMOTE_HOST'])) {
					$ox = $banguage[4];
					$ox = str_replace('{IDENT}', $ident, $ox);
					echo $ox;
					echo "</body></html>";
					exit();
				}
			}
		}

		echo "\n<!-- Before Proxy: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";
		require_once("../common/proxy.php");
		if ($proxyblock == 1 && empty($_SESSION[$realpath]['user']['uid'])) {
			$bl = Proxy_IsProxy($_SERVER['REMOTE_ADDR']);
			if ($bl !== false) {
				echo "This chat blocks open proxies, and you are using one. You have been banned for 8 hours.";
				echo "<br>The list that caught you is: <a href='$bl'>$bl</a>";
				echo "</body></html>";
				AddBan($ident, time()+28800, '[proxy]', $realpath);
				setcookie("pJJChat_Banned", "$ident", time()+604800);
				die();
			}
		}
		echo "\n<!-- After Proxy: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->\n";

		if (($memonly <= 1) || CheckFlags('1', $_SESSION[$realpath]['flags'])) {
			ShowList($realpath);
		}
		else {
			echo $language[0];
        }

		echo "<hr width='75%'><p><a href='{$_SERVER['REQUEST_URI']}'>Refresh</a><br>";
		if ($_REQUEST['noimg']) {
			echo "<a href='{$_SERVER['PHP_SELF']}?noimg=0'>Show Images</a><p>";
		}
		else {
			echo "<a href='{$_SERVER['PHP_SELF']}?noimg=1'>Hide Images</a><p>";
		}
	}
	else {
		echo "Can't view this while banned.";
		echo "</center></body></html>";
	}
//	echo "<br><br> Debug: ".round(getmicrotime()-$start, 2)." secs / $cqs queries";
	echo "<!-- Debug: ".round(getmicrotime()-$start, 2)." secs / $cqs queries -->";
	echo "<!-- ";
	print_r($GLOBALS['querylog']);
	echo "\n\n";
	print_r($_SESSION);
	echo " -->";

?>
</center>
<hr>
<b>Other:</b><br>
- <a href="http://facebook.com/groups/pjj.chats/" target="_blank">Facebook Group</a><br>
- <a href="https://mewe.com/join/pjj_chats" target="_blank">MeWe Group</a><br>
- <a href="https://discord.gg/PkNf559" target="_blank">Discord Server</a><br>
- <a href="https://pjj.cc/characters.php" target="_blank">Character Manager</a><br>
- <a href="custom.php" target="_top">Customize Layout</a><br>
- <a href="reader.php?urls=1" target="TextWindow">Recent URLs</a><br>
- <a href="register/viewer.php" target="_blank">Profiles</a><br>
- <a href="manual.php" target="TextWindow">Manual</a><br>
- <a href="https://pjj.cc/legal/" target="TextWindow">ToS</a><br>
- <a href="reader.php?p=inspect" target="TextWindow">Debug Info</a><br>
- <a href="/common/vstat.php?chat=<?php echo $rpath; ?>" target="_blank">Statistics</a><br>

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-87771-3']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>

<!--
<script src='https://pjj.cc/common/js/userlist.js' type='text/javascript'></script>
<script type='text/javascript'>
    jj_userlist.collectImages();
    jj_userlist.setImageSizes(<?=$pimgx;?>, <?=$pimgy;?>);
</script>
-->

</body>
</html>
