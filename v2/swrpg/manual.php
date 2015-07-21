<?php
	ob_start();

    include('../mysql.php');
	include("settings.php");

?>

<html>
<head>
	<title>Project JJ Chat Manual</title>
</head>
<body bgcolor=#000000 text=#ffffff alink=#eeeeee vlink=#dddddd link=#eeeeee>
<br>
<blockquote>
<font color="#FF8040"><b>The <a href="http://pjj.cc/legal/">Terms of Service</a> is required reading!</b></font><p>
<a href='manual.php'><u>Login Screen</u></a><br>
Fill out the form and click 'Enter Chat'.<br>
The 'Reload' box only applies to registered users. It is used to reload your last used color/link/image so you don't need to fill in those parts.<p>
<a href='manual.php'><u>General Commands</u></a><br>
The letters/numbers infront of names are idents. They are generated from your IP. Used to make impersonation more difficult.<p>
Use [x] where x is either of i-u-b-t for <i>italic</i>-<u>underlined</u>-<b>bold</b>-<tt>alternate</tt> text. End with [/x].<br>
For example [u]Word[/u] will output <u>Word</u>.<br>
Alternately, /word/ will output <i>word</i>, _word_ outputs <u>word</u> and -word- outputs <b>word</b>.<p>
Links are created by writing any line with tp:// in it.<br>
Example http://www.microsith.com/ would output <a href='http://www.microsith.com/'>link</a><p>
The 4 boxes labeled <b>B</b>, <i>I</i>, <u>U</u> and <tt>T</tt> will make the entire line the style of the checked box. Can be combined with the [] styles for extra effect.<br>
The last box, labeled IP, will make you show your entire IP and not the encrypted one. We do not encourage chatters to use this.<p>
Use '/me text' to make an action.<br>
Use '/nick newname' to change your name to newname.<br>
Use '/link http://something/' to change your link.<br>
Use '/image http://something/image.jpg' to change your image.<br>
'/undo' will remove your last post from the log (will not affect the <a href='register/biglog.php' target=_blank>complete log</a>). Cannot undo msgs or other commands.<br>
'/whois username' will check when that user was last in the chat.<p>
Use the 'Log out' button to exit, or the alternate /exit command.<p>
<a href='manual.php'><u>Registered user commands</u></a><br>
'/msg username message' will send a private message to username. If username is in multiple words, exchange spaces/whitespace with _.<p>
<a href='manual.php'><u>Moderative commands</u></a><br>
'/ban ident seconds' will ban everybody with the ident for the amount of seconds.<br>
'/unban ident' will unban the chosen ident.<br>
'/muban' will clear the ban list thus removing any bans.<br>
'/gag ident seconds' will gag everybody with the ident for the amount of seconds.<br>
'/ungag ident' will ungag the chosen ident.<br>
'/mugag' will clear the gag list thus removing any gags.<br>
'/rem text' will remove any line that contains 'text' from the log (will not affect the <a href='register/biglog.php' target=_blank>complete log</a>).<p>
<a href='manual.php'><u>Admin commands</u></a><br>
'/clear' will clear the chat and userlist of text, forcing a clean log.<br>
'/raw HTML line' will insert the HTML line into the chat log without changing it. Please make sure it is correct HTML and that it won't mess up the log.<br>
Admins also have access to the Message of the Day. Go to the Members Area and select 'Edit MOTD' (only if you actually have a useful thing to say, of course).
</blockquote><p>
<center><hr width=50%></center><p>

<?php

	echo "<blockquote>
<a href='manual.php'><u>Chat Settings</u></a><p>
Main text window refreshes every <b>$respeed</b> seconds.<br>
Userlist does it every <b>$userlistspeed</b> seconds.<br>
Server posts are color <b>#$servcol</b>.<br>
Identifiers are <b>$identlenght</b> long.<br>
<b>$maxlines</b> lines are stored, but max <b>$logsize</b> byte data is kept as log.<br>
People not posting within <b>$timeout</b> seconds won't appear in userlist.<br>
Images are $pimgx x $pimgy.
</blockquote><p>
<center><hr width=50%><p>
&copy; 2000-2002 <a href='http://projectjj.com'>Project JJ</a><br>
All rights reserved
</center>
</body>
</html>";
