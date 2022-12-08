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
    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = db_select_query($db_con, $query);
    foreach($result as $row){
        if($row['email'] == $email){
            return true;
        } else {
            return false;
        }
    }
    if($result){
        return true;
    } else {
        return false;
    }
}

//----------------functions for registering users-----------------
function display_register(){
    
    echo '<form method="post">
        <input type="hidden" name="action" value="register">
        <label>Name:</label>
        <input type="text" name="name" required><br>
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <label>&nbsp;</label>
        <input type="submit" value="Register" name="submit"><br>
        </form>';

}
//using post method to get form data
function get_form_data(){
    $user = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $saltedHashPass = password_hash($password, PASSWORD_DEFAULT, array('cost' => 12)); //add salt to prevent rainbow table attacks
    
    return $userInfo = array($user, $email, $saltedHashPass);
}
function addUser(){
    $userArray = get_form_data(); //converts form-to-string array and salts and hashes password
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
            echo "User registered";
        } else {
            echo "User not registered";
        }
    }
}


//password_verify() function to check password against hash need to make later