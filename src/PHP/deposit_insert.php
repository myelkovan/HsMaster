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


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}

$user_id            = $conn->escape_string($_POST["user_id"]);
$orders_id          = $conn->escape_string($_POST["orders_id"]);
$stripe_product_id  = isset($_POST["stripe_product_id"]) ? $conn->escape_string($_POST["stripe_product_id"]) : 'NULL';
$amount             = $conn->escape_string($_POST["amount"]);
$description        = isset($_POST["description"]) ? $conn->escape_string($_POST["description"]) : NULL;
$resettoken         = isset($_POST["resettoken"]) ? $conn->escape_string($_POST["resettoken"]) : 'NULL';
$now                = $conn->escape_string($_POST["now"]);
$shipment_refund    = $conn->escape_string($_POST["shipment_refund"]); 



//order_id varsa bir alis veris sonrasinda kullaniciya iade edilen bir ucret vardir
//order_id yoksa kullanici kendine deposit yatiriyordur
if (floatval($orders_id) > 0) {
 
     //bir order uzerinde tekrar tekrar kaydet islemi yapilabilir her islemde onceki girilen kaydi siliyoruz
    if ($shipment_refund == "1"){
         f_delete("delete from deposit where user_id = $user_id and orders_id = $orders_id and shipment_refund = 1", $conn, false);  
    }
    
    //yeni degeri gir
    if(floatval($amount != 0)){  
        $sql_insert = "Insert Into deposit( user_id, orders_id, amount, description, date, shipment_refund) Values($user_id, $orders_id, $amount, '$description', '$now',$shipment_refund)";
        //echo $sql_insert;
        echo f_update($sql_insert, $conn);
    }

//Kullanici deposit yatiriyor
}else{
    
    //stripe ile para yatirma isleminde anlik bir token uretip user.resettoken'a yazmistik guvenlik icin bunu kontrol ediyoruz
    $server_now = new DateTime();
    $server_now = $server_now->format('Y-m-d H:i:s');
    
    $sql = "SELECT 1 as valid FROM user WHERE id = $user_id AND resettoken = '$resettoken' and resettokenexp > '$server_now'";
    if ($result = $conn->query($sql)) {
        $row = $result->fetch_object();
        if ($row) {
            $valid = $row->valid;
    
            if ($valid == 1) {
                $sql_insert = "INSERT INTO deposit(user_id, orders_id, stripe_product_id, amount, description, date) 
                               VALUES($user_id, null, $stripe_product_id, $amount, '$description', '$now')";

 
                if (f_update($sql_insert, $conn, false) > 0){
                    $sql = "update user set resettoken = null, resettokenexp = null where id = $user_id";
                    echo f_update($sql, $conn);
                }


            }
        } else {
            echo -1;
        }
   } else {
       echo -1;
   }
}

?> 