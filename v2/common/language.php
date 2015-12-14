<?php

$GLOBALS['language'] = array(
	0 => "This chat can only be used by registered users.<p>If you are one you must log in and possibly also post before you can see anything.<p>In case you are not, <a href='https://pjj.cc/' target=_top>the portal</a> may have something else of interest.",
	1 => "<u>JOIN</u>: {USERNAME} has entered.<br>",
	2 => "<u>EXIT</u>: {USERNAME} has left the chat ( <i>{MESSAGE}</i> ).<br>",
	3 => "<u>BAN</u>: <font color='#{COLOR}'>{USERNAME} banned {BANIDENT} for {BANDURATION} seconds.</font><br>",
	4 => "<u>IGN</u>: <font color='#{COLOR}'>{USERNAME} ignored {IGNOREIDENT} for {IGNOREDURATION} seconds.</font><br>",
	5 => "<u>UGN</u>: <font color='#{COLOR}'>{USERNAME} removed {IGNOREIDENT} from ignore.</font><br>",
	6 => "<u>UNBAN</u>: <font color='#{COLOR}'>{USERNAME} removed ban for {BANIDENT}.</font><br>",
	7 => "<u>NICK</u>: {USERNAME} changed nick to {NEWNAME}.<br>",
	8 => "<u>WHO</u>: {WHOISNAME} is unknown.<br>",
	9 => "<u>WHO</u>: {WHOISNAME} last seen {WHOISDATE}<br>",
	10 => "<div align=right>Chat cleared by {USERNAME}.</div>",
	11 => "You are {USERNAME}.",
	12 => "<table border=0 width='100%' height='100%'><tr><td valign=center align=center><font color='#FF0000' size=+4>YOU ARE BANNED!<br>GO AWAY!<br>LEAVE US ALONE!</font><br>Or wait until you are unbanned in a while.</td></tr></table>"
);
$GLOBALS['language'][13] = '<font color="#{COLOR}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{ICON} {USERNAME} {MESSAGE}</font><br>';
$GLOBALS['language'][14] = '<font color="#{COLOR}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{USERNAME} last listened to {LASTPLAYED}</font><br>';
$GLOBALS['language'][15] = '<u>GAG</u>: <font color="#{COLOR}">{USERNAME} gagged {GAGIDENT} for {GAGDURATION} seconds.</font><br>';
$GLOBALS['language'][16] = '<u>UNGAG</u>: <font color="#{COLOR}">{USERNAME} ungagged {GAGIDENT}.</font><br>';
$GLOBALS['language'][17] = 'Sorry, you cannot speak while gagged.<br>';
$GLOBALS['language'][18] = '<font color="#{COLOR}">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{ICON} {USERNAME} shakes the Magic 8-Ball. It settles on: <i>{8BALLRESULT}</i></font><br>';

$GLOBALS['banguage'] = array(
	1 => "<tt><font size=-1>{IDENT},</font></tt> {SYMBOL}<u>{USERNAME}</u>: <font color='#{COLOR}'>{MESSAGE}</font><br>",
	2 => "<u>MSG</u>: {USERNAME} sent a message to {RECIPIENT}.<br>",
	3 => "<u>MSG</u>: {USERNAME} tried to message {RECIPIENT}.<br>",
	4 => "The administrator of this chat set a permanent ban for ident {IDENT}, which in this case is you.<br>",
	5 => "You are not a registered user.",
	6 => "<u>DICE</u>: <font color='#{COLOR}'>{USERNAME} rolls {DICERESULT} on a {DICETYPE}.</font><br>"
);

$GLOBALS['xxanguage'] = array(
	0 => "<tt><font size=-1>{IDENT},</font></tt> {SYMBOL}<u>{USERNAME}</u> <a href='{CHATPATH}/register/viewer.php?su={USERNAMEURL}&fm=view'>{ICON}</a>: <font color='#{COLOR}'>{MESSAGE}</font><br>"
);

$GLOBALS['m8ball'] = array(
	// Yes
	"It is certain",
	"It is decidedly so",
	"Without a doubt",
	"Yes definitely",
	"You may rely on it",
	"As I see it yes",
	"Most likely",
	"Outlook good",
	"Yes",
	"Signs point to yes",
	// Maybe
	"Reply hazy try again",
	"Ask again later",
	"Better not tell you now",
	"Cannot predict now",
	"Concentrate and ask again",
	// No
	"Don't count on it",
	"My reply is no",
	"My sources say no",
	"Outlook not so good",
	"Very doubtful",
);
