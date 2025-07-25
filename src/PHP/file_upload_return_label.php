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

if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}

$product_id  = $conn->escape_string($_POST["product_id"]);

  
  
$file_path = "";    
if(!empty($_FILES)) {
 
    if ($_FILES['file']['size'] == 0 && $_FILES['file']['error'] == 0){
         echo "file error";
    }
    
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
  
    $dot = explode('.', $file_name);
    $file_ext = strtolower(end($dot));
    $newFileName = uniqid() . '.'.$file_ext;
    $newFilePath = "../images/return_labels/" . $newFileName; 
    move_uploaded_file($file_tmp, $newFilePath);
    
    $sql = "Update product Set return_status = 1, return_label_path = '$newFileName' Where id = $product_id";

    if (f_update($sql, $conn)== 1){
        echo $newFileName;
    }else{
        echo -1;
    }


}  
    




?> 
