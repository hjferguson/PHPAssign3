<!--register new users need name, email and password-->
<html>
    <head>
        <title>Login</title>
        <!-- <link rel="stylesheet" type="text/css" href="main.css"> -->
    </head>
    <body>
        <main>
            <h1>Log In</h1>
            
            <?php
            display_login(); //shows form
            if(isset($_POST['submit'])){
              login();
            }
            ?>
           
        </main>
    </body>
</html>                                                                                                                                                             