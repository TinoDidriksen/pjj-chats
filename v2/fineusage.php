<?php
	ob_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html">
	<meta name="Author" content="Tino Didriksen (mail@tinodidriksen.com)">
	<meta name="GENERATOR" content="Tino Didriksen (mail@tinodidriksen.com)">
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
<b>Average usage per day</b><br>Measured from the last 28 days.<p>
<table border=0 cellspacing=0 cellpadding=2 bgcolor=#FFFFFF>
<tr><td colspan=4 bgcolor="#000000" height=1> </td></tr>
<tr>
	<td>#</td>
	<td>Chat</td>
	<td align="center">Lines</td>
	<td>Graph</td>
</tr>
<tr><td colspan=4 bgcolor="#000000" height=1> </td></tr>
<?php
	include("mysql.php");

function GetPortal($chat) {
	$sportal = 0;
	@include_once($chat."/options.php");
	return $sportal;
}

	$overall = 0;
    $chats = array();
	$res = array();

	$query = "SELECT chat, chat_id FROM chatv2.chats WHERE dtime IS NULL";
	$rez = $GLOBALS['sql']->query($query);
	$num_chats = $GLOBALS['sql']->numRows($rez);
	for ($i=0;$i<$num_chats;$i++) {
		$row = $GLOBALS['sql']->fetchAssoc($rez, $i);
		$chats[$row['chat_id']] = $row;
	}
	$GLOBALS['sql']->freeResult($rez);

    foreach ($chats as $chat) {
        $query = "SELECT count(stamp) as cnt FROM chatv2logs.log_{$chat['chat_id']} WHERE stamp > now()-'28 day'::interval";
        $rez = $GLOBALS['sql']->query($query);
        $row = $GLOBALS['sql']->fetchAssoc($rez);
        $GLOBALS['sql']->freeResult($rez);
        $res[$chat['chat']] = $row['cnt'];
        $overall += $row['cnt'];
    }

    $overall = ceil($overall/28);

	$bg = '#EEEEEE';
	$i=1;
	arsort($res);
	foreach($res as $key => $total) {
		if (($_REQUEST['hidden'] == 2) && !GetPortal($key)) {
		    continue;
		}
		else if (GetPortal($key) && empty($_REQUEST['hidden'])) {
			continue;
        }
		$total = ceil($total/28);
        if ($total < 5) {
            break;
        }
		$bg = ($bg=='#FFFFFF') ? '#EEEEEE':'#FFFFFF';
		$email = $key;
		echo <<<PHPEND
<tr bgcolor="{$bg}">
	<td>{$i}</td>
	<td><a href="{$email}/">{$email}</a></td>
	<td align="center">{$total}</td>
	<td><a href="common/vstat.php?chat={$email}">See graph</a></td>
</tr>
PHPEND;
		$i++;
	}

	echo <<<PHPEND
	<tr><td colspan=4 bgcolor="#000000" height=1> </td></tr>
	<tr>
		<td>-</td>
		<td>Sum</td>
		<td align="center">{$overall}</td>
		<td><a href="common/vstat.php">See graph</a></td></tr>
PHPEND;
?>
<tr><td colspan=4 bgcolor="#000000" height=1> </td></tr>
</table>
</td>
	<td valign="top" align="right" height="100%"><img src="gfx/up_r.gif" border="0"></td></tr>
	</table>
</td></tr>
<tr><td background="gfx/dn_tile.gif" align="center" valign="bottom" height="32"><center><a href="rq.php" target=_blank><img src="gfx/worlds.gif" border="0"></a></center></td></tr>
</table>
</body>
</html>
