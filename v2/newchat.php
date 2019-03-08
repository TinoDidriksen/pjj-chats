<?php
// This file is part of the Project JJ PHP Chat distribution.
// Created and maintained by Tino Didriksen <mail@tinodidriksen.com>
// The contents of this file is subject to a license.
// Read license.txt and readme.txt for more information.

require_once(__DIR__."/mysql.php");
require_once(__DIR__."/setup.php");
$base = getcwd()."/";

//echo "$base<p>";

$nchat		= strtolower(preg_replace("~([^-[:alnum:]_]+)~i", "", trim($_REQUEST['nchat'])));
$username	= strtolower(preg_replace('~'.$master_name_filter.'~i', "", trim($_REQUEST['username'])));
$password	= strtolower(preg_replace("~([^[:alnum:]]+)~i", "", trim($_REQUEST['password'])));
$email		= strtolower($_REQUEST['email']);

if (($nchat != "") && ($nchat != "_new") && ($nchat != $master_chat)) {
	if (strlen($_REQUEST['pass']) !== 32) {
		$_REQUEST['pass'] = md5($_REQUEST['pass']);
	}
	if ((strcasecmp($master_name, $_REQUEST['login']) != 0) || (strcmp($master_password, $_REQUEST['pass']) != 0)) {
		die("Unauthorized usage. Please report this to the system admin.<p>");
	}
	ob_start();
	$oldumask = umask(0);

	if (mkdir($base.$nchat, 0700)) {
		echo mkdir($base.$nchat."/jbb", 0700)."<br>\n";
		if (mkdir($base.$nchat."/register", 0700)) {
			echo mkdir($base.$nchat."/register/wizard_locked", 0700)."<br>\n";

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
				echo copy($base."_new".$files[$cc], $base.$nchat.$files[$cc])." ".$base.$nchat.$files[$cc]."<br>";
				echo chmod($base.$nchat.$files[$cc], 0600).'<br>';
			}

			unset($files);
			$files = array(
				"/iconlist.php",
				"/settings.php",
				"/options.php"
			);

			for ($cc=0;$cc<count($files);$cc++) {
				echo copy($base."clean".$files[$cc], $base.$nchat.$files[$cc])." ".$base.$nchat.$files[$cc]."<br>";
				echo chmod($base.$nchat.$files[$cc], 0600)." ".$base.$nchat.$files[$cc]."<br>";
			}

			$fm = fopen($base."clean/settings.php", "r");
			$file = fread($fm, filesize($base."clean/settings.php"));
			fclose($fm);

			$fx = fopen($base.$nchat."/settings.php", "w");
			fwrite($fx, stripslashes($file)."\n\$cadmin = \"$email\";\n?>\n");
			fclose($fx);

			$fz = fopen($base.$nchat."/register/motd.dat", "w");
			fwrite($fz, "<center>- <a href=\"jbb/\">Board</a> -</center>");
			fclose($fz);
			chmod($base.$nchat."/register/motd.dat", 0600);

			$db_table = "uo_chat_database";
			$chatpath = "chat".$nchat;

			$username = str_replace("_", " ", $username);
			$username = strtolower(preg_replace('~'.$master_name_filter.'~i', "", $username));
			$master_name = strtolower(preg_replace('~'.$master_name_filter.'~i', "", $master_name));

			$regnotes = "Add comments here. You can for example specify what you want the applicant to write in the description field.";

			mysqli_query($handler, "INSERT INTO $db_table (chat,username,password,flags,email) VALUES ('$chatpath','$master_name','$master_password','M','$master_email')");
			mysqli_query($handler, "INSERT INTO $db_table (chat,username,password,flags,email) VALUES ('$chatpath','$username','".md5($password)."','m','$email')");

			$GLOBALS['sql']->begin();
/*
			$query = "UPDATE chatv2.chats SET savedpath=chat,chat=null WHERE chat='$nchat'";
			if ($GLOBALS['sql']->query($query) === null) {
				$GLOBALS['sql']->rollback();
				die ("Boom in update.");
			}
//*/
			$query = "INSERT INTO chatv2.chats
			(chat,utime,email,owner,ctime,regnotes,savedpath)
			VALUES ('$nchat', now(),'$email','$username', now(),'$regnotes','$nchat')";
			if ($GLOBALS['sql']->query($query) === false) {
				$GLOBALS['sql']->rollback();
				die ("Boom in insert.");
			}
			$GLOBALS['sql']->commit();

			$rez = $GLOBALS['sql']->query("SELECT chat_id FROM chatv2.chats WHERE chat='$nchat'");
			if ($rez === false) {
				die ("Boom in select chat_id");
			}
			$row = $GLOBALS['sql']->fetchAssoc($rez);
			$chat = $row['chat_id'];
			$GLOBALS['sql']->freeResult($rez);

			$tablesql = "
DROP SEQUENCE chatv2logs.log_{$chat}_message_id_seq;
CREATE SEQUENCE chatv2logs.log_{$chat}_message_id_seq
  INCREMENT 1
  MINVALUE 1
  MAXVALUE 9223372036854775807
  START 1
  CACHE 1;
ALTER TABLE chatv2logs.log_{$chat}_message_id_seq OWNER TO projectjj;

DROP TABLE chatv2logs.log_{$chat};
CREATE TABLE chatv2logs.log_{$chat}
(
  message_id int4 NOT NULL DEFAULT nextval('chatv2logs.log_{$chat}_message_id_seq'::regclass),
  message text NOT NULL,
  user_id int4 NOT NULL DEFAULT 0,
  user_ident text NOT NULL,
  user_name text NOT NULL,
  stamp timestamptz NOT NULL DEFAULT now(),
  stamp_year int4 NOT NULL,
  stamp_week int4 NOT NULL,
  CONSTRAINT log_{$chat}_pkey PRIMARY KEY (message_id)
)
WITHOUT OIDS;
DELETE FROM chatv2logs.log_{$chat};
ALTER TABLE chatv2logs.log_{$chat} OWNER TO projectjj;
ALTER SEQUENCE chatv2logs.log_{$chat}_message_id_seq RESTART WITH 1;
CREATE INDEX index_log_{$chat}_stamp
  ON chatv2logs.log_{$chat}
  (stamp);
CREATE INDEX index_log_{$chat}_stamp_week
  ON chatv2logs.log_{$chat}
  (stamp_week);
CREATE INDEX index_log_{$chat}_user_id
  ON chatv2logs.log_{$chat}
  (user_id);
CREATE INDEX index_log_{$chat}_user_name
  ON chatv2logs.log_{$chat}
  (user_name);
CREATE INDEX index_log_{$chat}_stamp_yw
  ON chatv2logs.log_{$chat}
  (stamp_year, stamp_week);
VACUUM ANALYZE chatv2logs.log_{$chat};
                ";
/*
DROP TABLE chatv2index.index_{$chat};
CREATE TABLE chatv2index.index_{$chat}
(
   word_id integer NOT NULL,
   message_id integer NOT NULL,
    PRIMARY KEY (word_id, message_id)
--    , FOREIGN KEY (word_id) REFERENCES chatv2.index_words (word_id) ON UPDATE CASCADE ON DELETE CASCADE
--    , FOREIGN KEY (message_id) REFERENCES chatv2logs.log_{$chat} (message_id) ON UPDATE CASCADE ON DELETE CASCADE
)
WITH (
  OIDS = FALSE
)
;
ALTER TABLE chatv2index.index_{$chat} OWNER TO projectjj;
//*/
			$qs = explode(';', $tablesql);
			foreach ($qs as $q) {
				$q = trim($q);
				if (!empty($q)) {
					$ret = @$GLOBALS['sql']->query($q);
				}
			}

			$cpath = "https://pjj.cc";

			$username = ucwords($username);
			$master_name = ucwords($master_name);
			$subject = "pJJ: Chat /$nchat created, ".ucwords($username);
			$message = "Login: $username\n";
			$message .= "Password: $password\n";
			$message .= "\n";
			$message .= "Chat: $cpath/$nchat/\n";
			$message .= "Controlpanel: $cpath/$nchat/register/login.php\n";
			$message .= "Application Form: $cpath/$nchat/register/regapp.php\n";
			$message .= "Settings: $cpath/$nchat/gui_set.php\n";
			$message .= "Options: $cpath/$nchat/gui_opt.php\n";
			$message .= "Icons: $cpath/$nchat/gui_icon.php\n";
			$message .= "Language: $cpath/$nchat/gui_lang.php\n";
			$message .= "\n";
			$message .= "Common Icons: $cpath/common/icon/\n";
			$message .= "Image Service: http://image.projectjj.com/\n";
			$message .= "Preferences Help: $cpath/common/help.php?man=pref\n";
			$message .= "Flags Help: $cpath/common/help.php?man=flag\n";
			$message .= "Chat Pref Help: $cpath/common/help.php?man=chat\n";
			$message .= "Portal: $cpath/\n";
			$message .= "\n";
			$message .= "It is also a good idea to look at https://plus.google.com/communities/115521193128885558520 or https://www.facebook.com/groups/pjj.chats/ to get the latest developments.\n";
			$message .= "\n";
			$message .= "-- Tino Didriksen / Project JJ\n";
			mail("$username <$email>", $subject, $message, "From: $master_name <$master_email>\nReply-To: $master_name <$master_email>\nBcc: $master_name <$master_email>\n");
		}
	}

	umask($oldumask);
	ob_end_clean();
}
else {
	echo "<form method=post action=newchat.php><br>\n";
	echo "Chat: <input type=text name=nchat value=$nchat><br>\n";
	echo "Username: <input type=text name=username value=\"$username\"><br>\n";
	echo "Password: <input type=text name=password value=$password><br>\n";
	echo "Email: <input type=text name=email value=$email><p>\n";

	echo "Login: <input type=text name=login value='$login'><br>";
	echo "Pass: <input type=password name=pass value='$pass'><br>";
	echo "<input type=submit value=Create>\n";
	echo "</form>\n";
}
