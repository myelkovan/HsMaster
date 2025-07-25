<?php
include("login/db_connect.php");
include("utils/jwt.php");
include("utils/mail.php");
header('Content-Type: text/plain');

date_default_timezone_set("Europe/Paris");


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


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}



$tracking_number    = $conn->escape_string($_POST["tracking_number"]);
$now                = $conn->escape_string($_POST["now"]);
 



//Boyle bir tracking no var mi?
$sql = "Select orders_id, arrive_date from product_tracking_numbers where tracking_number = '$tracking_number'";
if ($result = $conn -> query($sql)) {
  
    //Var, order numarasini al
    if ($result->num_rows > 0) {
        $row = $result->fetch_object();
        $orders_id = $row->orders_id;
        $arrive_date = $row->arrive_date;
        
         if ($arrive_date != null) {
            echo $arrive_date;
        }else{
                
                //tracking numarasina arrived_date gir 
                $sql = "Update product_tracking_numbers set arrive_date = '$now' where orders_id = $orders_id and tracking_number = '$tracking_number'";
                f_update($sql, $conn, false);
                
                //order status te daha once tracking girildi diye kayit girilmis mi?
                $sql = "select 1 from order_status where orders_id = $orders_id and status_id=3";
                if ($result = $conn -> query($sql)) {
                    
                    //Girilmemis, o zaman kayit gir ve mail at arrived warehouse 
                    if ($result->num_rows == 0) {
                         $sql_insert = "Insert Into order_status(orders_id, status_id, date ) Values($orders_id, 3, '$now')";
                         $ret = f_update($sql_insert, $conn, false);
                        if ($ret >= 0){
                             send_mail_arrived_warehouse($conn, $orders_id, $tracking_number);
                        }
                        echo $ret;
                    }
                }
                echo '1';
         }
    }else{
        //tracking yok
       echo '0';
    }
}else{
    //tracking yok
   echo '0';
}



$conn->close();



?> 


