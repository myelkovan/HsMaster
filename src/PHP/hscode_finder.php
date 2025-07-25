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



function getGtipSearch($language, $hs_search, $hs_param_type)
{

    //$TAMurl = "https://www.tariffnumber.com/api/v1/cnSuggest?year=2025&lang=en&term=Toilet%20Paper";
    $url = "https://www.tariffnumber.com/api/v1/cnSuggest";
    $params = [
        'year' => '2025',
        'lang' => 'en',  // "tr","en" vb.
        'term' => $hs_search   //  aranacak değer "toilet paper" vb.
    ];

    // URL ile parametreleri birleştir
    $urlWithParams = $url . '?' . http_build_query($params);

    // cURL oturumu başlat
    $ch = curl_init();

    // cURL ayarları
    curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Yanıtı döndür
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL doğrulamasını devre dışı bırak

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


function getHsCodeRealOpenAI_API($description)
{

    // $url = "http://localhost/JB/PHP/hscode_finder_AI.php";
    // $params = "description=" . $description ;  //  aranacak değer "toilet paper" vb.

    //$url = "http://localhost/JB/PHP/hscode_finder_AI.php?description=" . urlencode($description);
    $url = "https://hsmaster.ai/HSMASTER/PHP/hscode_finder_AI.php?description=" . urlencode($description);
    // URL ile parametreleri birleştir
    $urlWithParams = $url; //. '?' . ($params);
    //echo "url" . $url;
    // cURL oturumu başlat
    $ch = curl_init();

    // cURL ayarları
    curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Yanıtı döndür
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL doğrulamasını devre dışı bırak

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


//echo  $response; // Yanıtı yazdır

// $array = json_decode($response, true);

// // PHP dizisini okunaklı biçimde JSON stringine dönüştürün
// $json_string = json_encode($array, JSON_PRETTY_PRINT);

// // Sonucu ekrana yazdırın
// echo "<pre>" . $json_string . "</pre>";



function getHSCode_from_CloudeAI($productDescription)
{
    $apiKey = 'YOUR_CLAUDE_API_KEY'; // Claude API anahtarınızı buraya ekleyin
    $url = 'https://api.anthropic.com/v1/claude'; // API URL'si

    $data = [
        'prompt' => "Aşağıdaki ürün bilgilerine dayanarak HS kodunu tahmin et:\n\nÜrün: $productDescription\n\nHS Kodu:",
        'max_tokens' => 10,
        'temperature' => 0.5,
    ];

    $options = [
        'http' => [
            'header'  => [
                "Authorization: Bearer $apiKey",
                "Content-Type: application/json"
            ],
            'method'  => 'POST',
            'content' => json_encode($data),
        ],
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        echo 'Error occurred while fetching HS code.';
        return null;
    }

    $responseData = json_decode($response, true);
    return trim($responseData['completion']);
}
// üstteki fonksiyonu çalıştırmak için aşağıdakileri aktif et
//$productDescription = 'Örnek ürün açıklaması';
//$hsCode = getHSCode_from_CloudeAI($productDescription);
//echo 'HS Kodu: ' . $hsCode;





function getHSCode_from_CloudeAI_2($productDescription)
{

    $url = 'https://api.anthropic.com/v1/messages';

    // Your OpenAI API key
    $apiKey = '[API KEY]';

    // Claude
    $model = 'claude-instant-1.2';
    $maxTokens = 2048;
    $temperature = 0.6;
    $messages = [
        [
            'role' => 'system',
            'content' => "Aşağıdaki ürün bilgilerine dayanarak HS kodunu tahmin et:\n\nÜrün: $productDescription\n\nHS Kodu:"
        ]
    ];

    $data = [
        'model' => $model,
        'max_tokens' => $maxTokens,
        'temperature' => $temperature,
        'messages' => $messages
    ];

    $jsonData = json_encode($data);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $jsonData,
        CURLOPT_HTTPHEADER => [
            'x-api-key: ' . $apiKey,
            'anthropic-version: 2023-06-01',
            'content-type: application/json'
        ],
    ]);

    $response = curl_exec($curl);

    curl_close($curl);

    $responseData = json_decode($response, true);

    $content = $responseData['content'][0]['text'];
    return $content;
}

// Kullanım örneği
//$productDescription = 'Örnek ürün açıklaması';
//$hsCode = getHSCode_from_CloudeAI_2($productDescription);
//echo 'HS Kodu: ' . $hsCode;



//**********************JAVASCRİPT KODU**********************
// function getGtipSearch(language, hs_search, hs_param_type) {
//     const url = new URL('https://www.deneme.com/service/getgtipsearch');
    
//     // Parametreleri URL'e ekleyin
//     const params = {
//         language: language,
//         hs_search: hs_search,
//         hs_param_type: hs_param_type
//     };
    
//     // Parametreleri URL'e ekleme
//     Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));

//     // GET isteği gönderme
//     fetch(url)
//         .then(response => {
//             if (!response.ok) {
//                 throw new Error('Ağ isteğinde hata: ' + response.statusText);
//             }
//             return response.json(); // Yanıtı JSON formatına dönüştür
//         })
//         .then(data => {
//             console.log(data); // Yanıtı işleme
//         })
//         .catch(error => {
//             console.error('Hata:', error);
//         });
// }

// // Örnek kullanım
// getGtipSearch('tr', 'laptop', '2');


//**********************JAVASCRİPT KODU  SONU  **********************
