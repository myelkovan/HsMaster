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

//sadece depo ve agent user bu verileri gorebilir
if (f_isAgenOrWarehouseUser($token_user_id,$conn) == false){
    return;
}


$orders_id  = $conn->escape_string($_GET["orders_id"]);

$sql = "Select product_total_price, calculated_product_total_price, package_weight, package_length, package_width, package_height,calculated_service_fee,
        (select COALESCE(SUM(return_cost), 0) + (3 * count(1)) from product where orders_id = o.id and return_status > 0) as return_cost,
      (select JSON_ARRAYAGG(CASE WHEN return_status = 0 then 'Return requested' 
                                   WHEN return_status = 1 then 'Return confirmed'
                                   WHEN return_status = 2 then 'Returned'
                                   WHEN return_status = 3 then 'Not returnable' end ) from product where orders_id = o.id order by id) as product_return_status,
                                   

         (SELECT CASE when SUM(((product_price * product_quantity) + product_shipment_cost) * 1.06625) > sum(product_buy_price)
                     then SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2))
                     else SUM(ROUND(product_buy_price,2))
                     End
         FROM product WHERE orders_id = o.id and return_status ='2')  as returned_total,
             
             
             
             
                                   
FROM orders o where id = $orders_id";   
echo f_select($sql,$conn); 
?> 

