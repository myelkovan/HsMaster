
<?php
include("login/db_connect.php");
include('openAI_request.php');



// Kullanıcıdan gelen açıklama
$productDescription =  isset($_GET["description"]) ? $conn->escape_string($_GET["description"]) : 'NULL';
$originCountry =  isset($_GET["from"]) ? $conn->escape_string($_GET["from"]) : 'NULL';
$destinationCountry =  isset($_GET["to"]) ? $conn->escape_string($_GET["to"]) : 'NULL';

// $originCountry ="USA";
//  $destinationCountry ="CANADA";

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$referer_host = parse_url($referer, PHP_URL_HOST);
$start_time = microtime(true); // örn. 1720272046.3451
$return_error = null;

// LOGLAMA DA KULLANILACAK DEĞERLERİ TOPLA
$request_time = date('Y-m-d H:i:s', (int) $start_time);
$endpoint = "api/v1/restricted-product-finder/description/";
$actionName = "restricted-product";
$input_value = "[from:" . $originCountry . ",to:" . $destinationCountry . ",description:"   . $productDescription . "]";

$productCount = "1";    //tekli sorgu olduğu için --> 1

// Header'ları al
$headers = getallheaders();

// Authorization başlığı var mı kontrol et
if (isset($headers['Authorization'])) {
    $authHeader = $headers['Authorization'];
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $token = $matches[1];
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


//token bizde bilinen bir token mi ve api_referrer varsa al
$sql = "select id as user_id, 1 as valid, api_referrer, used_token_count, 
               (select sum(credit) from deposit where user_id = user.id and paid_date is not null ) total_token_count 
        from user where api_token = '$token'";


if ($result = $conn->query($sql)) {
    if ($row = $result->fetch_object()) {
        $user_id = $row->user_id;
        $valid = $row->valid;
        $api_referrer = $row->api_referrer;
        $total_token_count = $row->total_token_count;
        $used_token_count = $row->used_token_count;


        //credi yok
        if (intval($total_token_count) - intval($used_token_count) < intval($productCount)) {
            $return_error = "Request rejected: You do not have enough credit.";
            f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
        }


        //token gecerli
        if ($valid == 1) {
            //api_referrer null ise bu token ilk kez kullaniliyor ilk kullanilan referrer i kaydedecegiz bundan sonra o kullanabilecek
            if ($api_referrer == null) {
                $sql = "update user set api_referrer = '$referer_host' where id = $user_id";
                f_update($sql, $conn, false);
            } else {
                //api_referrer var ama token baska bir referrer den gelmis izin verme
                if ($api_referrer != $referer_host) {
                    $return_error = "Access denied. Please use the correct API referrer.";
                    f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
                }
            }
        }
    } else {
        http_response_code(403);
        $user_id = 0;
        $return_error = "API request blocked: no matching token found.";
        f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
    }
} else {
    $user_id = 0;
    $return_error = "API request blocked: token issue detected.";
    f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
}


if (trim($productDescription) === 'NULL' || trim($originCountry) === 'NULL' || trim($destinationCountry) === 'NULL') {
    $return_error = "Required parameter is missing.";
    f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
}


//  $originCountry ="USA";
//  $destinationCountry ="CANADA";

$katiTutum = " Always take a conservative approach in regulatory interpretation";
//$katiTutum= "";

//örnek ÜRÜN ---> "Fobus, Barrel, Fits Glock 17/19/19X/22/23/31/32/34/35/45" katı tutum boş olursa  bu ürün için serbest derken katıtutumu doldurup katı bir tutum belirle diyince yasaklı diyor.  
// daha yumuşak bir tutum takınması için $katiTutum= "" şeklindeki satırı aç


$systemMessage =  "You are a global trade compliance assistant. Based on a product description, determine whether exporting this product from "
    . $originCountry . " to " . $destinationCountry . " is legally restricted, prohibited, or allowed. Return only one of the following status labels: \"Allowed\", \"Restricted\", \"Prohibited\". If the description is insufficient or ambiguous, return \"Unclear\". Do not include any explanation." . $katiTutum;


$systemMessage =  "You are a global trade compliance assistant. Given a product description, determine whether exporting the product from "
    . $originCountry . " to " . $destinationCountry . " legally \"Allowed\", \"Restricted\", or \"Prohibited\". If there is not enough information to make a clear determination, return \"Unclear\". Do not explain your answer. Your goal is to stay consistent across all product types and use regulatory logic, not assumptions or opinions." . $katiTutum;

$systemMessage = <<<EOT
You are a global trade compliance assistant. Given a product description, determine whether transferring the product from a specified origin country to a specified destination country is legally Allowed, Restricted, or Prohibited based on applicable export and import regulations. If there is insufficient detail to make a confident determination, return "Unclear".

Use the following logic:
- "Allowed" only if there are no known restrictions, prohibitions, or special licensing requirements in either the origin or destination country.
- "Restricted" if the product may require special permits, regulatory review, customs declarations, or may be subject to country-specific sensitivities (e.g., cultural, religious, environmental, or strategic concerns).
- "Prohibited" if the export or import of the product is banned under the laws of either the origin or destination country.
- "Unclear" if the product or country description is too vague to evaluate.

Be conservative in your classification and prioritize regulatory caution. Do not include explanations. Return only one of: "Allowed", "Restricted", "Prohibited", "Unclear".
EOT;


//     // daha kısa hali
// $systemMessage = <<<EOT
// You are a global trade compliance assistant. Based on a product description, determine if exporting it from the USA to a given country is Allowed, Restricted, Prohibited, or Unclear.

// Use this logic:
// - "Allowed" if there are no known export/import restrictions or licensing requirements.
// - "Restricted" if it may require permits, review, or faces cultural/legal sensitivities.
// - "Prohibited" if banned by U.S. or destination law.
// - "Unclear" if product or country details are too vague.

// Be conservative. Do not explain. Return only one of: "Allowed", "Restricted", "Prohibited", "Unclear".
// EOT;


$userMessage = $productDescription . " Export from " . $originCountry . " to " . $destinationCountry;

$response = callOpenAI($systemMessage, $userMessage, 2, 0.0, "gpt-4-turbo");

// Sonucu göster
if ($response === FALSE) {
    $return_error = "API request failed. HTTP connection could not be established.";
    f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
} else {
    $result = json_decode($response, true);

    if (isset($result['error'])) {
        // Hata bilgilerini topla
        $errorMessage = $result['error']['message'] ?? 'Bilinmeyen API hatası';
        $errorCode = $result['error']['code'] ?? 'unknown_error';
        $karsiya_donecek_hata = "ErrorCode:" . $errorCode . 'An unexpected error occurred. Please try again later.';
        $return_error = "OpenAI Hatası [{$errorCode}]: {$errorMessage}";
        // buradaki loglamada özel bir işlem var openAI hatasınındetayları loga yazılıyor ama karsıtarafa bu detaylar verilmiyor.
        f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time, true, $karsiya_donecek_hata);
    }


    // Cevabı işle
    $restrictionStatus = $result['choices'][0]['message']['content'] ?? null;
    //echo "["restrictionStatus:" . trim($restrictionStatus)];

    if (!$restrictionStatus) {
        //throw new Exception("Yanıt başarıyla geldi ancak HS kodu içeriği bulunamadı.");
        $return_error = "Restriction status information could not be found";
        echo json_encode([
            'success' => false,
            'message' => $return_error
        ]);
        return;
    }

    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000); // örn. 347 ms
    $return_value = 'Status:' . trim($restrictionStatus);

    $sql =  "UPDATE user SET used_token_count = IFNULL(used_token_count, 0)  + 1 WHERE id = {$user_id}";
    f_update($sql, $conn, false);

    f_hslog($user_id,  $request_time, $endpoint, $actionName, $input_value, $return_value,  $response_time_ms, $conn, $disconnect = true, $return_error = null);

    //echo json_encode(["restrictionStatus" => trim($restrictionStatus)]);
    echo json_encode([
        'success' => true,
        'data' => [
            'Status' => trim($restrictionStatus),
            'description' => trim($productDescription),
            'from-to' => trim($originCountry) . "-->" . $destinationCountry
        ]
    ]);
}



// Aynı dosyada en alta eklenebilir
function f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time, $disconnect = true)
{
    // eğer kullanıcı belirlenmeden hata almış isek yada yetkisiz erişimi loglamak istersek user_id 0 yani tanımsız.
    if (!isset($user_id)) {
        $user_id = 0;
    }

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $return_error
    ]);

    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000);
    $return_value = '';

    f_hslog($user_id, $request_time, $endpoint, $actionName, $input_value, $return_value, $response_time_ms, $conn, $disconnect, $return_error);

    exit;
}

// ürün açıklamalarını yapayzekaya göndermeden basitleştirmek için bir fonksiyon gerkirse kullanırım.
function simplifyProductDescription($description)
{
    // Parantez içlerini, tekrarları ve fazla süslemeleri kaldır
    $description = preg_replace('/\s+/', ' ', $description);  // fazla boşlukları temizle
    $description = preg_replace('/with .*$/i', '', $description);  // "with" sonrası opsiyonel parça
    $description = trim($description);
    return $description;
}




?>