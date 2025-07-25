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
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$id                 =$conn->escape_string($_POST["id"]);
$user_id            =$conn->escape_string($_POST["user_id"]);
$alias              =$conn->escape_string($_POST["alias"]);
$name               =$conn->escape_string($_POST["name"]);
$address            =$conn->escape_string($_POST["address"]);
$city               =$conn->escape_string($_POST["city"]);
$state              =isset($_POST["state"]) ? $conn->escape_string($_POST["state"]) : 'NULL';
$zipcode            =$conn->escape_string($_POST["zipcode"]);
$country_id         =$conn->escape_string($_POST["country_id"]);
$phone              =$conn->escape_string($_POST["phone"]);



if ($token_user_id != $user_id){
    return;
}



$sql_insert = "Insert Into user_address(user_id, alias, name, address, city, state, zipcode, country_id, phone) Values($user_id, '$alias', '$name', '$address', '$city', '$state', '$zipcode', $country_id, '$phone')";

$sql_update = "Update user_address Set alias = '$alias', name = '$name',  address = '$address', city = '$city', state ='$state',  zipcode = '$zipcode', country_id = $country_id, phone ='$phone' Where id = $id";



if ($id == "" or $id == 0){
    echo f_update($sql_insert, $conn);
}else{
    echo f_update($sql_update, $conn);
}

?> 
