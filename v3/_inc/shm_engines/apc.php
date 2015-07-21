<?php

$GLOBALS['APC_SEMAPHORE_RESOURCES'] = array();

function MMC_Lock($name) {
	if (false && function_exists('sem_get') && function_exists('sem_acquire')) {
		$semid = sem_get(crc32(MMC_Name($name)), 1, 0644 | IPC_CREAT, true);
		$GLOBALS['APC_SEMAPHORE_RESOURCES'][MMC_Name($name)] = $semid;
		return sem_acquire($semid);
	}
}

function MMC_Unlock($name) {
	if (false && function_exists('sem_release')) {
		return sem_release($GLOBALS['APC_SEMAPHORE_RESOURCES'][MMC_Name($name)]);
	}
}

function MMC_Set($name, $data, $ttl=3600) {
	return apc_store(MMC_Name($name), serialize($data), $ttl);
}

function MMC_Unset($name) {
	return apc_delete(MMC_Name($name));
}

function MMC_Get($name) {
	$data = apc_fetch(MMC_Name($name));
	return (!empty($data)) ? unserialize($data) : NULL;
}
