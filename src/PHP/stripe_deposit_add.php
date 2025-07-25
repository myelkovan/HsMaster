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


if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


//virgul nokta duzelt
$amount   = $conn->escape_string($_POST["amount"]);
$amount = number_format($amount, 2, '.', '');
$amount = floatval(str_replace('.', '', $amount));


//kullanici adini al
$sql = "select name from user where id = $token_user_id";
if ($result = $conn -> query($sql)) {
    $row = $result->fetch_object();
    $name =  $row->name;
}
    

$issuedAt   = new DateTimeImmutable();
$date = $issuedAt->modify('+20 minutes')->format("Y-m-d H:i:s");
$token = bin2hex(random_bytes(16));
$stripe = new \Stripe\StripeClient($stripeSecretKey);


$product = $stripe->products->create([
    'name' => 'JetBasket',
    'description' => "To: " . $name 
]);
$product_id = $product->id;



$price = $stripe->prices->create([
  'unit_amount' => $amount,
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
  'return_url' => "https://login.hsmaster.ai/DepositConfirm/$token_user_id/$product_id/$token.'-'.$amount",
]);


    
$sql = "update user set resettoken = '$token', resettokenexp = '$date' where id = $token_user_id";
f_update($sql, $conn, false);


echo json_encode(array('clientSecret' => $checkout_session->client_secret));


?> 
