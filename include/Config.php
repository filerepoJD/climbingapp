<?php
/**
 * Database configuration
 */

define('OPENSHIFT_MYSQL_DB_USERNAME', 'adminSuCeKe3');
define('OPENSHIFT_MYSQL_DB_PASSWORD', 'jVqDkpPA3bnP');
define('OPENSHIFT_MYSQL_DB_HOST', '127.13.89.2');
define('OPENSHIFT_MYSQL_DB_PORT', '3306');
define('OPENSHIFT_GEAR_NAME', 'climbingapp');

define('DB_HOST', getenv('OPENSHIFT_MYSQL_127.13.89.2'));
define('DB_PORT', getenv('OPENSHIFT_MYSQL_3306'));
define('DB_USER', getenv('OPENSHIFT_MYSQL_adminSuCeKe3'));
define('DB_PASS', getenv('OPENSHIFT_MYSQL_jVqDkpPA3bnP'));
define('DB_NAME', getenv('OPENSHIFT_climbingapp'));





define('USER_CREATED_SUCCESSFULLY', 0);
define('USER_CREATE_FAILED', 1);
define('USERNAME_ALREADY_EXISTED', 2);
define('USER_ALREADY_EXISTED', 3);
define('EMAIL_ALREADY_EXISTED', 4);
define('CRAG_CREATED_SUCCESSFULLY', 5);
define('CRAG_CREATED_FAILED', 6);
define('CRAG_ALREADY_EXISTED', 7);
define('ROUTE_CREATED_SUCCESSFULLY', 5);
define('ROUTE_CREATED_FAILED', 6);
define('ROUTE_ALREADY_EXISTED', 7);
define('PALMARESROW_CREATED_SUCCESSFULLY', 8);
define('PALMARESROW_CREATE_FAILED', 9);


?>
