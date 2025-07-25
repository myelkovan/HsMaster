<?php
include("login/db_connect.php");
include("utils/jwt.php");

$ret = f_isTokenValid();
if ($ret['success'] == false) {
    echo "invalidToken";  
    return;
}
$user_id = $ret['user_id'];


if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$id             =$conn->escape_string($_POST["id"]);
$table          =$conn->escape_string($_POST["table"]);
$column         =$conn->escape_string($_POST["column"]);
    
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
    $newFilePath = "../images/" . $newFileName; 
    move_uploaded_file($file_tmp, $newFilePath);

}  
    
    

$sql = "Update $table Set $column = '$newFileName' Where id = $id";
//echo $sql;

echo f_update($sql, $conn);


?> 
