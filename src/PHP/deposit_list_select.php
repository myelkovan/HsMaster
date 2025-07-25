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

$user_id = $conn->escape_string($_GET["user_id"]);

//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
if ($token_user_id != $user_id){
    //Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
    if (f_isWarehouseUser($token_user_id, $conn) == false){
        echo -1;
        return;
    }
}


//kullanicinin deposito gecmisi
$sql = "SELECT date, amount, description, orders_id FROM deposit where user_id = $user_id order by id desc LIMIT 100"; 
f_select($sql,$conn); 
?> 
