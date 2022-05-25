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
                    $prepare_result[] = $_SESSION['username'];
                    while($row = mysqli_fetch_assoc($result)){
                        $prepare_result[] = [$row['id'], $row['username']];
                    }
                    $return_data = json_encode($prepare_result);
                }
            } else if (isset($_POST['id']) && isset($_POST['get_message'])) {
                $prepare_result = [];
                $sql ="SELECT m.message, m.date, u1.username AS s, u2.username AS r FROM sent_messages sm INNER JOIN messages m ON sm.m_id=m.m_id JOIN users u1 ON sm.s_id=u1.id JOIN users u2 ON sm.r_id=u2.id WHERE (sm.r_id='$user_id' OR sm.s_id='$user_id') AND (sm.r_id='".$_POST['id']."' OR sm.s_id='".$_POST['id']."');";
                $result = mysqli_query($link, $sql);
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)) {
                            $dt = $row['date'];
                            $newdt = ((new DateTime($dt))->setTimezone(new DateTimeZone($_POST['tz'])))->format("Y-m-d H:i:s");

                            $datetime = explode(" ", $newdt);
                            $date = $datetime[0];
                            $time = $datetime[1];
                            if (isset($prepare_result[$date])) {
                                $prepare_result[$date][0][] = $row['s'];
                                $prepare_result[$date][1][] = $row['message'];
                                $prepare_result[$date][2][] = $time;
                            } else {
                                $prepare_result[$date] = [[$row['s']], [$row['message']], [$time]];
                            }

                        }
                } else {
                    $return_data = 0;
                }
                $sql = "SELECT username FROM users WHERE id = '".$_POST['id']."';";
                $result = mysqli_query($link, $sql);
                if(mysqli_num_rows($result) > 0) {
                    $prepare_result[$_POST['id']] = mysqli_fetch_assoc($result);
                }
                $return_data = json_encode($prepare_result);

            } else if (isset($_POST['message']) && isset($_SESSION['id']) && isset($_POST['to'])) {
                $message = trim($_POST['message']);
                $from = $_SESSION['id'];
                $to = $_POST['to'];

                if (strlen($message) <= 250) {
                    if ($to > $from) {
                        $sql = "UPDATE dm SET date = NOW() WHERE id_1 = '$from' AND id_2 = '$to';";
                        mysqli_query($link, $sql);
                    } elseif ($to < $from) {
                        $sql = "UPDATE dm SET date = NOW() WHERE id_1 = '$to' AND id_2 = '$from';";
                        mysqli_query($link, $sql);
                    }

                    $sql = "SELECT m_id FROM sent_messages ORDER BY m_id DESC LIMIT 1;";
                    $result = mysqli_query($link, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $next = $row['m_id'] + 1;
                            $sql = "INSERT INTO messages VALUES ('$next', '$message', NOW());";
                            mysqli_query($link, $sql);

                            $sql = "INSERT INTO sent_messages (s_id, r_id, g_id, m_id) VALUES ('$from', '$to', null, '$next');";
                            mysqli_query($link, $sql);

                        }
                    }
                }
                $return_data = $message;
            }
        }
        echo $return_data;
    }
} else {
    header('Location: ../login.html');
}

