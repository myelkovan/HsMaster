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
//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
$user_id = (int) $conn->escape_string($_GET["id"]);
if ($token_user_id != $user_id)
{  
    return;
}



$sql = "Select id, billing_name, billing_address, billing_phone, billing_tax_id 
        From user 
        where id = $user_id";   
f_select($sql,$conn); 

?> 
