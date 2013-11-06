<?php
require_once('../lib/police.php');

$POLICE = new PoliceUK();

if (isset($_POST['lat']) && isset($_POST['lng'])) {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    
    $crimes = $POLICE->crime_locate($lat, $lng);
    echo json_encode($crimes);
}
?>
