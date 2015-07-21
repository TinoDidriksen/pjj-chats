<?php
// This file is part of the Project JJ PHP Chat distribution.
// Created and maintained by Tino Didriksen <td@projectjj.com>
// The contents of this file is subject to a license.
// Read license.txt and readme.txt for more information.
	if (!empty($_REQUEST['source'])) {
		readfile(__FILE__);
		die();
	}

function AddIgnore($new_ident, $new_time, $auth, $chatpath) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_ignore WHERE chat='$chatpath' AND ident='$new_ident' AND auth='$auth'", $handler, "ignore.php: AddIgnore() 1/2");
	@count_mysql_query("INSERT INTO uo_chat_ignore (chat,ident,utime,auth) VALUES ('$chatpath','$new_ident','$new_time','$auth')", $handler, "ignore.php: AddIgnore() 2/2");
}

function CheckIgnore($new_ident) {
	global $handler;

	foreach($GLOBALS['ignores'] as $entry) {
		if ($entry['ident'] == $new_ident)
			return 1;
	}

	return 0;
}

function CheckAnyIgnore($chatpath, $auth) {
	global $handler;

	if (!$GLOBALS['ignores']) {
		$GLOBALS['ignores'] = array();

		@count_mysql_query("DELETE FROM uo_chat_ignore WHERE chat='$chatpath' AND utime<'".(time())."'", $handler, "ignore.php: CheckAnyIgnore() 1/2");
		$result = @count_mysql_query("SELECT ident,auth FROM uo_chat_ignore WHERE chat='$chatpath' AND auth='$auth'", $handler, "ignore.php: CheckAnyIgnore() 2/2");
		while ($usel = mysql_fetch_assoc($result))
			$GLOBALS['ignores'][] = $usel;
		mysql_free_result($result);
	}

	return count($GLOBALS['ignores']);
}

function DeleteIgnore($userident, $chatpath, $auth) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_ignore WHERE chat='$chatpath' AND ident='$userident' AND auth='$auth'", $handler, "ignore.php: DeleteIgnore() 1/1");
}

function ClearIgnore($userident, $chatpath) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_ignore WHERE chat='$chatpath' AND auth='$userident'", $handler, "ignore.php: ClearIgnore() 1/1");
}
