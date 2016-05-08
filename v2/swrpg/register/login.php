<?php
    ob_start();
    ignore_user_abort(true);
	$cqs = 0;
?><!DOCTYPE html>
<html>
<head>
	<META NAME="ROBOTS" CONTENT="NOARCHIVE">
	<META NAME="ROBOTS" CONTENT="NOINDEX">
	<META NAME="ROBOTS" CONTENT="NOFOLLOW">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (tino@didriksen.cc)">
	<meta name="GENERATOR" content="Tino Didriksen (tino@didriksen.cc)">
	<title>Control Panel<?php
	if (!empty($_REQUEST['login']) && ($_REQUEST['adminaction'] != "logout")) {
		echo " - ".ucwords($_REQUEST['login']);
	}
?></title>

<script type="text/javascript">
if (window != window.top) {
  top.location.href = location.href;
}
</script>
<style>
td {
	font-family: Verdana;
	font-size: 10pt;
}

input,select {
	font-family: Verdana;
	font-size: 9pt;
	background-color: #FFFFFF;
	border: 1px black solid;
}
</style>
</head>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="https://pjj.cc/gfx/up_tile.gif" valign="top" align="left" height="32"> </td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%" width=80> </td>
	<td valign="top" height="100%">
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
<?php
	// Very dirty hack, but I really don't want to go through this bloody file now.
	extract($_REQUEST);

	include("../../common/session.php");
	include("../../mysql.php");
	include("../../setup.php");

	include("../settings.php");
	include("../options.php");
	include("../../common/tome_of_power.php");
	include("../../common/faction_help.php");

	ChatSessionSuspend();

function GetFile($file) {
	$text = '';

	if (file_exists($file)) {
		$text = file_get_contents($file);
	}
	return $text;
}

	if (!empty($altdata)) {
			$chatpath = $altdata;
	}
	else {
		$chatpath = ereg_replace(".*/([^/]+)/register/login.php$", "chat\\1", $_SERVER['PHP_SELF']);
		if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc'))
			$chatpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);
	}
	$realpath = ereg_replace(".*/([^/]+)/register/login.php$", "chat\\1", $_SERVER['PHP_SELF']);
	if (($_SERVER['HTTP_HOST'] != 'v2.pjj.cc') && strstr($_SERVER['HTTP_HOST'], '.pjj.cc'))
		$realpath = preg_replace('/(.*?)\.pjj\.cc/is', 'chat\1', $_SERVER['HTTP_HOST']);

	if ($_SERVER['HTTP_HOST']) {
		$cpath = "http://".$_SERVER['HTTP_HOST'].ereg_replace("(.*)/register/login.php", "\\1", $_SERVER['PHP_SELF']);
	}
	else {
		$cpath = "http://".$_SERVER['SERVER_NAME'].ereg_replace("(.*)/register/login.php", "\\1", $_SERVER['PHP_SELF']);
	}

	$prefs = GetChatPrefs($chatpath);

	if (strcmp($_REQUEST['adminaction'], 'logout') == 0) {
		unset($_REQUEST['password']);
		unset($_REQUEST['login']);
	}
	else {
		if (empty($_REQUEST['password']) && !empty($_SESSION[$realpath]['user']['password'])) {
			$_REQUEST['password'] = $_SESSION[$realpath]['user']['password'];
		}
		if (empty($_REQUEST['login']) && !empty($_SESSION[$realpath]['user']['username'])) {
			$_REQUEST['login'] = $_SESSION[$realpath]['user']['username'];
		}
	}

	if (($_REQUEST['login']) && ($_REQUEST['password'])) {
		$login = trim(ereg_replace($master_name_filter, "", strtolower($_REQUEST['login'])));
		$password = $_REQUEST['password'];
		$adminaction = $_REQUEST['adminaction'];

		$userlevel = VerifyLogin($login, $password, $chatpath);
		if ($userlevel != -1) {
			if (empty($_SESSION[$realpath]['user']['username'])) {
				$_SESSION[$realpath]['user']['username'] = $login;
			}
			if (empty($_SESSION[$realpath]['user']['password'])) {
				if (strlen($password) != 32) {
					$_SESSION[$realpath]['user']['password'] = md5($password);
				}
				else {
					$_SESSION[$realpath]['user']['password'] = $password;
				}
			}
			echo '<h2>Logged in as ', $login ,'</h2>', "\n";
			echo "<p><br><FORM ACTION='login.php' METHOD='POST'>";
			if ($adminaction) {
				if ($adminaction == "adduser") {
					$new_name	= $_REQUEST['new_name'];
					$new_pass	= $_REQUEST['new_pass'];
					$new_level	= $_REQUEST['new_level'];
					if (($new_name) && ($new_pass) && ($new_level)) {
						if ($new_pass == "random") {
							$new_pass = RandomPass(8);
						}

						$new_mail	= $_REQUEST['new_mail'];
						$message	= $_REQUEST['message'];
						$subject	= $_REQUEST['subject'];

						if (($message) && ($subject) && ($new_email)) {
							mail($new_email, $subject, $message, "From: $master_email\nReply-To: $cadmin\nX-pJJ-IP: {$_SERVER['REMOTE_ADDR']}\nX-pJJ-Chat: https://pjj.cc/{$chatpath}/\nX-pJJ-Auth: {$_REQUEST['login']}\n");
							echo "Mail sent to $new_email.";
						}
						else {
							$new_faction = $_REQUEST['new_faction'];

							if (empty($new_faction)) {
								$new_faction = "0";
							}
							if (AddUser($login, $password, trim($new_name), trim($new_pass), trim($new_faction), trim($new_email), trim($new_level), $chatpath) >= 1) {
								if ($ruid) {
									count_mysql_query("UPDATE uo_chat_regapps SET appstat=1 WHERE chat='{$chatpath}' AND id='{$ruid}'", $handler);
								}

								if ($new_email) {
									$uname = urlencode($new_name);
									$upass = urlencode($new_pass);
									$subject = "Welcome to $ctitle, $new_name.";
									$message = "You have been accepted in $ctitle with the login:\n";
									$message .= "Name: $new_name\n";
									$message .= "Password: $new_pass\n";
									$message .= "\n";
									$message .= "Please log in at $cpath/register/login.php?login=$uname&password=$upass and edit your profile.\n";
									$message .= "Your flag $new_level, which is ";
									if (CheckFlags("m", $new_level)) {
										$message .= "Chat Master";
									}
									else if (CheckFlags("Z", $new_level)) {
										$message .= "Master";
									}
									else if (CheckFlags("X", $new_level)) {
										$message .= "Administrator";
									}
									else if (CheckFlags("x", $new_level)) {
										$message .= "Moderator";
									}
									else if (CheckFlags("1", $new_level)) {
										$message .= "Member";
									}
									else if (CheckFlags("z", $new_level)) {
										$message .= "OOC Member";
									}
									$message .= ".\n";
									$message .= "\n";
									$message .= ucwords($login)."\nAdministrator of $ctitle.";
									echo "<br>Email will be sent to $new_email with the following message:<br>
									Subject: <input type=text name='subject' value='$subject'>
									<br>Message: <textarea name='message' cols=60 rows=10>$message</textarea><input type=hidden name='new_name' value='$new_name'>
									<input type=hidden name='new_pass' value='$new_pass'><input type=hidden name='new_level' value='$new_level'>
									<input type=hidden name='new_email' value='$new_email'>
									<input type=hidden name='adminaction' value='$adminaction'>";
								}
							}
						}
					}
					else {
						echo "<input type=hidden name=adminaction value=$adminaction>
						<br>
						<table BORDER=0 CELLSPACING=3 CELLPADDING=3>
						<tr><td>
						Name:
						</td><td>
						<input type=text name=new_name size=15 max=25 value='$new_name'>
						</td></tr>
						<tr><td>
						Password:
						</td><td>
						<input type=text name=new_pass size=15 max=25 value='random'>
						</td></tr>
						<tr><td>
						Mail info to:
						</td><td>
						<input type=text name=new_email size=15 max=128 value='$new_email'>
						</td></tr>
						<tr><td>
						Faction:
						</td><td><select name=new_faction>
							<option value=0>No Faction";
							$fact = @count_mysql_query("SELECT id,name FROM uo_chat_faction WHERE chat='$chatpath' ORDER BY name ASC", $handler);
							while($facl = @mysql_fetch_row($fact))
								echo "<option value=$facl[0]>$facl[1]";
							@mysql_free_result($fact);
						echo "</select>
						</td></tr>
						</table>
						<br>Userlevel: <select name=new_level>
						<option value=z>OOC Member
						<option value=1 SELECTED>Member";
						if (CheckFlags("XZmM", $userlevel)) {
							echo "<option value=x>Moderator";
							echo "<option value=X>Administrator";
						}
						if (CheckFlags("mM", $userlevel))
							echo "<option value=Z>Master";
						if (CheckFlags("M", $userlevel))
							echo "<option value=m>Chat Master";
						echo "</select>";
					}
				} else if ($adminaction == "sendmail") {
					$subject = $_REQUEST['subject'];
					$message = $_REQUEST['message'];
					$new_mail = $_REQUEST['new_mail'];
					if (($subject) && ($message) && ($new_email)) {
						mail($new_email, $subject, $message, "From: $master_email\nReply-To: $cadmin\nX-pJJ-IP: {$_SERVER['REMOTE_ADDR']}\nX-pJJ-Chat: https://pjj.cc/{$chatpath}/\nX-pJJ-Auth: {$_REQUEST['login']}\n");
						echo "Email sent to $new_email.";
					}
					else {
						echo "<input type=hidden name=adminaction value=$adminaction>
						<table BORDER=0 CELLSPACING=3 CELLPADDING=3>
						<tr><td>To:</td><td><input type=text name='new_email' value=''></td></tr>
						<tr><td>Subject:</td><td><input type=text name='subject' value='No Subject'></td></tr>
						<tr><td>

						Message body:
						</td><td>
						<br><textarea name='message' cols=60 rows=10></textarea>
						</td></tr>
						</table>";
					}
				} else if ($adminaction == "profile") {
					$p_data = $_REQUEST['p_data'];
					$p_delete = $_REQUEST['p_delete'];
					if (($p_data) || ($p_delete)) {
						if ((!stristr($p_data, "<html>")) && (!stristr($p_data, "<body>"))) {
							echo "Raw text detected. Enabling auto-HTML...<br>";
							$cnick = ucwords($login);
							$p_data = nl2br(htmlentities($p_data));
							$p_data = "<html><head><title>Profile for $cnick</title></head><body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' text='#E1E1C1' link='#F1F1F1' vlink='#F1F1F1' alink='#FFFFFF' bgcolor='#000000'>".$p_data."</body></html>";
						}
						if ($master_zlib == 0)
							$p_data = addslashes($p_data);
						else
							$p_data = addslashes(gzcompress($p_data, 9));

						if ($p_delete == "on") {
							@count_mysql_query("UPDATE uo_chat_database SET profile='' WHERE chat='$chatpath' AND username='$login' AND dtime IS NULL", $handler);
							echo "<p>Profile for $login was deleted.<p>";
						} else {
							@count_mysql_query("UPDATE uo_chat_database SET profile='$p_data' WHERE chat='$chatpath' AND username='$login' AND dtime IS NULL", $handler);
							echo "<p>Profile for $login has been updated.<p>";
						}
					} else {
						echo "<input type=hidden name=adminaction value=$adminaction>

						Profile data.
						<br>If you exclude the &lt;html&gt; tag	the system will convert it to text.<br>
						<u>Remember the &lt;html&gt; if you are using HTML!</u>
						<p><textarea name=p_data cols=100 rows=30>";

						$rez = @count_mysql_query("SELECT profile FROM uo_chat_database WHERE chat='$chatpath' AND username='$login' AND profile!='' AND dtime IS NULL", $handler);

						if ($prof = @mysql_fetch_row($rez)) {
							if ($prof[0][0] != 'x') {
								echo htmlentities(str_replace("</textarea", "&lt;/textarea", stripslashes($prof[0])));
							} else {
								echo htmlentities(str_replace("</textarea", "&lt;/textarea", stripslashes(gzuncompress($prof[0]))));
                            }
						} else {
							echo "";
						}
						@mysql_free_result($rez);

						echo "</textarea><p>Delete profile: <input type=checkbox name=p_delete>
						<br>If you don't want the selection menu, place the following script right before &lt;/head&gt;:<p>
						<blockquote>
						&lt;script language='JavaScript'&gt;
							if (window != window.top)
							top.location.href = location.href;
						&lt;/script&gt;
						</blockquote>";
					}
				} else if ($adminaction == "xprofile") {
					$p_data = $_REQUEST['p_data'];
					$p_delete = $_REQUEST['p_delete'];
					if (($p_data) || ($p_delete)) {
						if ((!stristr($p_data, "<html>")) && (!stristr($p_data, "<body>"))) {
							echo "Raw text detected. Enabling auto-HTML...<br>";
							$cnick = ucwords($login);
							$p_data = nl2br(htmlentities($p_data));
							$p_data = "<html><head><title>Profile for $cnick</title></head><body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' text='#E1E1C1' link='#F1F1F1' vlink='#F1F1F1' alink='#FFFFFF' bgcolor='#000000'>".$p_data."</body></html>";
						}
						if ($master_zlib == 0)
							$p_data = addslashes($p_data);
						else
							$p_data = addslashes(gzcompress($p_data, 9));

						if ($p_delete == "on") {
							@count_mysql_query("UPDATE uo_chat_database SET profile='' WHERE chat='$chatpath' AND username='$login' AND dtime IS NULL", $handler);
							echo "<p>Profile for $login was deleted.<p>";
						} else {
							@count_mysql_query("UPDATE uo_chat_database SET profile='$p_data' WHERE chat='$chatpath' AND username='$login' AND dtime IS NULL", $handler);
							echo "<p>Profile for $login has been updated.<p>";
						}
					} else {
						echo <<<PHPEND
<script type="text/javascript">
  _editor_lang = "en";
  _editor_url = "https://pjj.cc/common/htmlarea/";
</script>
<script type="text/javascript" src="https://pjj.cc/common/htmlarea/htmlarea.js"></script>

<style type="text/css">
textarea { background-color: #ffffff; border: 1px solid #000000; }
</style>

<script type="text/javascript">
// load the plugin files
HTMLArea.loadPlugin("FullPage");

var editor = null;
function initEditor() {
  // create an editor for the "ta" textbox
  editor = new HTMLArea("p_data");

  // register the TableOperations plugin with our editor
  editor.registerPlugin(FullPage);

  editor.generate();
  return false;
}

function insertHTML() {
  var html = prompt("Enter some HTML code here");
  if (html) {
    editor.insertHTML(html);
  }
}
function highlight() {
  editor.surroundHTML('<span style="background-color: yellow">', '</span>');
}
</script>
PHPEND;
						echo "<input type=hidden name=adminaction value=$adminaction>

						<p><textarea name='p_data' id='p_data' cols=100 rows=30 style='width: 100%;'>";

						$rez = @count_mysql_query("SELECT profile FROM uo_chat_database WHERE chat='$chatpath' AND username='$login' AND profile!='' AND dtime IS NULL", $handler);

						if ($prof = @mysql_fetch_row($rez)) {
							if ($prof[0][0] != 'x') {
								echo stripslashes($prof[0]);
							} else {
								echo stripslashes(gzuncompress($prof[0]));
                            }
						} else {
							echo "";
						}
						@mysql_free_result($rez);

						echo "</textarea><p>Delete profile: <input type=checkbox name=p_delete><br>";
						echo <<<PHPEND
<script type="text/javascript">
	initEditor();
</script>
<br>
<input type="button" name="ins" value="  insert html  " onclick="return insertHTML();" />
<input type="button" name="hil" value="  highlight text  " onclick="return highlight();" />
PHPEND;
					}
				}
				else if ($adminaction == "manageicons") {
					$selecteduser = $_REQUEST['selecteduser'];
					if (($selecteduser) && CheckFlags("IXZmM", $userlevel)) {
						$iconedit = $_REQUEST['iconedit'];
						if ($iconedit) {
							$name = $_REQUEST['name'];
							$file = $_REQUEST['file'];

							$write_me = array();
							for ($cc=0;$cc<count($name);$cc++) {
								if (!empty($name[$cc]))
									$write_me[$cc] = "$name[$cc]¥$file[$cc]\n";
							}
							array_unique($write_me);
							sort($write_me);

							$write_me = mysql_escape_string(implode('', $write_me));
							echo "$write_me<br>";
							count_mysql_query("UPDATE uo_chat_database SET icon='$write_me' WHERE chat='$chatpath' AND username='$selecteduser' AND dtime IS NULL", $handler);

							echo "<p>Icons updated.<br>\n";
						}
						else {
							unset($iconedit);
							EnumerateIcons($selecteduser, $chatpath);
							echo "<input type=hidden name='adminaction' value='$adminaction'>";
							echo "<input type=hidden name='selecteduser' value='$selecteduser'>";
						}
					}
					else {
						echo "<input type=hidden name=adminaction value=$adminaction>";
						ListIconsModify($chatpath);
					}
				}
				else if ($adminaction == "alterflags") {
					$selecteduser = $_REQUEST['selecteduser'];
					if (($selecteduser) && CheckFlags("fZmM", $userlevel)) {
						$nflag = $_REQUEST['nflag'];
						if ($nflag) {
							$vflag = "";
							if ($fl_mm == "on")
								$vflag .= "M";
							if ($fl_m == "on")
								$vflag .= "m";
							if ($fl_zz == "on")
								$vflag .= "Z";

							if ($fl_xx == "on")
								$vflag .= "X";
							if ($fl_x == "on")
								$vflag .= "x";
							if ($fl_z == "on")
								$vflag .= "z";

							if ($fl_pp == "on")
								$vflag .= "P";
							if ($fl_aa == "on")
								$vflag .= "A";
							if ($fl_dd == "on")
								$vflag .= "D";

							if ($fl_rr == "on")
								$vflag .= "R";
							if ($fl_p == "on")
								$vflag .= "p";
							if ($fl_ff == "on")
								$vflag .= "F";

							if ($fl_f == "on")
								$vflag .= "f";
							if ($fl_s == "on")
								$vflag .= "s";
							if ($fl_o == "on")
								$vflag .= "o";

							if ($fl_i == "on")
								$vflag .= "i";
							if ($fl_ii == "on")
								$vflag .= "I";
							if ($fl_oo == "on")
								$vflag .= "O";

							if ($fl_cc == "on")
								$vflag .= "C";
							if ($fl_bb == "on")
								$vflag .= "B";
							if ($fl_b == "on")
								$vflag .= "b";

							if ($fl_r == "on")
								$vflag .= "r";
							if ($fl_a == "on")
								$vflag .= "a";
							if ($fl_l == "on")
								$vflag .= "l";

							if ($fl_vv == "on")
								$vflag .= "V";
							if ($fl_ll == "on")
								$vflag .= "L";

							if (CheckFlags("M", $vflag))
								$vflag = "M";
							if (CheckFlags("m", $vflag))
								$vflag = "m";
							if (CheckFlags("Z", $vflag))
								$vflag = "Z";
							if (CheckFlags("X", $vflag))
								$vflag = ereg_replace("[ADRpFsoiIOCBbra]", "", $vflag);

							ChangeUser($login, $password, $selecteduser, $vflag, $chatpath);
						} else {
							$flags = GetFlags($selecteduser, $chatpath);
							echo "<table cellspacing=0 cellpadding=2 border=0>\n";
							echo "<tr><td colspan=3 bgcolor=#FFFFFF></td></tr>";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_mm ".(CheckFlags("M", $flags) ? ("CHECKED") : (""))."> System Master</td>\n";
								echo "<td><input type=checkbox name=fl_m ".(CheckFlags("m", $flags) ? ("CHECKED") : (""))."> Chat Master</td>\n";
								echo "<td><input type=checkbox name=fl_zz ".(CheckFlags("Z", $flags) ? ("CHECKED") : (""))."> Master</td>\n";
							echo "</tr>\n";
							echo "<tr><td colspan=3 bgcolor=#FFFFFF></td></tr>";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_xx ".(CheckFlags("X", $flags) ? ("CHECKED") : (""))."> Administrator</td>\n";
								echo "<td><input type=checkbox name=fl_x ".(CheckFlags("x", $flags) ? ("CHECKED") : (""))."> Moderator</td>\n";
								echo "<td><input type=checkbox name=fl_z ".(CheckFlags("z", $flags) ? ("CHECKED") : (""))."> OOC</td>\n";
							echo "</tr>\n";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_pp ".(CheckFlags("P", $flags) ? ("CHECKED") : (""))."> Protected</td>\n";
								echo "<td><input type=checkbox name=fl_aa ".(CheckFlags("A", $flags) ? ("CHECKED") : (""))."> Add users</td>\n";
								echo "<td><input type=checkbox name=fl_dd ".(CheckFlags("D", $flags) ? ("CHECKED") : (""))."> Delete users</td>\n";
							echo "</tr>\n";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_rr ".(CheckFlags("R", $flags) ? ("CHECKED") : (""))."> Rename users</td>\n";
								echo "<td><input type=checkbox name=fl_p ".(CheckFlags("p", $flags) ? ("CHECKED") : (""))."> Reset passwords</td>\n";
								echo "<td><input type=checkbox name=fl_ff ".(CheckFlags("F", $flags) ? ("CHECKED") : (""))."> Change factions</td>\n";
							echo "</tr>\n";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_f ".(CheckFlags("f", $flags) ? ("CHECKED") : (""))."> Alter flags</td>\n";
								echo "<td><input type=checkbox name=fl_s ".(CheckFlags("s", $flags) ? ("CHECKED") : (""))."> Edit Settings</td>\n";
								echo "<td><input type=checkbox name=fl_o ".(CheckFlags("o", $flags) ? ("CHECKED") : (""))."> Edit Options</td>\n";
							echo "</tr>\n";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_i ".(CheckFlags("i", $flags) ? ("CHECKED") : (""))."> Edit Icons</td>\n";
								echo "<td><input type=checkbox name=fl_ii ".(CheckFlags("I", $flags) ? ("CHECKED") : (""))."> Edit personal icons</td>\n";
								echo "<td><input type=checkbox name=fl_oo ".(CheckFlags("O", $flags) ? ("CHECKED") : (""))."> Edit MotD</td>\n";
							echo "</tr>\n";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_cc ".(CheckFlags("C", $flags) ? ("CHECKED") : (""))."> /clear</td>\n";
								echo "<td><input type=checkbox name=fl_bb ".(CheckFlags("B", $flags) ? ("CHECKED") : (""))."> /ban & /unban</td>\n";
								echo "<td><input type=checkbox name=fl_b ".(CheckFlags("b", $flags) ? ("CHECKED") : (""))."> /muban</td>\n";
							echo "</tr>\n";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_r ".(CheckFlags("r", $flags) ? ("CHECKED") : (""))."> /rem</td>\n";
								echo "<td><input type=checkbox name=fl_a ".(CheckFlags("a", $flags) ? ("CHECKED") : (""))."> /raw</td>\n";
								echo "<td><input type=checkbox name=fl_l ".(CheckFlags("l", $flags) ? ("CHECKED") : (""))."> No JOIN or EXIT</td>\n";
							echo "</tr>\n";
							echo "<tr>";
								echo "<td><input type=checkbox name=fl_vv ".(CheckFlags("V", $flags) ? ("CHECKED") : (""))."> Edit Poll</td>\n";
								echo "<td><input type=checkbox name=fl_ll ".(CheckFlags("L", $flags) ? ("CHECKED") : (""))."> Faction Leader</td>\n";
								echo "<td></td>\n";
							echo "</tr>\n";
							echo "</table><input type=hidden name=nflag value=x>";
							echo "<input type=hidden name='adminaction' value='$adminaction'>";
							echo "<input type=hidden name='selecteduser' value='$selecteduser'>";
						}
					}
					else {
						echo "<input type=hidden name=adminaction value=$adminaction>";
						ListIconsModify($chatpath);
					}
				}
				else if ($adminaction == "manageusers") {
					$modaction = $_REQUEST['modaction'];
					$selecteduser = $_REQUEST['selecteduser'];

					if (($modaction) && ($selecteduser) && CheckFlags("pDRFXZmM", $userlevel)) {
						$newname = $_REQUEST['newname'];
						/*
						if ($modaction == "resetpass") {
							ResetPass($selecteduser, $login, $password, $chatpath);
						}
						else
						//*/
						if ($modaction == "delete") {
							DeleteUser($login, $password, $selecteduser, $chatpath);
						}
						else if ($modaction == "rename") {
							RenameUser($selecteduser, $login, $password, $newname, $chatpath);
						}
						else if ($modaction == "faction") {
							ChangeFaction($selecteduser, $login, $password, $newname, $chatpath);
						}
					}
					else {
						echo "<input type=hidden name=adminaction value=$adminaction>";
						ListUsersModify($userlevel, $chatpath);
						ListFactions($chatpath);
					}
				}
				else if ($adminaction == "delself") {
					if (strcmp($_REQUEST['sure'], 'yes') == 0) {
						DeleteSelf($login, $password, $chatpath);
						echo "<input type='hidden' name='adminaction' value='logout'>";
					}
					else {
						echo "<p>Clicking the button below will delete yourself...</p>";
						echo "<input type='hidden' name='sure' value='yes'>";
						echo "<input type='hidden' name='adminaction' value='delself'>";
					}
				}
/*
				else if ($adminaction == "bulkmanageusers") {
					if (($modaction) && ($selecteduser) && CheckFlags("pDRFXZmM", $userlevel)) {
						if ($modaction == "bulkresetpass")
							BulkResetPass($selecteduser, $login, $password, $chatpath);
						else if ($modaction == "bulkdelete")
							BulkDeleteUser($login, $password, $selecteduser, $chatpath);
						else if ($modaction == "bulkfaction")
							BulkChangeFaction($selecteduser, $login, $password, $newname, $chatpath);
					}
					else {
						echo "<input type=hidden name=adminaction value=$adminaction>";
						BulkListUsersModify($userlevel, $chatpath);
					}
				}
*/
				else if (($adminaction == "faction") && CheckFlags("LZmM", $userlevel)) {
					$fedit = $_REQUEST['fedit'];
					if (!$fedit && !CheckFlags("ZmM", $userlevel))
						$fedit = GetFaction($login, $chatpath);

					if ($fedit) {
						$ficon = $_REQUEST['ficon'];
						if ($ficon) {
							AlterFaction($fedit, $ficon);
						} else {
							$fact = GetFactionDetails($chatpath, $fedit);
							echo "Editing ".ucwords($fact[2])."<br>\nFaction icon: <input type=text name=ficon value=\"$fact[3]\">";
							echo "<input type=hidden name=adminaction value=$adminaction><input type=hidden name=fedit value=\"$fedit\">";
						}
					} else {
						echo "Edit faction <select name=fedit>";
							$fact = @count_mysql_query("SELECT id,name FROM uo_chat_faction WHERE chat='$chatpath' ORDER BY name ASC", $handler);
							while($facl = @mysql_fetch_row($fact))
								echo "<option value=$facl[0]>$facl[1]";
							@mysql_free_result($fact);
						echo "</select><p>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				} else if (($adminaction == "addfaction") && CheckFlags("FAZmM", $userlevel)) {
					$newf = $_REQUEST['newf'];
					if ($newf) {
						AddFaction($chatpath, $newf);
					} else {
						echo "Create new faction with name: <input type=text name=newf><p>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				} else if (($adminaction == "renfaction") && CheckFlags("FAZmM", $userlevel)) {
					$newf = $_REQUEST['newf'];
					$old_id = $_REQUEST['old_id'];
					if ($newf) {
						RenameFaction($chatpath, $old_id, $newf);
					} else {
						echo "Rename faction <select name=old_id>";
							$fact = @count_mysql_query("SELECT id,name FROM uo_chat_faction WHERE chat='$chatpath' ORDER BY name ASC", $handler);
							while($facl = @mysql_fetch_row($fact))
								echo "<option value=$facl[0]>$facl[1]";
							@mysql_free_result($fact);
						echo "</select>";
						echo " to <input type=text name=newf><p>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				} else if (($adminaction == "delfaction") && CheckFlags("FAZmM", $userlevel)) {
					$old_id = $_REQUEST['old_id'];
					if ($old_id) {
						DeleteFaction($chatpath, $old_id);
					} else {
						echo "Delete faction <select name=old_id>";
							$fact = @count_mysql_query("SELECT id,name FROM uo_chat_faction WHERE chat='$chatpath' ORDER BY name ASC", $handler);
							while($facl = @mysql_fetch_row($fact))
								echo "<option value=$facl[0]>$facl[1]";
							@mysql_free_result($fact);
						echo "</select><p>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				} else if (($adminaction == "poll") && CheckFlags("VZmM", $userlevel)) {
					$result = @count_mysql_query("SELECT chat,topic,nselect,ta,ca,tb,cb,tc,cc,td,cd,te,ce FROM uo_chat_poll WHERE chat='$chatpath'", $handler);
					$poll = mysql_fetch_row($result);
					@mysql_free_result($poll);

					$ptopic = $_REQUEST['ptopic'];
					$npoll = $_REQUEST['npoll'];
					$newpoll = $_REQUEST['newpoll'];
					$popt = $_REQUEST['popt'];
					$pcol = $_REQUEST['pcol'];

					if ($ptopic) {
						$npoll = round($npoll);
						if ($npoll < 2)
							$npoll = 2;
						if ($npoll > 5)
							$npoll = 5;

						if ($newpoll == "on") {
							@count_mysql_query("DELETE FROM uo_chat_vote WHERE chat='$chatpath'", $handler);
							echo "<br>Poll results cleared.<br>";
						}
						$ptopic = str_replace("\"","?",$ptopic);
						$ptopic = str_replace("'","?",$ptopic);
						$result = @count_mysql_query("DELETE FROM uo_chat_poll WHERE chat='$chatpath'", $handler);
						for ($cc=0;$cc<5;$cc++) {
							$popt[$cc] = str_replace("'","?",$popt[$cc]);
						}
						@count_mysql_query("INSERT INTO uo_chat_poll (chat,topic,nselect,ta,ca,tb,cb,tc,cc,td,cd,te,ce) VALUES ('$chatpath','$ptopic','$npoll','$popt[0]','$pcol[0]','$popt[1]','$pcol[1]','$popt[2]','$pcol[2]','$popt[3]','$pcol[3]','$popt[4]','$pcol[4]')", $handler);

					} else {
						echo "Topic: <input type=text name=ptopic value=\"$poll[1]\" size=40><br>";
						echo "Options: <input type=text size=2 name=npoll value=$poll[2]><p>";
						echo "<table border=0 cellspacing=0 cellpadding=2>";
						echo "<tr><td>#</td><td>Option</td><td>Color</td></tr>";
						echo "<tr><td>1</td><td><input type=text name=popt[0] size=30 value=\"$poll[3]\"></td><td><input type=text size=6 name=pcol[0] value=$poll[4]></td></tr>";
						echo "<tr><td>2</td><td><input type=text name=popt[1] size=30 value=\"$poll[5]\"></td><td><input type=text size=6 name=pcol[1] value=$poll[6]></td></tr>";
						echo "<tr><td>3</td><td><input type=text name=popt[2] size=30 value=\"$poll[7]\"></td><td><input type=text size=6 name=pcol[2] value=$poll[8]></td></tr>";
						echo "<tr><td>4</td><td><input type=text name=popt[3] size=30 value=\"$poll[9]\"></td><td><input type=text size=6 name=pcol[3] value=$poll[10]></td></tr>";
						echo "<tr><td>5</td><td><input type=text name=popt[4] size=30 value=\"$poll[11]\"></td><td><input type=text size=6 name=pcol[4] value=$poll[12]></td></tr>";
						echo "</table>";
						echo "<input type=checkbox name=newpoll> Clear poll results?<p>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				} else if ($adminaction == "xmotd") {
					$motd = trim($_REQUEST['motd']);

					if (!empty($motd)) {
						file_put_contents("motd.dat", $motd);
						@chmod("motd.dat", 0600);
					}
					else {
						echo <<<PHPEND
<script type="text/javascript">
  _editor_lang = "en";
  _editor_url = "https://pjj.cc/common/htmlarea/";
</script>
<script type="text/javascript" src="https://pjj.cc/common/htmlarea/htmlarea.js"></script>

<style type="text/css">
textarea { background-color: #ffffff; border: 1px solid #000000; }
</style>

<script type="text/javascript">
// load the plugin files
HTMLArea.loadPlugin("TableOperations");

var editor = null;
function initEditor() {
  // create an editor for the "ta" textbox
  editor = new HTMLArea("motd");

  // register the TableOperations plugin with our editor
  editor.registerPlugin(TableOperations);

  editor.generate();
  return false;
}

function insertHTML() {
  var html = prompt("Enter some HTML code here");
  if (html) {
    editor.insertHTML(html);
  }
}
function highlight() {
  editor.surroundHTML('<span style="background-color: yellow">', '</span>');
}
</script>
PHPEND;
						echo "<textarea id='motd' name='motd' cols=100 rows=30 style='width: 100%;'>";
						if (file_exists("motd.dat")) {
							$motd = file_get_contents("motd.dat");
							echo htmlentities($motd);
						}
						echo "</textarea>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
						echo <<<PHPEND
<script type="text/javascript">
	initEditor();
</script>
<br>
<input type="button" name="ins" value="  insert html  " onclick="return insertHTML();" />
<input type="button" name="hil" value="  highlight text  " onclick="return highlight();" />
PHPEND;
					}
				}
				else if ($adminaction == "mmotd") {
					$motd = trim($_REQUEST['motd']);

					if (!empty($motd)) {
						file_put_contents("motd.dat", $motd);
						/*
						$tidy_config = array('clean' => false, 'output-html' => true, 'show-body-only' => true,
							'wrap' => 0, 'hide-endtags' => false);
						$tidy = tidy_parse_string($motd, $tidy_config, 'utf8');
						$tidy->cleanRepair();
						$motd = ''.$tidy.'';
						//*/
						@chmod("motd.dat", 0600);
					}
					else {
						echo "<textarea name='motd' cols=100 rows=30>";
						if (file_exists("motd.dat")) {
							$motd = file_get_contents("motd.dat");
							echo htmlentities($motd);
						}
						echo "</textarea>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				}
				else if ($adminaction == "getip") {
                    require_once('../../common/proxy.php');
					$iname = $_REQUEST['iname'];
					$known = array();

					$result = @count_mysql_query("SELECT DISTINCT username,ip,ident,proxyip FROM uo_chat_log WHERE chat='$realpath' ORDER BY username ASC", $handler);
					echo "- If you want to ban someone from this, you might want to check the DNS to see what you are banning.<br>If it, for example, has aol.com in it then it's probably not the best idea to ban that mask.";
					echo "<br>- Proxied IP will show underneath the connecting IP for those who use a proxy that sends the optional X-Forwarded-For header. The Proxied IP is then that user's real IP, behind the proxy. Either can be the target of a ban.";
					echo "<br>- Click an IP to get the assignment information for it.";
					echo "<p><b>Chatters:</b><br>";
					echo "<table cellspacing=1 cellpadding=3 border=0 bgcolor=#000000>";
					echo "<tr bgcolor=#eeeeee><td><b>Name</b></td><td><b>IPs</b></td><td><b>Ident</b></td><td><b>Hostnames</b></td></tr>";
					while ($uun = mysql_fetch_assoc($result)) {
                        $proxy = '';
                        $isproxy = Proxy_IsProxy($uun['ip']);
                        if ($isproxy !== false) {
                            $proxy = '<br>Possible proxy, see <a href="'.$isproxy.'">'.$isproxy.'</a>';
                        }
						echo "<tr bgcolor=#ffffff><td>{$uun['username']}</td>";
						echo "<td><a href='http://tools.projectjj.com/ipwhois.php?ip={$uun['ip']}'>{$uun['ip']}</a><div><a href='http://tools.projectjj.com/ipwhois.php?ip={$uun['proxyip']}'><i>{$uun['proxyip']}</i></a></div></td>";
						echo "<td>{$uun['ident']}</td>";
						echo "<td>".gethostbyaddr($uun['ip'])."<div><i>".(@gethostbyaddr($uun['proxyip']))."</i></div>$proxy</td></tr>";
						$known[$uun['ip']] = $uun['username'];
						$known[$uun['proxyip']] = $uun['username'];
					}
					echo "</table>";
					@mysql_free_result($result);

					$result = @count_mysql_query("SELECT DISTINCT INET_NTOA(ip) as ip,INET_NTOA(proxyip) as pip, user_agent as ua FROM uo_chat WHERE chat='$realpath' ORDER BY ip ASC", $handler);
					echo "<p><b>Lurkers, Viewers, Everybody Else:</b><br>";
					echo "<table cellspacing=1 cellpadding=3 border=0 bgcolor=#000000>";
					echo "<tr bgcolor=#eeeeee><td>#</td><td><b>Chatter</b></td><td><b>IPs</b></td><td><b>Hostnames</b><td><b>User Agent</b></td></tr>";
					for ($i=1 ; $uun = mysql_fetch_assoc($result) ; $i++) {
                        $proxy = '';
                        $isproxy = Proxy_IsProxy($uun['ip']);
                        if ($isproxy !== false) {
                            $proxy = '<br>Possible proxy, see <a href="'.$isproxy.'">'.$isproxy.'</a>';
                        }
						echo "<tr bgcolor=#ffffff><td>{$i}</td><td>";
						if (!empty($known[$uun['ip']])) {
							echo $known[$uun['ip']];
						}
						echo "</td><td><a href='http://tools.projectjj.com/ipwhois.php?ip={$uun['ip']}'>{$uun['ip']}</a><div><a href='http://tools.projectjj.com/ipwhois.php?ip={$uun['pip']}'><i>{$uun['pip']}</i></a></div></td>";
						echo "<td>".(@gethostbyaddr($uun['ip']))."<div><i>".(@gethostbyaddr($uun['pip']))."</i></div>$proxy</td><td>".htmlentities($uun['ua'])."</td></tr>";
					}
					echo "</table>";
					@mysql_free_result($result);
				}
				else if ($adminaction == "chain") {
					$xchan = $_REQUEST['xchan'];
					$chain = $_REQUEST['chain'];

					if ($xchan) {
						$row = mysql_escape_string(implode("\n", $chain));
						$result = count_mysql_query("UPDATE uo_chat_database SET chain='$row' WHERE chat='$chatpath' AND username='$login' AND dtime IS NULL", $handler);
					}
					else {
						$result = count_mysql_query("SELECT chain FROM uo_chat_database WHERE chat='$chatpath' AND username='$login' AND dtime IS NULL", $handler);
						$row = @mysql_fetch_row($result);
						@mysql_free_result($result);

						$row[0] = trim($row[0]);
						$chain = array_unique(explode("\n", $row[0]));
						sort($chain);

						echo "<table cellspacing=0 cellpadding=2 border=0>";
						echo "<tr><td>#</td><td>Username</td></tr>";
						for ($cc=0;$cc<count($chain);$cc++) {
							echo "<tr><td>$cc</td><td><input type=text size=24 name=chain[$cc] value=\"$chain[$cc]\"></td></tr>";
						}
						for ($aa=$cc;$aa<$cc+5;$aa++) {
							echo "<tr><td>$aa</td><td><input type=text size=24 name=chain[$aa] value=\"\"></td></tr>";
						}
						echo "</table><input type=hidden name=xchan value=wombat>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				}
				else if ($adminaction == "prefs") {
					$npref = $_REQUEST['npref'];

					if ($npref) {
						$vpref = "";
						if ($pf_advan == "on")
							$vpref .= "A";
						if ($pf_simple == "on")
							$vpref .= "S";
						if ($pf_motd == "on")
							$vpref .= "O";

						if ($pf_bold == "on")
							$vpref .= "b";
						if ($pf_uline == "on")
							$vpref .= "u";
						if ($pf_ital == "on")
							$vpref .= "i";

						if ($pf_tt == "on")
							$vpref .= "t";
						if ($pf_rev == "on")
							$vpref .= "R";
						if ($pf_mlb == "on")
							$vpref .= "m";

						if ($pfc_nn == "on")
							$vpref .= "N";
						if ($pfc_cc == "on")
							$vpref .= "C";
						if ($pfc_tt == "on")
							$vpref .= "T";

						if ($pfc_c == "on")
							$vpref .= "c";
						if ($pfc_ll == "on")
							$vpref .= "L";
						if ($pfc_xx == "on")
							$vpref .= "X";

						if ($pfc_mm == "on")
							$vpref .= "M";
						/*
						if ($pfc_qq == "on")
							$vpref .= "Q";
						if ($pfc_a == "on")
							$vpref .= "a";

						if ($pfc_s == "on")
							$vpref .= "s";
						if ($pfc_y == "on")
							$vpref .= "y";
						if ($pfc_l == "on")
							$vpref .= "l";
						//*/
						ChangePrefs($login, $password, $vpref, $pmail, $chatpath, $icquin, $aimsn, $ym, $msn, $site, $skype, $lastfm, $flickr, $_REQUEST['displayname'], $_REQUEST['facebook'], $_REQUEST['gplus'], $_REQUEST['steam']);
					} else {
						$prefs = GetPrefs($login, $chatpath);
						foreach ($prefs as $k => $v) {
							$prefs[$k] = htmlentities($v);
						}
						echo "Preferences<table cellspacing=0 cellpadding=2 border=0>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_advan ".(CheckFlags("A", $prefs['prefs']) ? ("CHECKED") : (""))."> Advanced</td>\n";
							echo "<td><input type=checkbox name=pf_simple ".(CheckFlags("S", $prefs['prefs']) ? ("CHECKED") : (""))."> Simple</td>\n";
							echo "<td><input type=checkbox name=pf_motd ".(CheckFlags("O", $prefs['prefs']) ? ("CHECKED") : (""))."> No MotD</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_bold ".(CheckFlags("b", $prefs['prefs']) ? ("CHECKED") : (""))."> Bold</td>\n";
							echo "<td><input type=checkbox name=pf_uline ".(CheckFlags("u", $prefs['prefs']) ? ("CHECKED") : (""))."> Underline</td>\n";
							echo "<td><input type=checkbox name=pf_ital ".(CheckFlags("i", $prefs['prefs']) ? ("CHECKED") : (""))."> Italic</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_tt ".(CheckFlags("t", $prefs['prefs']) ? ("CHECKED") : (""))."> Alternate</td>\n";
							echo "<td><input type=checkbox name=pf_rev ".(CheckFlags("R", $prefs['prefs']) ? ("CHECKED") : (""))."> Reverse Output</td>\n";
							echo "<td><input type=checkbox name=pf_mlb ".(CheckFlags("m", $prefs['prefs']) ? ("CHECKED") : (""))."> Multi-Line Box</td>\n";
						echo "</tr>\n";
						echo "</table>";
						echo "<br><table cellspacing=1 cellpadding=2 border=0 bgcolor=#000000>";
						echo "<tr bgcolor=#eeeeee><td colspan=3><b>Contact Options</b></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Display Name</td><td><input type=text size=32 name=displayname value=\"{$prefs['displayname']}\"></td><td><i>How you want your name formatted.</i></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Email</td><td><input type=text size=32 name=pmail value=\"{$prefs['email']}\"></td><td></td></tr>";
						echo "<tr bgcolor=#ffffff><td>AIM</td><td><input type=text size=16 name=aimsn value=\"{$prefs['aim']}\"></td><td></td></tr>";
						echo "<tr bgcolor=#ffffff><td>ICQ</td><td><input type=text size=10 name=icquin value=\"{$prefs['icq']}\"></td><td></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Yahoo!</td><td><input type=text size=16 name=ym value=\"{$prefs['ym']}\"></td><td></td></tr>";
						echo "<tr bgcolor=#ffffff><td>MSN</td><td><input type=text size=32 name=msn value=\"{$prefs['msn']}\"></td><td><i>Complete login, for example tino@didriksen.cc.</i></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Skype</td><td><input type=text size=16 name=skype value=\"{$prefs['skype']}\"></td><td></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Last.fm</td><td><input type=text size=16 name=lastfm value=\"{$prefs['lastfm']}\"></td><td><i>Only the username, for example Jezral.</i></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Flickr</td><td><input type=text size=16 name=flickr value=\"{$prefs['flickr']}\"></td><td><i>Only the account identifier, not the full URL.</i></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Facebook</td><td><input type=text size=16 name=facebook value=\"{$prefs['facebook']}\"></td><td><i>Only the account identifier, for example tinodidriksen.</i></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Google Plus</td><td><input type=text size=16 name=gplus value=\"{$prefs['gplus']}\"></td><td><i>Only the account identifier, for example 111239563331808808678.</i></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Steam</td><td><input type=text size=16 name=steam value=\"{$prefs['steam']}\"></td><td><i>Only the account identifier, for example tinodidriksen.</i></td></tr>";
						echo "<tr bgcolor=#ffffff><td>Site</td><td><input type=text size=32 name=site value=\"{$prefs['site']}\"></td><td></td></tr>";
						echo "</table><br>";
						echo "Console Setup<table cellspacing=0 cellpadding=2 border=0>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pfc_nn ".(CheckFlags("N", $prefs['prefs']) ? ("CHECKED") : (""))."> No Name</td>\n";
							echo "<td><input type=checkbox name=pfc_cc ".(CheckFlags("C", $prefs['prefs']) ? ("CHECKED") : (""))."> No Chain/Refresh</td>\n";
							echo "<td><input type=checkbox name=pfc_tt ".(CheckFlags("T", $prefs['prefs']) ? ("CHECKED") : (""))."> No Text Styles</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pfc_c ".(CheckFlags("c", $prefs['prefs']) ? ("CHECKED") : (""))."> No Icon Changer</td>\n";
							echo "<td><input type=checkbox name=pfc_ll ".(CheckFlags("L", $prefs['prefs']) ? ("CHECKED") : (""))."> No Color</td>\n";
							echo "<td><input type=checkbox name=pfc_xx ".(CheckFlags("X", $prefs['prefs']) ? ("CHECKED") : (""))."> No Buttons</td>\n";
						echo "</tr>\n";
						echo "</table>\n";
						echo "Privacy Options<table cellspacing=0 cellpadding=2 border=0>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pfc_mm ".(CheckFlags("M", $prefs['prefs']) ? ("CHECKED") : (""))."> Public Email</td>\n";
						echo "</tr>\n";
						/*
							echo "<td><input type=checkbox name=pfc_qq ".(CheckFlags("Q", $prefs['prefs']) ? ("CHECKED") : (""))."> Public ICQ</td>\n";
							echo "<td><input type=checkbox name=pfc_a ".(CheckFlags("a", $prefs['prefs']) ? ("CHECKED") : (""))."> Public AIM</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pfc_s ".(CheckFlags("s", $prefs['prefs']) ? ("CHECKED") : (""))."> Public MSN</td>\n";
							echo "<td><input type=checkbox name=pfc_y ".(CheckFlags("y", $prefs['prefs']) ? ("CHECKED") : (""))."> Public Yahoo!</td>\n";
							echo "<td><input type=checkbox name=pfc_l ".(CheckFlags("l", $prefs['prefs']) ? ("CHECKED") : (""))."> Public Last.fm</td>\n";
						echo "</tr>\n";
						//*/
						echo "</table><input type=hidden name=npref value=x>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				} else if ($adminaction == "chatpref") {
					if ($npref) {
						$vpref = "";
						if ($pf_ii == "on")
							$vpref .= "I";
						if ($pf_bb == "on")
							$vpref .= "B";
						if ($pf_jj == "on")
							$vpref .= "J";

						if ($pf_ww == "on")
							$vpref .= "W";
						if ($pf_uu == "on")
							$vpref .= "U";
						if ($pf_pp == "on")
							$vpref .= "P";

						if ($pf_ff == "on")
							$vpref .= "F";
						if ($pf_cc == "on")
							$vpref .= "C";
						if ($pf_i == "on")
							$vpref .= "i";

						if ($pf_ll == "on")
							$vpref .= "L";
						if ($pf_mm == "on")
							$vpref .= "M";
						if ($pf_dd == "on")
							$vpref .= "N";

						if ($pf_c == "on")
							$vpref .= "c";
						if ($pf_n == "on")
							$vpref .= "n";
						if ($pf_m == "on")
							$vpref .= "m";

						if ($pf_rr == "on")
							$vpref .= "R";
						if ($pf_l == "on")
							$vpref .= "l";
						if ($pf_s == "on")
							$vpref .= "s";
						ChangeChatPrefs($login, $password, $vpref, $chatpath);
					} else {
						$prefs = GetChatPrefs($chatpath);
						echo "Chat Preferences<table cellspacing=0 cellpadding=2 border=0>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_ii ".(CheckFlags("I", $prefs['prefs']) ? ("CHECKED") : (""))."> No Ignore</td>\n";
							echo "<td><input type=checkbox name=pf_bb ".(CheckFlags("B", $prefs['prefs']) ? ("CHECKED") : (""))."> No Banning</td>\n";
							echo "<td><input type=checkbox name=pf_jj ".(CheckFlags("J", $prefs['prefs']) ? ("CHECKED") : (""))."> No Join/Exit</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_ww ".(CheckFlags("W", $prefs['prefs']) ? ("CHECKED") : (""))."> No Whois</td>\n";
							echo "<td><input type=checkbox name=pf_uu ".(CheckFlags("U", $prefs['prefs']) ? ("CHECKED") : (""))."> No Undo</td>\n";
							echo "<td><input type=checkbox name=pf_pp ".(CheckFlags("P", $prefs['prefs']) ? ("CHECKED") : (""))."> No Private Messages</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_ff ".(CheckFlags("F", $prefs['prefs']) ? ("CHECKED") : (""))."> No Filter</td>\n";
							echo "<td><input type=checkbox name=pf_cc ".(CheckFlags("C", $prefs['prefs']) ? ("CHECKED") : (""))."> No Icons</td>\n";
							echo "<td><input type=checkbox name=pf_i ".(CheckFlags("i", $prefs['prefs']) ? ("CHECKED") : (""))."> No Image</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_ll ".(CheckFlags("L", $prefs['prefs']) ? ("CHECKED") : (""))."> No Link</td>\n";
							echo "<td><input type=checkbox name=pf_mm ".(CheckFlags("M", $prefs['prefs']) ? ("CHECKED") : (""))."> No MotD</td>\n";
							echo "<td><input type=checkbox name=pf_dd ".(CheckFlags("N", $prefs['prefs']) ? ("CHECKED") : (""))."> Unique Avatar Usernames</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_c ".(CheckFlags("c", $prefs['prefs']) ? ("CHECKED") : (""))."> No Multi-Color</td>\n";
							echo "<td><input type=checkbox name=pf_n ".(CheckFlags("n", $prefs['prefs']) ? ("CHECKED") : (""))."> Registered Images</td>\n";
							echo "<td><input type=checkbox name=pf_m ".(CheckFlags("m", $prefs['prefs']) ? ("CHECKED") : (""))."> No Userlist IMs</td>\n";
						echo "</tr>\n";
						echo "<tr>";
							echo "<td><input type=checkbox name=pf_rr ".(CheckFlags("R", $prefs['prefs']) ? ("CHECKED") : (""))."> Reversed Output</td>\n";
							echo "<td><input type=checkbox name=pf_l ".(CheckFlags("l", $prefs['prefs']) ? ("CHECKED") : (""))."> Multi-Line Box</td>\n";
							echo "<td><input type=checkbox name=pf_s ".(CheckFlags("s", $prefs['prefs']) ? ("CHECKED") : (""))."> Force Single-Line Box</td>\n";
						echo "</tr>\n";
						echo "</table><input type=hidden name=npref value=x>";
						echo "<input type=hidden name=adminaction value=$adminaction>";
					}
				} elseif (strcasecmp($adminaction, "regapps") == 0) {
					$arr = GetFactionNames($chatpath);

					if ($ruid) {
						$reguser = mysql_fetch_row(count_mysql_query("SELECT id,username,email,faction,description,appstat FROM uo_chat_regapps WHERE id=".intval($ruid)." AND chat='{$chatpath}'", $handler));
						$rfaction = $arr[$reguser[3]];

						echo "
						<table>
							<tr>
								<input type='hidden' name='adminaction' value='regapps'>
								<td>Name: </td><td>{$reguser[1]}</td>
							</tr>
							<tr>
								<td>E-Mail: </td><td>{$reguser[2]}</td>
							</tr>
							<tr>
								<td>Faction: </td><td>{$rfaction}</td>
							</tr>
							<tr>
								<td valign='top'>Description: </td><td>{$reguser[4]}</td>
							</tr>";
						if (($reguser[5] == 0) || ($reguser[5] == 2)) {
							echo"
								<tr>
									<td><a href='login.php?adminaction=adduser&ruid={$reguser[0]}&login={$login}&password={$password}&new_name={$reguser[1]}&new_pass=random&new_email={$reguser[2]}&new_level=1&new_faction={$reguser[3]}&ruid={$ruid}'>Accept</a></td>
									<td><a href='login.php?adminaction=regapps&decline={$reguser[0]}&login={$login}&password={$password}&runame={$reguser[1]}&rmail={$reguser[2]}'>Decline</a></td>
								</tr>";
						}

						echo "</table>";
					} elseif (!empty($admcomed)) {
						// Admin notes
						$admcom = $GLOBALS['sql']->fetchAssoc($GLOBALS['sql']->query("SELECT regnotes FROM chatv2.chats WHERE chat_id=".$GLOBALS['biglog']['chat_id']));
						$admcom = $admcom['regnotes'];
						$admcom = htmlentities($admcom);

						echo "
							<B>Edit admin notes</B>: <BR>
							<div style='margin-left: 1em;'>
								<BR>
								<textarea name='regadmcom' cols=50 rows=5>{$admcom}</textarea>
							</div>
							<BR>
							<input type='hidden' name='adminaction' value='regapps'>";

					} else {
						if (!empty($regadmcom)) {
							$regadmcom = $GLOBALS['sql']->escapeString($regadmcom);
							$GLOBALS['sql']->query("UPDATE chatv2.chats SET regnotes='{$regadmcom}' WHERE chat_id=".$GLOBALS['biglog']['chat_id']);
							echo "Admin comment edited.<BR>";
						}

						if ($decline) {
							$decline = intval($decline);
							count_mysql_query("UPDATE uo_chat_regapps SET appstat=2 WHERE chat='{$chatpath}' AND id='{$decline}'", $handler);
							$rsubject = "Application for chat {$ctitle} declined";
							$rmessage = "Your application for chat {$ctitle} with the username {$runame}, has been declined and deleted from the database.\n\n". ucwords($login)."\nAdministrator of $ctitle.";
							mail($rmail, $rsubject, $rmessage, "From: $master_email\nReply-To: $cadmin\nX-pJJ-IP: {$_SERVER['REMOTE_ADDR']}\nX-pJJ-Chat: https://pjj.cc/{$chatpath}/\nX-pJJ-Auth: {$_REQUEST['login']}\n");
							echo "Declined applicant {$runame}<BR>";
						}

						echo "<a href='login.php?adminaction=regapps&admcomed=edit&login={$login}&password={$password}'>Edit admin comment</a>\n<BR><BR>\n";

						// Applications
						$regapps = count_mysql_query("SELECT id,username,email,faction,rtime,appstat FROM uo_chat_regapps WHERE chat='{$chatpath}' ORDER BY rtime ASC", $handler);


						$rn = $rd = $ra = 0;
						$apps_n = $apps_d = $apps_a = "	<table cellpadding=2 cellspacing=0 style='border-collapse: collapse'>
															<tr>
																<td style='border: 1px black solid;'>Username</td>
																<td style='border: 1px black solid;'>E-Mail</td>
																<td style='border: 1px black solid;'>Faction</td>
																<td style='border: 1px black solid;'>Applied date</td>
															</tr>";

						while (list($ruid, $rname, $remail, $rfaction, $regtime, $rstat) = mysql_fetch_row($regapps)) {
							$regtime = date("F j, g:i a", $regtime);
							$rfaction = $arr[$rfaction];

							$apps_n .= "<tr>\n";
							$apps_d .= "<tr>\n";
							$apps_a .= "<tr>\n";

							if ($rstat == 0) {
								$rn++;
								$apps_n .= "<td style='border: 1px black solid;'><a href='login.php?adminaction=regapps&ruid={$ruid}&login={$login}&password={$password}'>{$rname}</a></td>\n";
								$apps_n .= "<td style='border: 1px black solid;'>{$remail}</td>\n<td style='border: 1px black solid;'>{$rfaction}</td>\n<td style='border: 1px black solid;'>{$regtime}</td>\n</tr>";
							} elseif ($rstat == 1) {
								$ra++;
								$apps_a .= "<td style='border: 1px black solid;'><a href='login.php?adminaction=regapps&ruid={$ruid}&login={$login}&password={$password}'>{$rname}</a></td>\n";
								$apps_a .= "<td style='border: 1px black solid;'>{$remail}</td>\n<td style='border: 1px black solid;'>{$rfaction}</td>\n<td style='border: 1px black solid;'>{$regtime}</td>\n</tr>";
							} elseif ($rstat == 2) {
								$rd++;
								$apps_d .= "<td style='border: 1px black solid;'><a href='login.php?adminaction=regapps&ruid={$ruid}&login={$login}&password={$password}'>{$rname}</a></td>\n";
								$apps_d .= "<td style='border: 1px black solid;'>{$remail}</td>\n<td style='border: 1px black solid;'>{$rfaction}</td>\n<td style='border: 1px black solid;'>{$regtime}</td>\n</tr>";
							}
						}
						$apps_n .= "</table>\n";
						$apps_d .= "</table>\n";
						$apps_a .= "</table>\n";

						echo "<B>New Applications</B><BR>\n";
						echo (($rn > 0) ? $apps_n : "There are no new applications") . "<BR><BR><BR><BR>\n";
						echo "<B style='font-size: 12pt; text-decoration: underline;'>Log</B><BR><BR>";
						echo "<B>Accepted Applications</B><BR>\n";
						echo (($ra > 0) ? $apps_a : "You haven't accepted any applications yet.") . "<BR><BR>\n";
						echo "<B>Declined Applications</B><BR>\n";
						echo (($rd > 0) ? $apps_d : "You haven't declined any applications yet.") . "<BR><BR>\n";
					}
				}
				else if ($adminaction == "chpass") {
					if (($oldpass == $password || md5($oldpass) == $password || $oldpass == md5($password)) && ($newpass) && ($repeatpass)) {
						if ($newpass == $repeatpass) {
							ChangePass($login, $newpass, $chatpath);
							echo "<p>Password for $login was changed.<p>";
							$password = $newpass;
						}
					}
					else {
					echo "<p>Fill out the form.<p>
					<input type=hidden name=adminaction value=$adminaction>
					<table BORDER=0 CELLSPACING=3 CELLPADDING=3>
					<tr><td>
					Current password:
					</td><td>
					<input type=password name=oldpass size=15 max=25>
					</td></tr>
					<tr><td>
					New password:
					</td><td>
					<input type=password name=newpass size=15 max=25>
					</td></tr>
					<tr><td>
					Repeat new:
					</td><td>
					<input type=password name=repeatpass size=15 max=25>
					</td></tr>
					</table>";
					}
				}
			}
			else {
				echo "<SELECT NAME='adminaction' size='25'>\n";
				if (CheckFlags("AXZmM", $userlevel)) {
					echo "<OPTION VALUE='adduser'>Add User...\n";
					echo "<OPTION VALUE='regapps'>Application List...\n";
					echo "<OPTION VALUE='sendmail'>Send Email...\n";
					echo "<OPTION VALUE='getip'>Lookup Name2IP...\n";
				}
				if (CheckFlags("FpRDXZmM", $userlevel)) {
					echo "<OPTION VALUE='manageusers'>Manage Current Users...\n";
					//echo "<OPTION VALUE='bulkmanageusers'>Manage Bulk Users...\n";
				}
				if (CheckFlags("fZmM", $userlevel))
					echo "<OPTION VALUE='alterflags'>Manage User Flags...\n";
				if (CheckFlags("IXZmM", $userlevel))
					echo "<OPTION VALUE='manageicons'>Manage Icons...\n";
				if (CheckFlags("FAZmM", $userlevel)) {
					echo "<OPTION VALUE='addfaction'>Create Faction...\n";
					echo "<OPTION VALUE='renfaction'>Rename Faction...\n";
					echo "<OPTION VALUE='delfaction'>Delete Faction...\n";
				}
				if (CheckFlags("LZmM", $userlevel))
					echo "<OPTION VALUE='faction'>Edit Faction Details...\n";
				if (CheckFlags("VZmM", $userlevel))
					echo "<OPTION VALUE='poll'>Edit Poll...\n";
				if (CheckFlags("OXZmM", $userlevel)) {
					echo "<OPTION VALUE='mmotd'>Edit MotD...\n";
					echo "<OPTION VALUE='xmotd'>Edit MotD as HTMLArea...\n";
				}
				if (CheckFlags("ZmM", $userlevel))
					echo "<OPTION VALUE='chatpref'>Edit Chat Preferences...\n";

				if ($userlevel) {
					echo "<OPTION VALUE='chain'>Edit Character Chain...\n";
					echo "<OPTION VALUE='prefs' SELECTED>Edit Preferences...\n";
					echo "<OPTION VALUE='profile'>Edit Profile...\n";
					echo "<OPTION VALUE='xprofile'>Edit Profile as HTMLArea...\n";
					//echo "<OPTION VALUE='stats'>Chat Statistics...\n";
					echo "<OPTION VALUE='chpass'>Change Password...\n";
					echo "<OPTION VALUE='delself'>Delete Myself...\n";
					echo "<OPTION VALUE='logout'>Log out\n";
				}
				echo "</select>\n";
			}
			echo "<INPUT TYPE=HIDDEN NAME='login' value='".htmlentities($login)."'>
			<INPUT TYPE=HIDDEN NAME='password' value='".htmlentities($password)."'>
			<br><INPUT TYPE=SUBMIT VALUE='Ok...'>
			</form>";
		} else {
			echo "False login. Try again:<p>
				<FORM ACTION='login.php' METHOD='POST'>
				<tt>
				<br>Username: <INPUT TYPE=TEXT NAME='login' SIZE=15>
				<br>Password: <INPUT TYPE=PASSWORD NAME='password' SIZE=15>
				</tt>
				<br><INPUT TYPE='SUBMIT' Value='Login...'>
				</FORM>";
		}
	} else {
		echo "Please enter your login information:<p>
			<FORM ACTION='login.php' METHOD='POST'>
			<tt>
			<br>Username: <INPUT TYPE=TEXT NAME='login' SIZE=15>
			<br>Password: <INPUT TYPE=PASSWORD NAME='password' SIZE=15>
			</tt>
			<br><INPUT TYPE='SUBMIT' Value='Login...'>
			</FORM>";
	}

	echo "<p>
	<a href='../' target='_blank'>Back to chat...</a>
	</p>
	<p>
			<table cellspacing=1 cellpadding=0 border=0 width=450><tr align=center>
			<tr align=center>
				<td>-<a href=\"https://pjj.cc/common/help.php?man=pref\" target=_blank>Preference help</a>-</td>
				<td>-<a href=../gui_set.php target=_blank>Settings</a>-</td>
				<td>-<a href=biglist.php target=_blank>Complete Userlist</a>-</td>
				<td>-<a href=adminlog.php target=_blank>Admin Log</a>-</td>
			</tr>
			<tr align=center>
				<td>-<a href=\"https://pjj.cc/common/help.php?man=flag\" target=_blank>Flag help</a>-</td>
				<td>-<a href=../gui_opt.php target=_blank>Options</a>-</td>
				<td>-<a href=viewer.php target=_blank>Profiles</a>-</td>
				<td>-<a href='adminlog.php?log=cpanel' target=_blank>CPanel Log</a>-</td>
			</tr>
			<tr align=center>
				<td>-<a href=\"https://pjj.cc/common/help.php?man=chat\" target=_blank>ChatPrefs</a>-</td>
				<td>-<a href=../gui_icon.php target=_blank>Icons</a>-</td>
				<td>-<a href=biglog.php target=_blank>Old Chat Logs</a>-</td>
				<td>-<a href=../jbb/ target=_blank>Board</a>-</td>
			</tr>
			<tr align=center>
				<td>-</td>
				<td>-<a href=../gui_lang.php target=_blank>Language</a>-</td>
				<td>-<a href=dblog.php target=_blank>Chat Logs</a>-</td>
				<td>-<a href=\"https://pjj.cc/common/vote.php?chatpath=$chatpath\" target=_blank>Poll</a>-</td>
			</tr>
			</table><!-- Debug: $cqs -->";
?>
<center><img src="https://pjj.cc/gfx/null.gif" border=0></center>
</td>
	<td valign="top" align="right" height="100%" width=80> </td></tr>
	</table>
</td></tr>
<tr><td background="https://pjj.cc/gfx/dn_tile.gif" align="center" valign="bottom" height="32"> </td></tr>
</table>
</body>
</html>
