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

//gelen degerleri al
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true); 
}


$orders_id   = $conn->escape_string($_POST["orders_id"]);
$product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];  //[1,3,5]
$actions     = isset($_POST['actions']) ? $_POST['actions'] : [];         // [15,c5,c4] c ile baslarsa cancel ve 5 de cancel sebebi
$now         = $conn->escape_string($_POST["now"]);                        //  15,05,05  1 return demek 5 de sebebi, 0 iade request 5 musteri istedi


$sql = "SELECT user_id FROM orders WHERE id = $orders_id";
if ($result = $conn->query($sql)) {
    $row = $result->fetch_object();
    $user_id =  $row->user_id;
}

        
//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik -- kendisi degilse
if ($token_user_id != $user_id){
    //Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
    if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false){
        echo -1;
        return;
    }
}



        
$ProductTotalCost = 0;
$index = 0;
$ret = "0";

//id arrayi varsa tum arrayi dolasacagiz 
if (!empty($product_ids)) {
    $canceledCount = 0;
    $index = 0;
    
    //iptal olan tum urunlerin id lerini dolasiyoruz
    foreach ($product_ids as $product_id) {
        //urunun fiyat bilgilerini alip iptal edilen urunlerin fiyatini depositoya yatiracagiz
        $sql = "SELECT product_price, product_quantity, product_shipment_cost, bought_date, product_buy_price,
                       (SELECT you_buy FROM orders WHERE id = $orders_id) AS you_buy, 
                       (SELECT user_id FROM orders WHERE id = $orders_id) AS user_id ,
                       (SELECT paid_date_additional FROM orders WHERE id = $orders_id) AS paid_date_additional 
                FROM product WHERE id = $product_id and cancel_date is null";


        if ($result = $conn->query($sql)) {
            $row = $result->fetch_object();
            $product_price = (float) $row->product_price; 
            $product_buy_price = (float) $row->product_buy_price; 
            $paid_date_additional = (float) $row->paid_date_additional; 
            $product_shipment_cost = (float) $row->product_shipment_cost; 
            $product_quantity = (int) $row->product_quantity; 
            $bought_date =  $row->bought_date; 
            $you_buy = (int) $row->you_buy; 
            $user_id = (int) $row->user_id; 

            //urunun toplan fiyatini bul
            if ($you_buy == 2){
                $product_price = ($product_price * $product_quantity) + $product_shipment_cost;
                $tax = round($product_price * 0.06625, 2);
                $product_price = round($product_price + $tax, 2);
            }else{
                $product_price = 0; // urunu musteri almis geri odenecek tutar yok
            }
                
            //islem default cancel ama arrayde baska geldi ise onu dikkate al
            $action = "";
            $add_sql = "";
            if (!empty($actions)){
                $action = substr($actions[$index], 0, 1);  // Get the first character 0=reguested,1= returned,2 = not possible c=cancel
                $reason = substr($actions[$index], 1);  // Get the remaining part
            }else{
                $action = 'c';
                $reason = 5;
            }
            
            if ($action == 'c' || $action == "2")  {   // cancel veya return
                //musteri product price difference odemis ve sonra iptal etmis bu durumda son odedigini geri iade etmeliyiz ama bazen musteri fiyati calculateddan fazla olabilir
                if($paid_date_additional != null && $product_buy_price > $product_price ){ //buy price bir ununun tax shipment vs dahil fiyati
                     $ProductTotalCost += $product_buy_price;  //urun fiyatlarini topla
                }else{
                    // urunun musteri tarafindan odenen toplam fiyatiniu bul
                    $ProductTotalCost += $product_price;  //urun fiyatlarini topla
                }
            }
 
  //echo $action . $reason;
       
       
            if ($action == 'c') {
                $sql = "Update product set cancel_date = '$now' ,cancel_reason = $reason WHERE orders_id = $orders_id AND id = $product_id"; 
                $ret = f_update($sql, $conn, false);
                $canceledCount = $canceledCount + 1;
            }else{
                if ($action == "0" || $action == "1" || $action == "2" || $action == "3"){ //iade turleri request, iade, iade olamaz
                    $sql = "Update product set return_status = $action, cancel_reason = $reason  WHERE orders_id = $orders_id AND id = $product_id"; 
                    //echo $sql;
                    $ret = f_update($sql, $conn, false);
                    
                    //iade
                    if($action == "2" || ($action == "0" && $you_buy !== 2)) { //musteri kendi aldi ve iade etti ise return request yapiyor ama labelda aliyor kensin iade paet olcusu kuculecek asagiya devam etmesi lazim
                        $canceledCount = $canceledCount + 1;
                    }
                }
            }
     
         }
        $index = $index + 1;
    }

  // iade ve cancel varsa asagiya devem edecegiz, iade talet ettiyse devam etme
   if ($canceledCount == 0){
        $conn->close();
        echo $ret;     
        return;
   }



    
  //cancel edilmeyen urun var mi? yoksa hepsi mi cancel edilmis? Hepsi ise orderi da cancel yapacagiz     
  $sql = "select 1 from product WHERE orders_id = $orders_id AND cancel_date is null"; 
  if ($result = $conn->query($sql)) {
    if ($result->num_rows == 0) { //heposi iptal olmus
        //orderi iptal et
        $sql = "UPDATE orders SET cancel_date = '$now' WHERE id = $orders_id AND user_id = $user_id"; 
        $ret = f_update($sql, $conn, false);
    }
  }
  
  
  //islem sonrasinda devam eden urun kaldimi? yoksa hepsi iptal veya return ise paranin tamamini deposit yap
  $sql = "select 1 from product WHERE orders_id = $orders_id AND cancel_date is null and (return_status !=2 or return_status is null)"; 
  if ($result = $conn->query($sql)) {
    if ($result->num_rows == 0) { //tum urunler iade veya iptal
  
        //order iptal olursa musterinin odedigi tum parayi iade et. product price difference odenmisse calculated_product_total_price i dikkate al
        // bu noktada sorun cikti calculated 5 ama adamin fiyati 6 bu durumda adam 6 odedi.
        //Tek urun iade etse urunun parasini iade edip daha sonra biz kargolayinca kargodan dolayi degiseni iade edecegiz
        
        $sql = "select (CASE WHEN you_buy != 2 then    
                                    case when paid_date_final != null 
                                          then (calculated_custom_fee + calculated_service_fee + calculated_shipment_cost )
                                          else (custom_fee + service_fee + shipment_cost )
                                    end      
                                                
                                    else 
                                        CASE When paid_date_additional is not null and calculated_product_total_price >  product_total_price 
                                         then (custom_fee + service_fee + shipment_cost + calculated_product_total_price)
                                         else (custom_fee + service_fee + shipment_cost + product_total_price ) end 
                                    End) as total_price 
                from orders WHERE id = $orders_id"; 
        if ($result = $conn->query($sql)) {
            $row = $result->fetch_object();
            $ProductTotalCost = (float) $row->total_price; 
           /* 
            //iade edilen urun varsa bu urunlerin 3 dolar fee ve extra costunu topla
            $sql = "select (count(*) * 3) + COALESCE(SUM(return_cost), 0) as return_cost from product WHERE orders_id = $orders_id and return_status = 1 "; 
            
            if ($result = $conn->query($sql)) {
                $row = $result->fetch_object();
                $returnCost = (float) $row->return_cost;   
                $ProductTotalCost = $ProductTotalCost - $returnCost;
            }
            */
            $sql = "update orders set  product_total_price = 0, calculated_product_total_price = 0 WHERE id = $orders_id";
            $ret = f_update($sql, $conn, false);
        }
        

  
    }else{    
        //tek urun iptal edildi ise yeni toplam fiyati ordera yaz. Iptal olmamis productlarin bizim fiyatlarimizi topla calculated alana yaz. Bizim fiyat dusukse musterininkini al
        $sql = "update orders set   
               product_total_price = (
                    SELECT SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2))
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and $you_buy != 2) )
                ),
                
                calculated_product_total_price = (
                    SELECT SUM(
                        CASE
                            WHEN (((product_price * product_quantity) + product_shipment_cost) * 1.06625) > COALESCE(product_buy_price,0)
                            THEN ROUND((((product_price * product_quantity) + product_shipment_cost) * 1.06625),2)
                            ELSE COALESCE(ROUND(product_buy_price,2), 0)
                        END
                    )
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and $you_buy != 2) )
                ),
                
                package_weight = (
                     SELECT COALESCE(SUM(
                            CASE 
                                WHEN product_weight_units = 'kg' 
                                THEN product_weight * 2.20462 * product_quantity
                                WHEN product_weight_units = 'gr' 
                                THEN product_weight * 0.00220462 * product_quantity
                                WHEN product_weight_units = 'oz' 
                                THEN product_weight * 0.0625 * product_quantity
                                ELSE product_weight * product_quantity
                            END
                        ), 0)
                        FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and $you_buy != 2) )
                ),
                
                package_length = (
                    SELECT COALESCE(SUM(
                        CASE 
                            WHEN product_length_units = 'cm' 
                            THEN (product_length / 2.54) * product_quantity
                            ELSE product_length * product_quantity
                        END), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and $you_buy != 2) )
                ),
                
                package_width = (
                    SELECT COALESCE(MAX(
                        CASE 
                            WHEN product_length_units = 'cm' 
                            THEN (product_width / 2.54)
                            ELSE product_width
                        END), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and $you_buy != 2) )
                ),
                
                package_height = (
                    SELECT COALESCE(MAX(
                        CASE 
                            WHEN product_length_units = 'cm' 
                            THEN (product_height / 2.54)
                            ELSE product_height
                        END), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and $you_buy != 2) )
                ) 
            WHERE id = $orders_id";
            
           $ret = f_update($sql, $conn, false);
        
        //fiyati fazla baska urun var mi? varsa payment waiting kalmali yoksa statuyu in progress yap     
        $sql = "select 1 from product WHERE orders_id = $orders_id AND cancel_date is null and 
                                            round((((product_price * product_quantity) + product_shipment_cost) * 1.06625),2)  <  COALESCE(ROUND(product_buy_price,2),0)"; 
        //odenmemis urun yok in progress yap
        if ($result = $conn->query($sql)) {
            if ($result->num_rows == 0) {
                //iptal islemi sonrasinda statuyu in progress yap
                $sql_insert = "insert into order_status (orders_id, status_id, date) values($orders_id, 1, '$now')";
                f_update($sql_insert, $conn, false);
            }
        }
    }
  }




    //cancel islemi sonrasinda geriye kalan urunler in tamami satin alinmis olabilir bu durumda order statu bought yapilmali. Ayni islem product_status_update te var bunlar function yapilmali
    $sql = "SELECT COUNT(*) AS rowcount, COUNT(bought_date) AS bought_count, COUNT(arrive_date) AS arrive_count FROM product WHERE orders_id = $orders_id and cancel_date is null";
    if ($result = $conn -> query($sql)) { 
        $row = $result->fetch_object();
        $rowcount = $row->rowcount;
        $bought_count = $row->bought_count;
        $arrive_count = $row->arrive_count; 

        //hepsi satin alinmissa order statusu bought yap
        if ($rowcount==$bought_count){
            //zaten 2 ise islem yapmana gerek yok
            $sql = "select 1 from order_status where orders_id = $orders_id and status_id=2";
            if ($result = $conn -> query($sql)) {
                 if ($result->num_rows == 0) { 
                    $sql_insert = "Insert into order_status(orders_id, status_id, date) Values($orders_id, 2, '$now')";
                    $ret = f_update($sql_insert, $conn, false);
                    
                    if ($ret >= 0){
                       send_mail_bought_all_items($conn, $orders_id );
                    }
                 }
            }    
        }else{
             $ret = f_delete("delete from order_status where orders_id=$orders_id and status_id = 2", $conn, false);
        }
    }
        
        
        
        
        
        


  //iade miktarini deposito olarak yatir  
  if ($ProductTotalCost != 0) {  
        $sql_insert = "INSERT INTO deposit (user_id, orders_id, amount, description, date) 
                                    VALUES ($user_id, $orders_id, $ProductTotalCost, 'JB-$orders_id Product canceled or returned', '$now')";
        $ret = f_update($sql_insert, $conn, false);
  }

  
  
}


$conn->close();
echo $ret;
?>


 