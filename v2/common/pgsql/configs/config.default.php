<?php

/**
 * These may have different meanings or be entirely unused depending
 * on which SQL engine is used.
 * For the plain PostgreSQL engine, if socket is !empty()
 * it will try to connect without hostname or port.
 */

// PostgreSQL
$GLOBALS['sql_hostname']    = 'wms.projectjj.com';
$GLOBALS['sql_port']        = 5432;
$GLOBALS['sql_socket']      = false;
$GLOBALS['sql_username']    = 'wms';
$GLOBALS['sql_password']    = '';
$GLOBALS['sql_database']    = 'wms';
$GLOBALS['sql_persistent']  = true;

$GLOBALS['sql_prefix'] = '';

$GLOBALS['sql_serials'] = array();
$GLOBALS['sql_serials']['logs.errors']          = $GLOBALS['sql_prefix'].'logs.errors_error_id_seq';
$GLOBALS['sql_serials']['i18n.texts']           = $GLOBALS['sql_prefix'].'i18n.texts_text_id_seq';
$GLOBALS['sql_serials']['i18n.languages']       = $GLOBALS['sql_prefix'].'i18n.languages_language_id_seq';
$GLOBALS['sql_serials']['product.aspects']      = $GLOBALS['sql_prefix'].'product.aspects_aspect_id_seq';
$GLOBALS['sql_serials']['product.bases']        = $GLOBALS['sql_prefix'].'product.bases_base_id_seq';
$GLOBALS['sql_serials']['product.properties']   = $GLOBALS['sql_prefix'].'product.properties_property_id_seq';
$GLOBALS['sql_serials']['product.variations']   = $GLOBALS['sql_prefix'].'product.variations_variation_id_seq';

/**
 * There are also several SHM APIs available to offload the SQL database
 * and cache other data.
 *
 * Available engines are:
 * auto			Will autodetect
 * eaccelerator	forces eAccelerator
 * mmcache		forces Turck MMCache
 */
$GLOBALS['shm_engine']  = 'auto';

/**
 * This should be a key that is unique to this particular installation.
 * I figure that the current path should satisfy that nicely, but
 * in case anyone wants to control it themselves, go ahead.
 */
$GLOBALS['shm_key']     = md5(__FILE__);

/**
 * Set this to true for output on screen
 */
$GLOBALS['error']['debug'] = true;

/**
 * Set this to false for full backtracing. Simple backtracing is usually enough.
 * Full backtracing is always saved in the log.
 */
$GLOBALS['error']['simple'] = true;

/**
 * Commaseperated list of the emails that should be notified upon critical errors
 */
$GLOBALS['error']['emails'] = "forexs@lazy.dk";
