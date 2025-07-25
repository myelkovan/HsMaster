<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Eğer preflight OPTIONS isteği gelirse, uygun yanıt dön
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    exit; // Boş yanıt göndererek işlemi sonlandır
}

// Asıl yanıt için CORS başlıkları ekle
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json");


function getHsCodeRealOpenAI_API($description)
{

    //$token = "JB-abd6b3c438040215867739a97aa9cb397b9d201a3bf74dc25718248cfe03774a"; // Göndermek istediğin token
    $token = "HSC_bb62196dac3ecbb83f5dc5a434e0bf72"; // Göndermek istediğin token

    //$url = "http://localhost/JB/PHP/hscode_finder_AI.php?description=" . urlencode($description);
    $url = "https://hsmaster.ai/HSMASTER/PHP/hscode_finder_AI.php?description=" . urlencode($description);
    $urlWithParams = $url; //. '?' . ($params);

    //echo "url" . $url;
    // cURL oturumu başlat
    $ch = curl_init();

    // Token içeren header ayarları
    $headers = [
        "Authorization: Bearer $token", // Bearer tipi, senin backend'ine göre değişebilir
        "Content-Type: application/x-www-form-urlencoded", // Gerekirse ekle
        "Referer: https://hsmaster.ai" // Burayı çağıran sayfanın URL’siyle değiştir // Gerekirse ekle
    ];

    // cURL ayarları
    curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Yanıtı döndür
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL doğrulamasını devre dışı bırak
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); // Header'ları ekliyoruz

    // İsteği çalıştır
    $response = curl_exec($ch);

    // Hata kontrolü - JSON formatında döndür
    if ($response === false) {
        echo json_encode(["error" => "API çağrısı başarısız!", "details" => curl_error($ch)]);
        curl_close($ch);
        exit;
    }

    // cURL oturumunu kapat
    curl_close($ch);

    // JSON formatında döndür
    //echo $response;



    // Yanıtı döndür
    return $response;
}

//echo "geldim";

if (!isset($_GET["value"])) {
    echo json_encode(["error" => "Eksik parametre: 'value'"]);
    exit;
}



$value    = $_GET["value"];
//echo $value;
if ($value === "") {
    $value = "toilet paper";
}
// Örnek kullanım

//$response = getGtipSearch('en', $value, '2');
$response = getHsCodeRealOpenAI_API($value);

//echo "response" . $response;

$jsonResponse = json_decode($response, true);

if ($jsonResponse === null) {
    echo json_encode(["error" => "Geçersiz JSON yanıtı!", "rawData" => $response]);
} else {
    echo $response;
}
