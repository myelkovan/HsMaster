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
$orders_id=$conn->escape_string($_GET["orders_id"]);

$sql = "select paid_date, paid_date_additional, paid_date_final from orders where id = $orders_id";
echo f_select($sql,$conn); 

?> 


