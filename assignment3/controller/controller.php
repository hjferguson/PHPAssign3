<?php
    //Harlan Ferguson 101133838
    
    require "functions.php";
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
    }
?>