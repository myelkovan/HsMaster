<?php

include("login/db_connect.php");
include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
    echo "invalidToken";
    return;
}

//Bu islemi warehouse kullanicilari yapabilir. Kullanicinin hyetkisi yoksa cik
if (f_isWarehouseUser($token_user_id, $conn) == false) {
    echo -1;
    return;
}


$sql = "select id, carrier_id, country_id,
            (select service_name from kt_carrier where id = profit_settings.carrier_id) as service_name, 
            (select carrier_code + '.png' from kt_carrier where id = profit_settings.carrier_id) as carrier_icon, 
            (select name from kt_country where id = profit_settings.country_id) as country_name,
            (select flag from kt_country where id = profit_settings.country_id) as country_flag,
            percentage_value, fixed_value, weight_1, weight_2 
        From profit_settings 
        order by country_id, carrier_id asc, weight_1";



echo f_select($sql, $conn);
