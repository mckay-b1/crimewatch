<?php
require_once('../lib/police.php');

$POLICE = new PoliceUK();

//Get all available crime dates from Police.uk
$dates = $POLICE->crime_street_dates();

//Convert all dates into "September 2013" format
$new_dates = array();

foreach ($dates as $date) {
    $new_dates []= array($date['date'], date("F Y", strtotime($date['date'])));
}

$response = array('success'=>1, 'message'=>'Crime dates retrieved successfully!', 'crimeDates'=>$new_dates);

echo json_encode($response);