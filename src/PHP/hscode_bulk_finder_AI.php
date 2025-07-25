 
<?php
include("login/db_connect.php");
include('openAI_request.php');

// $_POST = []; // POST kullanmıyorsan temizle

$rawInput = file_get_contents("php://input");
$productDescriptions  = json_decode($rawInput, true);

$referer = $_SERVER['HTTP_REFERER'] ?? '';
$referer_host = parse_url($referer, PHP_URL_HOST);
$start_time = microtime(true); // örn. 1720272046.3451
$return_error = null;

// LOGLAMA DA KULLANILACAK DEĞERLERİ TOPLA
$request_time = date('Y-m-d H:i:s', (int) $start_time);
$endpoint = "api/v1/hscode-finder/description-list/";
$actionName = "description-list";
$input_value = $productDescriptions;   //json_encode($productDescriptions);

$productCount = count($productDescriptions);

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

$token = "HSC_bb62196dac3ecbb83f5dc5a434e0bf72";
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

if (empty($productDescriptions)) {
    $return_error = "Required parameter description is missing.";
    f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time);
}

$systemMessage = 'You will receive a list of product descriptions, each represented as ["id", "description"]. For each item, return the most accurate 6-digit HS code. If unsure, make your best approximation. Respond with a JSON object containing "summary" and "results". "summary" should include {"total": N, "success": M}. "results" should be a 2D array like [["id", "hs"]]. Do not include explanations.';
$userMessage = json_encode($productDescriptions);

$response = callOpenAI($systemMessage, $userMessage, 1000);

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
    // var_dump($summary['success']); // Beklenen: int(3) gibi bir çıktı

    // başarılı bir şekilde hscode bulunan kayıt sayısını user tablosunda ki alana ekle
    $sql =  "UPDATE user SET used_token_count = IFNULL(used_token_count, 0)  + {$summary['success']} WHERE id = {$user_id}";
    f_update($sql, $conn, false);

    f_hslog_list($user_id,  $request_time, $endpoint, $actionName, $input_value, $return_value['results'] ?? [],  $response_time_ms, $conn, $disconnect = true, $return_error = null);

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


function f_hs_errorResponse($user_id, $request_time, $endpoint, $actionName, $input_value, $return_error, $conn, $start_time, $disconnect = true, $karsiyaDonecekOzelHata = null)
{
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $karsiyaDonecekOzelHata ?? $return_error
    ]);

    $end_time = microtime(true);
    $response_time_ms = (int) round(($end_time - $start_time) * 1000);
    $return_value = '';

    f_hslog($user_id, $request_time, $endpoint, $actionName, $input_value, $return_value, $response_time_ms, $conn, $disconnect, $return_error);

    exit;
}


?>