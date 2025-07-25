 
<?php
include("login/db_connect.php");
include('openAI_request.php');

$start_time = microtime(true); // örn. 1720272046.3451
$return_error=null;

$allowed_referers = [
    'https://jetbasket.us',
    'https://jetbasket.us',
    'https://hsmaster.ai/',
    'localhost/JB/PHP/hscode_finder_AI.php'
];

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$referer_host = parse_url($referer, PHP_URL_HOST);

$allowed_hosts = array_map(function ($url) {
    return parse_url($url, PHP_URL_HOST);
}, $allowed_referers);

 //echo "referer :" . $referer . " / ". $referer_host . ":--";
// echo (!in_array($referer_host, $allowed_hosts));
// exit;

if (!in_array($referer_host, $allowed_hosts)) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}

// Header'ları al
$headers = getallheaders();

// Authorization başlığı var mı kontrol et
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];

    // "Bearer" ifadesini ayıklayıp token'ı al
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
        // Token'ı kullanmaya hazırsınız
        //echo "Gelen token: " . $token;
    } else {
        http_response_code(403);
        echo "Authorization header is not in the expected format.";
        exit;
        
    }
} else {
    http_response_code(403);
    echo "Access denied.";
    exit;
}


if (empty($token)) {
    http_response_code(403);
    echo "Access denied.";
    exit;
}



// Geçerli token listesi: token => açıklama
$allowedKeys = [
    "JB-abd6b3c438040215867739a97aa9cb397b9d201a3bf74dc25718248cfe03774a" => "JetBasket- için Token",
    "5f81f17f52f0d387229fdf588f9d1bd5e382926eeba6df8f0ee7ff0a58d5b43a" => "ByeLabel- için Token ",
    "4cb79468337d42ac87db4e807401b2cc17a3731b7517647188d4daf158caaa35" => "Sahibini Bekleyen Token"
];


$user_id =0;

switch ($token) {
    case 'JB-abd6b3c438040215867739a97aa9cb397b9d201a3bf74dc25718248cfe03774a':
        $user_id = '1'; // jetbasket
        break;
    case '5f81f17f52f0d387229fdf588f9d1bd5e382926eeba6df8f0ee7ff0a58d5b43a':
        $user_id = '2'; //ByeLabel
        break;
    case '4cb79468337d42ac87db4e807401b2cc17a3731b7517647188d4daf158caaa35':
        $user_id = '3';
        break;
    default:
        http_response_code(403);
        echo "Erişim reddedildi. Geçersiz token.";
        exit;
}

// Kullanıcıdan gelen açıklama
// $productDescription = "Arewyt 14 Gauge Wire 100FT, 14AWG Low Voltage Electrical Wire, 2 Conductor Automotive Cable, 14/2 AWG Flexible Connection Cord 12V/24V DC Extension Cords for LED Strips, Speaker, Auto, Home";
$productDescription =  isset($_GET["description"]) ? $conn->escape_string($_GET["description"]) : 'NULL';

    // LOGLAMA DA KULLANILACAK DEĞERLERİ TOPLA
$request_time = date('Y-m-d H:i:s', (int) $start_time);
$endpoint="api/v1/hscode-finder/description/";
$actionName="description";
$input_value=$productDescription;
// f_hslog($user_id,  $request_time,$endpoint, $actionName, $input_value ,$return_value,  $response_time_ms, $conn, $disconnect = true,$return_error=null)


if (empty($productDescription)) {
     $return_error = "Required parameter description is missing.";
    //echo json_encode(["error" => $return_error]);
     echo json_encode([
        'success' => false,
        'message' => $return_error   
    ]);
    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000); // örn. 347 ms
    $return_value=''; 
    
    f_hslog($user_id,  $request_time, $endpoint, $actionName, $input_value ,$return_value,  $response_time_ms, $conn, $disconnect = true, $return_error);
 
    exit;
}

 //// Kullanım hakkı var mı kontrol edelim?
$productCount = "1";

$usageStatus = f_hs_hasRemainingUsageRights($user_id, $productCount, $conn);

switch ($usageStatus) {
    case 'ok':
        // Kullanım hakkı mevcut → devam et
        break;

    case 'insufficient':
        $return_error = "Request rejected: remaining usage count is lower than required.";
        break;

    case 'user_not_found':
        $return_error = "User record not found." ;
        break;

    case 'sql_error':
        $return_error =  "Unable to validate usage rights due to database error.";
        break;

    default:
        $return_error =  "Unexpected error occurred during usage rights check.";
}

if ($usageStatus !== 'ok') {
     echo json_encode([
        'success' => false,
        'message' => $return_error   
    ]);
    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000); // örn. 347 ms
    $return_value=''; 
    
    f_hslog($user_id,  $request_time, $endpoint, $actionName, $input_value ,$return_value,  $response_time_ms, $conn, $disconnect = true, $return_error);
 
    exit;
}

$systemMessage = "Based on the product description, return the most accurate 6-digit HS code. Return only the code.";
$userMessage = $productDescription;

$response = callOpenAI($systemMessage, $userMessage, 2);


// Sonucu göster
if ($response === FALSE) {
    $return_error = "API request failed. HTTP connection could not be established.";
    //throw new Exception($return_error);
    echo json_encode([
        'success' => false,
        'message' => $return_error   
    ]);
    return;
} else {
    $result = json_decode($response, true);

    if (isset($result['error'])) {
        // Hata bilgilerini topla
        $errorMessage = $result['error']['message'] ?? 'Bilinmeyen API hatası';
        $errorCode = $result['error']['code'] ?? 'unknown_error';
        $karsiya_donecek_hata="ErrorCode:" . $errorCode . 'An unexpected error occurred. Please try again later.';
        $return_error = "OpenAI Hatası [{$errorCode}]: {$errorMessage}";
    
       // HTTP status kodu ayarla
        http_response_code(500); // veya duruma göre 400, 403, vs.
        // Kullanıcıya döneceğin genel hata yanıtı (JSON)
       echo json_encode([
        'success' => false,
        'message' => $karsiya_donecek_hata  //  'An unexpected error occurred. Please try again later.'
    ]);

    return;
}


    // Cevabı işle
    $hsCode = $result['choices'][0]['message']['content'] ?? null;
    //echo "["HsCode:" . trim($hsCode)];
    
    if (!$hsCode) {
        //throw new Exception("Yanıt başarıyla geldi ancak HS kodu içeriği bulunamadı.");
        $return_error="The HS code content could not be found";
         echo json_encode([
            'success' => false,
            'message' => $return_error   
        ]);
        return;
    }
   


    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000); // örn. 347 ms
    $return_value='HsCode:' . trim($hsCode); 
    
    $sql =  "UPDATE user SET used_token_count = IFNULL(used_token_count, 0)  + 1 WHERE id = {$user_id}";
    f_update($sql, $conn, false);

    f_hslog($user_id,  $request_time, $endpoint, $actionName, $input_value ,$return_value,  $response_time_ms, $conn, $disconnect = true, $return_error=null);
 
    //echo json_encode(["HsCode" => trim($hsCode)]);
    echo json_encode([
        'success' => true,
        'data' => [
            'HsCode' => trim($hsCode)
            ]
    ]);

}








?>