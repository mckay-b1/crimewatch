<?php
require_once('../lib/myPolice.php');

$POLICE = new myPoliceUK();

$lat = filter_input(INPUT_POST, 'lat');
$lng = filter_input(INPUT_POST, 'lng');
$crimeDate = filter_input(INPUT_POST, 'crimeDate');

//Ensure lat/lng values have been posted as these are crucial to the functionality of this script
if ((isset($lat) && !empty($lat)) &&
        (isset($lng) && !empty($lng))) {
    
    if (!isset($crimeDate) || empty($crimeDate)) {
        //Retrieve and convert latest crime data date to 'YYYY-MM' format
        $crimeDate = date("Y-m", strtotime($POLICE->lastupdated()));
    }
    
    $response = array(
        'success'=>0,
        'message'=>''
    );
    
    $crimeData = array();
    $forceData = array();
    $categoryData = array();
    
    //Get the crimes from Police.uk server
    $crimes = $POLICE->crimes_at_location($lat, $lng, $crimeDate);
    
    if (count($crimes) > 0) {
        //Filter out necessary data, reformat and store in new array
        foreach ($crimes as $crime) {
            //Crime category/type filter - skip this crime if it a filter has been set and it's category doesn't match the filter
            $new_crime = new stdClass();

            $new_crime->crime_id = $crime['id'];

            //Get all available categories from Police.uk server
            $categories = $POLICE->crime_categories($crimeDate);

            $new_crime->category = $categories[$crime['category']];

            //Modify date from 2013-09 to September 2013
            $new_crime->month = date("F Y", strtotime($crime['month']));

            $new_crime->street = lcfirst($crime['location']['street']['name']);
            
            //Logic needs to be inplace to prevent marker overlapping for crimes with the same exactly lat/lng
            //This will be achieved by padding the lng by 0.00005

            //Loop through the existing crimes
            $padding = 0.0;
            foreach ($crimeData as $k=>$v) {
                //If this crimes lat/lng is the same as the current crime, pad it
                
                if ($crimeData[$k]->location->latitude == $crime['location']['latitude'] &&
                        $crimeData[$k]->location->longitude == $crime['location']['longitude']+$padding) {
                    $padding += 0.00005;
                }
            }
            
            $new_crime->location = new stdClass();
            $new_crime->location->latitude = $crime['location']['latitude'];
            $new_crime->location->longitude = $crime['location']['longitude']+$padding;
            
            //Formulate 'Outcome status' into human-readable text
            if (isset($crime['outcome_status'])) {
                $new_crime->outcome_status = $crime['outcome_status']['category']." (as of ".date("F Y", strtotime($crime['outcome_status']['date'])).")";
            } else {
                $new_crime->outcome_status = null;
            }
            
            //Append newly formulated crime to crime data array
            $crimeData []= $new_crime;
        }
        
        //Tally the crime categories in the reformulated crimeData
        foreach ($crimeData as $data) {
            if (isset($categoryData[$data->category->url])) {
                $categoryData[$data->category->url]->count += 1;
            } else {
                $obj = new stdClass();
                $obj->url = $data->category->url;
                $obj->nicename = $data->category->nicename;
                $obj->color = $data->category->color;
                $obj->count = 1;
                $categoryData[$data->category->url] = $obj;
            }
        }
        
        //Get the Police force information for this area
        $force = $POLICE->neighbourhood_locate($lat, $lng);
        $force = $force['force'];

        $forceData = $POLICE->force($force);
        
        //Populate the response array
        $response['success']        = 1;
        $response['message']        = 'Crimes retrieved successfully!';
        $response['crimeData']      = $crimeData;
        $response['forceData']      = $forceData;
        $response['categoryData']   = $categoryData;
    } else {
        //Populate the response array
        $response['success']        = 0;
        $response['message']        = 'Unfortunately there was no data found in our system for this area. Please note that crime data is not available for Scotland.';
    }
} else {
    //Populate the response array
    $response['success']        = 0;
    $response['message']        = 'ERROR: Missing location lat/lng data! Please contact the site administrator.';
}

echo json_encode($response);