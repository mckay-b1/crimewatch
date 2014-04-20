 <?php
require_once('config.php');

include_once('header.php');
?>
<div id="content">
<?php
$key = filter_input(INPUT_GET, 'key');
 
if (isset($key) &&
        !empty($key)) {
    
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_errno) {
        echo "ERROR: Database connection failed! ".$mysqli->connect_errno;
    }

    $key = stripslashes($key);
    $key = $mysqli->real_escape_string($key);

    $sql = "SELECT id FROM users WHERE activation_code='$key' LIMIT 1";
    $result = $mysqli->query($sql);

    if ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        
        //First check if user has already activated
        $sql = "SELECT confirmed FROM users WHERE id='$id' LIMIT 1";
        $result = $mysqli->query($sql);
        $row = $result->fetch_assoc();
        
        if ($row['confirmed']) {
            echo "You've already activated your account. You may now login.";
        } else {
            $sql = "UPDATE users SET confirmed = 1 WHERE id = $id";
            $result = $mysqli->query($sql);
            
            if ($result) {
                echo "Account successfully activated! You may now login.";
            } else {
                echo "An error occurred. Please try again or contact a site administrator if you continue to have issues.";
            }
        }
    } else {
        echo "Invalid activation code. Please try again or contact a site administrator if you continue to have issues.";
    }
} else {
    //Missing parameters so redirect to homepage
    header('Location: '.SITE_URL);
}

echo $message;

?>
</div>
<?php
include_once('footer.php');
?>