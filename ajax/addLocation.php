<?php

require_once('../config.php');

if (!isset($_SESSION)) {
    session_start();
}

$response['success'] = 0;
$response['message'] = '';
$response['locationsHTML'] = '';

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

if ($mysqli->connect_errno) {
    $response['message'] = "ERROR: Database connection failed! ".$mysqli->connect_errno;
    echo json_encode($response);
    return false;
}

//Validation against empty values and no login
$name = filter_input(INPUT_POST, 'name');
$address = filter_input(INPUT_POST, 'address');

if (!isset($_SESSION['userid']) ||
        empty($_SESSION['userid']) || 
        !isset($_SESSION['email']) ||
        empty($_SESSION['email'])) {
    $response['message'] = 'ERROR: You must be logged in to do this!';
    echo json_encode($response);
    return false;
}

if ((!isset($name) || empty($name)) ||
        (!isset($address) || empty($address))) {
    $response['message'] = 'ERROR: Missing location name and/or address data!';
    echo json_encode($response);
    return false;
}

$sql = "INSERT INTO locations (user_id, name, address) VALUES ('".$_SESSION['userid']."','".$name."','".$address."')";
$result = $mysqli->query($sql);

if (!$result) {
    $response['message'] = 'ERROR: Could not add location record to database. Please contact an administrator.';
    echo json_encode($response);
    return false;
}

//Generate the HTML to populate the locations list
$sql = "SELECT * FROM locations WHERE user_id = '".$_SESSION['userid']."'";
$results = $mysqli->query($sql);

$locationsHTML = "";

while ($row = $results->fetch_assoc()) {
    $locationsHTML .= "<li id=\"location-".$row['id']."\">";
    $locationsHTML .= "    <span title=\"".$row['address']."\">".$row['name']."</span>";
    $locationsHTML .= "    <img src=\"pix/delete.png\" class=\"deleteLocation\" />";
    $locationsHTML .= "</li>";
}

$mysqli->close();

$response['success'] = 1;
$response['message'] = 'Location successfully added!';
$response['locationsHTML'] = $locationsHTML;

echo json_encode($response);