<?php
setcookie("user", $_SESSION['user'], time() + (86400 * 5), "/"); //set cookie for 5 days
echo "Session variable is: " . $_SESSION['user'] . "<br>";
echo "cookie is: " . $_COOKIE['user'] . "<br>";
