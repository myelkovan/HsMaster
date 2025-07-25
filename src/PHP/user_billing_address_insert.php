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



//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
$user_id = (int) $conn->escape_string($_POST["id"]);
if ($token_user_id != $user_id) {
    //Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
    echo -1;
    return;
}

$billing_name       = isset($_POST["billing_name"]) ? $conn->escape_string($_POST["billing_name"]) : 'NULL';    
$billing_address    = isset($_POST["billing_address"]) ? $conn->escape_string($_POST["billing_address"]) : 'NULL';   
$billing_phone      = isset($_POST["billing_phone"]) ? $conn->escape_string($_POST["billing_phone"]) : 'NULL';    
$billing_tax_id     = isset($_POST["billing_tax_id"]) ? $conn->escape_string($_POST["billing_tax_id"]) : 'NULL';  


$sql_update = "Update user Set billing_name = '$billing_name', billing_address = '$billing_address', billing_phone = '$billing_phone', billing_tax_id = '$billing_tax_id' 
               Where id = $user_id ";
echo f_update($sql_update, $conn);


?> 
