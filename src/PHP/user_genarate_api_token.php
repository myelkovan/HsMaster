<?php
 
include("login/db_connect.php");
include("utils/jwt.php");


//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}

// Generate secure token starting with HSC_
$random_bytes = bin2hex(random_bytes(16));
$token = 'HSC_' . $random_bytes;



$sql = "UPDATE user set api_token = '$token' where id = $token_user_id"; 
if (f_update($sql,$conn) == 1)
    echo $token;
else 
    echo "-1";



?> 
