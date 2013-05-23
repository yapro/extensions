<?php

/* Search & Replace All Databases + Fields + Columns
*
* Written by Kris Law for KASL Network
* October 21, 2012
*
* This script will search and replace specified strings from
* all columns from all tables from all databases.
*
* USE AT YOUR OWN RISK!
*/

// Database Credentials
$test_db='db.host.com';
$db_user='myDb_us3r';
$db_pass='myDb_p@ssw0rd';

// Search & Replace
$search = 'the quick brown fox jumped over the lazy dog'; // term to have replaced
$replace = 'the lazy dog was quicker and blocked the quick brown fox'; // string to put in place

// Open DB Connection
$link = mysql_connect($test_db, $db_user, $db_pass);

### Show list of databases
$query_string = "SHOW databases";
$query = mysql_query($query_string);

echo "Running Search & Replace on Databases. This will take a while..." . PHP_EOL . PHP_EOL;

while($array = mysql_fetch_array($query)){

  $database = $array['Database'];
  $q2 = mysql_query("show tables in $database");

  if($database != 'information_schema' && $database != 'mysql' && $database != 'test'){

    while($array2 = mysql_fetch_array($q2)){

      $tables = $array2['Tables_in_'. $database];

      $q3 = mysql_query("show columns in $database.$tables");

      while($array3 = mysql_fetch_array($q3)){

        $column = $array3['Field'];

        $replace_query = "update $database.$tables set $column = replace($column, '$search','$replace')";
        $replace = mysql_query($replace_query) or die(mysql_error());

        echo "Searching $database.$tables->$column" . PHP_EOL;

      }

    }
  }

}

echo PHP_EOL . "All instances of $search have been replaced with $replace!";

?>
