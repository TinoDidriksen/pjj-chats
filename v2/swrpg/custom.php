<?php
    ob_start();
	require_once("../common/session.php");
	require_once("../../chatv3/_inc/mmcache.php");
	require_once("../mysql.php");
	require_once("../setup.php");
	require_once("../common/helpers.php");
?><!DOCTYPE html>
<html>
<head>
	<title>
<?php
	require_once("settings.php");
	require_once("options.php");
	echo $ctitle;

	if ($_SERVER['HTTP_HOST']) {
		$cpath = "https://".$_SERVER['HTTP_HOST'].preg_replace("~(.*)custom.php~", "\\1", $_SERVER['PHP_SELF']);
	}
	else {
		$cpath = "https://".$_SERVER['SERVER_NAME'].preg_replace("~(.*)custom.php~", "\\1", $_SERVER['PHP_SELF']);
	}

	if (empty($initlink)) {
		$initlink = "manual.php";
	}

?>
</title>
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<script type="text/javascript">
if (window != window.top) {
	top.location.href = location.href;
}
</script>
</head>

<?php
	$realpath = preg_replace('@.*/([^/]+)/custom.php$@', '\1', $_SERVER['PHP_SELF']);
	$chatpath = $realpath;

	GetChatPrefs($realpath);
	$GLOBALS['biglog']['real_id'] = $GLOBALS['biglog']['chat_id'];

	if ($_REQUEST['custom']) {
		echo "<frameset cols='*, 0' border='1' MARGINWIDTH='0' MARGINHEIGHT='0'>";
		if (strstr($_REQUEST['avatar'], "long")) {
			if (strstr($_REQUEST['avatar'], "right")) {
				if ($_REQUEST['console'] == "bottom") {
					echo "<FRAMESET Cols='*, {$_REQUEST['uwidth']}' border=1>
					<FRAMESET Rows='*, {$_REQUEST['cheight']}' border=1><FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
					<FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0'>
					</FRAMESET><FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'></frameset>";
				}
				else {
					echo "<FRAMESET Cols='*, {$_REQUEST['uwidth']}' border=1>
					<FRAMESET Rows='{$_REQUEST['cheight']}, *' border=1>
					<FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0'>
					<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
					</FRAMESET><FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'></frameset>";
				}
			}
			else {
				if ($_REQUEST['console'] == "bottom") {
					echo "<FRAMESET Cols='{$_REQUEST['uwidth']}, *' border=1><FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
					<FRAMESET Rows='*, {$_REQUEST['cheight']}' border=1><FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
					<FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0'>
					</FRAMESET></frameset>";
				}
				else {
					echo "<FRAMESET Cols='{$_REQUEST['uwidth']}, *' border=1><FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
					<FRAMESET Rows='{$_REQUEST['cheight']}, *' border=1>
					<FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0'>
					<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
					</FRAMESET></frameset>";
				}
			}
		}
		else if (strstr($_REQUEST['avatar'], "left")) {
			if ($_REQUEST['console'] == "bottom") {
				echo "<FRAMESET Rows='*, {$_REQUEST['cheight']}' border=1><FRAMESET Cols='{$_REQUEST['uwidth']}, *' border=1>
				<FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				</FRAMESET><FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0' ></frameset>";
			}
			else {
				echo "<FRAMESET Rows='{$_REQUEST['cheight']}, *' border=1><FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0' >
				<FRAMESET Cols='{$_REQUEST['uwidth']}, *' border=1>
				<FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				</FRAMESET></frameset>";
			}
		}
		else if (strstr($_REQUEST['avatar'], "right")) {
			if ($_REQUEST['console'] == "bottom") {
				echo "<FRAMESET Rows='*, {$_REQUEST['cheight']}' border=1><FRAMESET Cols='*, {$_REQUEST['uwidth']}' border=1>
				<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				<FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				</FRAMESET><FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0' ></frameset>";
			}
			else {
				echo "<FRAMESET Rows='{$_REQUEST['cheight']}, *' border=1><FRAME SRC='$cpath"."login.php' NAME='Console' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0' >
				<FRAMESET Cols='*, {$_REQUEST['uwidth']}' border=1>
				<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				<FRAME SRC='$cpath"."userlist.php' NAME='Userlist' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				</FRAMESET></frameset>";
			}
		}
		else {
			if ($_REQUEST['console'] == "bottom") {
				echo "<FRAMESET Rows='*, {$_REQUEST['cheight']}' border=1>
				<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				<FRAME SRC='$cpath"."login.php' NAME='Console' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				</FRAMESET>";
			}
			else {
				echo "<FRAMESET Rows='{$_REQUEST['cheight']}, *' border=1>
				<FRAME SRC='$cpath"."login.php' NAME='Console' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				<FRAME SRC='$initlink' NAME='TextWindow' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='AUTO'>
				</FRAMESET>";
			}
		}
		echo "<frame src='https://pjj.cc/common/xmlsocket/worker.php?chatid=".$GLOBALS['biglog']['real_id']."' name='XMLSocket' SCROLLING='auto' MARGINWIDTH='0' MARGINHEIGHT='0'>
		</frameset>";
	}
	else {
		echo "$bodytag<p><blockquote><form action=custom.php method=get>
		<table border=0 cellspacing=0 cellpadding=2><tr><td>
		Avatar:<br>None<input type=radio name=avatar value=none><br>
		Right<input type=radio name=avatar value=right checked><br>
		Left<input type=radio name=avatar value=left><br>
		Long Right<input type=radio name=avatar value=longright><br>
		Long Left<input type=radio name=avatar value=longleft><br>
		</td><td>Avatar width:<br><input type=text name=uwidth value=150></td><td>Console height:<br><input type=text name=cheight value=115></td>
		<td>Console:<br>
		Top:<input type=radio name=console value=top><br>
		Bottom:<input type=radio name=console value=bottom checked><br></td></tr></table>
		<input type=hidden name=custom value=custom><input type=submit name=submit value=Customize></form></blockquote></body>";
	}

?>
</html>
