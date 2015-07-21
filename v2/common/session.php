<?php

if (empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	$_SERVER['HTTP_X_FORWARDED_FOR'] = '';
}

$_REQUEST = array_merge($_GET, $_POST);

require_once(__DIR__.'/../../chatv3/_inc/mmcache.php');
$GLOBALS['chat_session_name'] = 'chatv2';
$GLOBALS['chat_session_life'] = 2592000;

/*
if (!empty($_REQUEST[$GLOBALS['chat_session_name']])) {
	$_COOKIE[$GLOBALS['chat_session_name']] = $_REQUEST[$GLOBALS['chat_session_name']];
}
unset($_REQUEST[$GLOBALS['chat_session_name']]);
//*/

$_COOKIE[$GLOBALS['chat_session_name']] = preg_replace('/[^a-z0-9]/', '', $_COOKIE[$GLOBALS['chat_session_name']]);

/*
*	BrowserID	void
*	Generates a sort of identification tag based on the client's browser.
*	Is by no means secure, but it will stop most casual session hijackings.
*/
function BrowserRawID() {
	return $_SERVER['HTTP_USER_AGENT'];
}

function BrowserID() {
	return md5(BrowserRawID());
}

function IPClassA($ip) {
	return preg_replace('/^([0-3]+)\..*$/', '\1', $ip);
}

function ChatSessionInit() {
	session_name($GLOBALS['chat_session_name']);

	$empty = false;
	//echo "\n<!-- {$_COOKIE[$GLOBALS['chat_session_name']]} -->\n";
	//*
	if (empty($_COOKIE[$GLOBALS['chat_session_name']])) {
		$empty = true;
		//echo '<!-- Empty SID. Assigning you one, then... -->';
		$_COOKIE[$GLOBALS['chat_session_name']] = md5(uniqid(rand()).time());
		session_id($_COOKIE[$GLOBALS['chat_session_name']]);
	}
	//*/

	session_start();

	/*
	if (($empty == false) && empty($_SESSION['session_id'])) {
		session_regenerate_id(); // prevent fixation attacks
		echo '<!-- No, you are not allowed to supply your own Session ID. -->';
	}
	//*/
	//echo "\n<!-- {$_SESSION['session_id']} -->\n";
	$_SESSION['session_id'] = session_id();
	//echo "<!-- {$_SESSION['session_id']} -->\n";
	/*
	if (!preg_match('@^[a-z0-9]{32}$@', $_SESSION['session_id'])) {
		ChatSessionRestart('Invalid Session ID', FALSE);
		return false;
	}
	//*/

	/*
	if (empty($_SESSION['client_id'])) {
		$_SESSION['client_id']		= BrowserID();
		$_SESSION['client_id_raw']	= BrowserRawID();
	}
	else if ($_SESSION['client_id'] != BrowserID()) {
		$reason = 'Client BrowserID changed, possible session hijacking.';
		$reason .= "\n New ID: ".BrowserID().' :: '.BrowserRawID();
		$reason .= "\n Old ID: {$_SESSION['client_id']} :: {$_SESSION['client_id_raw']}";
		ChatSessionRestart($reason, FALSE);
		return false;
	}

	if (empty($_SESSION['chap'])) {
		$_SESSION['chap'] = array();
		$_SESSION['chap']['challenge'] = sha1(uniqid(rand()));
	}

	if (empty($_SESSION['security']))
		$_SESSION['security'] = 1;
	//*/

	if (!is_array($_SESSION['host'])) {
		$_SESSION['host'] = array();
		$_SESSION['host']['ip']			= $_SERVER['REMOTE_ADDR'];
		$_SESSION['host']['addr']		= @gethostbyaddr($_SERVER['REMOTE_ADDR']);
		/*
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$_SESSION['host']['proxyip']		= $_SERVER['HTTP_X_FORWARDED_FOR'];
			$_SESSION['host']['proxyaddr']		= @gethostbyaddr($_SERVER['HTTP_X_FORWARDED_FOR']);
		}
		//*/
	}

	setcookie($GLOBALS['chat_session_name'], $_SESSION['session_id'], time()+$GLOBALS['chat_session_life'], '/', 'pjj.cc');
	setcookie($GLOBALS['chat_session_name'], $_SESSION['session_id'], time()+$GLOBALS['chat_session_life'], '/', '.pjj.cc');

/*
	session_write_close();
	session_start();
//*/
}

function ChatSessionKill($reason='') {
	session_write_close();
	$_SESSION = array();

	if ($reason)
		echo "<!-- Session destroyed: {$reason} -->";
}

function ChatSessionRestart($reason='', $natural=TRUE) {
	/*
	if (!$natural) {
		$mail = $reason."\n\n".var_export($_SESSION, TRUE);
		mail('sessions@projectjj.com', 'ASR by '.$_SERVER['REMOTE_ADDR'], $mail);
	}
	//*/
	ChatSessionKill($reason);
	session_id(md5(uniqid(time().'JJ'.rand())));
	ChatSessionInit();
}

function ChatSessionSuspend() {
	session_write_close();
}

function ChatSessionResume() {
	session_start();
}

$reason = '';
if (strcasecmp($_SERVER['HTTP_USER_AGENT'], 'Microsoft URL Control') == 0) {
	$reason .= 'That User-Agent will not work here.';
}
if (strcasecmp($_SERVER['HTTP_USER_AGENT'], 'SURF') == 0) {
	$reason .= 'FunWebProducts is spyware!';
}
/*
if (empty($_SERVER['HTTP_USER_AGENT'])) {
	$reason .= 'You need a valid User-Agent to chat here.';
}
//*/

if (!empty($reason)) {
	echo <<<REJECTED
<html>
<head>
	<title>Rejected</title>
</head>
<body>
Your request was rejected based on your User-Agent:<br>
<b>{$_SERVER['HTTP_USER_AGENT']}</b><br>
<br>
Reason:<br>
<b>{$reason}</b>
</body>
</html>
REJECTED;
	die();
}

//MMC_Lock('Session.'.$_COOKIE[$GLOBALS['chat_session_name']]);
if (empty($_REQUEST['xml'])) {
	ignore_user_abort(true);
	ChatSessionInit();
//	session_start();
	register_shutdown_function('session_write_close');
}
