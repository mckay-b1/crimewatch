<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('config.php');

if (isset($_SESSION['userid']) && !empty($_SESSION['userid']) &&
        isset($_SESSION['email']) && !empty($_SESSION['email']) &&
        isset($_SESSION['firstname']) && !empty($_SESSION['firstname'])) {
    header("Location: ".SITE_URL);
    die();
}

include_once('header.php');

$submit = filter_input(INPUT_POST, 'submit');
$email = filter_input(INPUT_POST, 'email');
$password = filter_input(INPUT_POST, 'password');

$errors = array();
    
?>
        <div id="content" class="page-login">
            <h1 class="outline">Login</h1>

<?php
if (isset($submit) &&
        $submit == "Login") {
    
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($mysqli->connect_errno) {
        $errors []= "ERROR: Database connection failed! ".$mysqli->connect_errno;
    }
    
    if (!isset($email) ||
            !isset($password) ||
            empty($email) ||
            empty($password)) {
        if (!isset($email) || empty($email)) {
            $errors []= "ERROR: Enter your email address!";
        }

        if (!isset($password) || empty($password)) {
            $errors []= "ERROR: Enter your password!";
        }
    }

    $email = stripslashes($email);
    $password = stripslashes($password);

    $email = $mysqli->real_escape_string($email);
    $password = $mysqli->real_escape_string($password);

    $password = md5(SALT.$password);

    $sql = "SELECT * FROM users WHERE email='$email' and password='$password' LIMIT 1";
    $result = $mysqli->query($sql);

    if ($row = $result->fetch_assoc()) {
        if ($row['confirmed']) {
            $_SESSION['userid']     = $row['id'];
            $_SESSION['email']      = $row['email'];
            $_SESSION['firstname']  = $row['firstname'];

            //Update 'last_login' for this user
            $last_login = date('Y-m-d H:i:s');
            $id = $row['id'];

            $sql = "UPDATE users SET last_login='$last_login' WHERE id=$id";
            $result = $mysqli->query($sql);

            $response['name']    = $row['firstname'];
            $response['success'] = 1;
            $response['message'] = "Successfully logged in!";

            header("Location: ".SITE_URL);
            die();
        } else {
            $errors []= "ERROR: Please activate your account before attempting to login. An activation email should be in your inbox.";
        }
    } else {
        $errors []= "ERROR: Invalid email or password!";
    }
    
    $mysqli->close();
    
} else {

}
?> 

<?php
if (count($errors) > 0) {
    echo '<div class="errorBox">';
    
    foreach ($errors as $error) {
        echo $error;
    }
    
    echo '</div>';
} else {
    echo '<div class="errorBox hide"></div>';
}
?>
            <form id="loginForm" method="POST" action="login.php">
                
                <div class="field" id="email">
                    <p class="label"><label for="emailInput">Enter your email:</label></p>
                    <input type="text" id="emailInput" name="email" value="<?php echo $email; ?>" />
                </div>
                <div class="field" id="password">
                    <p class="label"><label for="passwordInput">Enter your password:</label></p>
                    <input type="password" id="passwordInput" name="password" value="" />
                </div>
<!--                ADD IN POSTED EMAIL IF LOGIN FAILS-->
                
                <input type="submit" id="submit" class="button" name="submit" value="Login" />
            </form>
        </div>
<?php
    include_once('footer.php');
?>