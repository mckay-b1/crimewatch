<?php
require_once('../lib/police.php');

$POLICE = new PoliceUK();

if (isset($_POST['lat']) && isset($_POST['lng'])) {
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];
    
    $new_crimes = array();
    
    $crimes = $POLICE->crime_locate($lat, $lng);
    
    $categories = $POLICE->crime_categories();
    
    $dates = $POLICE->crime_street_dates();
    
    $new_dates = array();
    
    foreach ($dates as $date) {
        $new_dates []= array($date['date'], date("F Y", strtotime($date['date'])));
    }
    
    //Filter out necessary data and store in new array
    foreach ($crimes as $crime) {
        $new_crime = new stdClass();
        
        $new_crime->crime_id = $crime['id'];
        
        //Get category nicename
        foreach ($categories as $category) {
            if ($category['url'] === $crime['category']) {
                $new_crime->category = $category['name'];
            }
        }
        
        //Modify date from 2013-09 to September 2013
        $new_crime->month = date("F Y", strtotime($crime['month']));

        $new_crime->street = lcfirst($crime['location']['street']['name']);
        
        $new_crime->latitude = $crime['location']['latitude'];
        
        $new_crime->longitude = $crime['location']['longitude'];
                
        if (isset($crime['outcome_status'])) {
            $new_crime->outcome_status = $crime['outcome_status']['category']." (as of ".date("F Y", strtotime($crime['outcome_status']['date'])).")";
        } else {
            $new_crime->outcome_status = null;
        }
        
        $new_crimes []= $new_crime;
    }
    $response = array('success'=>1, 'message'=>'Crimes successfully retrieved!', 'geometry'=>array('lat'=>$_POST['lat'], 'lng'=>$_POST['lng']), 'data'=>$new_crimes, 'dates'=>$new_dates);
} else {
    //Add error handling logic here
    $response = array('success'=>0, 'message'=>'An error occured!');
}

echo json_encode($response);
?>
