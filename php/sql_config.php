<?php
define('DB_SERVER', '192.168.56.116');
define('DB_USERNAME', 'www');
define('DB_PASSWORD', 'P@ssw0rd');
define('DB_NAME', 'test');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}