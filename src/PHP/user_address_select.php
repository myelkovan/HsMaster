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
$user_id=$conn->escape_string($_GET["user_id"]);

//baskasinin ordderini silemezsin
if ($token_user_id != $user_id){
    return;
}


$sql = "Select id, user_id, alias, name, address, city, state, zipcode, country_id, 
            (select name from kt_country where id = country_id) as country, 
            (select flag from kt_country where id = country_id) as flag,
            phone,
            (select count(*) from orders where user_id = user_address.user_id and to_alias = user_address.alias) as ship_count
        From user_address where user_id = $user_id
        order by ship_count desc, id ";

echo f_select($sql,$conn); 
?> 

