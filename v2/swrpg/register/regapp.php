<?php
$altdata = null; // Chat ident
$ctitle = null; // Chat title
$handler = null; // MySQL

require_once '../../mysql.php';
require_once '../../setup.php';
require_once '../settings.php';
require_once '../options.php';
require_once '../../common/tome_of_power.php';
require_once '../../common/faction_help.php';
require_once '../../common/antispam.php';

if (!empty($altdata)) {
	$chatpath = $altdata;
}
else {
	$chatpath = ereg_replace('.*/([^/]+)/register/regapp.php$', 'chat\\1', $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc')) {
		$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
	}
}

GetChatPrefs($chatpath);

// Functions
function MakeFactionSelect($name, $chat, $style='', $optarg='') {
	$arr = GetFactionNames($chat);
	echo "<select name='$name' style='$style' $optarg>\n"; // Start select field
	foreach ($arr as $key => $value) {
		echo "<option value='{$key}'>{$value}</option>\n"; // Add option
	}
	echo "</select>\n"; // End
}

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Sune Wettersteen (forexs@lazy.dk)">
	<meta name="GENERATOR" content="Sune Wettersteen (forexs@lazy.dk)">
	<title>Registration Application - <?=$ctitle?></title>
	<style>
		td {
			font-family: Verdana;
			font-size: 10pt;
		}

		input,textarea,select {
			font-family: Verdana;
			font-size: 9pt;
			background-color: #FFFFFF;
			border: 1px black solid;
		}
	</style>
<script type="text/javascript">
if (window != window.top) {
  top.location.href = location.href;
}
</script>
</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="https://pjj.cc/gfx/up_tile.gif" valign="top" align="left" height="32"> </td></tr>
<tr><td valign="top" width="100%" height="100%">
	<div style="height: 20px;"></div>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%" width=80> </td>
	<td valign="top" height="100%">
	<span style="font-size: 16pt; font-weight: bold">Registration Application</span><BR>
	<span style="font-size: 11pt;"><?=$ctitle?></span>
	<BR><BR>
	<?php
		$nu_err = false;
		if (!empty($_POST['nu_add'])) {
			$nu_handle = PrepDBData(trim($_POST['nu_handle']));
			$nu_email = PrepDBData(trim($_POST['nu_email']));
			$nu_desc = PrepDBData(trim($_POST['nu_desc']));
			$nu_faction = intval($_POST['nu_faction']);

			if (empty($nu_handle)) {
				print('<div style="color: #CC0000;">You must specify a handle name</div>');
				$nu_err = true;
			}
			if (empty($nu_email)) {
				print('<div style="color: #CC0000;">You must specify an e-mail address</div>');
				$nu_err = true;
			}
			if (empty($_POST['nu_antispam_q']) || empty($_POST['nu_antispam_a']) || empty($GLOBALS['anti-spam-questions-sha1'][$_POST['nu_antispam_q']])
				|| $GLOBALS['anti-spam-questions-sha1'][$_POST['nu_antispam_q']] !== $_POST['nu_antispam_a']) {
				print('<div style="color: #CC0000;">Your anti-spam answer was wrong.</div>');
				$nu_err = true;
			}

			if (UserExists($chatpath, $nu_handle) != 0) {
				print('<div style="color: #CC0000;">A user already exists with the chosen handle</div>');
				$nu_err = true;
			}

			if (!$nu_err) {
				$query = sprintf("INSERT INTO uo_chat_regapps SET chat='%s', username='%s', email='%s', description='%s', faction=%d, rtime=%d, appstat=%d", $chatpath, $nu_handle, $nu_email, $nu_desc, $nu_faction, time(), 0);
				count_mysql_query($query, $handler);

				$emails = array();
				$query = "SELECT DISTINCT email
					FROM uo_chat_database
					WHERE chat='".$chatpath."'
					AND email IS NOT NULL AND email != ''
					AND (flags LIKE BINARY '%m%' OR flags LIKE BINARY '%A%' OR flags LIKE BINARY '%X%' OR flags LIKE BINARY '%Z%')
					AND dtime IS NULL
					";
				$rez = count_mysql_query($query, $handler);
				while ($row = mysql_fetch_assoc($rez)) {
					$row['email'] = mb_strtolower($row['email']);
					if (preg_match('/^[-@.+_\pL\pN\pM]+$/u', $row['email'])) {
						$emails[] = $row['email'];
					}
				}
				mysql_free_result($rez);
				$emails[] = $master_email;
				sort($emails);
				$emails = array_unique($emails);

				$chatpath = substr($chatpath, 4);

				$headers = '';
				$headers .= "From: $master_email\n";
				$headers .= "Reply-To: $master_email\n";
				$headers .= "X-pJJ-IP: {$_SERVER['REMOTE_ADDR']}\n";
				$headers .= "X-pJJ-Chat: https://pjj.cc/{$chatpath}/\n";
				$message = '';
				$message .= "New registration application in https://pjj.cc/{$chatpath}/ from $nu_handle <$nu_email>\n";
				//$message .= "BCC: ".implode(', ', $emails)."\n";
				foreach ($emails as $email) {
					mail($email, "pJJ: New Regapp in /$chatpath", $message, $headers);
				}

				print("Your application has been saved in the database.<BR>\n You will be notified by e-mail at ". $nu_email ." regarding wether or not your request has been accepted, as soon as an administrator has had a look at the application.<BR>\n<a href='?'>Back (New application)</a>");
			}
		}
		if (empty($_POST['nu_add']) || $nu_err) {
			$admcom = $GLOBALS['sql']->fetchAssoc($GLOBALS['sql']->query("SELECT regnotes FROM chatv2.chats WHERE chat_id=".$GLOBALS['biglog']['chat_id']));
			$admcom = $admcom['regnotes'];

			if ($admcom != "") {
				echo "
					<div style='margin-left: 1em; width: 40em;'>
						<B>Admin notes</B>: <I>{$admcom}</I>
					</div><BR>";
			}
	?>
	<form action="?" method="POST">
	<table border="0" cellspacing="0" cellpadding="2">
		<tr>
			<td>
				Handle:
			</td>
			<td>
				<input name="nu_handle" type="text" value="<?($nu_err ? print($_POST['nu_handle']) : false)?>" maxlength="64" style="width: 20em">
			</td>
		</tr>
		<tr>
			<td>
				E-Mail:
			</td>
			<td>
				<input name="nu_email" type="text" value="<?($nu_err ? print($_POST['nu_email']) : false)?>" maxlength="128" style="width: 20em">
			</td>
		</tr>
		<tr>
			<td>
				Faction:
			</td>
			<td>
				<?php
					MakeFactionSelect("nu_faction", $chatpath, "width: 20em;");
				?>
			</td>
		</tr>
		<tr>
			<td valign="top">
				Description/<BR>
				Comments:
			</td>
			<td>
				<textarea name="nu_desc" cols=70 rows=10><?($nu_err ? print($_POST['nu_desc']) : false)?></textarea>
			</td>
		</tr>
		<tr>
<?php
$keys = array_keys($GLOBALS['anti-spam-questions']);
shuffle($keys);
$key = $keys[0];
$key_hash = sha1(date('Y-m-d').$keys[0]);
echo <<<XOUT
<td>Anti-spam question:<br>
$key ...?
<input type="hidden" name="nu_antispam_q" value="$key_hash">
</td>
<td>
<select name="nu_antispam_a">
<option value=""></option>

XOUT;

$vals = array_values($GLOBALS['anti-spam-questions']);
shuffle($vals);
foreach ($vals as $v) {
	echo "<option value='$v'>$v</option>\n";
}

?>
			</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" name="nu_add" value="Apply">
			</td>
		</tr>
	</table>
	</form>
	<?php
		}
	?>
</td>
	<td valign="top" align="right" height="100%" width=80> </td></tr>
	</table>
</td></tr>
<tr><td background="https://pjj.cc/gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>
</body>
</html>
