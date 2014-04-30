<?php
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (isset($_SESSION['userid']) && !empty($_SESSION['userid']) &&
            isset($_SESSION['email']) && !empty($_SESSION['email']) &&
            isset($_SESSION['firstname']) && !empty($_SESSION['firstname'])) {
        header("Location: index.php");
        die();
    }
    
    require_once('config.php');
    
    include_once('header.php');
    
    $errors = array();
    
    $submit     = filter_input(INPUT_POST, 'submit');
    $email      = filter_input(INPUT_POST, 'email');
    $cEmail     = filter_input(INPUT_POST, 'cEmail');
    $password   = filter_input(INPUT_POST, 'password');
    $cPassword  = filter_input(INPUT_POST, 'cPassword');
    $firstname  = filter_input(INPUT_POST, 'firstname');
    
    function checkEmailExists($mysqli, $email) {
        $email = strtolower($email);
        $email = $mysqli->real_escape_string($email);

        $sql = "SELECT email FROM users WHERE LOWER(email) = '".$email."'";
        $result = $mysqli->query($sql);   

        return ($result->num_rows > 0);
    }
    
?>
        <div id="content" class="page-register">
            <h1 class="outline">Register</h1>
<?php
    if (isset($submit) &&
            $submit == "Register") {
        
        $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
        
        if ($mysqli->connect_errno) {
            $errors []= "ERROR: Database connection failed! ".$mysqli->connect_errno;
        }
        
        //Validation
        if (empty($firstname)) {
            $errors []= "First name is missing.";
        } else if (strlen($firstname) > 50) {
           $errors []= "First name is too long.";
        } else {
            $firstname = stripslashes($firstname);
            $firstname = $mysqli->real_escape_string($firstname);
        }
        
        if (empty($email)) {
            $errors []= "Email is missing.";
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                //Regex for email validation
                $errors []= 'Email address not a valid format.';
            } else if (strlen($email) > 254) {
                $errors []= 'Email address is too long.';
            } else if (checkEmailExists($mysqli, $email)) {
                $errors []= 'Email address is in use!';
            } else {
                $email = stripslashes($email);
                $email = $mysqli->real_escape_string($email);
            }
        }
        
        if (empty($cEmail)) {
            $errors []= "Please confirm your email address.";
        } else {
            if ($cEmail !== $email) {
               $errors []= "Email addresses do not match!"; 
            }
        }
        
        if (empty($password)) {
            $errors []= "Password is missing.";
        } else {
            if (strlen($password) > 50) {
               $errors []= "Password is too long (maximum 50 characters)."; 
            } else if (strlen($password) < 8) {
                $errors []= "Password is too short (minimum 8 characters)."; 
            } else {
                $password = stripslashes($password);
                $password = $mysqli->real_escape_string($password);
            }
        }
        
        if (empty($cPassword)) {
            $errors []= "Please confirm your password.";
        } else {
            if ($cPassword !== $password) {
               $errors []= "Passwords do not match!"; 
            }
        }
        
        if (empty($errors)) {
            //No errors so insert the user record
            $password = md5(SALT.$password);
            $registration_date = date('Y-m-d H:i:s');
            
            // Create unique activation code
            $activation_code = md5(uniqid(rand(), true));
  
            //The following commented code blocks relate to confirmation email functionality, which I am unable to implement on the Dunluce server. Alternative code is in place to bypass this.
            
//            if ($stmt = $mysqli->prepare("INSERT INTO users (email, password, firstname, registration_date, activation_code) VALUES (?, ?, ?, ?, ?)")) {
//                $stmt->bind_param('sssss',
//                    $email,
//                    $password,
//                    $firstname,
//                    $registration_date,
//                    $activation_code
//                );
            
            $confirmed = 1;
            
            if ($stmt = $mysqli->prepare("INSERT INTO users (email, password, firstname, registration_date, activation_code, confirmed) VALUES (?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param('sssssi',
                $email,
                $password,
                $firstname,
                $registration_date,
                $activation_code,
                $confirmed
            );
            
                if ($result = $stmt->execute()) {
//                    echo "<h1>Registration successful! You will receive an activation email shortly.</h1>";
//                    echo "<br>";
//                    echo "<a href=\"".SITE_URL."\">Click here to return to the homepage</a>";
//                    echo "</div>";
//
//                    //Email the user their activation code
//                    $message = "Hello $firstname,";
//                    $message .= "Welcome to Crime Watch";
//                    $message .= "To activate your account, please click on this link:\n\n";
//                    $message .= SITE_URL."/activate.php?key=$activation_code";
//
//                    mail($email, 'Crime Watch - Account Activation', $message, 'From:'.SITE_EMAIL);
                    echo "<h1>Registration successful! You may now login.</h1>";
                    echo "<br>";
                    echo "<a href=\"".SITE_URL."\">Click here to return to the homepage</a>";
                    echo "</div>";
                } else {
                    echo("<h1>Registration failed due to the following error:<br>".$mysqli->error."</h1>");
                }
            } else {
                echo("<h1>Registration failed due to the following error:<br>".$mysqli->error."</h1>");
            }
        } else {
            echo '<div class="errorBox">';
            echo '    <h3>Registration failed due to the following errors:</h3>';
            echo '    <ul>';
            foreach ($errors as $k=>$v) {
                echo '    <li>'.$v.'</li>';
            }
            echo '    </ul>';
            echo '</div>';
        }

        $mysqli->close();
?>

<?php
    }
    
    if (!isset($submit) || !empty($errors)) {
?>
            <form id="registrationForm" method="POST" action="register.php">
                <div class="field" id="firstname">
                    <p class="label"><label for="firstnameValue">Enter your first name</label></p>
                    <input type="text" placeholder="" name="firstname" class="value" id="firstnameValue" value="<?php echo (isset($firstname) && !empty($firstname)) ? $firstname : '' ?>">
                </div>
                
                <div class="field" id="email">
                    <p class="label"><label for="emailValue">Enter your email address</label></p>
                    <input type="text" placeholder="" name="email" class="value" id="emailValue" value="<?php echo (isset($email) && !empty($email)) ? $email : '' ?>">
                </div>
                
                <div class="field" id="cEmail">
                    <p class="label"><label for="cEmailValue">Confirm your email address</label></p>
                    <input type="text" placeholder="" name="cEmail" class="value" id="cEmailValue" value="<?php echo (isset($cEmail) && !empty($cEmail)) ? $cEmail : '' ?>">
                </div>
                
                <div class="field" id="password">
                    <p class="label"><label for="passwordValue">Create a password</label></p>
                    <input type="password" placeholder="" name="password" class="value" id="passwordValue" value="">
                </div>
                
                <div class="field" id="cPassword">
                    <p class="label"><label for="cPasswordValue">Confirm your password</label></p>
                    <input type="password" placeholder="" name="cPassword" class="value" id="cPasswordValue" value="">
                </div>
                
                <input type="submit" id="submit" class="button" name="submit" value="Register" >
            </form>
        </div>
<?php
    }
    include_once('footer.php');
?>