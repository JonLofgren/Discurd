<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'www');
define('DB_PASSWORD', 'pp}7%dACb=j^zD*^');
define('DB_NAME', 'discurd');

$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

if($link === false) {
    die("ERROR: Could not connect. " . mysqli_connect_error());
}