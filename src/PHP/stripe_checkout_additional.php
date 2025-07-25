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
    

    
//orderen product price degismesi sebebi ile fiyat farkini bul    
$sql = "select user_id, stripe_product_id, 
            (calculated_product_total_price - product_total_price) as price,
            (select sum(amount) from deposit where user_id = orders.user_id) as deposit
        From orders 
        Where id = $orders_id"; 

if ($result = $conn -> query($sql)) {
    $row = $result->fetch_object();
    $product_id =  $row->stripe_product_id;
    $order_user_id = $row->user_id;
    $user_deposit = ($row->deposit === NULL) ? 0 : floatval($row->deposit);
    $total_price = floatval($row->price);
}


//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik
if ($token_user_id != $order_user_id){
    return;
}

//tum miktari stripe ile alacagiz diye dusunelim
$stripe_price = $total_price;
//toplam odenecek miktar depositodan fazla ise depositoun tamamini kullan. Deposito yazma isi 
//paid_update_additional.php de cunku kullanici gercekten strip odemesi yaparsa o cagirilacak
if ($user_deposit +1 >= $total_price){
    //odenecek miktar depositodan kucuk ise zaten fronttan buraya gelmeyecek orada depositodan odeniyor
}else{
    $stripe_price = $total_price - $user_deposit;
}


    
$stripe_price = number_format($stripe_price, 2, '.', '');
$stripe_price = floatval(str_replace('.', '', $stripe_price));

//islem tarihi icin tarih saat al 20 dakikalik token olustur
$issuedAt   = new DateTimeImmutable();
$date = $issuedAt->modify('+20 minutes')->format("Y-m-d H:i:s");
$token = bin2hex(random_bytes(16));

$stripe = new \Stripe\StripeClient($stripeSecretKey);

$price = $stripe->prices->create([
  'unit_amount' => $stripe_price,
  'currency' => 'usd',
  'product' => $product_id,
]);

$payType = 'additional';
$checkout_session = $stripe->checkout->sessions->create([
  'ui_mode' => 'embedded',
  'line_items' => [[
    'price' => $price['id'],
    'quantity' => 1,
  ]],
  'mode' => 'payment',
  'return_url' => "https://login.hsmaster.ai/PayConfirm_Additional/$orders_id/$token/$payType",
]);

  

$sql = "update user set resettoken = '$token', resettokenexp = '$date' where id = $order_user_id";
f_update($sql, $conn);


echo json_encode(array('clientSecret' => $checkout_session->client_secret));


?> 





