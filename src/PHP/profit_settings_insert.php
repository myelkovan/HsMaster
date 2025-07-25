
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

//Bu islemi warehouse kullanicilari yapabilir. Kullanicinin hyetkisi yoksa cik
if (f_isWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$id                 = $conn->escape_string($_POST["id"]);
$carrier_id         = isset($_POST["carrier_id"]) ? $conn->escape_string($_POST["carrier_id"]) : 'NULL';
$percentage_value   = isset($_POST["percentage_value"]) ? $conn->escape_string($_POST["percentage_value"]) : 'NULL';
$fixed_value        = isset($_POST["fixed_value"]) ? $conn->escape_string($_POST["fixed_value"]) : 'NULL';
$country_id         = isset($_POST["country_id"]) ? $conn->escape_string($_POST["country_id"]) : 'NULL';
$weight_1           = isset($_POST["weight_1"]) ? $conn->escape_string($_POST["weight_1"]) : 'NULL';
$weight_2           = isset($_POST["weight_2"]) ? $conn->escape_string($_POST["weight_2"]) : 'NULL';




  
$sql_insert = "Insert Into profit_settings(carrier_id, percentage_value, fixed_value, country_id, weight_1, weight_2) 
                                    Values($carrier_id, $percentage_value, $fixed_value, $country_id, $weight_1, $weight_2)";

$sql_update = "Update profit_settings Set percentage_value = $percentage_value, fixed_value = $fixed_value, country_id = $country_id, 
                                          weight_1 = $weight_1, weight_2 = $weight_2, carrier_id = $carrier_id  
               Where id = $id";



if ($id == "" or $id == 0){
    echo f_update($sql_insert, $conn);
}else{
    echo f_update($sql_update, $conn);
}


?> 



