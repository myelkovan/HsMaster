<?php


include("db_connect.php");
include("../utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $user_id==null || $user_id == 0) {
    echo "invalidToken";  
    return;
}


$issuedAt   = new DateTimeImmutable();
$date = $issuedAt->modify('+20 minutes')->format("Y-m-d H:i:s");
$token = bin2hex(random_bytes(16));
    
$sql = "update user set resettoken = '$token', resettokenexp = '$date' where id = $user_id";
f_update($sql, $conn, false);


$sql = "Select resettoken From user where id = $user_id";
echo f_select($sql, $conn);



?> 
