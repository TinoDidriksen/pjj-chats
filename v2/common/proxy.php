<?php

//	require_once(__DIR__.'/../../chatv3/_inc/mmcache.php');

function Proxy_IsProxy($ip) {
	return false;
	$parts = explode('.', $ip);
	$tocheck = "{$parts[3]}.{$parts[2]}.{$parts[1]}.{$parts[0]}.";
	$lists = array(
//		'opm.blitzed.org' => 'http://opm.blitzed.org/',
		'cbl.abuseat.org' => 'http://cbl.abuseat.org/lookup.cgi?ip=',
//		'http.dnsbl.sorbs.net' => 'http://www.dnsbl.nl.sorbs.net/lookup.shtml?',
//		'socks.dnsbl.sorbs.net' => 'http://www.dnsbl.nl.sorbs.net/lookup.shtml?',
//		'rbl.efnet.org' => 'http://rbl.efnet.org/?i=',
		'xbl.spamhaus.org' => 'http://www.spamhaus.org/query/bl?ip='
		);
	foreach ($lists as $list => $lookup) {
		$check = $tocheck.$list;
		$result = gethostbyname($check);
		//echo "\n<!-- $check = $result -->\n";
		if ($result != $check && strpos($result, '127.') === 0)
			return $lookup.$ip;
	}
	return false;
}
