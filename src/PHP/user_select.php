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
$user_id = $conn->escape_string($_GET["id"]);


//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik -- kendisi degilse
if ($token_user_id != $user_id){
    //Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
    if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
        echo -1;
        return;
    }
}

$sql = "Select id, name, email, birth_date, mobile, sex, description, picture_path, background_path, starting_date, permission_level 
        From user 
        where id = $user_id";   
f_select($sql,$conn); 
?> 
