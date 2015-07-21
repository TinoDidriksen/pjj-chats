<?php

function MMC_Name($name) {
	return sha1($GLOBALS['mmc_key'].$name);
}

require_once(__DIR__.'/config.php');

$GLOBALS['shm_engine'] = strtolower(trim($GLOBALS['shm_engine']));

if ($GLOBALS['shm_engine'] === 'auto' || empty($GLOBALS['shm_engine'])) {
	if (
	function_exists('eaccelerator_lock')
	&& function_exists('eaccelerator_unlock')
	&& function_exists('eaccelerator_put')
	&& function_exists('eaccelerator_rm')
	&& function_exists('eaccelerator_get')
		) {
		require_once(__DIR__.'/shm_engines/eaccelerator.php');
	}
	else if (
	function_exists('apc_store')
	&& function_exists('apc_delete')
	&& function_exists('apc_fetch')
		) {
		require_once(__DIR__.'/shm_engines/apc.php');
	}
	else if (
	function_exists('mmcache_lock')
	&& function_exists('mmcache_unlock')
	&& function_exists('mmcache_put')
	&& function_exists('mmcache_rm')
	&& function_exists('mmcache_get')
		) {
		require_once(__DIR__.'/shm_engines/mmcache.php');
	}
	else {
		require_once(__DIR__.'/shm_engines/file.php');
	}
}
else if ($GLOBALS['shm_engine'] === 'eaccelerator') {
	require_once(__DIR__.'/shm_engines/eaccelerator.php');
}
else if ($GLOBALS['shm_engine'] === 'mmcache') {
	require_once(__DIR__.'/shm_engines/mmcache.php');
}
else if ($GLOBALS['shm_engine'] === 'fake' || $GLOBALS['shm_engine'] === 'file') {
	require_once(__DIR__.'/shm_engines/file.php');
}
else if ($GLOBALS['shm_engine'] === 'none') {
	require_once(__DIR__.'/shm_engines/none.php');
}
