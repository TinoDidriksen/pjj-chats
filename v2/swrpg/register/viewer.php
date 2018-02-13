<?php
//ob_start("ob_gzhandler");
ob_start();

include ("../../mysql.php");
include ("../../setup.php");
include ("../../common/tome_of_power.php");

if (!empty($_REQUEST['fm'])) {
	$_REQUEST['frames'] = $_REQUEST['fm'];
}
if (!empty($_REQUEST['su'])) {
	$_REQUEST['selecteduser'] = $_REQUEST['su'];
}

include("../settings.php");
include("../options.php");
if (!empty($altdata)) {
	$chatpath = $altdata;
}
else {
	$chatpath = preg_replace("~.*/([^/]+)/register/viewer.php$~", "chat\\1", $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
		$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
	}
}

if (isset($_REQUEST['frames'])) {
	if ($_REQUEST['frames'] == "menu") {
		echo "<html><body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' bgcolor='#000000'>";
		echo "<div align=right>";

		ShowDropdown($chatpath);
		echo "</div></body></html>";
	}
	else if ($_REQUEST['frames'] == "view") {
		if ($_REQUEST['selecteduser']) {
			ShowProfile($_REQUEST['selecteduser'], $chatpath);
		}
		else {
			echo "<html><body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' bgcolor='#000000' text='#FFFFFF'><center>Select a profile from the list in the top right corner.</center></body></html>";
		}
	}
} else {
	echo "<html><head><title>Profile Viewer</title>
	<frameset rows='30,*' border=0>
		<FRAME SRC='viewer.php?frames=menu' NAME='menu' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='no'>
		<FRAME SRC='viewer.php?frames=view' NAME='view' MARGINWIDTH='0' MARGINHEIGHT='0' SCROLLING='auto'>
	</frameset></head><body>Requires frame-enabled browser.</body></html>";
}
