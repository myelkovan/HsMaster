<?php

include("login/db_connect.php");
include("utils/jwt.php");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: DELETE");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Only DELETE method is allowed"]);
    exit;
}


$ret = f_isTokenValid();
if ($ret['success'] == false) {
    echo "invalidToken";  
    return;
}
$user_id = $ret['user_id'];



// Extract the filename from the query parameters
parse_str(file_get_contents("php://input"), $data);
$id = isset($data['id']) ? $data['id'] : null;
$filename = isset($data['filename']) ? $data['filename'] : null;

if (!$filename) {
    http_response_code(400); // Bad Request
    echo json_encode(["error" => "Filename is required"]);
    exit;
}

// Path to the file directory
$filePath = '/path/to/your/images/directory/' . $filename;

if (file_exists($filePath)) {
    if (unlink($filePath)) {
        echo json_encode(["message" => "File deleted successfully"]);
        
    
    $sql = "Delete from order_files where orders_id =$id";
    echo f_update($sql, $conn);
        
        
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode(["error" => "Failed to delete the file"]);
    }
} else {
    
    $sql = "Delete from order_files where orders_id =$id";
    echo f_update($sql, $conn);
}
?>
