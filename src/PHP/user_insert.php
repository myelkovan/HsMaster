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

//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
$id                 =$conn->escape_string($_POST["id"]);
$name               =$conn->escape_string($_POST["name"]);
$email              =$conn->escape_string($_POST["email"]);
$mobile             =$conn->escape_string($_POST["mobile"]);
$birth_date         =$conn->escape_string($_POST["birth_date"]);
$permission_level   =$conn->escape_string($_POST["permission_level"]);




$sql_insert = "Insert Into user(name, email, mobile, birth_date, permission_level) Values('$name', '$email', '$mobile', '$birth_date', permission_level)";
$sql_update = "Update user Set name = '$name', email = '$email', mobile = '$mobile', birth_date = '$birth_date', permission_level = $permission_level Where id = $id";

if ($id == 0){
    echo f_update($sql_insert, $conn);
}else{
    echo f_update($sql_update, $conn);
}

?> 
