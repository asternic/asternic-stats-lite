#!/usr/bin/php -q
<?php
/*
   Copyright 2007, 2020 Nicolás Gudiño

   This file is part of Asternic Call Center Stats.

    Asternic Call Center Stats is free software: you can redistribute it 
    and/or modify it under the terms of the GNU General Public License as 
    published by the Free Software Foundation, either version 3 of the 
    License, or (at your option) any later version.

    Asternic Call Center Stats is distributed in the hope that it will be 
    useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Asternic Call Center Stats.  If not, see 
    <http://www.gnu.org/licenses/>.
*/

require_once("config.php");

error_reporting(0);

if(isset($argv[1])) {
    if($argv[1]=="purge") {
        echo "Purging tables...\n";
        $query = "DELETE FROM qname";
        $res = $midb->consulta($query);
        $query = "DELETE FROM qagent";
        $res = $midb->consulta($query);
        $query = "DELETE FROM queue_stats";
        $res = $midb->consulta($query);
        echo "Done...\n";
        exit;
    }

    if($argv[1]=="convertlocal") {
        $convertlocal=1;
    } else {
        $convertlocal=0;
    }
} else {
    $convertlocal=0;
}

// Select the most recent event saved
$query = "SELECT datetime FROM queue_stats ORDER BY datetime DESC LIMIT 1";
$res = $midb->consulta($query);
if($midb->num_rows($res)>0) {
    $row = $midb->fetch_row($res);
    $last_event_ts = return_timestamp($row[0]);
    $last_event_ts -= 10;
} else {
    $last_event_ts = 0;
}

//$last_event_ts = 0;

// Populates an array with the EVENTS ids
$query = "SELECT * FROM qevent ORDER BY event_id";
$res = $midb->consulta($query);
while($row = $midb->fetch_row($res)) {
    $event_array["$row[1]"] = $row[0];
}

$filename = "$queue_log_dir/$queue_log_file";
$dataFile = fopen( $filename, "r" );

if ( $dataFile ) {
    while (!feof($dataFile)) {
        $buffer = fgets($dataFile, 4096);
        procesa($buffer);
    }
    fclose($dataFile);
} 
else {
    die( "fopen failed for $filename" ) ;
}

?>
