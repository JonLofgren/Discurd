<?php

//require_once 'sql_config.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $return_data = [];
    $return_data[0] = $_POST['message'];
//use trim to get rid of \n

    echo json_encode($return_data);
}