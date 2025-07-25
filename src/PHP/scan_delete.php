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

//Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
if (f_isWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}


//gelen degerleri al
$id=$conn->escape_string($_GET["id"]);

$sql = "Update product_tracking_numbers set arrive_date = null Where id = $id";  
 
echo f_delete($sql, $conn);

?>
 