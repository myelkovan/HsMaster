<?php

include("login/db_connect.php");
include("byelabel.php");
include("utils/mail.php");

    // Get the current date and time in UTC
    date_default_timezone_set('UTC');
    $now = date('Y-m-d H:i:s');


    $sql = "SELECT o.id , o.tracking_number,(select carrier_code from kt_carrier where code = o.service_code) as carrier_code
        FROM orders o
        JOIN order_status os ON o.id = os.orders_id
        GROUP BY o.id
        HAVING MAX(os.status_id) = 5 AND NOT EXISTS (
            SELECT 1
            FROM order_status os2
            WHERE os2.orders_id = o.id AND os2.status_id = 6)";
        
    if ($result = $conn -> query($sql)) {
        $ByelabelService = new ByelabelService(null, null);
 
    	while($row = $result->fetch_object())
    	{
    	   $orders_id = $row->id;
    	   $tracking_number = $row->tracking_number;
    	   $carrier_code = $row->carrier_code;
     
        
            try{
                
                if ($tracking_number !== "" && $carrier_code !== "" && $carrier_code != null) {
                    
                    $data = $ByelabelService->trackShipment_FromByeLabel($carrier_code, $tracking_number );
                    if ($data['delivered']){
                         $sql_insert = "Insert into order_status(orders_id, status_id, date) Values($orders_id, 6, '$now')";
                         f_update($sql_insert, $conn, false);
                         send_mail_delivered($conn, $orders_id);
                      
                    }
                    
                    //domains/jetbasket.us/public_html/PHP/label_track.pbp
                    //DÖNEN DATA örneği
                    // $item = [
                    //     'status' => $status, //  "recent"  gibi bir değer
                    //     'delivered' => $delivered,   // false
                    //     'estimated_delivery_datetime' => $estimated_delivery_datetime,   // "2025-07-21T08:00:00.000Z"
                    //     'package_count' => $package_count,       // 3
                    //     'package_weight' => $package_weight,     //2.2,
                    //     'package_width' => $package_width,
                    // ];

                }
            
                   
                
            }  catch (Exception $e) {
                echo $e->getMessage();
            }
    	}
    }

$conn->close();

?>
