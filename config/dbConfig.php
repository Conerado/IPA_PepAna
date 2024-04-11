<?php

global $conn;
global $mySqlConn;

// Create connection to Oracle-DB
//emptied for Commit
$dbUsername = '';
$dbPassword = '';
$dbConnectionString = '';


$conn = oci_connect($dbUsername, $dbPassword, $dbConnectionString);

if (!$conn) {
    $e = oci_error();
    trigger_error(htmlentities($e['message'], ENT_QUOTES), E_USER_ERROR);
}

// Create connection to MySQL-DB
$servername = "localhost";
$username = "root";
$password = "";
$dbName = "Pepana";


$mySqlConn = new mysqli($servername, $username, $password, $dbName);

// Check connection
if ($mySqlConn->connect_error) {
    die("Connection failed: " . $mySqlConn->connect_error);
}