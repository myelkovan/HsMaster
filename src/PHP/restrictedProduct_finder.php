<?php
//TEST EDERKEN ALTTAKI KODLARI AÇABİLİRSİN
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$allowed_origins = [
    'http://localhost:3000',
    'https://hsmaster.ai'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

if (in_array($origin, $allowed_origins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
} else {
    header("Access-Control-Allow-Origin: *");
}

// Ortak header'lar — her istekte eklenecek
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

// BU localden test ederken cors hatası alamamak için açılmalı
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Max-Age: 86400");
    http_response_code(200);
    exit();
}

include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
    echo "invalidToken";
    return;
}

// Asıl yanıt için CORS başlıkları ekle
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");

if (!isset($_GET["product_description"]) || !isset($_GET["from"]) || !isset($_GET["to"])) {
    echo json_encode(["error" => "Eksik parametre'"]);
    exit;
}

$product_description    = $_GET["product_description"];
$origin_country    = $_GET["from"];
$destination_country    = $_GET["to"];
$token = "HSC_bb62196dac3ecbb83f5dc5a434e0bf72"; // Göndermek istediğin token

//$url = "https://hsmaster.ai/HSMASTER/PHP/restrictedProduct_finder_AI.php?from=" . urlencode($origin_country) . "&to=" . urlencode($destination_country) .  "&description=" . urlencode($product_description);
$url = "https://hsmaster.ai/api/v1/restrictedProduct-finder/description/" . urlencode($product_description) . "/from/" . urlencode($origin_country) . "/to/" . urlencode($destination_country);

$ch = curl_init();

// Token içeren header ayarları
$headers = [
    "Authorization: Bearer $token", // Bearer tipi, senin backend'ine göre değişebilir
    "Referer: https://hsmaster.ai" // Burayı çağıran sayfanın URL’siyle değiştir // Gerekirse ekle
];

// cURL ayarları
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Yanıtı döndür
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // SSL doğrulamasını devre dışı bırak
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Header'ları ekliyoruz

// İsteği çalıştır
$response = curl_exec($ch);

// Hata kontrolü - JSON formatında döndür
if ($response === false) {
    echo json_encode(["error" => "API request failed. Please try again later.", "details" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

// cURL oturumunu kapat
curl_close($ch);

// Yanıtı döndür
$jsonResponse = json_decode($response, true);

if ($jsonResponse === null) {
    echo json_encode(["error" => "Invalid JSON response", "rawData" => $response]);
} else {
    echo $response;
}
