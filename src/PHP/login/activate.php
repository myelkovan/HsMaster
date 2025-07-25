<?php


include("db_connect.php");
include("../utils/jwt.php");

$email=$conn->escape_string($_GET["email"]);
$activate_token=$conn->escape_string($_GET["activate_token"]);


$sql = "update user set activated = true where email = '$email' and resettoken = '$activate_token'";
        
//echo f_update($sql, $conn);

 try {
   
    $result = f_update($sql, $conn,false);

   if ($result < 1) { 
      f_log( "000", "User Activation", "FAIL- User: " . $email . " activation failed" ,  1, $conn, true);
    } else {
      f_log("000","User Activation", "SUCCESS- User: " . $email . " activation was successful.", 1, $conn, true);
    }

     echo $result;

 } catch (Exception $e) {
   f_log( "000", "User Activation", "ERROR:" . $e->getMessage() . " User:" .  $email,  1, $conn, true);
   echo "ERROR: " . $e->getMessage();
 }

?> 
