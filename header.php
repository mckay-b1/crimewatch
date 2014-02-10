<?php
    require_once('config.php');
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Crime Watch: Promoting vigilance and safety for your community</title>
        <link rel="shortcut icon" href="pix/favicon.ico">
        <link rel="stylesheet" href="css/index.css">
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDmBHpA9W3ynxRqxF55RQBI3S76AUPZQuI&sensor=false"></script>
        <script type="text/javascript" src="http://code.jquery.com/jquery-2.1.0.js"></script>
        <script type="text/javascript" src="js/markerclusterer.js"></script>
        <script type="text/javascript" src="js/jquery.ui.map.full.min.js"></script>
        <script type="text/javascript" src="js/index.js"></script>
    </head>
    <body>
	<header>
            <a href="<?php echo HOMEPAGE; ?>"><h1>Crime <img id="crosshair" src="pix/crosshair.png"><span class="alt">Watch</span></h1></a>
            <h3 id="tagline">Promoting vigilance and safety for your community</h3>
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
        echo '    <p>Hi '.$_SESSION['firstname'].'</p>';
        echo '    <input type="submit" value="Logout" />';
        echo '</form>';
    } else {
?>
                <div class="errorBox hide"></div>
                <form id="loginForm" method="POST" action="">
                    Email: <input type="text" id="emailInput" name="email" size="20" value="" /><br>
                    Password: <input type="password" id="passwordInput" name="password" size="20" value="" /><br>
                    <a href="register.php" id="register">Register</a>
                    <input type="submit" value="Login" />
                    <img class="ajaxLoader hidden" src="pix/ajax-loader.png" />
                </form>
<?php
    }
?>
            </div>
            <div id="social">
                <img src="pix/facebook.png">
                <img src="pix/twitter.png">
                <img src="pix/googleplus.png">
            </div>
            <div id="banner"></div>
	</header>