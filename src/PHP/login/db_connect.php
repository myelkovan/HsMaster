<?php
 
header('Content-Type: application/json'); 
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Access-Control-Max-Age: 1728000');

 

$conn = new mysqli('localhost', 'u242434967_hsmaster', 'nyrXov-5hucxy-herpot', 'u242434967_hsmaster');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

/**
 *  hs code sorgu kullanım hakkı kalıp kalmadığını kontrol eder
 *
 * @param int $user_id        Kullanıcının id si
 * @param int    $productcount   sorgulancak ürün sayısı
 * @return true/false            kullanım hakkı olup olmadığı
 */

function f_hs_hasRemainingUsageRights($user_id, $productcount, $conn)
{
     if ($user_id="1"){ // Eğer kullanıcı jetbasket kullanıcısı ise şimdilik true dönmeye devam et.
        return 'ok';
    }
    
    $sql = "SELECT total_token_count, used_token_count FROM user WHERE id = $user_id";
 
    if ($result = $conn->query($sql)) {
        if ($row = $result->fetch_object()) {
            $total = (int) $row->total_token_count;
            $used = (int) $row->used_token_count;
            $usage_rights_count = $total - $used;
            
            if ($usage_rights_count > -1 && $usage_rights_count >= $productcount) {
                return 'ok'; // her şey yolunda
            } else {
                return 'insufficient'; // hak yetersiz
            }
        } else {
            return 'user_not_found'; // satır boş
        }
    } else {
        return 'sql_error'; // query çalışmadı
    }
    
   
    return 'unknown_error';
}

function f_hslog_list($user_id, $request_time, $endpoint, $action_name, $input_array, $return_array, $response_time_ms, $conn, $disconnect = true, $return_error = null)
{
    $user_Ip = $_SERVER['REMOTE_ADDR'];
    $timezone = new DateTimeZone('America/New_York');
    $log_date = (new DateTime('now', $timezone))->format('Y-m-d H:i:s');

    $values = [];
    $count = count($input_array);

    for ($i = 0; $i < $count; $i++) {
        $input_item = json_encode($input_array[$i], JSON_UNESCAPED_UNICODE);
        // return_item boş olabilir kontrolü
        if (empty($return_array[$i]) || !is_array($return_array[$i])) {
            $return_item = '';
        } else {
            $return_item = json_encode($return_array[$i], JSON_UNESCAPED_UNICODE);
        }

        $input_escaped = $conn->escape_string($input_item);
        $return_escaped = $conn->escape_string($return_item);
        $error_escaped = $conn->escape_string($return_error ?? '');

        $values[] = "($user_id, '$user_Ip', '$log_date', '$endpoint', '$action_name', '$input_escaped', '$return_escaped', $response_time_ms, '$error_escaped')";
    }

    if (!empty($values)) {
        $sql_insert = "INSERT INTO log_HsCode (user_id, user_ip, request_time, endpoint, action_name, input_value, return_value, response_time_ms, return_error) VALUES " . implode(",", $values);
        
        f_update($sql_insert,$conn,$disconnect);
    }
}


function f_hslog($user_id, $request_time, $endpoint, $action_name, $input_value ,$return_value,  $response_time_ms, $conn, $disconnect = true,$return_error=null)
{
        $user_Ip = $_SERVER['REMOTE_ADDR'];
        $timezone = new DateTimeZone('America/New_York');
        $log_date = new DateTime('now', $timezone);
        $log_date = $log_date->format('Y-m-d H:i:s');
           
        $sql_insert ="INSERT INTO log_HsCode( user_id, user_ip, request_time, endpoint, action_name, input_value, return_value, response_time_ms,return_error) VALUES($user_id,'$user_Ip', '$log_date','$endpoint','$action_name','$input_value', '$return_value',$response_time_ms,'$return_error')";
   
                    
        f_update($sql_insert,$conn,$disconnect);
 }


function f_log($user_id, $actionName, $log_description = 'null',  $importance_level = 1, $conn, $disconnect = true)
{
        $user_Ip = $_SERVER['REMOTE_ADDR'];
        $timezone = new DateTimeZone('America/New_York');
        $log_date = new DateTime('now', $timezone);
        $log_date = $log_date->format('Y-m-d H:i:s');
        $sql_insert ="Insert Into logs( user_id, user_ip, log_date, action_name, log_description, importance_level) Values($user_id,'$user_Ip','$log_date','$actionName', '$log_description', $importance_level)";
             
                    
        f_update($sql_insert,$conn,$disconnect);
 }




function f_select($arg_sql,$conn, $disconnect = true)
{
	if ($result = $conn -> query($arg_sql)) {
		$resultArray = array();
		$resultArray = $result->fetch_all(MYSQLI_ASSOC);
		
       // echo json_encode($resultArray, JSON_PRETTY_PRINT);
       echo json_encode($resultArray,JSON_INVALID_UTF8_IGNORE + JSON_PRETTY_PRINT);
	}

    if ($disconnect == true)
	    $conn -> close();
}



function f_delete($arg_sql,$conn, $disconnect = true)
{
    if ($conn->query($arg_sql) === TRUE) {
        echo "1";
    } else {
        echo "-1";
    }
    
    if ($disconnect == true)
	    $conn -> close();
}



function f_update($arg_sql, $conn, $disconnect = true)
{
    $arg_sql = str_replace("= ," , "= null, ", $arg_sql);
    $arg_sql = str_replace(" = ," , "= null, ", $arg_sql);
    $arg_sql = str_replace(", ," , ",null,"  , $arg_sql);
    $arg_sql = str_replace("'null'" , "null" , $arg_sql);

   if ($conn->query($arg_sql) === TRUE) {
          //insert veya update sonrasinda hangi id ye islem yaptigimizi al
          $last_id = strval($conn->insert_id) ;

        //update yaptiysak yeni kayit olusmadi id bos gelecek, demekki update yapmisiz
        if ($last_id==0){
            //yaptigimiz updateden kayit etkilendi mi?
            //$prototype=$conn->info;
            $prototype = $conn->info ?? "";
		    list($matched, $changed, $warnings) = sscanf($prototype, "Rows matched: %d Changed: %d Warnings: %d");

            //etkilenen kayit yok
            if ($matched == 0) {
                if ($disconnect == true){
                    $conn -> close();
                }
                return 0;
            
            //etkilenen kayit var 1 donder    
            }else{
                if ($disconnect == true){
                    $conn -> close();
                }
                return 1;  
            }   
        //insert ise yeni kaydin id sini donder
        }else{   
    	   if ($disconnect == true){
    	       $conn -> close();
    	   }
           return $last_id;
        }
    } else {
        //tum islem hatali ise -1 donder
 	    if ($disconnect == true){
 	        $conn -> close();
 	    }
 	        
        return -1;
    }
    

}




function f_isWarehouseUser($user_id, $conn)
{
	$sql = "select permission_level&4 as permission_level from user where id = $user_id";
	if ($result = $conn -> query($sql)) {
	    $row = $result->fetch_object();
        $permission_level = $row->permission_level; 
        if ($permission_level == 4){
            return true;
        }
	}
    return false;
}


function f_isAgentUser($user_id, $conn)
{
	$sql = "select permission_level&2 as permission_level from user where id = $user_id";
	if ($result = $conn -> query($sql)) {
	    $row = $result->fetch_object();
        $permission_level = $row->permission_level; 
        if ($permission_level == 2){
            return true;
        }
	}
    return false;
}


function f_isAgenOrWarehouseUser($user_id, $conn)
{
    $sql = "SELECT permission_level FROM user WHERE id = $user_id";
    if ($result = $conn->query($sql)) {
        $row = $result->fetch_object();
        $permission_level = $row->permission_level;
    
        if (($permission_level & 2) == 2 || 
            ($permission_level & 4) == 4 || 
            ($permission_level & 8) == 8) {
            return true;
        }
    }
    return false;
}
















function f_update1($arg_sql_update, $arg_sql_insert, $conn)
{
    
	if ($conn->query($arg_sql_update) === TRUE) {

		$prototype=$conn->info;
		list($matched, $changed, $warnings) = sscanf($prototype, "Rows matched: %d Changed: %d Warnings: %d");
        
 
        if ($matched == 0) {
            if ($conn->query($arg_sql_insert) === TRUE) {
                echo "-OK-"; 
            }
            else {
                echo "Error: " . $arg_sql_insert . "<br>" . $conn->error;
            }
        }
        else{
            echo "-OK-";   // update edildi
        }
	}else{
         echo "Error: " . $arg_sql_update . "<br>" . $conn->error;
    }
	$conn -> close();
}






function f_send_apple_notification($shipment_id, $shipment_code, $status, $device_id, $token)
{
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.development.push.apple.com:443/3/device/$device_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "authorization: bearer $token",
        "apns-topic: ComfyShip",
        "apns-push-type: alert",
        "apns-priority: 5",
        "apns-expiration: 0",
        "content-type: application/x-www-form-urlencoded",
    ]);
    
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, '{"aps":{"alert":{"title-loc-key":"NEWS_NOTIFICATION_TITLE",
                                                           "loc-key": "' . $status . '",
                                                           "loc-args":  ["' . $shipment_code. '"]},
                                                    "badge" : 1,
                                                    "sound" : "Chord",
                                                    "category" :  "' . $status . '" ,
                                                    "target-content-id" :  "' . $shipment_id . '" 
                                                    }}');
    
                
                
    $response = curl_exec($ch);
    curl_close($ch);
    /*
    terminalden test yontemi
    setting tablosundan tokeni al ilk satira yapistir
    customer tablosundan kullanicinin device_id sini al son satira yapistir
    app adi degisirse asagida guncelle
    
    curl -v \
      --header "authorization: bearer eyJhbGciOiJFUzI1NiIsImtpZCI6Ik0yMkE5NTVVSjkifQ.eyJpc3MiOiI3NlZRN0pBMkw3IiwiaWF0IjoxNzA2NDgyNzIwfQ.jQrnzjcQiaFyoXgJRDkjp8R71YD-14frNB3g_E2zBwRpChpPNY4ujG_dfkQKHobOfui3mqEOVor7DGuDnuMkTg" \
      --header "apns-topic: ComfyShip" \
      --header "apns-push-type: alert" \
      --header "apns-priority: 5" \
      --header "apns-expiration: 0" \
      --data '{"aps":{"alert":{"title":"title","subtitle":"subtitle","body":"body"}}}' \
      --http2  https://api.development.push.apple.com:443/3/device/01688328d40139baf1ac0538b275fc3e0b81e576d798c8917ebf2a7b7deabfc6
      
    */  
}



function f_send_google_notification($shipment_id, $shipment_code, $status, $device_id, $serverKey)
{


    $url = "https://fcm.googleapis.com/fcm/send";
    $body = $shipment_code . " " . $status;
    $notification = array('title' =>$shipment_id , 
                          'body' => $body,
                          'sound' => 'default', 
                          'badge' => '1');
                          
    $arrayToSend = array('to' => $device_id, 
                         'notification' => $notification,
                         'priority'=>'high');
    $json = json_encode($arrayToSend);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key='. $serverKey;


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
    //Send the request
    $response = curl_exec($ch);
    
    echo $response;
    //Close request
    if ($response === FALSE) {
        die('FCM Send Error: ' . curl_error($ch));
    }
    curl_close($ch);


}    





?>
