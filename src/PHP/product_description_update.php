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

//Bu islemi depo ve agen kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}



//gelen degerleri al
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true); 
}

// urunlerin descriptionlari array olarak geldi
if (isset($_POST['description']) && is_array($_POST['description'])) {
    $descriptions = $_POST['description'];

    foreach ($descriptions as $description) {
        $product_id                = $conn->escape_string($description['product_id']);
        $product_description_label = $conn->escape_string($description['product_description_label']);

        $sql_update = "UPDATE product SET product_description_label = '$product_description_label' WHERE id = $product_id";
        $ret = f_update($sql_update, $conn, false);
    }
}

$conn->close();
echo $ret;
?>

