<!--register new users need name, email and password-->
<html>
    <head>
        <title>Register</title>
        <!-- <link rel="stylesheet" type="text/css" href="main.css"> -->
    </head>
    <body>
        <main>
            <h1>Register</h1>
            
            <?php
            display_register(); //shows form
            if(isset($_POST['submit'])){
              addUser();
            }
            ?>
           
        </main>
    </body>
</html>                                                                                                                                                             