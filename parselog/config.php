<?php
require_once("dblib.php");
require_once("misc.php");

$queue_log_dir  = '/var/log/asterisk/';
$queue_log_file = 'queue_log';

$dbhost = 'localhost';
$dbname = 'qstatslite';
$dbuser = 'root';
$dbpass = '';

$midb = new dbcon($dbhost, $dbuser, $dbpass, $dbname, true);
$self = $_SERVER['PHP_SELF'];

$DB_DEBUG = false;

?>
