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

//Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
if (f_isWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}



$orders_id  = $conn->escape_string($_GET["orders_id"]);


$sql = "Select id, orders_id, product_link, product_buy_link, product_description, product_description_label, 
product_additional_description, product_price, product_quantity, 
product_shipment_cost, product_buy_price,
 (select JSON_ARRAYAGG(JSON_OBJECT('value', tracking_number, 'label', tracking_number)) AS tracking_numbers from product_tracking_numbers where product_id = product.id) as tracking_number, 
        product_length, product_width, product_height, product_length_units,
        product_weight, product_weight_units,
        product_picture_path, arrive_date, bought_date ,  (select you_buy from orders where id = product.orders_id) as you_buy 
        From product where orders_id in ($orders_id) and cancel_date is null order by id asc";   
        
            
              
echo f_select($sql,$conn); 
?> 


