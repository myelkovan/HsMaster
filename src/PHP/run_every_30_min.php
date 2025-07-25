<?php
include("login/db_connect.php");
include("utils/jwt.php");

$apple_token = f_genAppleToken();

$sql = "Update settings Set value = '$apple_token' Where `key` = 'AppleToken'";
f_update($sql, $conn);


?> 
