<?php

require_once 'sql_config.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $return_data = 0;
    //                              HTTPS!!!!!!!
    if($_SERVER["HTTP_REFERER"] == 'http://'.$_SERVER['HTTP_HOST'].'/Discurd/signup.html'){
        $username = trim($_POST["username"]);
        if (strlen($username) > 3 && strlen(trim($_POST["password"])) > 6) {
            $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);
            $sql = "SELECT username FROM users WHERE username LIKE '$username';";
            $result = mysqli_query($link, $sql);
            if (mysqli_num_rows($result) < 1) {
                $sql = "INSERT INTO users (username, password) VALUES ('$username','$password');";
                if (mysqli_query($link, $sql)) {
                    $return_data = 1;
                }
            }
        }
        echo $return_data;
    }
}