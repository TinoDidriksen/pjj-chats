<?php

if (!function_exists("count_mysql_query")) {
	function count_mysql_query($query, $hand, $reason="Unknown") {
		global $cqs;

		$cqs++;

		return mysqli_query($hand, $query);
	}
}

function UpdateViewers($pid, $ip='', $pip='') {
	global $handler;

	if (empty($ip)) {
	    $ip = $_SERVER['REMOTE_ADDR'];
	}
	if (empty($pip)) {
	    $pip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}

	$host = gethostbyaddr($ip);
	if (strpos($host, '.search.msn.com') !== false) {
		return;
	}

	$ip = mysqli_escape_string($handler, $ip);
	$pip = mysqli_escape_string($handler, $pip);

	$ua = mysqli_escape_string($handler, $_SERVER['HTTP_USER_AGENT']);
    $pid = trim($pid);

	@count_mysql_query("INSERT INTO uo_chat (utime,ip,chat,proxyip,user_agent) VALUES ('".(time())."',INET_ATON('$ip'),'$pid',INET_ATON('$pip'),'$ua')", $handler, "std_uo.php: UpdateViewers() 1/2");
//	@count_mysql_query("INSERT INTO uo_chat_ips (ip) VALUES ('{$ip}')", $handler, "std_uo.php: UpdateViewers() 2/2");
}

function GetViewers($pid) {
	global $handler;

    $pid = trim($pid);
	$numview=0;

	if (mt_rand(0,10) == 5)
		@count_mysql_query("DELETE FROM uo_chat WHERE utime<'".(time()-300)."'", $handler, "std_uo.php: GetViewers() 1/2");
	$result = @count_mysql_query("SELECT DISTINCT ip FROM uo_chat WHERE chat='$pid' AND utime>'".(time()-300)."'", $handler, "std_uo.php: GetViewers() 2/2");
	$numview = @mysqli_num_rows($result);
	@mysqli_free_result($result);

	return $numview;
}
