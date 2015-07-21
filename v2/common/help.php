<?php
// This file is part of the Project JJ PHP Chat distribution.
// Created and maintained by Tino Didriksen <td@projectjj.com>
// The contents of this file is subject to a license.
// Read license.txt and readme.txt for more information.
	if ($_REQUEST['source']) {
		readfile(__FILE__);
		die();
	}
	ob_start("ob_gzhandler");
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="Author" content="Tino Didriksen (mail@tinodidriksen.com)">
	<meta name="GENERATOR" content="Tino Didriksen (mail@tinodidriksen.com)">
	<title>Project JJ Chats - Let Worlds Unfold</title>

<script type="text/javascript">
if (window != window.top)
  top.location.href = location.href;
</script>

<body text="#000000" bgcolor="#FFFFFF" link="#0783FF" vlink="#0783FF" alink="#0682FE" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
<tr><td background="../gfx/up_tile.gif" valign="top" align="left" height="32">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="32">
	<tr><td valign="top" align="left" height="32"><a href="../"><img src="../gfx/projectjj.gif" border="0"></a></td>
	<td valign="top" align="right" height="32"><a href="mailto:chats@projectjj.com"><img src="../gfx/phpchat.gif" border="0"></a></td></tr>
	</table>
</td></tr>
<tr><td valign="top" width="100%" height="100%">
	<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
	<tr><td valign="top" align="left" height="100%"><img src="../gfx/up_l.gif" border="0"></td>
	<td valign="top" height="100%">
<?php
	echo "<center><img src=\"../gfx/null.gif\" border=0><br>\n";
	if ($_REQUEST['man'] == "flag") {
		echo "<font color=#0783FF>Flags</font><br>These flags dictate what a user can/cannot do.<p>";
		echo "<table cellspacing=1 cellpadding=3 border=0 width=600 bgcolor=#000000>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>System Administrator [M]</font><p>All flags. Is protected from all other users.</td>";
			echo "<td width=200><font color=#0783FF>Chat Master [m]</font><p>All flags. Can only be set/removed by System Administrator.</td>";
			echo "<td width=200><font color=#0783FF>Master [Z]</font><p>All flags. Can only be set/removed by Chat Master or System Administrator.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Administrator [X]</font><p>Can do everything, except modify user flags. Is not protected. [ADRpFsoiIOCBbra]</td>";
			echo "<td width=200><font color=#0783FF>Moderator [x]</font><p>Can use /rem, /ban and /unban. [Br]</td>";
			echo "<td width=200><font color=#0783FF>OOC [z]</font><p>May not use icons and has the OOC symbol.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Protected [P]</font><p>Can only be deleted/flagged/renamed/faction by a Master or above.</td>";
			echo "<td width=200><font color=#0783FF>Add Users [A]</font><p>Can add users with Member or OOC flags.</td>";
			echo "<td width=200><font color=#0783FF>Delete Users [D]</font><p>Can delete users that are not Protected, Master or above.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Rename Users [R]</font><p>Can rename users that are not Protected, Master or above.</td>";
			echo "<td width=200><font color=#0783FF>Reset Password [p]</font><p>Can reset the password for users that are not Protected, Master or above.</td>";
			echo "<td width=200><font color=#0783FF>Change Faction [F]</font><p>Can change the faction for users that are not Protected, Master or above.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Change Flags [f]</font><p>Can change flags for users that are not Protected, Master or above.<br>Very powerful because a [f] user can set flags for her/himself.</td>";
			echo "<td width=200><font color=#0783FF>Edit Settings [s]</font><p>Can edit /gui_set.php settings.</td>";
			echo "<td width=200><font color=#0783FF>Edit Options [o]</font><p>Can edit /gui_opt.php options.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Edit Icons [i]</font><p>Can edit /gui_icon.php icons.</td>";
			echo "<td width=200><font color=#0783FF>Edit Personal Icons [I]</font><p>Can edit the personal icons of any user.</td>";
			echo "<td width=200><font color=#0783FF>Edit MotD [O]</font><p>Can edit the Message of the Day.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>/clear [C]</font><p>Can clear the chat.</td>";
			echo "<td width=200><font color=#0783FF>/ban & /unban [B]</font><p>Can ban and unban users.</td>";
			echo "<td width=200><font color=#0783FF>/muban [b]</font><p>Can clear the banlist.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>/rem [r]</font><p>Can remove single lines in chat.</td>";
			echo "<td width=200><font color=#0783FF>/raw [a]</font><p>Can insert raw HTML lines in chat.</td>";
			echo "<td width=200><font color=#0783FF>Stealth [l]</font><p>There will not be a JOIN or EXIT when the user moves in/out of the chat.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Edit Poll [V]</font><p>Can modify and clear the poll of the chat.</td>";
			echo "<td width=200><font color=#0783FF>Faction Leader [L]</font><p>The user had leader rights over the faction s/he is in.</td>";
			echo "<td width=200><font color=#0783FF></font><p></td>";
		echo "</tr>";
		echo "</table>";
	} else if ($_REQUEST['man'] == "pref") {
		echo "<font color=#0783FF>Preferences</font><br>The chosen preferences will be loaded on login, and only if the user has selected Reload.<p>";
		echo "<table cellspacing=1 cellpadding=3 border=0 width=600 bgcolor=#000000>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Advanced [A]</font><p>The console will start in Advanced mode. A bit chaotic.</td>";
			echo "<td width=200><font color=#0783FF>Simple [S]</font><p>The console will start in Simple mode. Very clean interface.</td>";
			echo "<td width=200><font color=#0783FF>No MotD [O]</font><p>Turns off showing the Message of the Day.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Bold [b]</font><p>Text will be <b>bold</b>.</td>";
			echo "<td width=200><font color=#0783FF>Underline [u]</font><p>Text will be <u>underlined</u>.</td>";
			echo "<td width=200><font color=#0783FF>Italic [i]</font><p>Text will be <i>italic</i>.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Alternate [t]</font><p>Text will be <tt>alternate</tt>.</td>";
			echo "<td width=200><font color=#0783FF>Reverse Output [R]</font><p>Shows new lines at the bottom (FILO order), like Beseen did.</td>";
			echo "<td width=200><font color=#0783FF>Email</font><p>Unused for now, but fill it in anyways just in case.</td>";
		echo "</tr>";
		echo "</table>";

		echo "<img src=\"../gfx/null.gif\" border=0><hr width=350><br>\n";

		echo "<font color=#0783FF>Console Setup</font><br>Controls the elements visible on the console.<p>";
		echo "<table cellspacing=1 cellpadding=3 border=0 width=600 bgcolor=#000000>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>No Name [N]</font><p>The \"You are Handle\" won't show.</td>";
			echo "<td width=200><font color=#0783FF>No Chain/Refresh [C]</font><p>Chained handle changer and custom refresh won't appear.</td>";
			echo "<td width=200><font color=#0783FF>No Text Styles [T]</font><p>None of the text styles controls will show.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>No Icon Changer [c]</font><p>Icon selector won't appear.</td>";
			echo "<td width=200><font color=#0783FF>No Color [L]</font><p>Color changer won't show.</td>";
			echo "<td width=200><font color=#0783FF>No Buttons [X]</font><p>None of the bottom buttons will show.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>Public Email [M]</font><p>Allows anyone to see your email.</td>";
			echo "<td width=200><font color=#0783FF>Public ICQ [Q]</font><p>Allows anyone to see your ICQ UIN.</td>";
			echo "<td width=200><font color=#0783FF>Public AIM [a]</font><p>Allows anyone to see your AIM SN.</td>";
		echo "</tr>";
		echo "</table>";
	} else if ($_REQUEST['man'] == "chat") {
		echo "<font color=#0783FF>Chat Preferences</font><br>These options will affect all users.<p>";
		echo "<table cellspacing=1 cellpadding=3 border=0 width=600 bgcolor=#000000>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>No Ignore [I]</font><p>Disables the /ignore and /unignore commands.</td>";
			echo "<td width=200><font color=#0783FF>No Banning [B]</font><p>Disables /ban, /unban and /muban commands.</td>";
			echo "<td width=200><font color=#0783FF>No Join/Exit[J]</font><p>Works as if everyone has the Stealth flag.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>No Whois [W]</font><p>Disables /whois.</td>";
			echo "<td width=200><font color=#0783FF>No Undo [U]</font><p>Disables the Undo button and /undo command.</td>";
			echo "<td width=200><font color=#0783FF>No Private Messages [P]</font><p>Disables /msg and reading messages.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>No Filter [F]</font><p>Disables the filter.</td>";
			echo "<td width=200><font color=#0783FF>No Icons [C]</font><p>Disallows icons.</td>";
			echo "<td width=200><font color=#0783FF>No Image [i]</font><p>Disallows images.</td>";
		echo "</tr>";
		echo "<tr valign=top bgcolor=#FFFFFF>";
			echo "<td width=200><font color=#0783FF>No Link [L]</font><p>Disallows links.</td>";
			echo "<td width=200><font color=#0783FF>No MotD [M]</font><p>Disables the MotD and logo.</td>";
			echo "<td width=200><font color=#0783FF>No Idents [D]</font><p>Turns off showing idents.</td>";
		echo "</tr>";
		echo "</table>";
	}
	echo "<img src=\"../gfx/null.gif\" border=0></center>\n";
?>
</td>
	<td valign="top" align="right" height="100%"><img src="../gfx/up_r.gif" border="0"></td></tr>
	</table>
</td></tr>
<tr><td background="../gfx/dn_tile.gif" align="center" valign="bottom" height="32"><center><a href="mailto:chats@projectjj.com"><img src="../gfx/worlds.gif" border="0"></a></center></td></tr>
</table>
</body>
</html>
