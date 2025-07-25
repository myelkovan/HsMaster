 
<?php
include("login/db_connect.php");
include('openAI_request.php');

// $_POST = []; // POST kullanmıyorsan temizle

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
// $productDescription =  isset($_GET["description"]) ? $conn->escape_string($_GET["description"]) : 'NULL';
$rawInput = file_get_contents("php://input");
$productDescriptions  = json_decode($rawInput, true);

// $productDescriptions = [    
//                              ["1", "Baby doll"],
//                             ["2", "toilet paper"],
//                             ["3", "iphone 16 pro max"]
//                         ];


    // LOGLAMA DA KULLANILACAK DEĞERLERİ TOPLA
$request_time = date('Y-m-d H:i:s', (int) $start_time);
$endpoint="api/v1/hscode-finder/description-list/";
$actionName="description-list";
$input_value=$productDescriptions;   //json_encode($productDescriptions);
// f_hslog($user_id,  $request_time,$endpoint, $actionName, $input_value ,$return_value,  $response_time_ms, $conn, $disconnect = true,$return_error=null)


if (empty($productDescriptions)) {
     $return_error = "Required parameter description is missing.";
    //echo json_encode(["error" => $return_error]);
     echo json_encode([
        'success' => false,
        'message' => $return_error   
    ]);
    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000); // örn. 347 ms
    $return_value=''; 
    
    f_hslog_list($user_id,  $request_time, $endpoint, $actionName, $input_value ,$return_value,  $response_time_ms, $conn, $disconnect = true, $return_error);
 
    exit;
}

 //// Kullanım hakkı var mı kontrol edelim?
$productCount = count($productDescriptions);
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
    $return_value = '';

    f_hslog($user_id,  $request_time, $endpoint, $actionName, $input_value, $return_value,  $response_time_ms, $conn, $disconnect = true, $return_error);
        exit;
}



// $systemMessage = 'You will receive a list of product description, each represented as ["id", "description"]. For each item,  return the most accurate 6-digit HS code. Respond as a pure 2D JSON array in the format: [["id", "hs"]]. Do not include any explanation or extra text.';

$systemMessage = 'You will receive a list of product descriptions, each represented as ["id", "description"]. For each item, return the most accurate 6-digit HS code. If unsure, make your best approximation. Respond with a JSON object containing "summary" and "results". "summary" should include {"total": N, "success": M}. "results" should be a 2D array like [["id", "hs"]]. Do not include explanations.';

$userMessage = json_encode($productDescriptions);

$response = callOpenAI($systemMessage, $userMessage, 1000);

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
    //$return_value='HsCode:' . trim($hsCode); 
    $return_value = json_decode($hsCode, true); // Artık bu bir dizi
    
        // Gelen hscode lar içinde nokta işareti var ise temizle
    if (!empty($return_value['results'])) {
        foreach ($return_value['results'] as &$item) {
            $item[1] = str_replace('.', '', trim($item[1]));
        }
    }
     // var_dump($return_value);
     
     // Summary bilgilerini (Kaç parametre geldi kaçı başarılı, bu bilgileri al ve başarılı olanları sorgu hakkından düş.
    // $summary = $return_value['summary'] ?? ['total' => 0, 'success' => 0];
    // echo "Toplam ürün sayısı: {$summary['total']}\n";
    // echo "Başarıyla Bulunan HS kodu ve sorgu hakkından düşülecek sayı : {$summary['success']}\n";
    
        // başarılı bir şekilde hscode bulunan kayıt sayısını user tablosunda ki alana ekle
    $sql =  "UPDATE user SET used_token_count = IFNULL(used_token_count, 0)  + {$summary['success']} WHERE id = {$user_id}";
    f_update($sql, $conn, false);

     
    f_hslog_list($user_id,  $request_time, $endpoint, $actionName, $input_value ,$return_value['results'] ?? [] ,  $response_time_ms, $conn, $disconnect = true, $return_error=null);
 
   // Sadece ID + temizlenmiş HS kodları gönderilir
    echo json_encode([
        'success' => true,
        'data' => array_map(function ($item) {
            return [
                'id' => $item[0],
                'HsCode' => str_replace('.', '', trim($item[1]))
            ];
        }, $return_value['results'] ?? [])
    ]);
}








?>