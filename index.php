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
            <p id="tagline">Promoting vigilance and safety for your community</p>
            <div id="banner"></div>
	</header>
        <div id="content">
            <div id="search">
                <form name="search" method="post" action="ajax/search.php">
                    <input type="text" name="address" placeholder="Enter postcode or street name">
                    <img id="do_search" src="pix/icon_search.png">
                </form>
            </div>
            <div id="map">
                
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
