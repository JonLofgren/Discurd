<?php

session_start();


if(isset($_SESSION['username'])){

    require_once 'sql_config.php';

    if ($_SERVER["REQUEST_METHOD"] == 'POST') {
        if($_SERVER["HTTP_REFERER"] == 'http://'.$_SERVER['HTTP_HOST'].'/Discurd/messaging.html') {
            $user_id = $_SESSION['id'];
            if (isset($_POST['update']) && (int)$_POST['update']){
                $sql = "SELECT id_1,id_2 FROM dm WHERE (id_1 = '$user_id' OR id_2 = '$user_id') AND date BETWEEN (NOW() - INTERVAL 1 SECOND) AND NOW();";
                $result = mysqli_query($link, $sql);
                if(mysqli_num_rows($result) > 0){
                    while($row = mysqli_fetch_assoc($result)){
                        if ($row['id_1'] === $_SESSION['id']) {
                            $prepare_result[] = $row['id_2'];
                        } elseif ($row['id_2'] === $_SESSION['id']) {
                            $prepare_result[] = $row['id_1'];
                        }
                    }
                    $return_data = json_encode($prepare_result);
                } else{
                    $return_data = 0;
                }
            } elseif (isset($_POST['load']) && (int)$_POST['load']){
                $prepare_result = [];
                $sql = "SELECT username,id FROM (SELECT u.username,dm.date,u.id FROM dm LEFT JOIN users u ON dm.id_1=u.id WHERE id_2 = '$user_id' UNION SELECT u.username,dm.date,u.id FROM dm LEFT JOIN users u ON dm.id_2=u.id WHERE id_1 = '$user_id') a ORDER BY date DESC;";
                $result = mysqli_query($link, $sql);
                if(mysqli_num_rows($result) > 0){
                    $prepare_result[] = $_SESSION['username'];
                    while($row = mysqli_fetch_assoc($result)){
                        $prepare_result[] = [$row['id'], $row['username']];
                    }
                    $return_data = json_encode($prepare_result);
                } else {
                    $return_data = json_encode([$_SESSION['username']]);
                }
            } elseif (isset($_POST['id']) && isset($_POST['get_message']) && isset($_POST['tz']) && isset($_POST['last'])) {
                $prepare_result = [];

                $sql ="SELECT m.message, m.date, u1.username AS s, u2.username AS r FROM sent_messages sm INNER JOIN messages m ON sm.m_id=m.m_id JOIN users u1 ON sm.s_id=u1.id JOIN users u2 ON sm.r_id=u2.id WHERE (sm.r_id='$user_id' OR sm.s_id='$user_id') AND (sm.r_id='".$_POST['id']."' OR sm.s_id='".$_POST['id']."') ";
                if ($_POST['last'] === "0"){
                    $sql .= "ORDER BY date;";
                } else {
                    $sql .= "AND date between (NOW() - INTERVAL 1 SECOND) AND NOW() ORDER BY date;";
                }
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
                                $prepare_result[$date][1][] = base64_decode($row['message']);
                                $prepare_result[$date][2][] = $time;
                            } else {
                                $prepare_result[$date] = [[$row['s']], [base64_decode($row['message'])], [$time]];
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

            } elseif (isset($_POST['message']) && isset($_SESSION['id']) && isset($_POST['to'])) {
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
                            $message = base64_encode($message);
                            $sql = "INSERT INTO messages VALUES ('$next', '$message', NOW());";
                            mysqli_query($link, $sql);

                            $sql = "INSERT INTO sent_messages (s_id, r_id, g_id, m_id) VALUES ('$from', '$to', null, '$next');";
                            mysqli_query($link, $sql);
                        }
                    }
                }
                $return_data = 1;
            } elseif (isset($_POST['user'])) {
                $user = filter_var($_POST['user'], FILTER_SANITIZE_SPECIAL_CHARS);
                $id = $_SESSION['id'];

                $sql = "SELECT id FROM users WHERE username = '$user';";
                $result = mysqli_query($link, $sql);
                if (mysqli_num_rows($result) > 0) {
                    $f_id = mysqli_fetch_assoc($result)['id'];
                    $sql = "SELECT date FROM dm WHERE (id_1 = '$f_id' AND id_2 = '$id') OR (id_1 = '$id' AND id_2 = '$f_id');";
                    $result = mysqli_query($link, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        $return_data = 0;
                    } else {
                        $return_data = $f_id;
                        if ($f_id > $id) {
                            $sql = "INSERT INTO dm VALUES ('$id', '$f_id', NOW());";
                            mysqli_query($link, $sql);
                        } elseif ($f_id < $id){
                            $sql = "INSERT INTO dm VALUES ('$f_id', '$id', NOW());";
                            mysqli_query($link, $sql);
                        } else {
                            $return_data = -2;
                        }
                    }
                } else {
                    $return_data = -1;
                }
            } elseif ($_POST['removeConvo'] && isset($_POST['id']) && is_numeric($_POST['id'])){
                $id2 = $_POST['id'];
                $sql = "DELETE FROM dm WHERE (id_1 = '$user_id' AND id_2 = '$id2') OR (id_1 = '$user_id' AND id_2 = '$id2');";
                mysqli_query($link, $sql);
                $return_data = 1;
            }
            echo $return_data;
        }
    }
} else {
    echo -1;
}

