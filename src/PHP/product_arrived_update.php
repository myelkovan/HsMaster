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

//sadece depo ve agent user bu verileri gorebilir
if (f_isAgenOrWarehouseUser($token_user_id,$conn) == false){
    return;
}


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$orders_id                   = $conn->escape_string($_POST["orders_id"]);
$product_id                  = $conn->escape_string($_POST["product_id"]);
$arrived                     = $conn->escape_string($_POST["arrived"]);
$now                         = $conn->escape_string($_POST["now"]);


if ($arrived == "1"){
    $sql_update = "Update product Set arrive_date='$now' Where id = $product_id";
}else{
    $sql_update = "Update product Set arrive_date = null Where id = $product_id"; 
}
$ret =  f_update($sql_update, $conn, false);



//arrive tarihi girildi ise hangi order oldugunu bul
if ($ret >= 0 && $arrived == "1"){
   
    $status_id = 3; //arrived to the warehouse
    //order status te daha once tracking girildi diye kayit girilmis mi?
    
    $sql = "select 1 from order_status where orders_id=$orders_id and status_id=$status_id";
    if ($result = $conn -> query($sql)) {
        
        //Girilmemis, o zaman kayit gir ve mail at arrived warehouse 
        if ($result->num_rows == 0) {
             $sql_insert = "Insert Into order_status(orders_id, status_id, date ) Values($orders_id, $status_id, '$now')";
             $ret = f_update($sql_insert, $conn, false);
            if ($ret >= 0){
                send_mail_arrived_warehouse($conn, $orders_id, '');
            }
            echo $ret;
        }
    }
}
     

$conn->close();
echo $ret;
                        

?> 
