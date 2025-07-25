
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


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$orders_id          = $conn->escape_string($_POST["orders_id"]);
$notes              = $conn->escape_string($_POST["notes"]);


$sql = "SELECT user_id FROM orders WHERE id = $orders_id";
if ($result = $conn->query($sql)) {
    $row = $result->fetch_object();
    $user_id =  $row->user_id;
}

//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik -- kendisi degilse
if ($token_user_id != $user_id){
    //Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
    if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
        echo -1;
        return;
    }
}

$sql = "update orders set notes = '$notes' where id = $orders_id";
echo f_update($sql, $conn);

?> 






