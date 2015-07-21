<?php

function AddFaction($chatpath, $new_name) {
	global $handler;

	$new_name = eregi_replace("([^()-/´`~={}><*:#&[:alnum:][:space:]]+)", "", $new_name);

	$result = @mysql_query("SELECT name FROM uo_chat_faction WHERE name='$new_name' AND chat='$chatpath'", $handler);
	$row = @mysql_fetch_row($result);
	@mysql_free_result($result);

	if ($row[0] != $new_name) {
		@mysql_query("INSERT INTO uo_chat_faction (chat,name) VALUES ('$chatpath','$new_name')", $handler);
		echo "<br>Faction '$new_name' created.<p>";
	} else {
		echo "<br>Faction named '$new_name' already exists.<p>";
	}
}

function RenameFaction($chatpath, $old_id, $new_name) {
	global $handler;

	$old_id += 0;

	$new_name = eregi_replace("([^()-/´`~={}><*:#&[:alnum:][:space:]]+)", "", $new_name);

	$result = @mysql_query("SELECT name FROM uo_chat_faction WHERE name='$new_name' AND chat='$chatpath'", $handler);
	$row = @mysql_fetch_row($result);
	@mysql_free_result($result);

	if ($row[0] != $new_name) {
		@mysql_query("UPDATE uo_chat_faction SET name='$new_name' WHERE id='$old_id' AND chat='$chatpath'", $handler);
		echo "<br>Faction $old_id renamed to '$new_name'.<p>";
	} else {
		echo "<br>Faction named '$new_name' already exists.<p>";
	}
}

function DeleteFaction($chatpath, $old_id) {
	global $handler;

	$old_id += 0;

	@mysql_query("DELETE FROM uo_chat_faction WHERE id='$old_id' AND chat='$chatpath'", $handler);
	@mysql_query("UPDATE uo_chat_database SET faction='0' WHERE faction='$old_id' AND chat='$chatpath'", $handler);
	echo "<br>Faction '$old_id' deleted.<p>";
}

function ListFactions($chatpath) {
	global $handler;

	$result = @mysql_query("SELECT name,id FROM uo_chat_faction WHERE chat='$chatpath' ORDER BY id ASC", $handler);
	echo "<table border=0 cellspacing=1 cellpadding=3 bgcolor=#000000>";
	echo "<tr bgcolor=#ffffff><td colspan=2><b>Valid Factions</b></td></tr>";
	echo "<tr bgcolor=#eeeeee><td>Name</td><td>Id #</td></tr>";
	echo "<tr bgcolor=#ffffff><td>No Faction</td><td>0</td></tr>";
	while($row = @mysql_fetch_row($result)) {
		echo "<tr bgcolor=#ffffff><td>$row[0]</td><td>$row[1]</td></tr>";
	}
	@mysql_free_result($result);
	echo "</table>";
}

function GetFactionNames($chatpath) {
	global $handler;

	$result = @mysql_query("SELECT id,name FROM uo_chat_faction WHERE chat='$chatpath'", $handler);
	$arr = array("No Faction");
	while($row = @mysql_fetch_row($result)) {
		$arr[$row[0]+0] = $row[1];
	}
	@mysql_free_result($result);

	return $arr;
}

function GetFactionIcon($chatpath, $faction) {
	global $handler;

	$faction = intval($faction);

	$result = count_mysql_query("SELECT icon FROM uo_chat_faction WHERE id={$faction} AND chat='$chatpath'", $handler);
	if (mysql_num_rows($result) && $row = mysql_fetch_row($result)) {
		return $row[0];
	}
	mysql_free_result($result);

	return NULL;
}
