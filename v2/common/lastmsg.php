<?php
// This file is part of the Project JJ PHP Chat distribution.
// Created and maintained by Tino Didriksen <td@projectjj.com>
// The contents of this file is subject to a license.
// Read license.txt and readme.txt for more information.

function SetLastMsg($path='') {
	global $handler;

	if (empty($GLOBALS['biglog']['real_id'])) {
		return 0;
	}

	$query = "UPDATE chatv2.chats SET utime=".time()."::int4::abstime WHERE chat_id=".$GLOBALS['biglog']['real_id'];
	$GLOBALS['sql']->begin();
	if ($GLOBALS['sql']->query($query) === false) {
		$GLOBALS['sql']->rollback();
	} else {
		$GLOBALS['sql']->commit();
	}

	return time();
}

function GetLastMsg($path='') {
	global $handler;

	if (empty($GLOBALS['biglog']['real_id'])) {
		return 0;
	}

	$query = "SELECT EXTRACT(EPOCH FROM utime) as lastpost FROM chatv2.chats WHERE chat_id=".$GLOBALS['biglog']['real_id'];
	$result = $GLOBALS['sql']->query($query);
	if ($result !== false) {
		$row = $GLOBALS['sql']->fetchAssoc($result);
		$GLOBALS['sql']->freeResult($result);
		return $row['lastpost'];
	}

	return $GLOBALS['biglog']['real_id'];
}
