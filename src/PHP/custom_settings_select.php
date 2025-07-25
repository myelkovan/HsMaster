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

//sadece depo bu verileri gorebilir
if (f_isWarehouseUser($token_user_id,$conn) == false){
    return;
}


$sql = "select id, country_id, price, tax_percentage, 
            (select name from kt_country where id = country_id) as country_name,
            (select flag from kt_country where id = country_id) as country_flag
        From custom_settings order by country_id asc";   
        
            
              
echo f_select($sql,$conn); 
?> 


