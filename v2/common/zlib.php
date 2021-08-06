<?php

function PackLog($realpath, $infile='complete.log') {
	global $master_zlib;

	if (file_exists($infile))
		$fz = filesize($infile);
	else
		$fz = 0;

	if ($master_zlib == 0) {
		$fn = "../logs/$realpath.".time().".log";
		copy($infile, $fn);
	} else {
		$fn = "../logs/$realpath.".time().".gz";
		$zp = gzopen($fn, "wb9");
		gzwrite($zp, fread(fopen($infile, "rb"), filesize($infile)));
		gzclose($zp);
	}
	chmod($fn, 0666);
	unlink($infile);
}

function PackNewLog($realpath) {
	$infile = "complete.log";
	if (file_exists($infile))
		$fz = filesize($infile);
	else
		$fz = 0;

	if ($fz >= 393216) {
		PackLog($realpath, $infile);
	}
	return 0;
}

function ListSort($a, $b) {
	$fn = mb_substr($GLOBALS['realpath'], 4).'.';

	$atime = str_replace('.log', '', str_replace('.gz', '', str_replace($fn, '', $a)));
	$btime = str_replace('.log', '', str_replace('.gz', '', str_replace($fn, '', $b)));
	if ($atime == $btime)
		return 0;
	return ($atime > $btime) ? -1 : 1;
}

function ListLogs() {
	global $logblock;
	$realpath = mb_substr($GLOBALS['realpath'], 4);

	$fn = "$realpath.";

	$handle=opendir('../../logs');
	while ($file = readdir($handle)) {
		if (preg_match("/^{$realpath}\./", $file)) {
			$midir[count($midir)] = $file;
		}
	}
	closedir($handle);

	if ($midir)
		usort($midir, 'ListSort');
	echo "<blockquote>Expect a 8:1 ratio on sizes, so a file of 128 KB in size is really 1 MB when you open it.<p><table cellspacing=0 cellpadding=2 border=0>\n";
	echo "<tr><td align=left>Date</td><td align=right>Packed size (KB)</td></tr>\n";
	//echo "<tr><td align=left><a href=\"biglog.php?time=current&action=view\">Current log</a></td><td align=right>".ceil(filesize("../complete.log")/1024)."<br>(not packed)</td></tr>\n";
	for ($cc=0;$cc<count($midir);$cc++) {
		$sname = str_replace(".log", "", str_replace(".gz","", str_replace($fn, "", $midir[$cc])));
		$dname = date("H:i:s - F d, Y T", $sname+0);
		$fs = ceil(filesize("../../logs/".$midir[$cc])/1024);
		echo "<tr><td align=left><a href=\"biglog.php?time=$sname&action=view\">$dname</a></td><td align=right>$fs</td></tr>\n";
	}
	echo "</table><br>\n";
	echo "</blockquote>\n";
}

function DisplayLog($time) {
	global $logblock, $master_zlib;
	$realpath = mb_substr($GLOBALS['realpath'], 4);

	$fn = "../../logs/$realpath.".$time.".gz";

	if ($time == "current") {
		readfile("../complete.log");
		return 1;
	} else if (file_exists($fn)) {
		readgzfile($fn);
		return 1;
	}
	else
		echo "The log $fn was not found.";

	return 0;
}
