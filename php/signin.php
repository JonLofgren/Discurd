<?php

require_once 'sql_config.php';

if ($_SERVER["REQUEST_METHOD"] == 'POST') {
    //                              HTTPS!!!!!!!
    if($_SERVER["HTTP_REFERER"] == 'http://'.$_SERVER['HTTP_HOST'].'/Discurd/login.html'){
        session_start();
        session_destroy();
        $return_data = 0;
        $username = $_POST['username'];

        $stmt = $link->prepare("SELECT username,password,id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if (mysqli_num_rows($result) > 0){
            while($row = mysqli_fetch_assoc($result)){
                if (password_verify(trim($_POST["password"]), $row['password'])){
                    $return_data = 1;
                    session_start();
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['id'] = (int)$row['id'];
                }
            }
        }
        echo $return_data;
    }
}
