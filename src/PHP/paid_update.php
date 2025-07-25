<?php
//Stripe ile odeme yapilinca ya dafa fake odendi tusuna basilinca burasi calisir

include("login/db_connect.php");
include("utils/mail.php");
include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}


//gelen degerleri al
$token=$conn->escape_string($_GET["token"]);
$now = $conn->escape_string($_GET["now"]);

if ($token == "x")
    $sql = "UPDATE deposit SET paid_date = $now WHERE date = (select resettokenexp from user where id = $token_user_id and resettoken = '$token')"; 
else {
    $sql = "UPDATE deposit SET paid_date = $now WHERE date = (select resettokenexp from user where id = $token_user_id )"; 
}
$ret = f_update($sql, $conn, false);
if ($ret < 0) {
    throw new Exception('Order update failed');
}
echo $ret;
$conn->close();
?> 
