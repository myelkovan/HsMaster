<?php

use GuzzleHttp\Psr7\Message;
include("login/db_connect.php");
include("byelabel.php");
include("utils/jwt.php");


// Validate the token
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
    echo "invalidToken";
    return;
}

//Bu islemi warehouse kullanicilari yapabilir. Kullanicinin hyetkisi yoksa cik
if (f_isWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}



try{
    $orders_id = $conn->escape_string($_GET["orders_id"]);
    $tracking_number = $conn->escape_string($_GET["tracking_number"]);

   
    if ($tracking_number !== "" ) {
        $ByelabelService = new ByelabelService(null, null);
        $data = $ByelabelService->voidLabel_FromByeLabel($tracking_number);
        //var_dump($data);

         if ($data['success']) {
   
            //void label yapildiysa bu order ve consolidated olan tum kayitlarin shipped status kaydini sil
            $sql = "delete from order_status where orders_id = $orders_id and status_id = 5";
            f_update($sql, $conn, false);  
            
            //consolidated order varsa onlari da shipped yapacagiz once listeyi al          
            $sql = "SELECT id FROM orders WHERE consolidated_to = $orders_id";
            $result = $conn->query($sql);
            
            //shipped order statusu sil
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $orders_id = $row['id'];
                    $sql = "delete from order_status where orders_id = $orders_id and status_id = 5";
                    f_update($sql, $conn, false);  
                }
            } 
    
            // orders tablosunda consolidated kayitlardan tracking vs sil
            $sql = "Update orders Set tracking_number = null, 
                                     label_id = null, 
                                     label_date = null, 
                                     label_data = null 
                    Where consolidated_to = $orders_id or id = $orders_id ";
            echo f_update($sql, $conn, false); 
        }else{
            echo -1;
        }
    }

}  catch (Exception $e) {
    echo "Failed to void label " . $e->getMessage();
}

$conn->close();

?>
