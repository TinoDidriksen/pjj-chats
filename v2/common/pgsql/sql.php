<?php
// die('Upgrading PostgreSQL; try again later...');

ignore_user_abort(true);
define('E_SQL_ERROR', E_USER_WARNING, true);

require_once __DIR__.'/library/datetime.php';

$GLOBALS['output_timer'] = GetMicroTime();

require_once __DIR__.'/sql_engines/postgresql.php';
require_once __DIR__.'/config.php';

$GLOBALS['sql'] = new SQLEnginePostgres(
    $GLOBALS['sql_username'],
    $GLOBALS['sql_password'],
    $GLOBALS['sql_socket'],
    $GLOBALS['sql_hostname'],
    $GLOBALS['sql_port'],
    $GLOBALS['sql_database'],
    $GLOBALS['sql_persistent']
    );
if ($GLOBALS['sql']->connect() === false) {
    trigger_error('Could not connect to the SQL server.', E_USER_ERROR);
}
