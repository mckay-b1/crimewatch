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
            <div id="search">
                <form name="search" method="post" action="ajax/search.php">
                    <input type="text" name="address" placeholder="Enter postcode or street name">
                    <img id="do-search" src="pix/icon_search.png">
                    <img id="ajax-loader" class="hidden" src="pix/ajax-loader.gif">
                </form>
            </div>
            <div id="map-container">
                <div id="map-canvas">

                </div>
                <div id="map-panel">
                    <table id="filters">
                        <tr>
                            <td><h3>Filters</h3></td>
                        </tr>
                        <tr>
                            <td>Crime Type:</td>
                        </tr>
                        <tr>
                            <td>
                                <select id="crime_type"></select>
                            </td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                        </tr>
                        <tr>
                            <td>Month:</td>
                        </tr>
                        <tr>
                            <td>
                                <select id="crime_date"></select>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="clear"></div>
            </div>
        </div>
        <footer>Created by Barry McKay (B00556648)</footer>
    </body>
</html>
<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
