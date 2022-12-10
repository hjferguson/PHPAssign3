<?php
//session_start();
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

function check_cookie(){
    if(isset($_COOKIE['user'])){
        //change value in email textbox to cookie value
        echo '<form method="post"> 
        <input type="hidden" name="action" value="login">
        <label>Email:</label>
        <input type="email" name="email" value="' . $_COOKIE['user'] . '" required><br>
        <label>Password:</label>
        <input type="password" name="password" minlength="8" required><br>
        <label>&nbsp;</label>
        <input type="submit" value="Login" name="submit"><br>
        </form>';
    } else {
        display_login();
    }
}

function display_upload(){
    echo '<form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="upload">
        <label>File:</label>
        <input type="file" name="file" required><br>
        <label>&nbsp;</label>
        <input type="submit" value="Upload" name="submit"><br>
        </form>';
}    

//function that gets upload file from display_upload(), confirms it is a txt file, then explodes the file into an array and creates a table in the database
function upload_file($user){
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    $file_ext = explode('.', $file_name);
    $file_ext = strtolower(end($file_ext));
    $allowed = array('txt');
    if(in_array($file_ext, $allowed)){
        if($file_error === 0){
            if($file_size <= 1000000){
                //$file_name_new = $user . uniqid('', true) . '.' . $file_ext; //creates a unique file name
                //$file_name_new = $user . $file_name . '.' . $file_ext;
                $file_name_new = $user . $file_name;
                $table_name = $file_name_new;//harlanjazz@gmail.comtest.txt
                $table_name = str_replace(".$file_ext", '', $table_name);//harlanjazz@gmail.comtest 
                $file_destination = '../model/uploads/' . $file_name_new;
                if(move_uploaded_file($file_tmp, $file_destination)){
                    $file = fopen($file_destination, "r");
                    $file_data = fread($file, filesize($file_destination));
                    $file_data = explode(',', $file_data); //Array ( [0] => 1 [1] => COMP1230 [2] => quiz [3] => Dec 10 [4] => 8pm [5] => Completed )
                    fclose($file);
                    
                    // Check the value of the variable for special characters or reserved words that 
                    // might cause the CREATE TABLE statement to fail. If the variable contains any of 
                    // these characters or words, you will need to modify the value of the variable or

                    //using static table name works... might be that the email's '@' is now allowed in sql
                    //or that it is actually displaying at %20 like in the cookie, which also not sql happy
                    //try making session variable for name, not email, and using that... 


                    require "../model/db_config.php";
                    $db_con = db_connect($db_info, $username, $password);
                    $query = "CREATE TABLE IF NOT EXISTS $table_name (   
                        AssessmentID INT NOT NULL PRIMARY KEY,
                        CourseCode VARCHAR(8) NOT NULL,
                        AssessmentType VARCHAR(255) NOT NULL,
                        AssessmentDate VARCHAR(255) NOT NULL,
                        AssessmentTime VARCHAR(255) NOT NULL,
                        AssessmentStatus VARCHAR(255) NOT NULL
                    )";
                    //$query = "CREATE TABLE IF NOT EXISTS test (AssessmentID INT NOT NULL, CourseCode VARCHAR(8) NOT NULL, AssessmentType VARCHAR(255) NOT NULL, AssessmentDate VARCHAR(255) NOT NULL, AssessmentTime VARCHAR(255) NOT NULL, AssessmentStatus VARCHAR(255) NOT NULL)";
                    if(db_insert_query($db_con, $query)){
                        echo "Table created successfully";
                    } else {
                        echo "Error creating table";
                    };
                    foreach($file_data as $value){
                        //$value = explode(' ', $value);
                        echo "show value before insert SQL<br><br>";
                        print_r($value);
                        $query = "INSERT INTO $table_name (AssessmentID, CourseCode, AssessmentType, AssessmentDate, AssessmentTime, AssessmentStatus) VALUES ('$value[0]', '$value[1]', '$value[2]', '$value[3]', '$value[4]', '$value[5]')";
                        db_insert_query($db_con, $query);
                    }
                    echo "File uploaded successfully";
                } else {
                    echo "There was an error uploading your file";
                }
            } else {
                echo "Your file is too big";
            }
        } else {
            echo "There was an error uploading your file";
        }
    } else {
        echo "You cannot upload files of this type";
    }
}



//upload:

// the user can upload a .txt file

// SQL command CREATE TABLE

// exploding the file after reading the file

// SQL commend INSERT INTO table


// Now anytime we load the data for current and completed pages, it should load from the database, not the file


function login(){
    require "../model/db_config.php";
    $db_con = db_connect($db_info, $username, $password);
    if(check_user($db_con, $_POST['email'])){ //check if user exists
        //if user does exsist, check if password entered matches password in database
        if(password_verify($_POST['password'], $db_con->query("SELECT Password FROM users WHERE Email = '$_POST[email]'")->fetchColumn())){
            if(!isset($_COOKIE['user'])){
                $_SESSION['user'] = $_POST['email'];   
            }
                  
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
            //send_email($email);
            //echo "Please check your email for a verification link in order to log in.";
        else {
            echo "User not registered!";
        }
    }
}

//PARTIAL MARKS???????? It's literally impossible for this scope of assignment????
function send_email($email){
    $to = $email;
    $subject = "Email Verification";
    $message = "Please click the link below to verify your email address: https://f2133838.gblearn.com/comp1230/assignments/assignment3/controller/controller.php?email=$email";
    $headers = "From: a_user@user.com";
    mail($to, $subject, $message, $headers);
}