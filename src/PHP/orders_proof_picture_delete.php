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

//Bu islemi depo ve agen kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}

//gelen degerleri al
$orders_id=$conn->escape_string($_GET["orders_id"]);
$picture_path=$conn->escape_string($_GET["picture_path"]);    



$filePath = '../images/' . $picture_path;

if (file_exists($filePath)) {
    if (unlink($filePath)) {
        $sql = "delete from order_files where orders_id = $orders_id and picture_path = '$picture_path'";
        echo f_delete($sql,$conn); 
    }
}else{
        $sql = "delete from order_files where orders_id = $orders_id and picture_path = '$picture_path'";
        echo f_delete($sql,$conn);
}        


?> 

        
        
        
