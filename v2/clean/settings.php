<?php
// This file is part of the Project JJ PHP Chat distribution.
// Created and maintained by Tino Didriksen <tino@didriksen.cc>
// Licensed under the GPL. Read license.txt for more information.
$ctitle = "Unnamed";
$respeed = "22";
$servcol = "ffffff";
$identlenght = "5";
$logsize = "7168";
$maxlines = "40";
$timeout = "900";
$userlistspeed = "90";
$logofile = "";
$logolink = "../";
$musiclink = "../";
$pimgx = "75";
$pimgy = "100";
$noname = "Noname";
$lastpos = "2";
$identxtsize = "-1";
$regident = "*";
$oocident = "'";
$modident = "";
$adminident = "º";
$s_link = "aaaaaa";
$s_active = "555555";
$s_visit = "bbbbbb";
$s_bgimg = "../gfx/null.gif";
$c_bgimg = "../gfx/null.gif";
$u_bgimg = "../gfx/null.gif";
$max_nick = "40";
$max_link = "512";
$max_image = "512";
$dbodytag = "<body topmargin='0' leftmargin='0' marginwidth='0' marginheight='0' text='#".$servcol."' link='#".$s_link."' vlink='#".$s_visit."' alink='#".$s_active."' bgcolor='#".$s_bgcol."'";
$bodytag = $dbodytag;
$ubodytag = $dbodytag;
$cbodytag = $dbodytag;
if ($s_bgimg != "") {
	$bodytag .= " background='".$s_bgimg."'";
}
$bodytag .= ">";
if ($u_bgimg != "") {
	$ubodytag .= " background='".$u_bgimg."'";
}
$ubodytag .= ">";
if ($c_bgimg != "") {
	$cbodytag .= " background='".$c_bgimg."'";
}
$cbodytag .= ">";
