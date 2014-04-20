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
$locationid = filter_input(INPUT_POST, 'locationid');

if (!isset($_SESSION['userid']) ||
        empty($_SESSION['userid']) || 
        !isset($_SESSION['email']) ||
        empty($_SESSION['email'])) {
    $response['message'] = 'You must be logged in to do this!';
    echo json_encode($response);
    return false;
}

if ((!isset($locationid) || empty($locationid))) {
    $response['message'] = 'Missing data!';
    echo json_encode($response);
    return false;
}

$sql = "DELETE FROM locations WHERE id='".$locationid."' AND user_id='".$_SESSION['userid']."'";
$result = $mysqli->query($sql);

if (!$result) {
    $response['message'] = 'Error deleting location record from database. Please contact an administrator.';
    echo json_encode($response);
    return false;
}

//Generate the HTML to populate the locations list
$locationsHTML = array();

$sql = "SELECT * FROM locations WHERE user_id = '".$_SESSION['userid']."'";
$results = $mysqli->query($sql);

if ($results->num_rows > 0) {
    while ($row = $results->fetch_assoc()) {
        $locationsHTML []= "<li id=\"location-".$row['id']."\">";
        $locationsHTML []= "    <span title=\"".$row['address']."\">".$row['name']."</span>";
        $locationsHTML []= "    <img src=\"pix/delete.png\" class=\"deleteLocation\" />";
        $locationsHTML []= "</li>";
    }
} else {
    $locationsHTML []= "You haven't saved any locations yet. You can add these using the form below.";
}

$locationsHTML = implode('', $locationsHTML);

$mysqli->close();

$response['success'] = 1;
$response['message'] = 'Location successfully deleted!';
$response['locationsHTML'] = $locationsHTML;

echo json_encode($response);
