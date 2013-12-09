<?php
require_once('../lib/police.php');

$POLICE = new PoliceUK();

if (isset($_POST['geometry']) &&
        (isset($_POST['geometry']['lat']) && (!empty($_POST['geometry']['lat']))) &&
        (isset($_POST['geometry']['lng']) && (!empty($_POST['geometry']['lng'])))) {
    $lat = $_POST['geometry']['lat'];
    $lng = $_POST['geometry']['lng'];

    if (isset($_POST['crimeDate']) && !empty($_POST['crimeDate'])) {
        $crimeDate = $_POST['crimeDate'];
    } else {
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
            
            //Color palette array for category markers
            $colors = array(
                'red'           =>'660000',
                'green'         =>'006600',
                'blue'          =>'0066FF',
                'yellow'        =>'FFD500',
                'pink'          =>'CC33CC',
                'orange'        =>'CC6633',
                'purple'        =>'6633CC',
                'brown'         =>'663300',
                'light-red'     =>'CC3333',
                'light-green'   =>'33CC33',
                'light-blue'    =>'33CCCC',
                'grey'          =>'888888',
                'white'         =>'FFFFFF',
                'black'         =>'000000'
            );
            
            //Dynamically assign colors to categories
            foreach ($categories as $k=>$v) {
                $keys = array_keys($colors);
                $categories[$k]['color'] = $colors[$keys[$k]];
            }
            
            foreach ($categories as $category) {
                if ($category['url'] === $crime['category']) {
                    //Setup category object for crime
                    $new_crime->category = new stdClass();
                    $new_crime->category->url = $category['url'];
                    $new_crime->category->name = $category['name'];
                    $new_crime->category->color = $category['color'];
                }
            }

            //Modify date from 2013-09 to September 2013
            $new_crime->month = date("F Y", strtotime($crime['month']));

            $new_crime->street = lcfirst($crime['location']['street']['name']);
            
            //Logic needs to be inplace to prevent marker overlapping for crimes with the same exactly lat/lng
            //This will be achieved by padding the lng by 0.0001
            $padding = 0.0;
            
            //Loop through the existing crimes
            foreach ($crimeData as $k=>$v) {
                //If this crimes lat/lng is the same as the current crime, buffer it
                if ($crimeData[$k]->location->latitude == $crime['location']['latitude'] &&
                        $crimeData[$k]->location->longitude == $crime['location']['longitude']) {
                    $padding = 0.0001;
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
                $obj->name = $data->category->name;
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
        $response['message']        = 'Unfortunately there was no data found in our system for this area.';
    }
} else {
    //Populate the response array
    $response['success']        = 0;
    $response['message']        = 'An error occured! Missing location data!';
}

echo json_encode($response);