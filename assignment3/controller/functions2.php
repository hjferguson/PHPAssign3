<?php

require "../model/db_config.php";
echo "<p>this is before the try</p>";
try {
    echo "this is after the try";
    $db_con = new PDO($db_info, $username, $password);
    echo "connected to DB";
    // echo $db_con;
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    echo "PDO database not connected error: " . $error_message . "<br>";
    // exit(); //stop script
}
echo "something before the try query";
try {
    echo "going to try a query";
    $query = "SELECT * FROM users";
    $statement = $db_con->prepare($query);
    if (!$statement) print_r($db_con->errorInfo()); //remove when done
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    $statement->closeCursor();
    foreach ($result as $row) {
        echo $row['Email'];
    }
} catch (PDOException $e) {
    $error_message = $e->getMessage();
    echo "PDO database query error: " . $error_message . "<br>";
    return false;
    //exit(); //stop script
}

//sql statement that clears a table called users

