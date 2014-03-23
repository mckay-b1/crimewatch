<?php
    require_once('config.php');
    
    $currentPage = basename(filter_input(INPUT_SERVER, 'PHP_SELF'));
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Crime Watch: Promoting vigilance and safety for your community</title>
        <link rel="shortcut icon" href="pix/favicon.ico">
        <link rel="stylesheet" type="text/css" href="http://fonts.googleapis.com/css?family=Metrophobic">
        <link rel="stylesheet" type="text/css" href="css/index.css">
        <script language="javascript" type="text/javascript" src="http://code.jquery.com/jquery-2.1.0.js"></script>
        
<?php
    if ($currentPage == "statistics.php") {
?>
        <link rel="stylesheet" href="css/statistics.css">
        <script language="javascript" type="text/javascript" src="js/jquery-dateformat.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/flot/jquery.flot.js"></script>
        <script language="javascript" type="text/javascript" src="js/flot/jquery.flot.axislabels.js"></script>
        <script language="javascript" type="text/javascript" src="js/flot/jquery.flot.categories.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/flot/jquery.flot.pie.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/flot/jquery.flot.tooltip.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/statistics.js"></script>
<?php
    } else {
?>
        <script language="javascript" type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=<?php echo GMAP_API_KEY;?>&sensor=false"></script>
        <script language="javascript" type="text/javascript" src="js/markerclusterer.js"></script>
        <script language="javascript" type="text/javascript" src="js/jquery.ui.map.full.min.js"></script>
        <script language="javascript" type="text/javascript" src="js/index.js"></script>
<?php
    }
?>
    </head>
    <body>
	<header>
            <a id="siteTitle" href="<?php echo HOMEPAGE; ?>">Crime <img id="crosshair" src="pix/crosshair.png"><span class="alt">Watch</span></a>
            <h4 id="tagline">Promoting vigilance and safety for your community</h4>
            <div id="login">
<?php
    if (!isset($_SESSION)) {
        session_start();
    }
    
    //Check if the user is logged in
    if (isset($_SESSION['userid']) && !empty($_SESSION['userid']) &&
            isset($_SESSION['email']) && !empty($_SESSION['email']) &&
            isset($_SESSION['firstname']) && !empty($_SESSION['firstname'])) {
        echo '<form id="logoutForm" method="POST" action="">';
        echo '    Welcome '.$_SESSION['firstname'].'!';
        echo '    <input type="submit"  class="button" value="Logout" />';
        echo '</form>';
    } else {
?>
                <div class="errorBox hide"></div>
                <form id="loginForm" method="POST" action="">
                    <div>
                        <label for="emailInput">Email:</label>
                        <input type="text" id="emailInput" name="email" size="20" value="" />
                    </div>
                    <div>
                        <label for="passwordInput">Password:</label>
                        <input type="password" id="passwordInput" name="password" size="20" value="" />
                    </div>
                    <div>
                        <input type="submit" class="button" value="Login" />
                        <img class="ajaxLoader hidden" src="pix/ajax-loader.gif" />
                    </div>
                    <a href="register.php" id="register">Register account</a>
                </form>
<?php
    }
?>
            </div>
<!--            <div id="social">
                <img src="pix/facebook.png">
                <img src="pix/twitter.png">
                <img src="pix/googleplus.png">
            </div>-->
            <div id="banner"></div>
	</header>