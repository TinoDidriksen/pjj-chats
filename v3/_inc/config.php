<?php

$GLOBALS['sql_prefix']			= 'chatv3.pjj_';

/*
 The point of this variable is to eventually support multiple
 pooling engines, and a plain PostgreSQL engine.
 Available engines:
 sqlrelay		A pooling/load-balancing engine based on SQLRelay
 postgresql		Normal PostgreSQL without any fancy bells or whistles.
 plain			Same as postgresql
*/
$GLOBALS['sql_engine']			= 'sqlrelay';

/*
 These may have different meanings or be entirely unused depending
 on which SQL engine is used.
 For the plain PostgreSQL engine, if socket is !empty()
 it will try to connect without hostname or port.
*/
/*
// SQLRelay
$GLOBALS['sql_engine']			= 'sqlrelay';
$GLOBALS['sql_hostname']		= 'localhost';
$GLOBALS['sql_port']			= 9100;
$GLOBALS['sql_socket']			= '/tmp/chatv3.socket';
$GLOBALS['sql_username']		= 'chatv3';
$GLOBALS['sql_password']		= 'chatv3';
$GLOBALS['sql_database']		= 'projectjj';
$GLOBALS['sql_retrytime']		= 0;
$GLOBALS['sql_maxtries']		= 2;
$GLOBALS['sql_persistent']		= true;
/*/
// PostgreSQL
$GLOBALS['sql_engine']			= 'postgresql';
$GLOBALS['sql_socket']			= true;
$GLOBALS['sql_username']		= 'projectjj';
$GLOBALS['sql_password']		= '';
$GLOBALS['sql_database']		= 'projectjj';
$GLOBALS['sql_persistent']		= true;
//*/

$GLOBALS['chat_min_handle']		= 10;
$GLOBALS['chat_max_handle']		= 250;
$GLOBALS['chat_max_lines']		= 250;
$GLOBALS['chat_min_lines']		= 10;
$GLOBALS['chat_postlen_max']	= 8192;
$GLOBALS['chat_postlen_min']	= 100;
$GLOBALS['chat_cookie_name']	= 'pJJv3';
$GLOBALS['chat_cookie_timeout']	= 28; // Days
$GLOBALS['chat_session_name']	= 'ChatID';
$GLOBALS['chat_session_life']	= 7200; // Seconds
$GLOBALS['chat_master_email']	= 'chats@projectjj.com';
$GLOBALS['chat_master_name']	= 'Project JJ Chats';
$GLOBALS['chatlist_postlimit']	= 180; // Minutes
$GLOBALS['chatlist_viewlimit']	= 300; // Seconds
$GLOBALS['stats_days']			= 28; // Days
$GLOBALS['new_chat_limit']		= 7; // Days
$GLOBALS['image_max_size']		= (100*1024); // byte
$GLOBALS['menu_cutoff_at']		= 15;

$GLOBALS['defaults']['title'] = 'Unnamed';
$GLOBALS['defaults']['description'] = '-';

/*
 There are also several SHM APIs available to offload the SQL database
 and cache other data.
 If a real SHM API is not available, this is where the internal
 faked SHM cache will save cached values.
 Available engines are:
 auto			Will autodetect and fall back on 'fake'
 eaccelerator	Use eAccelerator
 mmcache		Use Turck MMCache
 file			Use the file API, which relies on files and file locking...
 fake			Same as 'file'
 none			Turns off caching. This will hammer the SQL db with queries.
 				Using 'none' will also prevent proper locking, so don't use
				this except for debug purposes.
*/
$GLOBALS['shm_engine']			= 'auto';
$GLOBALS['shm_dir']				= '/www/php/mmcache/';
