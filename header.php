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
        <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Metrophobic">
        <link rel="stylesheet" type="text/css" href="css/index.css">
        
        <!-- ShareThis Scripts -->
        <script type="text/javascript">var switchTo5x=true;</script>
        <script type="text/javascript" src="https://ws.sharethis.com/button/buttons.js"></script>
        <script type="text/javascript">
            stLight.options({
                publisher: "334161b4-6c0b-46a3-95c1-78ec4ddbb56a",
                doNotHash: true,
                doNotCopy: true,
                hashAddressBar: false
            });
        </script>
        
        <script language="javascript" type="text/javascript" src="https://code.jquery.com/jquery-2.1.0.js"></script>
        
<?php
    if ($currentPage == "statistics.php" || $currentPage == "statistics") {
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
    } else if ($currentPage == "login.php" ||
            $currentPage == "login" ||
            $currentPage == "register.php" ||
            $currentPage == "register" ) {
?>
        <script language="javascript" type="text/javascript" src="js/index.js"></script>
<?php
    } else {
?>
        <script language="javascript" type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=drawing"></script>
        <script language="javascript" type="text/javascript" src="js/markerclusterer.js"></script>
        <script language="javascript" type="text/javascript" src="js/index.js"></script> 
<?php
    }
?>
    </head>
    <body>
	<header>
            <a id="siteTitle" href="<?php echo SITE_URL; ?>">Crime<img id="crosshair" src="pix/crosshair.png"><span class="alt">Watch</span></a>
            <h4 id="tagline">Promoting vigilance and safety for your community</h4>
            <div id="social">
                <span class='st_fblike_large' displayText='Facebook Like'></span>
                <span class='st_facebook_large' displayText='Facebook'></span>
                <span class='st_twitter_large' displayText='Tweet'></span>
                <span class='st_googleplus_large' displayText='Google +'></span>
                <span class='st_email_large' displayText='Email'></span>
            </div>
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
        <a href="login.php">Login</a><span style="margin: 0 10px;">|</span> 
        <a href="register.php">Register</a>

<?php
    }
?>
            </div>
            <div id="banner"></div>
	</header>