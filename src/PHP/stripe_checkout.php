<?php

 
include("login/db_connect.php");
include("utils/jwt.php");
require_once "utils/stripe_secrets.php";
require_once "utils/stripe/init.php";
header('Content-Type: application/json');


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

$orders_id  = $conn->escape_string($_POST["orders_id"]);
$payType    = $conn->escape_string($_POST["payType"]);  // total, additional, final

    
    //Bu order icin odenmesi gereken ucreti aliyoruz
    if ($payType == "total"){
        $sql = "select user_id, stripe_product_id, deposit_used, deposit_remaining,
                        CASE When you_buy = 2 
                            Then ((shipment_cost + service_fee + custom_fee + product_total_price)) 
                            Else ((shipment_cost + service_fee + custom_fee)) 
                       END as price,
                       CONCAT(to_name, '\n\n', to_address, '\n\r', to_city, ', ', to_state, ' ', to_zipcode, ' ' , to_country) as address,
                       (select sum(amount) from deposit where user_id = orders.user_id) as deposit
                from orders where id = $orders_id";
                
    }else if ($payType == "additional"){
        
        //orderen product price degismesi sebebi ile fiyat farkini bul    
        $sql = "select user_id, stripe_product_id, 
                (calculated_product_total_price - product_total_price) as price,
                (select sum(amount) from deposit where user_id = orders.user_id) as deposit
            From orders 
            Where id = $orders_id"; 
        
        
    } else if ($payType == "final"){
        
        //orderen fiyat farkini bul    
        $sql = "select user_id, 
                stripe_product_id, 
                    (SELECT CASE when SUM(((product_price * product_quantity) + product_shipment_cost) * 1.06625) > sum(product_buy_price)
                             then SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2))
                             else sum(ROUND(product_buy_price, 2))
                             End
                    FROM product WHERE orders_id = orders.id and return_status ='2')  as returned_total,
                 
                 (select (count(*) * 3) + COALESCE(SUM(return_cost), 0) from product WHERE orders_id = orders.id and return_status = 2) as return_cost,
                 
            (calculated_shipment_cost + calculated_service_fee + calculated_custom_fee ) - (shipment_cost + service_fee + custom_fee) as price,
            (select sum(amount) from deposit where user_id = orders.user_id) as deposit
            From orders 
            Where id = $orders_id"; 
    }    
    
    
    
    
    if ($result = $conn -> query($sql)) {
        $row = $result->fetch_object();
        $order_user_id = $row->user_id;
        $product_id =  $row->stripe_product_id;  
        $user_deposit = ($row->deposit === NULL) ? 0 : floatval($row->deposit);
        
        if ($payType == "total"){
            $deposit_used =  ($row->deposit_used === NULL) ? 0 : floatval($row->deposit_used); 
            $deposit_remaining =  ($row->deposit_remaining === NULL) ? 0 : floatval($row->deposit_remaining); 
            $address = $row->address;
        }  
           
        $stripe_price = floatval($row->price);
        if ($payType == "final"){
            $returned_total = floatval($row->returned_total);
            $return_cost = floatval($row->return_cost);
            $stripe_price =  $stripe_price - floatval($row->returned_total) + floatval($row->return_cost);
        }
    }
      
 
    
    //token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
    if ($token_user_id != $order_user_id){
        return;
    }
    
    
    //kullanici order hazirlarken 10 dolar deposit varmis ama arada baska orderi odemis deposit 0 olmus
    //bu orderin odeme ekranini gosterirken tekrar bakiyoruz gercek deposit nekadar ayni ise toplam fiyattan deposit oranini
    //dus geri kalani kullanici odesin
    //degismis ise bastan hesap yapip order kaydinida guncelleyecegiz
  
  
    //kullanicinin depositosu fazla tamamini odedi, asagidaki satirlarin yerini degistirme 
     if ($user_deposit +1 >= $stripe_price){   //???? && $user_deposit > 0. finalda bu kisimda vardi
         $deposit_used = $stripe_price;
         $deposit_remaining = $user_deposit - $stripe_price;
         $stripe_price = 0; 
    
    //deposito ile fiyat arasinda 1 dolardan fazla fark var deposito bir kismini odeyecek
    }else { 
         $stripe_price = $stripe_price - $user_deposit;
         $deposit_used = $user_deposit;
         $deposit_remaining = 0;

    }
      
      
     //order kaydini guncelle. sadece ilk odeme icin bu verileri sakliyoruz
     if ($payType == "total"){
         $sql = "update orders set deposit_used = $deposit_used, deposit_remaining= $deposit_remaining where id = $orders_id";
         f_update($sql, $conn, false);
     }


    $issuedAt   = new DateTimeImmutable();
    $date = $issuedAt->modify('+20 minutes')->format("Y-m-d H:i:s");
    $token = bin2hex(random_bytes(16));
    
    $sql = "update user set resettoken = '$token', resettokenexp = '$date' where id = $order_user_id";
    f_update($sql, $conn, false);
    
     
    
    if ($stripe_price == 0) {
         echo json_encode(array('clientSecret' => $token . "$3", 'token' => $token));
         return;
    }

    
    //Strip ekrani hazirla ---------------------------------------------------------------------------------
    //stripe ucreti 14.28 yerine 1428 diye istiyor
    $stripe_price = number_format($stripe_price, 2, '.', '');
    $stripe_price = floatval(str_replace('.', '', $stripe_price));
    $stripe = new \Stripe\StripeClient($stripeSecretKey);
    
     if ($payType == "total"){
        $product = $stripe->products->create([
            'name' => 'JetBasket',
            'description' => "To: " . $address 
        ]);
        $product_id = $product->id;
        
        $sql = "update orders set stripe_product_id = '$product_id' where id = $orders_id";
        f_update($sql, $conn);
     }else{
          $conn->close(); 
     }
     
    
    $price = $stripe->prices->create([
      'unit_amount' => $stripe_price,
      'currency' => 'usd',
      'product' => $product_id,
    ]);
    
    

    $checkout_session = $stripe->checkout->sessions->create([
      'ui_mode' => 'embedded',
      'line_items' => [[
        'price' => $price['id'],
        'quantity' => 1,
      ]],
      'mode' => 'payment',
      'return_url' => "https://login.hsmaster.ai/PayConfirm/$orders_id/$token/$payType",
    ]);


    echo json_encode(array('clientSecret' => $checkout_session->client_secret));
 

?> 



