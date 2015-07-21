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

	echo "<html>\n<head>\n<title>Color Charts</title>\n</head>\n<body bgcolor=#000000 text=#ffffff alink=#ffffff vlink=#ffffff link=#ffffff><p>\n<p>\n<center>\n<table border=0 cellspacing=1 cellpadding=1 width=1 style='font-family: courier; font: courier;'>\n<tr><td>";
	if ($_REQUEST['numcols']) {

		if ($_REQUEST['numcols'] > 16)
			$_REQUEST['numcols']=16;
		else if ($_REQUEST['numcols'] < 1)
			$_REQUEST['numcols']=1;

		$file = "chart-{$_REQUEST['numcols']}.col";

		if (file_exists($file)) {
			readfile($file);
		} else {
			$fc = fopen($file, "w");
			$factor = floor(255/$numcols);
			for ($r=1;$r<=$numcols;$r++) {
				for ($g=1;$g<=$numcols;$g++) {
					for ($b=1;$b<=$numcols;$b++) {
						$ur = dechex($r*$factor);
						$ug = dechex($g*$factor);
						$ub = dechex($b*$factor);

						if (strlen($ur) < 2)
							$ur = "0".$ur;
						if (strlen($ug) < 2)
							$ug = "0".$ug;
						if (strlen($ub) < 2)
							$ub = "0".$ub;

						$col = $ur.$ug.$ub;
						fwrite($fc, "<font color=#$col>$col</font>\n");
					}
					fwrite($fc, "</td><td>\n");
				}
				fwrite($fc, "</td></tr><tr><td>\n");
			}
			fclose($fc);
			readfile($file);
		}
	}
	echo "</td></tr></table></center>";

	echo "<blockquote>Number of colors to display:<br>\n";
	echo "<a href='color.php?numcols=5'>125</a> (tiny)<br>\n";
	echo "<a href='color.php?numcols=6'>216</a><br>\n";
	echo "<a href='color.php?numcols=7'>343</a><br>\n";
	echo "<a href='color.php?numcols=8'>512</a><br>\n";
	echo "<a href='color.php?numcols=9'>729</a><br>\n";
	echo "<a href='color.php?numcols=10'>1000</a> (recommended)<br>\n";
	echo "<a href='color.php?numcols=11'>1331</a><br>\n";
	echo "<a href='color.php?numcols=12'>1728</a><br>\n";
	echo "<a href='color.php?numcols=13'>2197</a><br>\n";
	echo "<a href='color.php?numcols=14'>2744</a><br>\n";
	echo "<a href='color.php?numcols=15'>3375</a><br>\n";
	echo "<a href='color.php?numcols=16'>4096</a> (150k)<br>\n</blockquote>\n";
	echo "</body></html>";
