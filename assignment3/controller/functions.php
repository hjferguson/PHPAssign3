<?php
//--------------------functions for navigation------------------
function getMenu(){ //use get method to work with switch case. page is the case

    //need to add to this and controller.php
    echo("<nav> 
        <a href='controller.php?page=register'>Register</a> 
        <a>|</a>
        <a href='controller.php?page=login'>Login</a>
        <a>|</a>
        <a href='controller.php?page=upload'>Upload</a>
        <hr>
        </nav>");
}

//----------------Functions for database-------------------
function db_connect($db_info, $username, $password){
    try{
    $db_con = new PDO($db_info, $username, $password); 
    return $db_con;
    } catch(PDOException $e){
        $error_message = $e->getMessage();
        echo "PDO database not connected error: " . $error_message . "<br>";
        exit(); //stop script
    }
}

function db_insert_query($db_con, $query){
    try{
        $statement = $db_con->prepare($query);
        if(!$statement) print_r($db_con->errorInfo());//remove when done
        $statement->execute();
        $statement->closeCursor();
        return true;
    } catch(PDOException $e){
        $error_message = $e->getMessage();
        echo "PDO database query error: " . $error_message . "<br>";
        return false;
        //exit(); //stop script
    }
}

function db_select_query($db_con, $query){
    try{
        $statement = $db_con->prepare($query);
        if(!$statement) print_r($db_con->errorInfo());//remove when done
        $statement->execute();
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $statement->closeCursor();
        return $result;
    } catch(PDOException $e){
        $error_message = $e->getMessage();
        echo "PDO database query error: " . $error_message . "<br>";
        return false;
        //exit(); //stop script
    }
}

//function that checks to see if user exists in database

function check_user($db_con, $email){
    $query = "SELECT * FROM users WHERE Email = '$email'";
    $result = db_select_query($db_con, $query);
    foreach($result as $row){ //result is an associative array so I then loop through it and if Email column has value of $email then return true
        if($row['Email'] == $email){
            return true;
        }
    }
    return false;
}

//----------------functions for registering users-----------------
function display_register(){
    
    echo '<form method="post">
        <input type="hidden" name="action" value="register">
        <label>Name:</label>
        <input type="text" name="name" maxlength="8" minlength="4" required><br>
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" minlength="8" required><br>
        <label>&nbsp;</label>
        <input type="submit" value="Register" name="submit"><br>
        </form>';

}

function display_login(){
    echo '<form method="post">
        <input type="hidden" name="action" value="login">
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" minlength="8" required><br>
        <label>&nbsp;</label>
        <input type="submit" value="Login" name="submit"><br>
        </form>';
}

function login(){
    require "../model/db_config.php";
    $db_con = db_connect($db_info, $username, $password);
    if(check_user($db_con, $_POST['email'])){ //check if user exists
        //if user does exsist, check if password entered matches password in database
        if(password_verify($_POST['password'], $db_con->query("SELECT Password FROM users WHERE Email = '$_POST[email]'")->fetchColumn())){
            echo "Login successful";
        } else {
            echo "Incorrect password";
        }
    } else {
        echo "User does not exist";
    }
}


//using post method to get form data
function get_reg_form_data(){
    $user = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $saltedHashPass = password_hash($password, PASSWORD_DEFAULT, array('cost' => 12)); //add salt to prevent rainbow table attacks
    
    return $userInfo = array($user, $email, $saltedHashPass);
}
function addUser(){
    $userArray = get_reg_form_data(); //converts form-to-string array and salts and hashes password
    require "../model/db_config.php";
    $db_con = db_connect($db_info, $username, $password); //connects to database
    $user = $userArray[0];
    $email = $userArray[1];
    $exsists = check_user($db_con, $email); //checks if user exists by comparing email (email is unique)
    if($exsists){
        echo "User already exists";
    } else {
        $password = $userArray[2]; //this is salted and hashed
        $query = "INSERT INTO users (Email, Username, Password, authenticated) VALUES ('$email', '$user', '$password','0')";
        $result = db_insert_query($db_con, $query); //query function returns result 
        //echo result
        if($result){     
            echo "User registered!";}
        else {
            echo "User not registered!";
        }
    }
}
//this returns false atm... waiting on discussion forum to see if I can access php.ini
function sendEmail($email, $user){
    $to = $email;
    $subject = "Email Subject";

    $message = 'Dear '.$user.',<br>';
    $message .= "We welcome you to be part of family<br><br>";
    $message .= "Regards,<br>";

    // Always set content-type when sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

    // More headers
    $headers .= 'From: <enquiry@example.com>' . "\r\n";
    $headers .= 'Cc: myboss@example.com' . "\r\n";

    mail($to,$subject,$message,$headers);   
}


