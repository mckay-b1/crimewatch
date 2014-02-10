<?php
if (!isset($_SESSION)) {
    session_start();
}

$logout = filter_input(INPUT_POST, 'logout');

if (isset($logout) && !empty($logout)) {
    session_destroy();
} else {
    require_once('../config.php');
    
    $response['success'] = 0;
    $response['message'] = "";

    $email = filter_input(INPUT_POST, 'email');
    $password = filter_input(INPUT_POST, 'password');

    if (!isset($email) ||
            !isset($password) ||
            empty($email) ||
            empty($password)) {
        $response['message'] = "ERROR: Login details were not recieved.";
    } else {
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

        if ($mysqli->connect_errno) {
            $response['message'] = "ERROR: Database connection failed! ".$mysqli->connect_errno;
        }

        $email = stripslashes($email);
        $password = stripslashes($password);

        $email = $mysqli->real_escape_string($email);
        $password = $mysqli->real_escape_string($password);

        $password = md5($password);

        $sql = "SELECT * FROM users WHERE email='$email' and password='$password' LIMIT 1";
        $result = $mysqli->query($sql);

        if ($row = $result->fetch_assoc()) {
            $_SESSION['userid']     = $row['id'];
            $_SESSION['email']      = $row['email'];
            $_SESSION['firstname']  = $row['firstname'];

            //Update 'last_login' for this user
            $last_login = date('Y-m-d H:i:s');
            $id = $row['id'];

            $sql = "UPDATE users SET last_login='$last_login' WHERE id=$id";
            $result = $mysqli->query($sql);

            $response['success'] = 1;
            $response['message'] = "Successfully logged in!";
        } else {
            $response['message'] = "ERROR: Invalid email or password!";
        }
    }

    echo json_encode($response);
    
    $mysqli->close();
}