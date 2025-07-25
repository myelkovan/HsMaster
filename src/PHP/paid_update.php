<?php
//Stripe ile odeme yapilinca ya dafa fake odendi tusuna basilinca burasi calisir

include("login/db_connect.php");
include("utils/mail.php");
include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}


//gelen degerleri al
$orders_id=$conn->escape_string($_GET["orders_id"]);
$token=$conn->escape_string($_GET["token"]);
$now = $conn->escape_string($_GET["now"]);
$type = $conn->escape_string($_GET["type"]);


//orderin fiyat bilgilerini ve deposito bilgisini al
$sql = "SELECT user_id, product_total_price, shipment_cost, service_fee, custom_fee, you_buy, 
               (select COALESCE(SUM(return_cost), 0) + (3 * count(1)) from product where orders_id = orders.id and return_status > 0) as return_cost,
               
                 (SELECT CASE when SUM(((product_price * product_quantity) + product_shipment_cost) * 1.06625) > sum(product_buy_price)
                     then SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2))
                     else sum(ROUND(product_buy_price,2))
                     End
                FROM product WHERE orders_id = orders.id and return_status ='2')  as returned_total,
               
               calculated_product_total_price, calculated_shipment_cost, calculated_service_fee, calculated_custom_fee,
               (SELECT sum(amount) as deposit FROM deposit where user_id = orders.user_id) as deposit 
        FROM orders where id = $orders_id and user_id = $token_user_id"; 

if ($result = $conn -> query($sql)) {
    $row = $result->fetch_object();
    $you_buy = $row->you_buy;
    $order_user_id = $row->user_id; 
    $shipment_cost = $row->shipment_cost ??0; 
    $service_fee = $row->service_fee ?? 0; 
    $custom_fee = $row->custom_fee ?? 0;
    $return_cost = $row->return_cost ??0; 
    $returned_total = $row->returned_total ??0; 
    $product_total_price = $row->product_total_price ?? 0;
    $calculated_product_total_price = $row->calculated_product_total_price?? 0;
    $calculated_shipment_cost = $row->calculated_shipment_cost?? 0;
    $calculated_service_fee = $row->calculated_service_fee?? 0;
    $calculated_custom_fee = $row->calculated_custom_fee?? 0;
    $user_deposit = $row->deposit ?? 0;
    $deposit_used = floatval(0);
    $deposit_remaining = floatval(0);
    
    
    //token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
    if ($token_user_id != $order_user_id){
        return;
    }

    if ($type == 'total'){
        //toplan kullanicinin odedigi miktari hesapla ve varsa depositodan kes
        $base = $shipment_cost + $service_fee +  $custom_fee;
        if ($you_buy == "2") {
            $price = $base + $product_total_price;
        } else {
            $price = $base;
        }
        $description = "JB-$orders_id Initial cost";
        
    }else if ($type == 'additional'){
        $price = $calculated_product_total_price - $product_total_price;
        $description = "JB-$orders_id Product price difference";
        
    }else if ($type == 'final'){ 
        
         if ($you_buy !== "2") {
            $returned_total = 0;
         }
          
        $price = ($calculated_shipment_cost + $calculated_service_fee + $calculated_custom_fee + $return_cost - $returned_total ) - ($shipment_cost + $service_fee + $custom_fee );
        $description = "JB-$orders_id Shipment cost difference";
    }
    $price = round($price, 2);


    if ($user_deposit > 0){
          //deposito yetiyor    
          if ($user_deposit >= $price){
                $deposit_used = $price;
                $deposit_remaining = $user_deposit - $deposit_used;
          
          //deposito yetmiyor
          }else if ($user_deposit < $price){
              
               //deposit yetmiyor ama sadece 1 doilar altinda eksik kaliyorsa deposite eksi yaz gec
              if (($price - $user_deposit) < 1){
                $deposit_used = $price;
                $deposit_remaining = $user_deposit - $price;
              
              //deposito yetmiyor fark 1 dolardan fazla ise stripe + deposito kullanildi. kullanilan miktari depositodan dus
              }else{
                $deposit_used = $user_deposit;
                $deposit_remaining = 0;
              }
           
          }
    }
        
    

    //order tablosuna tarih girdigimizde odenmis olacak
    $add_sql = "";
    if ($type == 'total'){
        $add_sql = " paid_date='$now' ,deposit_used = $deposit_used, deposit_remaining=$deposit_remaining ";
    }else if ($type == 'additional'){
        $add_sql = " paid_date_additional='$now'";
    }else if ($type == 'final'){ 
        $add_sql = " paid_date_final='$now'";
    }
    
     
    try {
        // Start the transaction
        $conn->autocommit(false);
        
        $server_now = new DateTime();
        $server_now = $server_now->format('Y-m-d H:i:s');
        
        // Update the orders table
        $sql = "UPDATE orders SET $add_sql
                WHERE id = $orders_id AND user_id = $order_user_id
                AND EXISTS (SELECT 1 FROM user WHERE user.id = orders.user_id AND user.resettoken = '$token' AND resettokenexp > '$server_now')";
        
        $ret = f_update($sql, $conn, false);
        if ($ret < 0) {
            throw new Exception('Order update failed');
        }
    
        // Check if user has a deposit and deduct it
        if ($user_deposit > 0) {
            $sql_insert = "INSERT INTO deposit (user_id, orders_id, amount, description, date) 
                           VALUES ($order_user_id, $orders_id, $deposit_used * -1, '$description', '$now')";
            $ret = f_update($sql_insert, $conn, false);
            if ($ret < 0) {
                throw new Exception('Failed to insert deposit');
            }

            $LogDescription = "orders_id :" .  $orders_id . " Amount :" . ($deposit_used * -1) . " Description :" .  $description;
            f_log($order_user_id,  "Paid Update-Deposit Kullanma", $LogDescription,  1, $conn, false);
            
        }
    
        // Insert order status to mark the order as paid (odendi yap)
        $sql_insert = "INSERT INTO order_status (orders_id, status_id, date) VALUES ($orders_id, 1, '$now')";
        $ret = f_update($sql_insert, $conn, false);
        if ($ret < 0) {
            throw new Exception('Failed to insert order status');
        }
    
        // Commit the transaction if everything is successful
        $conn->commit();
        
        // Send email notification about the order
       send_mail_order_received($conn, $orders_id);
 
        //$function_name = 'send_mail_order_received';
       // exec( "php https://jetbasket.us/shop/PHP/utils/mail.php?function_name=$function_name&order_id=$orders_id");


    } catch (Exception $e) {
        $conn->rollback();
    }
    
    
}
echo $ret;
$conn->close();
?> 
