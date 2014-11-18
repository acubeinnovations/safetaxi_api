<?php
// prevent execution of this page by direct call by browser
if ( !defined('CHECK_INCLUDED') ){
    exit();
}

// Mysql Configuration Constants

define('MYSQL_USERNAME', 'root');
define('MYSQL_PASSWORD', 'Mysql@Acube2');
define('MYSQL_HOST', 'localhost');
define('MYSQL_DB_NAME', 'safetaxi');

define('LOG_LOCATION',0);
define('LOG_LOCATION_AND_TRIP_DETAILS', 1);
define('NO_NEW_TRIP', 2);
define('NEW_INSTANT_TRIP', 3);
define('NEW_FUTURE_TRIP', 5);
define('CANCEL_TRIP', 7);
define('UPDATE_FUTURE_TRIP', 11);

define('TRIP_STATUS_BOOKED', 1);
define('TRIP_STATUS_ACCEPTED', 2);
define('TRIP_STATUS_ON_TRIP', 3);
define('TRIP_STATUS_COMPLETED', 4);
define('TRIP_STATUS_CANCELLED', 5);
define('TRIP_STATUS_DRIVER_CANCELLED', 6);
define('TRIP_STATUS_CUSTOMER_CANCELLED', 7);

define('DRIVER_STATUS_ACTIVE', 1);
define('DRIVER_STATUS_ENGAGED', 2);
define('DRIVER_STATUS_SUSPENDED', 3);
define('DRIVER_STATUS_DISMISSED', 4);

?>
