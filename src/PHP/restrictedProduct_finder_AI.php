
<?php
include("login/db_connect.php");
include('openAI_request.php');



// Kullanıcıdan gelen açıklama
$productDescription =  isset($_GET["description"]) ? $conn->escape_string($_GET["description"]) : 'NULL';

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$referer_host = parse_url($referer, PHP_URL_HOST);
$start_time = microtime(true); // örn. 1720272046.3451
$return_error = null;

// LOGLAMA DA KULLANILACAK DEĞERLERİ TOPLA
$request_time = date('Y-m-d H:i:s', (int) $start_time);
$endpoint = "api/v1/hscode-finder/description/";
$actionName = "description";
$input_value = $productDescription;

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
            $return_error = "Request rejected: remaining usage count is lower than required.";
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

if (empty($productDescription)) {
    $return_error = "Required parameter description is missing.";
    f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
}

$originCountry = "USA";
$destinationCountry = "CANADA";

$systemMessage =  "You are a global trade compliance assistant. Based on a product description, determine whether exporting this product from "
    . $originCountry . " to " . $destinationCountry . " is legally restricted, prohibited, or allowed. Return only one of the following status labels: \"Allowed\", \"Restricted\", \"Prohibited\". If the description is insufficient or ambiguous, return \"Unclear\". Do not include any explanation.";

//$systemMessage = "Based on the product description, return the most accurate 6-digit HS code. Return only the code.";
$userMessage = $productDescription;

$response = callOpenAI($systemMessage, $userMessage, 2);

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
    $hsCode = $result['choices'][0]['message']['content'] ?? null;
    //echo "["HsCode:" . trim($hsCode)];

    if (!$hsCode) {
        //throw new Exception("Yanıt başarıyla geldi ancak HS kodu içeriği bulunamadı.");
        $return_error = "The HS code content could not be found";
        echo json_encode([
            'success' => false,
            'message' => $return_error
        ]);
        return;
    }

    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000); // örn. 347 ms
    $return_value = 'HsCode:' . trim($hsCode);

    $sql =  "UPDATE user SET used_token_count = IFNULL(used_token_count, 0)  + 1 WHERE id = {$user_id}";
    f_update($sql, $conn, false);

    f_hslog($user_id,  $request_time, $endpoint, $actionName, $input_value, $return_value,  $response_time_ms, $conn, $disconnect = true, $return_error = null);

    //echo json_encode(["HsCode" => trim($hsCode)]);
    echo json_encode([
        'success' => true,
        'data' => [
            'HsCode' => trim($hsCode)
        ]
    ]);
}



// Aynı dosyada en alta eklenebilir
function f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time, $disconnect = true)
{
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




?>