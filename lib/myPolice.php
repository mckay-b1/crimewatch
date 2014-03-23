<?php
/**
 * Extended Class for Police.uk API PHP Curl Class (Author Matthew Gribben (Originally Rick Seymour))
 * @author Barry McKay
 */

require_once('police.php');

if (!function_exists('curl_init')) {
    die("NO CURL!");
}

Class myPoliceUK extends PoliceUK {
    
    protected function call($url) {
        $callurl = $this->baseUrl.$url;
        $this->setopt(CURLOPT_URL, $callurl);
        $result = curl_exec($this->curl);
        $curlinfo = curl_getinfo($this->curl);
        
        if ($this->debug) {
            $this->curlinfo = $curlinfo;
        }
        
        if ($curlinfo['http_code'] == 200) {
            if ($this->returnraw) {
                return $result;
            }
            return json_decode($result, TRUE);
        } else if ($curlinfo['http_code'] == 401) {
            die('Username / Password Incorrect'.PHP_EOL);
        } else if ($curlinfo['http_code'] == 404) {
            error_log('PoliceUKAPI Error - '.$callurl);
            return false;
        } else {
            return false;
        }
    }
    
    
    public function crime_categories($date) {
        $categories = array();
        
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
            'light-yellow'  =>'FFEE9F',
            'light-pink'    =>'EFD1EF',
            'grey'          =>'888888',
            'white'         =>'FFFFFF',
            'black'         =>'000000'
        );
        
        $data = $this->call(sprintf(
            'crime-categories?date=%s',
            $date
        ));
        
        $keys = array_keys($colors);
        
        $i = 0;
        foreach ($data as $category) {
            $categories[$category['url']] = new stdClass();
            $categories[$category['url']]->url = $category['url'];
            $categories[$category['url']]->nicename = $category['name'];
            $categories[$category['url']]->color = $colors[$keys[$i]];
            $i++;
        }
        
        return $categories;
    }
    

    public function crime_street_dates(){
        return $this->call("crimes-street-dates");
    }
    

    public function crimes_at_location($latitude, $longitude, $date=null) {
        
        $url = 'crimes-street/all-crime?lat=%s&lng=%s';
        
        if (isset($date) && !empty($date)) {
            $url .= '&date='.$date;
        }
        
        return $this->call(sprintf(
            $url,
            $latitude,
            $longitude
        ));
    }
    
    
    public function neighbourhood($force, $neighbourhood){
        return $this->call(sprintf(
            '%s/%s',
            urlencode($force),
            str_replace(' ', '%20', $neighbourhood)
        ));
    }
}

