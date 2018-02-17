<?php
ob_start();
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Global Character Manager</title>

<style type="text/css">
* {
	font-family: sans-serif, sans;
	text-align: left;
}

th {
	font-weight: bold;
	background-color: #ddd;
}
</style>
<script type="text/javascript">
function gx(w) {
	return document.getElementById(w);
}
function toggleall() {
	for (var i=1;;i++) {
		var b = gx('c'+i);
		if (!b) {
			break;
		}
		b.checked = !b.checked;
	}
}
function selectall() {
	for (var i=1;;i++) {
		var b = gx('c'+i);
		if (!b) {
			break;
		}
		b.checked = true;
	}
}
function unselectall() {
	for (var i=1;;i++) {
		var b = gx('c'+i);
		if (!b) {
			break;
		}
		b.checked = false;
	}
}
</script>
</head>
<body bgcolor="white" color="black">
<form action="/characters.php" method="post">
<?php
	$chars = array();
	if (!empty($_REQUEST['email']) && !empty($_REQUEST['password'])) {
		require_once __DIR__.'/mysql.php';

		$_REQUEST['email'] = mysqli_real_escape_string($handler, $_REQUEST['email']);
		$_REQUEST['org_password'] = $_REQUEST['password'];
		$_REQUEST['password'] = md5($_REQUEST['password']);

		$update = array();
		if (!empty($_REQUEST['newemail'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newemail']));
			$update[] = "email='".$new."'";
		}
		if (!empty($_REQUEST['newpassword'])) {
			$new = md5(trim($_REQUEST['newpassword']));
			$update[] = "password='".$new."'";
		}
		if (!empty($_REQUEST['newicq'])) {
			$new = intval(trim($_REQUEST['newicq']));
			$update[] = "icq='".$new."'";
		}
		if (!empty($_REQUEST['newcolor'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newcolor']));
			$update[] = "pcolor='".$new."'";
		}
		if (!empty($_REQUEST['newimage'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newimage']));
			$update[] = "pimage='".$new."'";
		}
		if (!empty($_REQUEST['newlink'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newlink']));
			$update[] = "plink='".$new."'";
		}
		if (!empty($_REQUEST['newprefs'])) {
			$new = mysqli_real_escape_string($handler, preg_replace('@[^A-Za-z0-9]@', '', trim($_REQUEST['newprefs'])));
			$update[] = "prefs='".$new."'";
		}
		if (!empty($_REQUEST['newaim'])) {
			$new = mysqli_real_escape_string($handler, preg_replace('@[^A-Za-z0-9]@', '', trim($_REQUEST['newaim'])));
			$update[] = "aim='".$new."'";
		}
		if (!empty($_REQUEST['newym'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newym']));
			$update[] = "ym='".$new."'";
		}
		if (!empty($_REQUEST['newmsn'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newmsn']));
			$update[] = "msn='".$new."'";
		}
		if (!empty($_REQUEST['newskype'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newskype']));
			$update[] = "skype='".$new."'";
		}
		if (!empty($_REQUEST['newsite'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newsite']));
			$update[] = "site='".$new."'";
		}
		if (!empty($_REQUEST['newlastfm'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newlastfm']));
			$update[] = "lastfm='".$new."'";
		}
		if (!empty($_REQUEST['newflickr'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newflickr']));
			$update[] = "flickr='".$new."'";
		}
		if (!empty($_REQUEST['newfacebook'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newfacebook']));
			$update[] = "facebook='".$new."'";
		}
		if (!empty($_REQUEST['newgplus'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newgplus']));
			$update[] = "gplus='".$new."'";
		}
		if (!empty($_REQUEST['newdisplayname'])) {
			$new = mysqli_real_escape_string($handler, trim($_REQUEST['newdisplayname']));
			$update[] = "displayname='".$new."'";
		}

		if (empty($_REQUEST['export']) && !empty($update)) {
			$update = implode(', ', $update);
			$query = "UPDATE uo_chat_database SET
			{$update}
			WHERE email='".$_REQUEST['email']."'
			AND password='".$_REQUEST['password']."'
			AND chat LIKE 'chat%'";
			foreach ($_REQUEST['uids'] as $key => $uid) {
				if (!empty($uid)) {
					$key = intval($key);
					mysqli_query($handler, "$query AND uid=$key");
				}
			}
		}

		$query = "SELECT uid, chat, username, displayname, prefs, pimage, plink, pcolor, icq, aim, ym, msn, site, skype, lastfm, flickr, facebook, gplus
		FROM uo_chat_database
		WHERE email='".$_REQUEST['email']."' AND password='".$_REQUEST['password']."'
		AND chat LIKE 'chat%'
		AND dtime IS NULL
		ORDER BY chat ASC, username ASC";

		$rez = mysqli_query($handler, $query);
		while ($row = mysqli_fetch_assoc($rez)) {
			$chars[] = $row;
		}
		mysqli_free_result($rez);

		if (!empty($_REQUEST['export']) && !empty($chars)) {
			ob_end_clean();
			header('Content-Type: text/tab-separated-values; charset=UTF-8');
			header('Content-Disposition: attachment; filename="characters.tsv"');
			echo implode("\t", array_keys($chars[0]))."\n";
			foreach ($chars as $c) {
				echo implode("\t", $c)."\n";
			}
			exit();
		}
	}

	if (!empty($chars)) {
		echo <<<OUTPUT
	<blockquote>
		Found the following characters matching those credentials...
	</blockquote>
	<table cellspacing="1" cellpadding="3" border="0">
	<tr valign="top">
		<th>&nbsp;</th>
		<th>ID</th>
		<th>Chat</th>
		<th>Username</th>
		<th>Color</th>
		<th>Image</th>
		<th>Link</th>
		<th>Prefs</th>
	</tr>
	<tr valign="top">
		<th>&nbsp;</th>
		<th>ICQ</th>
		<th>AIM</th>
		<th>Yahoo</th>
		<th>MSN</th>
		<th>Skype</th>
		<th>Website</th>
		<th>Last.fm</th>
	</tr>
	<tr valign="top">
		<th>&nbsp;</th>
		<th>Flickr</th>
		<th>Facebook</th>
		<th>Google+</th>
		<th>Display Name</th>
	</tr>
	<tr valign="top">
		<th colspan="8">
			<input type="button" onclick="toggleall();" value="Toggle All">
			<input type="button" onclick="selectall();" value="Select All">
			<input type="button" onclick="unselectall();" value="Unselect All">
		</th>
	</tr>
OUTPUT;
		$cl = '#ffffff';
		$cn = 0;
		foreach ($chars as $char) {
			$cn++;
			$chat = mb_substr($char['chat'], 4);
			$char['username'] = htmlentities($char['username']);
			$char['pcolor'] = htmlentities($char['pcolor']);
			$char['pimage'] = htmlentities($char['pimage']);
			$char['plink'] = htmlentities($char['plink']);
			$char['prefs'] = htmlentities($char['prefs']);
			$char['icq'] = htmlentities($char['icq']);
			$char['aim'] = htmlentities($char['aim']);
			$char['ym'] = htmlentities($char['ym']);
			$char['msn'] = htmlentities($char['msn']);
			$char['skype'] = htmlentities($char['skype']);
			$char['site'] = htmlentities($char['site']);
			$char['lastfm'] = htmlentities($char['lastfm']);
			$char['flickr'] = htmlentities($char['flickr']);
			$char['facebook'] = htmlentities($char['facebook']);
			$char['gplus'] = htmlentities($char['gplus']);
			$char['displayname'] = htmlentities($char['displayname']);

			$ck = '';
			if (!empty($_REQUEST['uids'][$char['uid']])) {
				$ck = ' checked';
			}

			echo <<<OUTPUT
			<tr valign="top" bgcolor="{$cl}">
				<td><input type="checkbox" name="uids[{$char['uid']}]" value="c{$char['uid']}" id="c{$cn}"$ck></td>
				<td>{$char['uid']}</td>
				<td><a href="/{$chat}/">{$chat}</a></td>
				<td>{$char['username']}</td>
				<td>{$char['pcolor']}</td>
				<td>{$char['pimage']}</td>
				<td>{$char['plink']}</td>
				<td>{$char['prefs']}</td>
			</tr>
			<tr valign="top" bgcolor="{$cl}">
				<td>&nbsp;</td>
				<td>{$char['icq']}</td>
				<td>{$char['aim']}</td>
				<td>{$char['ym']}</td>
				<td>{$char['msn']}</td>
				<td>{$char['skype']}</td>
				<td>{$char['site']}</td>
				<td>{$char['lastfm']}</td>
			</tr>
			<tr valign="top" bgcolor="{$cl}">
				<td>&nbsp;</td>
				<td>{$char['flickr']}</td>
				<td>{$char['facebook']}</td>
				<td>{$char['gplus']}</td>
				<td>{$char['displayname']}</td>
			</tr>
OUTPUT;
			if ($cl == '#ffffff') {
				$cl = '#eeeeee';
			} else {
				$cl = '#ffffff';
			}
		}
		echo <<<OUTPUT
	<tr valign="top">
		<th colspan="8">
			<input type="button" onclick="toggleall();" value="Toggle All">
			<input type="button" onclick="selectall();" value="Select All">
			<input type="button" onclick="unselectall();" value="Unselect All">
			<input type="submit" name="export" value="Export All as TSV">
		</th>
	</tr>
	</table>
OUTPUT;

	?>
	<blockquote>
		For the selected characters, set the following fields...
		Empty means it won't change that field. If you want it zeroed, put in a space.
	</blockquote>
	<table cellspacing="1" cellpadding="3" border="0">
	<tr valign="top">
		<th>Email</th>
		<th>Password</th>
		<th>Color</th>
		<th>Image</th>
		<th>Link</th>
		<th>Prefs</th>
	</tr>
	<tr valign="top">
		<th>ICQ</th>
		<th>AIM</th>
		<th>Yahoo</th>
		<th>MSN</th>
		<th>Skype</th>
		<th>Website</th>
		<th>Last.fm</th>
	</tr>
	<tr valign="top">
		<th>Flickr</th>
		<th>Facebook</th>
		<th>Google+</th>
		<th>Display Name</th>
	</tr>
	<tr valign="top">
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newemail']);?>" name="newemail"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newpassword']);?>" name="newpassword"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newcolor']);?>" name="newcolor"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newimage']);?>" name="newimage"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newlink']);?>" name="newlink"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newprefs']);?>" name="newprefs"></td>
	</tr>
	<tr valign="top">
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newicq']);?>" name="newicq"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newaim']);?>" name="newaim"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newym']);?>" name="newym"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newmsn']);?>" name="newmsn"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newskype']);?>" name="newskype"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newsite']);?>" name="newsite"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newlastfm']);?>" name="newlastfm"></td>
	</tr>
	<tr valign="top">
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newflickr']);?>" name="newflickr"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newfacebook']);?>" name="newfacebook"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newgplus']);?>" name="newgplus"></td>
		<td><input type="text" size="10" value="<?=htmlentities($_REQUEST['newdisplayname']);?>" name="newdisplayname"></td>
	</tr>
	</table>
	<?php
	}

?>

<blockquote>
	Fill in email and password.
</blockquote>
<table cellspacing="1" cellpadding="3" border="0">
<tr valign="top">
	<th>Email</th>
	<th>Password</th>
	<th>&nbsp;</th>
</tr>
<tr valign="top">
	<td><input type="text" size="20" value="<?=htmlentities($_REQUEST['email']);?>" name="email"></td>
	<td><input type="password" size="10" value="<?=htmlentities($_REQUEST['org_password']);?>" name="password"></td>
	<td><input type="submit" value="Submit"></td>
</tr>
</table>
</form>

<blockquote>
	This can be used to manage various aspects of all your characters across pJJ,
	if they all have the same email and password.
	Any questions should be directed to <a href="http://tinodidriksen.com/convoke/">Tino Didriksen</a>.
</blockquote>
</body>
</html>
