<?php


include("db_connect.php");
include("../utils/jwt.php");

$email = $conn->escape_string($_GET["email"]);
$password = $conn->escape_string($_GET["password"]);


$sql = "Select id, name, picture_path, '' as token, activated, permission_level,email, mobile, api_token,
        (select value from settings where `key`='service_fee') as service_fee 
        From user where email = '$email' and password = AES_ENCRYPT('$password', 'şifrıç.ş,üğişç.çölşipi*-0.iııııı')";


if ($result = $conn->query($sql)) {
    $resultArray = array();

    //Kayit dondu 
    while ($row = $result->fetch_object()) {
        
        $user_id = $row->id;

        //Kullanicinin ip adresinden ulkesini al ve depo adresine ulas 
        $ip = $_SERVER['REMOTE_ADDR'];  // Get the user's IP address

        try {
            // Send request to ip-api to get location data
            $response = file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");

            if ($response) {
                $data = json_decode($response, true);
                $user_ip_country_code = $data['countryCode'];
            } else {
                echo "-1";
            }
        } catch (Exception $e) {
            f_log($user_id,  "Login", "ERROR :" . $e->getMessage() . " user_ip_country_code :" .  $user_ip_country_code, 1, $conn, false);
        }



        //Aktif edilmemis kullanici ise NA donder
        if ($row->activated == null) {
            echo 'NA';
            f_log($user_id,  "Login", "HATA :" . "User is not activated " . "- user_ip_country_code :" .  $user_ip_country_code,  1, $conn, true);
            return;
        }


        $row->token = f_generateToken($email, $row->id);
        array_push($resultArray, $row);

        header('Content-Type: application/json');
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers: *');
        header('Access-Control-Max-Age: 1728000');

        echo json_encode($resultArray, JSON_PRETTY_PRINT);

        f_log($user_id,  "Login", "user_ip_country_code :" .  $user_ip_country_code,  1, $conn, true);

        return;
    }
}

//Kayit yok    
echo 'NO';
f_log("000",  "Login", "FAIL :" . " with email : " . $email  . " and password: ". $password,  2, $conn, false);
$conn->close();
return;
