
<?php

//Orders tablosundan bir order ve iliskili olan tablolari siler

include("login/db_connect.php");
include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}


//gelen degerleri al
$orders_id=$conn->escape_string($_GET["id"]);


$sql = "SELECT user_id FROM orders WHERE id = $orders_id";
    if ($result = $conn->query($sql)) {
    $row = $result->fetch_object();
    $user_id =  $row->user_id;
}


//baskasinin ordderini silemezsin
if ($token_user_id != $user_id){
    return;
}


$sql = "Delete From product Where orders_id = $orders_id"; 
f_delete($sql, $conn, false);

$sql = "Delete From order_status Where orders_id = $orders_id"; 
f_delete($sql, $conn, false);

$sql = "Delete From product_tracking_numbers Where orders_id = $orders_id"; 
f_delete($sql, $conn, false);

$sql = "Delete From orders Where id = $orders_id";  
echo f_delete($sql, $conn);

?>
 
