<?php

function noLogMenu(){
    echo("<nav> 
        <a href='controller.php?page=register'>Register</a> 
        <a>|</a>
        <a href='controller.php?page=login'>Login</a>
        <hr>
        </nav>");
}

function logMenu(){
    echo("<nav> 
        <a href='controller.php?page=logout'>Logout</a> 
        <a>|</a>
        <a href='controller.php?page=upload'>Upload</a>
        <a>|</a>
        <a href='controller.php?page=viewTable'>View Table</a>
        <hr>
        </nav>");
}


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
    foreach($result as $row){ //result is an associative array so I then loop through it and if Email column has line of $email then return true
        if($row['Email'] == $email){
            return true;
        }
    }
    return false;
}


function display_register(){
    
    echo '<form method="post">
        <input type="hidden" name="action" line="register">
        <label>Name:</label>
        <input type="text" name="name" maxlength="8" minlength="4" required><br>
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" minlength="8" required><br>
        <label>&nbsp;</label>
        <input type="submit" line="Register" name="submit"><br>
        </form>';

}

function display_login(){
    echo '<form method="post">
        <input type="hidden" name="action" line="login">
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" minlength="8" required><br>
        <label>&nbsp;</label>
        <input type="submit" line="Login" name="submit"><br>
        </form>';
}

function check_cookie(){
    if(isset($_COOKIE['user'])){
        //change line in email textbox to cookie line
        echo '<form method="post"> 
        <input type="hidden" name="action" line="login">
        <label>Email:</label>
        <input type="email" name="email" line="' . $_COOKIE['user'] . '" required><br>
        <label>Password:</label>
        <input type="password" name="password" minlength="8" required><br>
        <label>&nbsp;</label>
        <input type="submit" line="Login" name="submit"><br>
        </form>';
    } else {
        display_login();
    }
}

function display_upload(){
    echo '<form method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" line="upload">
        <label>File:</label>
        <input type="file" name="file" required><br>
        <label>&nbsp;</label>
        <input type="submit" line="Upload" name="submit"><br>
        </form>';
}   


//function that gets upload file from display_upload(), confirms it is a txt file,
// then explodes the file into an array and creates a table in the database
function upload_file($user){
    $file = $_FILES['file'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    $file_error = $file['error'];
    if(check_file_format($file)){
        $file_ext = explode('.', $file_name);
        $file_ext = strtolower(end($file_ext));
        $allowed = array('txt');
        if(in_array($file_ext, $allowed)){
            if($file_error === 0){
                if($file_size <= 1000000){
                    $file_name_new = $user . $file_name;
                    $table_name = $file_name_new;//harlanjazz@gmail.comtest.txt
                    $table_name = strstr($table_name, '@', true);
                    $table_name = $table_name . '_' . strstr($file_name, '.', true);
                    
                    $file_destination = '../model/uploads/' . $file_name_new;

                    if(move_uploaded_file($file_tmp, $file_destination)){
                        $file = fopen($file_destination, "r");
                        $lines_read = file($file_destination);
                        fclose($file);
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
                        
                        if(db_insert_query($db_con, $query)){
                            echo "Table created successfully";
                        } else {
                            echo "Error creating table";
                        };

                        foreach($lines_read as $line){
                            $line = explode(',', $line);
                            $query = "INSERT INTO $table_name (AssessmentID, CourseCode, AssessmentType, AssessmentDate, AssessmentTime, AssessmentStatus) VALUES ('$line[0]', '$line[1]', '$line[2]', '$line[3]', '$line[4]', '$line[5]')";
                            db_insert_query($db_con, $query);
                        }
                        echo "File uploaded successfully";
                        if(!isset($_SESSION['file'])){
                            $_SESSION['file'] = $file_name;
                        } 
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
    } else {
            echo "File not formatted correctly";
        }
}

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
// function send_email($email){
//     $to = $email;
//     $subject = "Email Verification";
//     $message = "Please click the link below to verify your email address: https://f2133838.gblearn.com/comp1230/assignments/assignment3/controller/controller.php?email=$email";
//     $headers = "From: a_user@user.com";
//     mail($to, $subject, $message, $headers);
// }


function display_table($user, $fileName){
    require "../model/db_config.php";
    $db_con = db_connect($db_info, $username, $password);
    $file_name_new = $user . $fileName;
    $table_name = $file_name_new;//harlanjazz@gmail.comtest.txt
    $table_name = strstr($table_name, '@', true);
    $table_name = $table_name . '_' . strstr($fileName, '.', true); //harlanjazz_test
    $query = "SELECT * FROM $table_name";
    $result = db_select_query($db_con, $query);
    
    echo "<table>";
    echo "<tr>";
    echo "<th>Assessment ID</th>";
    echo "<th>Course Code</th>";
    echo "<th>Assessment Type</th>";
    echo "<th>Assessment Date</th>";
    echo "<th>Assessment Time</th>";
    echo "<th>Assessment Status</th>";
    echo "</tr>";
    foreach($result as $row){
        echo "<tr>";
        echo "<td>" . $row['AssessmentID'] . "</td>";
        echo "<td>" . $row['CourseCode'] . "</td>";
        echo "<td>" . $row['AssessmentType'] . "</td>";
        echo "<td>" . $row['AssessmentDate'] . "</td>";
        echo "<td>" . $row['AssessmentTime'] . "</td>";
        echo "<td>" . $row['AssessmentStatus'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}


function check_file_format($file){ 
    $lines_read = file($file);
    $line_count = count($lines_read);
    $line_number = 0;
    $error = false;
    foreach($lines_read as $line){
        $line_number++;
        $line = explode(',', $line);
        if(count($line) != 6){
            echo "Line $line_number does not have 6 values";
            $error = true;
        }
        if(!is_numeric($line[0])){
            echo "Line $line_number does not have a numeric value for Assessment ID";
            $error = true;
        }
        if(strlen($line[1]) != 8){
            echo "Line $line_number does not have a 8 character value for Course Code";
            $error = true;
        }
        if(!is_string($line[2])){
            echo "Line $line_number does not have a string value for Assessment Type";
            $error = true;
        }
        if(!is_string($line[5])){
            echo "Line $line_number does not have a string value for Assessment Status";
            $error = true;
        }
        if($line[5] != "Current" && $line[5] != "Completed"){
            echo "Line $line_number does not have a valid value for Assessment Status";
            $error = true;
        }
    }
    if($error){
        return false;
    } else {
        return true;
    }
}

//function that displays all tables in the database where the table name has  the name of the user in it
// function display_tables($user){
//     require "../model/db_config.php";
//     $db_con = db_connect($db_info, $username, $password);
//     $query = "SHOW TABLES";
//     $result = db_select_query($db_con, $query);
//     foreach($result as $row){
//         $table_name = $row['Tables_in_assessment'];
//         if(strstr($table_name, $user)){
//             display_table($_SESSION['user'], $_SESSION['file']);
//         }
//     }
// }

