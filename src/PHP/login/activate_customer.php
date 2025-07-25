<?php


include("db_connect.php");
include("../utils/jwt.php");

$customer_id    = $conn->escape_string($_GET["customer_id"]);
$activate_token = $conn->escape_string($_GET["activate_token"]);
$device_id      = $conn->escape_string($_GET["device_id"]);


$sql = "update customer_device set activated = true where customer_id = $customer_id and token = '$activate_token' and device_id = '$device_id'";
echo f_update($sql, $conn);

?> 
