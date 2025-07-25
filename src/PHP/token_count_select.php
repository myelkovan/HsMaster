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



//kullanicinin toplam depositosunu al
$sql = "SELECT (select sum(credit) from deposit where user_id = $user_id and paid_date is not null) as total_token_count, used_token_count FROM user where id = $user_id"; 
f_select($sql,$conn); 
?> 
