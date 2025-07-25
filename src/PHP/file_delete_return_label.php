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


$product_id  = $conn->escape_string($_GET["product_id"]);
$filename   = $conn->escape_string($_GET["filename"]);  

// Path to the file directory
$filePath = '../images/return_labels/' . $filename;

if (file_exists($filePath)) {
    if (unlink($filePath)) {
        $sql = "update product set return_label_path = null where id = $product_id";
        echo f_update($sql, $conn);
        
        
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Failed to delete the file"]);
    }
}else{
    echo $filePath;
}

?> 
