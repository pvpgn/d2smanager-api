<?php

$config_data = array
(

/* API OPTIONS */
	
	// you must provide this key in the service control panel
	'access_key' => 'CHANGE_IT',
	
	// ip allowed to use this api, put our service ip here
	'allow_ip' => '127.0.0.1',


/* PVPGN DATABASE OPTIONS */

	// database pdo type: mysql, pgsql, odbc, sqlite
	//  http://www.php.net/manual/en/pdo.drivers.php
	'db_type' => 'mysql',
	
	// database host (not needed for odbc and sqlite)
	'db_host' => '127.0.0.1',
	
	// database login (not needed for sqlite)
	'db_user' => 'root', 
	
	// database password (not needed for sqlite)
	'db_pass' => '',
	
	// pvpgn database name
	'db_name' => 'bnet',
	
	// pvpgn table prefix
	'table_prefix' => 'pvpgn_',
	
	
/* D2GS TELNET OPTIONS */

	// telnet host
	'telnet_host' => '127.0.0.1',
	
	// telnet port, default 8888
	'telnet_port' => '8888',
	
	// telnet password
	'telnet_pass' => 'abcd123',
	
	
/* D2DBS OPTIONS 
  (script must have read+write permissions to the directories declared below) */

	// full path to var/charsave directory
	'charsave_path' => 'M:\SERVERS\D2GS\multu_realm_test\d2server_1\var\charsave',

	// full path to var/charinfo directory
	'charinfo_path' => 'M:\SERVERS\D2GS\multu_realm_test\d2server_1\var\charinfo',
	

);




