
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
$country_id         = $conn->escape_string($_POST["country_id"]);
$price              = isset($_POST["price"]) ? $conn->escape_string($_POST["price"]) : 'NULL';
$tax_percentage     = $conn->escape_string($_POST["tax_percentage"]);



$sql_insert = "Insert Into custom_settings( country_id, price, tax_percentage) Values($country_id, $price, $tax_percentage)";

$sql_update = "Update custom_settings Set country_id = $country_id, price = $price, tax_percentage = $tax_percentage  Where id = $id";


if ($id == "" or $id == 0){
    echo f_update($sql_insert, $conn);
}else{
    echo f_update($sql_update, $conn);
}


?> 



