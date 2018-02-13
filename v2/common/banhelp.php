<?php

function AddBan($new_ident, $new_time, $auth, $chatpath) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_ban WHERE (chat='$chatpath' AND ident='$new_ident') OR (utime<'".(time())."')", $handler, "banhelp.php: AddBan() 1/2");
	@count_mysql_query("INSERT INTO uo_chat_ban (chat,ident,utime,auth) VALUES ('$chatpath','$new_ident','$new_time','$auth')", $handler, "banhelp.php: AddBan() 2/2");
}

function CheckBan($new_ident, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT ident FROM uo_chat_ban WHERE chat='$chatpath' AND ident='$new_ident' AND utime>'".(time())."'", $handler, "banhelp.php: CheckBan() 1/1");
	$usel = @mysqli_fetch_row($result);
	@mysqli_free_result($result);

	if (!empty($usel[0])) {
		return 1;
	}
	return 0;
}

function DeleteBan($userident, $chatpath) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_ban WHERE (chat='$chatpath' AND ident='$userident') OR (utime<'".(time())."')", $handler, "banhelp.php: DeleteBan() 1/1");
}


function AddGag($new_ident, $new_time, $auth, $chatpath) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_gag WHERE (chat='$chatpath' AND ident='$new_ident') OR (utime<'".(time())."')", $handler, "banhelp.php: AddGag() 1/2");
	@count_mysql_query("INSERT INTO uo_chat_gag (chat,ident,utime,auth) VALUES ('$chatpath','$new_ident','$new_time','$auth')", $handler, "banhelp.php: AddGag() 2/2");
}

function CheckGag($new_ident, $chatpath) {
	global $handler;

	$result = @count_mysql_query("SELECT ident FROM uo_chat_gag WHERE chat='$chatpath' AND ident='$new_ident' AND utime>'".(time())."'", $handler, "banhelp.php: CheckGag() 1/1");
	$usel = @mysqli_fetch_row($result);
	@mysqli_free_result($result);

	if (!empty($usel[0])) {
		return 1;
	}
	return 0;
}

function DeleteGag($userident, $chatpath) {
	global $handler;

	@count_mysql_query("DELETE FROM uo_chat_gag WHERE (chat='$chatpath' AND ident='$userident') OR (utime<'".(time())."')", $handler, "banhelp.php: DeleteGag() 1/1");
}
