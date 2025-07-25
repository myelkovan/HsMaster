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
if (f_isWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}



//gelen degerleri al
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true); 
}


$product_id   = $conn->escape_string($_POST["product_id"]);
$tracking_id   = $conn->escape_string($_POST["tracking_id"]);
$location     = $conn->escape_string($_POST["location"]);


if($tracking_id != null ){
    $sql = "update product_tracking_numbers set location = '$location' where id = $tracking_id";
    echo f_update($sql, $conn);
}

if($product_id != null ){
    $sql = "update product set location = '$location' where id = $product_id";
    echo f_update($sql, $conn);
}

?>

