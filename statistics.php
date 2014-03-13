<?php

$lat = filter_input(INPUT_GET, 'lat');
$lng = filter_input(INPUT_GET, 'lng');

//require_once('lib/GChartPhp/gChart.php');
require_once('header.php');

require_once('lib/myPolice.php');

$POLICE = new myPoliceUK();
?>
    <div id="content" class="statisticsPage">
        <h1 class="outline" id="statisticsPageTitle">Statistics</h1>

<?php
if ((isset($lat) && !empty($lat)) &&
    (isset($lng) && !empty($lng))) {

    $availableDates = $POLICE->crime_street_dates();
?>      
        <h3 class="outline" id="currentAddress"></h3>
        <a id="changeAddress" href="/statistics">Change address</a>
        <div class="errorBox hide"></div>
        <form id="datesForm">
            <input type="hidden" id="addressLat" name="lat" value="<?php echo $lat; ?>" />
            <input type="hidden" id="addressLng" name="lng" value="<?php echo $lng; ?>" />
            <label>Date from:</label>
            <select id="fromSelect" name="from">
                <?php
                    foreach ($availableDates as $date) {
                        echo "<option value=\"".$date['date']."\">".date("F Y", strtotime($date['date']))."</option>";
                    }
                ?>
            </select>
            <br>
            <label>Date to:</label>
            <select id="toSelect" name="to">
                <?php
                    foreach ($availableDates as $date) {
                        echo "<option value=\"".$date['date']."\">".date("F Y", strtotime($date['date']))."</option>";
                    }
                ?>
            </select>
            <br>
            <input type="submit" id="datesButton" class="button" value="Get statistics" />
            <img class="ajaxLoader hidden" src="pix/ajax-loader.png" />
        </form>
        <div id="crimeLevels" class="hide">
            <h2 class="outline">Crime levels</h2>
            <p class="outline">View the level of crime in this area across your specified time period, with the option of viewing specific types of crime.</p>
            <div id="crimeLevelsFilter" class="hide"></div>
            <div id="crimeLevelsChart">&nbsp;</div>
            <hr>
        </div>
        <div id="crimeTypes" class="hide">
            <h2 class="outline">Crime types</h2>
            <p class="outline">View the proportion of different crimes in this area across your specified time period or for a specific month.</p>
            <div id="crimeTypesFilter" class="hide"></div>
            <div id="crimeTypesChart">&nbsp;</div>
            <hr>
        </div>
        <div id="statisticsData" class="hide">
            <h2 class="outline">Statistics data</h2>
            <p class="outline">View the raw crime data figures for your specified time period.</p>
            <table id="statisticsTable"></table>
        </div>
<?php
} else {
    //Lat/Lng data missing
?>
        <form id="searchForm" method="POST" action="">
            <input id="searchValue" type="text" name="address" placeholder="Enter address or postcode">
            <img id="searchButton" class="button" src="pix/icon_search.png">
            <img id="geolocateButton" class="button" src="pix/globe.png" title="Search using your current location" />
            <img class="ajaxLoader hidden" src="pix/ajax-loader.png">
        </form>
<?php
}
?>
    </div>
<?php

require_once('footer.php');

?>