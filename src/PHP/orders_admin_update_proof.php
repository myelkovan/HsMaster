
<?php
include("login/db_connect.php");
include("utils/jwt.php");
include("utils/mail.php");


//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}

//sadece depo ve agent user bu verileri gorebilir
if (f_isAgenOrWarehouseUser($token_user_id,$conn) == false){
    return;
}

if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$orders_id      = $conn->escape_string($_POST["orders_id"]);
$proof          = $conn->escape_string($_POST["proof"]);


$sql_update = "Update orders Set proof = '$proof' Where id = $orders_id";
echo f_update($sql_update, $conn, false);


send_mail_price_increased($conn, $orders_id );

$conn->close();


?> 




