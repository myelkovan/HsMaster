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

//Bu islemi warehouse kullanicilari yapabilir. Kullanicinin hyetkisi yoksa cik
if (f_isWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}



$sql = "SELECT pt.id, 
               pt.orders_id, 
               pt.arrive_date, 
               pt.tracking_number,p.product_description, p.product_quantity, p.product_picture_path, pt.location
        FROM product p, product_tracking_numbers pt 
        WHERE DATE(pt.arrive_date) >= DATE_SUB(CURDATE(), INTERVAL 3 DAY) and p.id = pt.product_id
        ORDER BY pt.arrive_date DESC"; 

f_select($sql,$conn); 
?> 

