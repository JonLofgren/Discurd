<?php

session_start();


if(isset($_SESSION['username'])){

    require_once 'sql_config.php';

    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
        if($_SERVER["HTTP_REFERER"] == 'http://'.$_SERVER['HTTP_HOST'].'/Discurd/messaging.html') {
            $user_id = $_SESSION['id'];
            if (isset($_POST['load']) && (int)$_POST['load']){
                $prepare_result = [];
                $sql = "SELECT username,id FROM (SELECT u.username,dm.date,u.id FROM dm LEFT JOIN users u ON dm.id_1=u.id WHERE id_2 = '$user_id' UNION SELECT u.username,dm.date,u.id FROM dm LEFT JOIN users u ON dm.id_2=u.id WHERE id_1 = '$user_id') a ORDER BY date DESC;";
                $result = mysqli_query($link, $sql);
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)){
                        $prepare_result[$row['id']] = $row['username'];
                    }
                    $prepare_result[0] = $_SESSION['username'];
                    $return_data = json_encode($prepare_result);
                } else {
                    //prompt to add friends
                }
            } else if (isset($_POST['id']) && isset($_POST['get_message'])) {
                $prepare_result = [];
                $sql ="SELECT m.message, m.date, u1.username AS s, u2.username AS r FROM sent_messages sm INNER JOIN messages m ON sm.m_id=m.m_id JOIN users u1 ON sm.s_id=u1.id JOIN users u2 ON sm.r_id=u2.id WHERE (sm.r_id='$user_id' OR sm.s_id='$user_id') AND (sm.r_id='".$_POST['id']."' OR sm.s_id='".$_POST['id']."');";
                $result = mysqli_query($link, $sql);
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)) {
                            $datetime = explode(" ", $row['date']);
                            $date = $datetime[0];
                            $time = $datetime[1];
                            if (isset($prepare_result[$date])) {
                                $prepare_result[$date][0][] = $row['s'];
                                $prepare_result[$date][1][] = $row['message'];
                                $prepare_result[$date][2][] = $time;
                            } else{
                        $prepare_result[$date] = [[$row['s']], [$row['message']], [$time]];
                        }
                    }
                    $return_data = json_encode($prepare_result);
                }

            } else if (isset($_POST['message'])){
                $return_data = trim($_POST['message']);
            }

        }
        echo $return_data;
    }
} else {
    header('Location: ../login.html');
}

