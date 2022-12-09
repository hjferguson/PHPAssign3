<?php
    //Harlan Ferguson 101133838
    
    session_start();
    require "functions.php";
    setcookie("user", htmlspecialchars($_SESSION['user']), time() + (86400 * 5), "/"); //set cookie for 5 days

    $page = $_GET['page'] ?? "login"; //get page or default to upload
    getMenu();
    
    switch($page){
        case "register":
            
            require "../view/register.php";
            break;
        
        case "login":
            require "../view/login.php";
            
            break;
        
        case "upload":
            require "upload.php";
            break;

        case "setcookie":
            require "setcookie.php";
            break;
    }
?>