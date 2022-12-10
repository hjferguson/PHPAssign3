<?php

    display_upload();
    if(isset($_POST['submit'])){
        echo $_SESSION['user'];
        upload_file($_SESSION['user']);
    }