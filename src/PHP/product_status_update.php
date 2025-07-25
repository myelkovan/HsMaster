<?php
include("login/db_connect.php");
include("utils/jwt.php");
include("utils/mail.php");



//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}

//Bu islemi agent kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}

$orders_id                   = $conn->escape_string($_POST["orders_id"]);
$product_id                  = $conn->escape_string($_POST["product_id"]);
$bought                      = $conn->escape_string($_POST["bought"]);
$product_description_label   = $conn->escape_string($_POST["product_description_label"]);
$product_price               = $conn->escape_string($_POST["product_price"]);
$product_buy_price           = $conn->escape_string($_POST["product_buy_price"]);
$product_buy_link            = isset($_POST["product_buy_link"]) ? $conn->escape_string($_POST["product_buy_link"]) : 'null';
$return_status               = isset($_POST["return_status"]) ? $conn->escape_string($_POST["return_status"]) : 'null';   
$return_cost                 = isset($_POST["return_cost"]) ? $conn->escape_string($_POST["return_cost"]) : 'null'; 
$cancel_reason               = isset($_POST["cancel_reason"]) ? $conn->escape_string($_POST["cancel_reason"]) : 'null'; 
$now                         = $conn->escape_string($_POST["now"]);



//product tablosuna gelen datalari yaz bouth 1 ise bought_date girilecek
$a = "";
$b = "";
$status= "";

if ($bought == 1){
   $b = ",bought_date = '$now'";
}elseif ($bought == -1){
   $b = ",bought_date = null";
}  

$sql_update = "Update product Set product_description_label='$product_description_label', product_buy_link = '$product_buy_link', 
                product_buy_price=$product_buy_price, return_status=$return_status, return_cost=$return_cost, cancel_reason='$cancel_reason' " . $b . $a .
              " Where id = $product_id";

$ret = f_update($sql_update, $conn, false);
        


//tracking numarasi array olarak geldi bunlari product_tracking_numbers tablosuna ekle 
$tracking_numbers = isset($_POST['product_tracking_numbers']) ? json_decode($_POST['product_tracking_numbers'], true) : [];
if (!empty($tracking_numbers)) {

    $full_tracking_string = '';
    foreach ($tracking_numbers as $tracking) {
        $tracking_number = $conn->escape_string($tracking['tracking_number']);
        
        if($full_tracking_string !='' ){
            $full_tracking_string = $full_tracking_string . ",";
        }
        $full_tracking_string = $full_tracking_string . "'".$tracking_number."'";
        
        $sql = "SELECT 1 from product_tracking_numbers WHERE orders_id = $orders_id and product_id = $product_id and tracking_number = '$tracking_number'  ";
        if ($result = $conn->query($sql)) {
            if ($result->num_rows == 0) {
      
                $tracking_sql = "INSERT INTO product_tracking_numbers (orders_id, product_id, tracking_number) 
                                 VALUES ($orders_id, $product_id, '$tracking_number')
                                 ON DUPLICATE KEY UPDATE tracking_number = VALUES(tracking_number)";
                $conn->query($tracking_sql);
            }
        }
    }
    
    f_update("Delete from product_tracking_numbers where orders_id = $orders_id and product_id = $product_id and tracking_number not in ($full_tracking_string)", $conn, false);  




//tracking numarasi arrayi bossa oncekiler sil, kullanici bilerek tracking numaralarini silmis olabilir
}else{
    $query = "DELETE FROM product_tracking_numbers WHERE orders_id = $orders_id AND product_id = $product_id";
    f_update($query, $conn, false);
}

   
//order tablosundaki calculated urun fiyat toplamini guncelle
$sql = "update orders set calculated_product_total_price = (select SUM(CASE WHEN (((product_price * product_quantity) + product_shipment_cost) * 1.06625)  > product_buy_price 
                                                                            THEN ROUND((((product_price * product_quantity) + product_shipment_cost) * 1.06625),2)
                                                                            ELSE ROUND(product_buy_price,2)
                                                                    END)
                                                                    from product where orders_id = $orders_id and cancel_date is null )
        where id = $orders_id";
        
   
$ret = f_update($sql, $conn, false);



  
  
//bought degismisse kac product var kaci bought kontrol et -1 veya 1 geliyor 
if ($bought != 0 ){
    $sql = "SELECT COUNT(*) AS rowcount, COUNT(bought_date) AS bought_count, COUNT(arrive_date) AS arrive_count FROM product WHERE orders_id = $orders_id and cancel_date is null";
    if ($result = $conn -> query($sql)) { 
        $row = $result->fetch_object();
        $rowcount = $row->rowcount;
        $bought_count = $row->bought_count;
        $arrive_count = $row->arrive_count; 

        //hepsi satin alinmissa order statusu bought yap
        if ($rowcount==$bought_count){
            $sql = "select 1 from order_status where orders_id = $orders_id and status_id=2";
            if ($result = $conn -> query($sql)) {

                $sql_update = "Insert into order_status(orders_id, status_id, date) Values($orders_id, 2, '$now')";
                $ret = f_update($sql_update, $conn, false);
                
                if ($ret >= 0){
                   send_mail_bought_all_items($conn, $orders_id );
                }
            }    
        }else{
             $ret = f_delete("delete from order_status where orders_id=$orders_id and status_id = 2", $conn, false);
        }
        
        
        //hepsi arrived olmus
        if ($rowcount==$arrive_count){
            $sql = "select 1 from order_status where orders_id = $orders_id and status_id=3";
            if ($result = $conn -> query($sql)) {
                
                //Girilmemis, o zaman kayit gir ve mail at
                if ($result->num_rows == 0) {
                     $sql_insert = "Insert Into order_status(orders_id, status_id, date ) Values($orders_id, 3, '$now')";
                     $ret = f_update($sql_insert, $conn, false);
                     if ($ret >= 0){
                         send_mail_arrived_warehouse($conn, $orders_id, $tracking_number);
                     }
                }
            }
        }else{
            $ret = f_delete("delete from order_status where orders_id=$orders_id and status_id = 3", $conn, false);  
        }    

    }
}

else{

    //Payment Waiting yap
    if ($product_buy_price !='null' && $product_price != 'null') {
        //urunun fiyati fazla
        if ($product_buy_price > $product_price){
       
             //odenmemis ise payment waiting yapacagiz?
             $sql = "SELECT 1 FROM orders WHERE id = $orders_id and paid_date_additional is null";
             if ($result = $conn -> query($sql)) { 
                 if ($result->num_rows > 0) {
                     
                      $sql_insert = "Insert Into order_status(orders_id, status_id, date ) Values($orders_id, 0, '$now')";
                      $ret = f_update($sql_insert, $conn, false);
                      if ($ret >= 0){
                         send_mail_price_increased($conn, $orders_id );
                      }
                 }
             }
                        
                        
            
        }else{ 
            //agent bir urunun fyatini dusurdu fiyat dustu ama payment waitingden in progrese gececek miyiz?
            //diger urunlerde fiyat farki nedir ona bakmamiz lazim bir tane yuksek fiyatli varsa hala odeme yapilmali
            $sql = "SELECT 1 FROM orders WHERE id = $orders_id and cancel_date is null and calculated_product_total_price > product_total_price and paid_date_additional is null ";
            if ($result = $conn -> query($sql)) { 
                //fiyati yuksek kayit yok payment waitin yerine in progress yapmak lazim tabi payment waitingdeysek
                if ($result->num_rows == 0) {
                     
                    //son statu payment waiting mi?
                    $sql = "SELECT status_id FROM order_status WHERE orders_id = $orders_id order by id desc limit 1";
                    if ($result = $conn -> query($sql)) { 
                        $row = $result->fetch_object();
                        $status_id = $row->status_id;
                        
                        
                        //payment waiting ise in progress yap 
                        if ($status_id == 0){
                             $sql_insert = "Insert Into order_status(orders_id, status_id, date ) Values($orders_id, 1, '$now')";
                             $ret = f_update($sql_insert, $conn, false);
                        }
                    }
                     
                }
            }
        } 
        
    }
    
}

$conn->close();
echo $ret;


?> 
