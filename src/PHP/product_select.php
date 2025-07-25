<?php

include("login/db_connect.php");
include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
    echo "invalidToken";
    return;
}


//gelen degerleri al
$orders_id  = $conn->escape_string($_GET["orders_id"]);

$sql = "SELECT user_id FROM orders WHERE id = $orders_id";
if ($result = $conn->query($sql)) {
    $row = $result->fetch_object();
    $user_id =  $row->user_id;
}



//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik -- kendisi degilse
if ($token_user_id != $user_id) {
    //Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
    if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false) {
        echo -1;
        return;
    }
}


$sql = "Select id,orders_id, product_link, product_buy_link, product_description, product_description_label, 
            product_additional_description, product_price, product_quantity, product_shipment_cost, product_buy_price,
            product_length, product_width, product_height, product_length_units,
            product_weight, product_weight_units,
            product_picture_path, 
            bought_date,
            arrive_date,
            cancel_date,
            cancel_reason,
            return_status,
            return_cost,
            return_label_path,
            location, hscode,
            (select you_buy from orders where id =  $orders_id) as you_buy,
            
            (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    'product_id',      pt.product_id,
                    'tracking_number', COALESCE(pt.tracking_number, NULL), -- if no tracking number, return NULL
                    'arrive_date',     COALESCE(pt.arrive_date, product.arrive_date), -- if no arrive_date, return product's arrive_date
                    'location', COALESCE(pt.location, product.location)
                   ))
            FROM product_tracking_numbers pt WHERE pt.orders_id = $orders_id AND pt.product_id = product.id AND product.cancel_date is null) AS product_tracking_numbers
        
        From product where orders_id = $orders_id  order by id asc";



echo f_select($sql, $conn);
