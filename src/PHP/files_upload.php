<?php
include("login/db_connect.php");
include("utils/jwt.php");

if (f_isTokenValid() == false){
    echo "invalidToken";  
    return;
}

if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$id             =$conn->escape_string($_POST["id"]);

$file_path = "";    
if(!empty($_FILES)) {
 
    if ($_FILES['file']['size'] == 0 && $_FILES['file']['error'] == 0){
         echo "file error";
    }
    
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    $tmp = explode('.', $file_name);
    $file_ext = strtolower(end($tmp));
    $file = uniqid() . '.'.$file_ext;
    $file_path = "../images/" . $file; 
    move_uploaded_file($file_tmp, $file_path);

    $file_path = $file;
    
    
    $sql = "Insert into order_files (orders_id,picture_path) Values( $id, '$file_path')";
    echo f_update($sql, $conn);
}     
    
    



?> 
