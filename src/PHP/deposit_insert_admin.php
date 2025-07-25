
<?php

//Admin kullaniciyi buldu manuel kullaniciya para iadesi yapiyor
include("login/db_connect.php");
include("utils/jwt.php");


//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}

//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
//Bu islemi warehouse ve agent kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}





$user_id            = $conn->escape_string($_POST["user_id"]);
$amount             = $conn->escape_string($_POST["amount"]);
$description        = isset($_POST["description"]) ? $conn->escape_string($_POST["description"]) : 'NULL';
$now                = $conn->escape_string($_POST["now"]);

$LogDescription = " user_id :" .  $user_id .  " Amount :" .  $amount . " Description :" .  $description;
f_log($token_user_id,  "Deposit Insert Admin", $LogDescription,  1, $conn, false);


$sql_insert = "Insert Into deposit( user_id, orders_id, stripe_product_id, amount, description, date) Values($user_id, null, null, $amount, '$description', '$now')";
echo f_update($sql_insert, $conn);




?> 



