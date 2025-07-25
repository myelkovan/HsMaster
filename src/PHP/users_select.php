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
if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}


//gelen degerleri al
$key  = $conn->escape_string($_GET["key"]);
   

//admin filitre olarak kullanici id veya isim girerse uyan kayitlarri yoksa ilk 50 kaydi goster. tum kullanicilari gostermeyiz  
if ($key == ''){
    $sql = "Select id, name, email, birth_date, mobile, sex, description, picture_path, background_path, starting_date, permission_level 
            From user limit 50";   
}else{
    $sql = "Select id, name, email, birth_date, mobile, sex, description, picture_path, background_path, starting_date, permission_level 
             From user where id = '$key' or name like '%$key%'";    
}            

f_select($sql,$conn); 
?> 
