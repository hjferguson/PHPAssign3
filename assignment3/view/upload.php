<?php

    display_upload();
    if(isset($_POST['submit'])){
        upload_file($_SESSION['user']);
    }