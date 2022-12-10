<?php

    display_register(); //shows form
    if(isset($_POST['submit'])){
        addUser();
    }

?>