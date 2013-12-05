<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Crime Watch: Promoting vigilance and safety for your community</title>
        <link rel="shortcut icon" href="pix/favicon.ico">
        <link rel="stylesheet" href="css/index.css">
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmBHpA9W3ynxRqxF55RQBI3S76AUPZQuI&sensor=false"></script>
        <script type="text/javascript" src="js/jquery-2.0.3.min.js"></script>
        <script type="text/javascript" src="js/markerclusterer.js"></script>
        <script type="text/javascript" src="js/jquery.ui.map.full.min.js"></script>
        <script type="text/javascript" src="js/index.js"></script>
    </head>
    <body>
	<header>
            <h1>Crime <img id="crosshair" src="pix/crosshair.png"><span class="alt">Watch</span></h1>
            <h3 id="tagline">Promoting vigilance and safety for your community</h3>
            <div id="social">
                <img src="pix/facebook.png">
                <img src="pix/twitter.png">
                <img src="pix/googleplus.png">
            </div>
            <div id="banner"></div>
	</header>
        <div id="content">
            <input type="hidden" id="addressLat" value="" />
            <input type="hidden" id="addressLng" value="" />
            <input type="hidden" id="crimeType" value="" />
            <input type="hidden" id="crimeDate" value="" />
            <div id="feedback" class="hide"></div>
            <div id="search">
                <form name="search" method="POST" action="">
                    <input type="text" name="address" placeholder="Enter address or postcode">
                    <img id="do-search" src="pix/icon_search.png">
                    <img class="ajaxLoader hidden" src="pix/ajax-loader.png">
                </form>
            </div>
            <p id="notice" class="outline">IMPORTANT NOTE: The crimes markers shown are only an approximation of where the actual crimes occurred, NOT the exact locations.</p>
            <div id="map-container">
                <div id="resultsInfo" class="outline"></div>
                <div class="vc"></div>
                <div id="map-canvas"></div>
                <div id="mapOverlay" class="hidden"></div>
                <div id="mapPanel">
                    <div id="filters">
                        <h2>Filters</h2>
                        <label>Crime Type:</label>
                        <br>
                        <select id="crimeTypesSelect"></select>
                        <br><br>
                        <label>Date:</label>
                        <br>
                        <select id="crimeDatesSelect"></select>
                    </div>
                    <hr>
                    <div id="forceInformation"></div>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <footer class="outline">Created by Barry McKay (B00556648)</footer>
    </body>
</html>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
