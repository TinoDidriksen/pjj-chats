<?php
// This file is part of the Project JJ PHP Chat distribution.
// Created and maintained by Tino Didriksen <mail@tinodidriksen.com>
// The contents of this file is subject to a license.
// Read license.txt and readme.txt for more information.

	require_once("mysql.php");
	require_once("setup.php");
	$base = getcwd()."/";

	//echo "$base<p>";
	
	$rez = count_mysql_query('SELECT DISTINCT chat FROM uo_chat_last', $handler);

	$oldumask = umask(0); 
	while($row = mysqli_fetch_assoc($rez)) {
	    $nchat = mb_substr($row['chat'], 4);
	    echo $nchat."<br>\n";

    	if (($nchat != "") && ($nchat != "_new") && ($nchat != $master_chat)) {
    		
    		if (is_dir($base.$nchat) || mkdir($base.$nchat, 0777)) {
    		    if (!is_dir($base.$nchat."/jbb"))
    				echo mkdir($base.$nchat."/jbb", 0777)."<br>\n";
    
    			if (is_dir($base.$nchat."/register") || mkdir($base.$nchat."/register", 0777)) {
    			    if (!is_dir($base.$nchat."/register/wizard_locked"))
    					echo mkdir($base.$nchat."/register/wizard_locked", 0777)."<br>\n";
    
    				$files = array(
    					"/sendmsg.php",
    					"/index.php",
    					"/login.php",
    					"/reader.php",
    					"/manual.php",
    					"/gui_opt.php",
    					"/gui_set.php",
    					"/gui_icon.php",
    					"/gui_lang.php",
    					"/custom.php",
    					"/userlist.php",
    					"/register/biglist.php",
    					"/register/adminlog.php",
    					"/register/dblog.php",
    					"/register/biglog.php",
    					"/register/index.php",
    					"/register/login.php",
    					"/register/viewer.php",
    					"/register/regapp.php",
    					"/jbb/index.php"
    				);
    				
    				for ($cc=0;$cc<count($files);$cc++) {
    				    if (!file_exists($base.$nchat.$files[$cc])) {
        					echo copy($base."_new".$files[$cc], $base.$nchat.$files[$cc])." ".$base.$nchat.$files[$cc]."<br>";
        					echo chmod($base.$nchat.$files[$cc], 0666).'<br>';
    					}				
    				}
    
    				unset($files);
    				$files = array(
    					"/iconlist.php",
    					"/settings.php",
    					"/options.php"
    				);
    
    				for ($cc=0;$cc<count($files);$cc++) {
    				    if (!file_exists($base.$nchat.$files[$cc])) {
        					echo copy($base."clean".$files[$cc], $base.$nchat.$files[$cc])." ".$base.$nchat.$files[$cc]."<br>";
        					echo chmod($base.$nchat.$files[$cc], 0666)." ".$base.$nchat.$files[$cc]."<br>";
    					}				
    				}
    
    				if (!file_exists($base.$nchat."/register/motd.dat")) {
        				$fz = fopen($base.$nchat."/register/motd.dat", "w");
        				fwrite($fz, "<center>- <a href=\"jbb/\">Board</a> -</center>");
        				fclose($fz);
        				chmod($base.$nchat."/register/motd.dat", 0666);
    				}				
    
    			}
    		}
    
    	}
     }
	umask($oldumask);
