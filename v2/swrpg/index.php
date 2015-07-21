<?php
    require_once('../mysql.php');
	if (strpos(strtolower($_SERVER['HTTP_HOST']), 'chat.projectjj.') !== false) {
		$chat = preg_replace('@.*/([^/]+)/index.php@', '\1', $_SERVER['PHP_SELF']);
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: http://pjj.cc/'.$chat.'/');
		die();
	}
	if (strpos(strtolower($_SERVER['HTTP_HOST']), '.pjj.cc') !== false) {
		$chat = preg_replace('@^(.+)\.pjj\.cc$@', '\1', $_SERVER['HTTP_HOST']);
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: http://pjj.cc/'.$chat.'/');
		die();
	}

	require_once('../common/session.php');
	require_once('../../chatv3/_inc/mmcache.php');
	require_once('../mysql.php');
	require_once('../setup.php');
	require_once('../common/helpers.php');

?><!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>
<?php
	require_once('settings.php');
	require_once('options.php');
	echo $ctitle;

	if (empty($initlink)) {
		$initlink = 'manual.php';
	}

    $uwidth = max(150, $pimgx*1.2);
    $uwidth = min($uwidth, 200);

	$realpath = preg_replace('@.*/([^/]+)/index.php$@', '\1', $_SERVER['PHP_SELF']);

	GetChatPrefs($realpath);
	$GLOBALS['biglog']['real_id'] = $GLOBALS['biglog']['chat_id'];
?>
</title>
<meta name="robots" content="noarchive">
<link rel="shortcut icon" type="image/x-icon" href="/favicon.ico" />
<link rel="icon" type="image/x-icon" href="/favicon.ico" />
<style type="text/css">
<?=$csshead;?>
</style>
<script>
if (window != window.top) {
    top.location.href = location.href;
}
</script>
</head>

<frameset cols="*, 0" border="1" marginwidth="0" marginheight="0">
<frameset Cols="*, <?php echo $uwidth; ?>" border="1" marginwidth="0" marginheight="0">
	<frameset Rows="*, 115" border="1" marginwidth="0" marginheight="0">
		<frame src="<?php echo $initlink; ?>" name="TextWindow" marginwidth="0" marginheight="0" scrolling="auto">
		<frame src="login.php" name="Console" scrolling="auto" marginwidth="0" marginheight="0" >
	</frameset>
	<frame src="userlist.php" name="Userlist" marginwidth="0" marginheight="0" scrolling="auto">
    <noframes>
<body>
<p><?=$cdescript; ?></p>
<p>Frames required, sorry.<p>- <a href="mailto:chat@projectjj.com">Project JJ</a> -</p>
</body>
    </noframes>
</frameset>
<frame src="http://pjj.cc/common/xmlsocket/worker.php?chatid=<?=$GLOBALS['biglog']['real_id'];?>" name="XMLSocket" scrolling="auto" marginwidth="0" marginheight="0" >
</frameset>
</html>
