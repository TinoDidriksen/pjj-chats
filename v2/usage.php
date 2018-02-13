<?php
	//ob_start("ob_gzhandler");
	ob_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Project JJ Chats - Let Worlds Unfold</title>

<script type="text/javascript">
if (window != window.top)
  top.location.href = location.href;
</script>
</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="gfx/up_tile.gif" valign="top" align="left" height="32">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="32">
	<tr><td valign="top" align="left" height="32"><a href="?"><img src="gfx/projectjj.gif" border="0"></a></td>
	<td valign="top" align="right" height="32"><a href="rq.php" target=_blank><img src="gfx/phpchat.gif" border="0"></a></td></tr>
	</table>
</td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%"><img src="gfx/up_l.gif" border="0"></td>
	<td valign="top" height="100%">
<center><img src="gfx/null.gif" border=0><br>
<table border=0 cellspacing=0 cellpadding=2 bgcolor=#FFFFFF>
<tr align=center>
	<td><b>Chat</b></td>
	<td><b>Last Usage</b></td>
	<td><b>Warnings</b></td>
	<td><b>Days Inactive</b></td>
	<td><b>Email</b></td>
	<td><b>Created</b></td>
</tr>
<?php
	include("mysql.php");
	include("setup.php");

	$maxdays = 28;
	$deldays = 42;
	$bdays = $deldays-$maxdays; // 14
	$warnc = array();
	for ($i=0;$i<$bdays;$i++) {
		$grad = (256/$bdays)*($i);
		$warnc[$i] = "#".dechex((int)$grad)."0000";
	}

	$result = @mysqli_query($handler, "SELECT chat,utime,numwarn,ctime FROM uo_chat_last WHERE utime<=(UNIX_TIMESTAMP()-({$maxdays}*86400-86400)) ORDER BY utime ASC");

	while ($row = mysqli_fetch_row($result)) {
		$path = mb_substr($row[0], 4);
		if ((is_dir($path)) && (file_exists($path."/sendmsg.php")) && (file_exists($path."/settings.php"))) {
			$stamp = round((time()-$row[1])/86400);
			include($path."/settings.php");
			if ($stamp < $maxdays)
				$color = "#000000";
			if ($stamp >= $deldays)
				$color = "#FF0000";
			else
				$color = $warnc[($stamp-$maxdays)%$bdays];
			if ($_SERVER['REMOTE_ADDR'] != "194.192.135.214")
				$cadmin = "Hidden";

			if ($row[3] != 0)
				$cdate = (date("F d, Y",$row[3]));
			else
				$cdate = "";

			echo "<tr align=left><td><a href=\"/$path/\">$path</a></td><td><font color='$color'>".(date("F d",$row[1]))."</font></td><td align=center><font color='$color'>$row[2]</font></td><td align=center><font color='$color'>$stamp</font></td><td><a href=\"mailto:$cadmin\">$cadmin</a></td><td>$cdate</td></tr>";
		}
	}
?>
</table>
</td>
	<td valign="top" align="right" height="100%"><img src="gfx/up_r.gif" border="0"></td></tr>
	</table>
</td></tr>
<tr><td background="gfx/dn_tile.gif" align="center" valign="bottom" height="32"><center><a href="rq.php" target=_blank><img src="gfx/worlds.gif" border="0"></a></center></td></tr>
</table>
</body>
</html>
