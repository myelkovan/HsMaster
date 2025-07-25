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
$id=$conn->escape_string($_GET["id"]);


$sql = "Delete From user_address Where id = $id and user_id =$token_user_id ";  
echo f_delete($sql, $conn);

?>
 