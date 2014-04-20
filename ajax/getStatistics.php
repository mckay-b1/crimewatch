<?php

require_once('../lib/myPolice.php');

$response = array(
    'success'=>0,
    'message'=>''
); 

$POLICE = new myPoliceUK();

$lat            = filter_input(INPUT_POST, 'lat');
$lng            = filter_input(INPUT_POST, 'lng');
$from           = filter_input(INPUT_POST, 'from');
$to             = filter_input(INPUT_POST, 'to');
$categoryFilter = filter_input(INPUT_POST, 'categoryFilter');


function getCrimeLevels($POLICE, $lat, $lng, $from = null, $to = null) {
    $data = array();
    
    if (!isset($from) || empty($from)) {
        $from = date("Y-m", strtotime($POLICE->lastupdated()));
    }
    
    if (!isset($to) || empty($to)) {
        $to = date("Y-m", strtotime($from." -12 months"));
    }
    
    //Get the difference in number of months used to control the data loop
    $diff = date_diff(date_create($from), date_create($to));
    $diff = (int)($diff->format('%y') * 12) + $diff->format('%m');
    
    $data = array();
    
    for ($i = 0; $i <= $diff; $i++) {
        $date = date("Y-m", strtotime($from." +$i months"));
        
        $crimes = $POLICE->crimes_at_location($lat, $lng, $date);
        
        if (count($crimes) > 0) {
        
            $categories = $POLICE->crime_categories_with_date($date);

            $data[$date]['count'] = 0;

            //Tally the crime categories
            foreach ($crimes as $crime) {
                $data[$date]['count'] += 1;
                
                $category = $crime['category'];

                if (isset($data[$date]['categories'][$category])) {
                    $data[$date]['categories'][$category]->count += 1;
                } else {                
                    $obj = new stdClass();
                    $obj->nicename = $categories[$category]->nicename;
                    $obj->count = 1;
                    $data[$date]['categories'][$category] = $obj;
                }
            }
        }
    }

    return $data;
}


function getUniqueCategories($data) {
    $categories = array();
    
    foreach ($data as $a=>$b) {
        foreach($b['categories'] as $c=>$d) {
            if (!in_array($c, $categories)) {
                $categories[$c] = $d->nicename;
            }
        }
    }
    
    return $categories;
}


function populateEmptyCategories($data) {
    //Step 1 - Get the list of all unique categories in the data set
    $categories = getUniqueCategories($data);

    //Step 2 - Compare all possible categories versus the categories for each month to determine and populate empty ones with 0
    foreach ($data as $a=>$b) {
        foreach($categories as $c=>$d) {
            if (!array_key_exists($c, $b['categories'])) {
                $data[$a]['categories'][$c] = new stdClass();
                $data[$a]['categories'][$c]->nicename = $d;
                $data[$a]['categories'][$c]->count = 0;
            }
        }
    }
    
    return $data;
}


if ((isset($lat) && !empty($lat)) &&
        (isset($lng) && !empty($lng)) &&
        (isset($from) && !empty($from)) &&
        (isset($to) && !empty($to))) {
        
    //Get the difference in number of months used to control the data loop
    $diff = date_diff(date_create($from), date_create($to));
    $diff = (int)($diff->format('%y') * 12) + $diff->format('%m');
    
    //Validation
    //1 - The 'from' date is after the 'to' date
    //2 - The months are the same
    
    if (strtotime($from) > strtotime($to)) {
        $response['message'] = "ERROR: 'Date from' must be before 'Date to'.";
    }  else if ($diff < 1) {
        $response['message'] = "ERROR: 'Date from' and 'Date to' are the same.";
    } else {

        $data = getCrimeLevels($POLICE, $lat, $lng, $from, $to);
        
        if (count($data) > 0) {
            $data = populateEmptyCategories($data);

            $newdata = array();

            $categoryFilter = array();
            $categoryFilter []= "Filter by category:&nbsp;";
            $categoryFilter []= "<select>";
            $categoryFilter []= "    <option value=\"\">All categories</option>";

            $categories = getUniqueCategories($data);

            foreach ($categories as $k=>$v) {
                $categoryFilter []= "    <option value=\"".$k."\">".$v."</option>";
            }

            $categoryFilter []= "</select>";

            $categoryFilter = implode('', $categoryFilter);

            $typeFilter = array();
            $typeFilter []= "Filter by:&nbsp;";
            $typeFilter []= "<select>";
            $typeFilter []= "    <option value=\"\">All months</option>";

            foreach ($data as $a=>$b) {
                $typeFilter []= "    <option value=\"".$a."\">".date("F Y", strtotime($a))."</option>"; 
            }

            $typeFilter []= "</select>";

            $typeFilter = implode('', $typeFilter);

            $statisticsTable = array();

            //First Row - Headings
            $statisticsTable []= "    <thead>";
            $statisticsTable []= "        <tr>";
            $statisticsTable []= "            <th>&nbsp;</th>";

            foreach ($data as $k=>$v) {
                $statisticsTable []= "            <th>".date("M y", strtotime($k))."</th>";
            }

            $statisticsTable []= "        </tr>";
            $statisticsTable []= "    </thead>";

            //Data rows - each category
            $statisticsTable []= "    <tbody>";
            foreach ($categories as $a=>$b) {
                $statisticsTable []= "        <tr>";
                $statisticsTable []= "            <th>".$b."</th>";

                $tally = 0;
                foreach ($data as $c=>$d) {
                    $statisticsTable []= "            <td>".$d['categories'][$a]->count."</td>";
                    $tally += $d['categories'][$a]->count;
                }
                $statisticsTable []= "            <td>".$tally."</td>";

                $statisticsTable []= "        </tr>";
            }

            //Final Row - Totals
            $statisticsTable []= "        <tr>";
            $statisticsTable []= "            <td>&nbsp;</td>";

            $tally = 0;
            foreach ($data as $k=>$v) {
                $statisticsTable []= "            <td>".$v['count']."</td>";
                $tally += $v['count'];
            }

            $statisticsTable []= "            <td>".$tally."</td>";

            $statisticsTable []= "        </tr>";
            $statisticsTable []= "    </tbody>";

            $statisticsTable = implode('', $statisticsTable);

            $response['success']            = 1;
            $response['message']            = "Success";
            $response['data']               = $data;
            $response['categoryFilter']     = $categoryFilter;
            $response['typeFilter']         = $typeFilter;
            $response['statisticsTable']    = $statisticsTable;
        } else {
            $response['message'] = "Unfortunately there was no data found in our system for this area. Are you sure you entered a valid UK address?"; 
        }
    }
} else {
    $response['message']    = "ERROR: Missing data.";
}

echo json_encode($response);