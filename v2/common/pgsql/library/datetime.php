<?php

/**
 * Fetches the time with microsecond precision.
 *
 * @return float The time with microsecond precision.
 * @access public
 */
if (!function_exists('GetMicroTime')) {
	function GetMicroTime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
}
