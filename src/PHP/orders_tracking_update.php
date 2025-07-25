
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

//gelen data boyle birsey
//[{"orders_id":"398","product_id":"1025","tracking_number":"[{\"tracking_number\":\"1\",\"arrive_date\":null},
//                                                            {\"tracking_number\":\"2\",\"arrive_date\":null}]"},
// {"orders_id":"398","product_id":"1026","tracking_number":"[{\"product_id\": 1026, \"tracking_number\": \"3\", \"arrive_date\": null}]"}]



$deleted = false;

foreach($_POST as $product) {
    $orders_id = $conn->escape_string($product['orders_id']);
    $product_id = $conn->escape_string($product['product_id']);
    $tracking_numbers = isset($product['tracking_number']) ? json_decode($product['tracking_number'], true) : [];

    //bir defa en basta hepsini sil asagida tekrar ekleyecegiz
    if ($deleted == false){
        f_update("DELETE FROM product_tracking_numbers WHERE orders_id = $orders_id", $conn, false);
        $deleted = true;
    }

    // ekle
    if (!empty($tracking_numbers)) {
        foreach ($tracking_numbers as $tracking) {
            
            $tracking_number = $conn->escape_string($tracking['tracking_number']);
            $tracking_sql = "INSERT INTO product_tracking_numbers (orders_id, product_id, tracking_number) 
                                                           VALUES ($orders_id, $product_id, '$tracking_number')";
            $conn->query($tracking_sql);
        }
    }
}


$conn -> close();
echo $product_id;

?> 




