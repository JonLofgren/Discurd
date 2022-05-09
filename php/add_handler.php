<?php

//require_once 'sql_config.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $return_data = [];
    $return_data[0] = $_POST['username'];


    echo json_encode($return_data);
}