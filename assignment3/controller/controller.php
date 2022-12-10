<?php
    //Harlan Ferguson 101133838 
    session_start();
    require "functions.php";
    setcookie("user", htmlspecialchars($_SESSION['user']), time() + (86400 * 5), "/"); //htmlsepcialchars helps with output

    $page = $_GET['page'] ?? "login"; //get page or default to upload
    

    //multiple switches depending on session user

    if(isset($_SESSION['user'])){
        logMenu();

        switch($page){
            case("upload"):
                require "../view/upload.php";
                break;

            case("logout"):
                require "../view/logout.php";
                break;

            case("viewTable"):
                require "../view/viewTable.php";
                break;
        }

    }

    else{
        noLogMenu();

        switch($page){
            case "register":
                
                require "../view/register.php";
                break;
            
            case "login":
                require "../view/login.php";
                
                break;
            
        }


    }
    
   
?>