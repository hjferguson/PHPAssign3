<?php
    //Harlan Ferguson 101133838 
    session_start();
    require "functions.php";
    setcookie("user", htmlspecialchars($_SESSION['user']), time() + (86400 * 5), "/"); //htmlsepcialchars helps with output

    $page = $_GET['page'] ?? "login"; //get page or default to upload
    getMenu();

    //multiple switches depending on session user

    if(isset($_SESSION['user'])){
        //make a switch
        //switch($page)
        switch($page){
            case("upload"):
                require "../view/upload.php";
                break;
        }

    }

    else{
        switch($page){
            case "register":
                
                require "../view/register.php";
                break;
            
            case "login":
                require "../view/login.php";
                
                break;
            
            // case "upload":
            //     require "upload.php";
            //     break;
    
        }


    }
    
   
?>