<?php

require_once 'sql_config.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    $return_data = 0;
    //                              HTTPS!!!!!!!
    if($_SERVER["HTTP_REFERER"] == 'http://'.$_SERVER['HTTP_HOST'].'/Discurd/signup.html'){
        $username = trim($_POST["username"]);
        if (strlen($username) > 3 && strlen(trim($_POST["password"])) > 6) {
            $password = password_hash(trim($_POST["password"]), PASSWORD_DEFAULT);

            $stmt = $link->prepare("SELECT username FROM users WHERE username LIKE ?");
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if (mysqli_num_rows($result) < 1) {
                $stmt = $link->prepare("INSERT INTO users (username, password) VALUES (?,?)");
                $stmt->bind_param('ss', $username, $password);

                if ($stmt->execute()) {
                    $return_data = 1;
                }
            }
        }
        echo $return_data;
    }
}