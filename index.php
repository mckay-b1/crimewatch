<?php
require_once('config.php');

include_once('header.php');
?>
        <div id="content" class="page-index">
            <input type="hidden" id="address" value="" />
            <input type="hidden" id="addressLat" value="" />
            <input type="hidden" id="addressLng" value="" />
            <input type="hidden" id="crimeType" value="" />
            <input type="hidden" id="crimeDate" value="" />
            <h1 class="outline" class="title">Search crimes</h1>
            <div id="notice">
                <img src="pix/exclamation.png" />
                <p class="hide">
                    <b>IMPORTANT NOTE:</b><br>
                    The crimes markers shown are only an approximation of where the actual crimes occurred, NOT the exact locations.<br>
                    Crime data is available for England, Wales and Northern Ireland ONLY.
                </p>
            </div>
            <div id="search">
                <div class="errorBox hide"></div>
                <form id="searchForm" method="POST" action="">
                    <input id="searchValue" type="text" name="address" placeholder="Enter address or postcode">
                    <img id="searchButton" class="button" src="pix/icon_search.png">
                    <img id="geolocateButton" class="button" src="pix/globe.png" title="Search using your current location" />
                    <img class="ajaxLoader hidden" src="pix/ajax-loader.gif">
                </form>
            </div>
            <div id="resultsInfo" class=""></div>
            <div id="mapContainer" class="hidden">
                <div id="mapCanvas"></div>
                <div id="mapOverlay" class="hidden">
                    <img class="ajaxLoader" src="pix/ajax-loader.gif" />
                </div>
            </div>
            <div class="clear"></div>
            <a id="viewStatistics" class="hide" href="<?php echo SITE_URL; ?>/statistics.php" target="_blank" title="View detailed statistics for this area" >View detailed statistics for this area</a>
            <div id="mapFilters" class="section hide">
                <h2>Filters</h2>
                <div>
                    <label>Crime Type:</label>
                    <select id="crimeTypesSelect"></select>
                </div>
                <div>
                    <label>Date:</label>
                    <select id="crimeDatesSelect"></select>
                </div>
            </div>
            <?php
//Check if the user is logged in
if (isset($_SESSION['userid']) && !empty($_SESSION['userid']) &&
        isset($_SESSION['email']) && !empty($_SESSION['email']) &&
        isset($_SESSION['firstname']) && !empty($_SESSION['firstname'])) {
?>
                    <div id="savedLocations" class="section">
                        <h2>My Saved Locations</h2>
                        <ul>
<?php
    
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_errno) {
        echo "ERROR: Database connection failed! ".$mysqli->connect_errno;
    }

    $sql = "SELECT * FROM locations WHERE user_id = '".$_SESSION['userid']."'";
    $results = $mysqli->query($sql);
    
    if ($results->num_rows > 0) {
        while ($row = $results->fetch_assoc()) {
            echo "<li id=\"location-".$row['id']."\">";
            echo "    <span title=\"".$row['address']."\">".$row['name']."</span>";
            echo "    <img src=\"pix/delete.png\" class=\"deleteLocation\" />";
            echo "</li>";
        }
    } else {
        echo "You haven't saved any locations yet. You can add these using the form below.";
    }

    $mysqli->close();
?>      
                        
                        </ul>
                        <div class="errorBox hide"></div>
                        <form id="addLocationForm">
                            <label for="locName">Location name:</label><br>
                            <input type="text" name="locName" id="locName" />
                            <br>
                            <label for="locAddress">Location address/postcode:</label><br>
                            <input type="text" name="locAddress" id="locAddress" />
                            <div style="text-align: center;">
                                <input type="submit" id="addLocationButton" class="button" value="Save location" />
                                <img class="ajaxLoader hidden" src="pix/ajax-loader.gif" />
                            </div>
                        </form>
                    </div>
<?php
    }
?>
            <div id="localTeamInfo" class="section hide"></div>
            <div id="forceInfo" class="section hide"></div>
        </div>
<?php
    include_once('footer.php');
?>