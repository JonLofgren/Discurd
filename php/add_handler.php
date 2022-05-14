<?php

//require_once 'sql_config.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $return_data = 0;
    $return_data = $_POST['username'];


    echo $return_data;
}