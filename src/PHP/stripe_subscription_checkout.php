<?php
// Stripe Checkout kullanarak abonelik yönetimi için PHP API
require '../PHP/utils/GuzzleHttp/vendor/autoload.php';
require_once "utils/stripe/init.php";

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

use Stripe\Checkout\Session;
use Stripe\Subscription;


$stripeSecretKey = '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';    //  OZON

\Stripe\Stripe::setApiKey($stripeSecretKey);

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    echo json_encode(["error" => "Geçersiz JSON verisi", "raw_input" => $inputJSON]);
    exit;
}

$price_id = isset($input['price_id']) ? $input['price_id'] : null;

if ($method == "OPTIONS") {
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

try {
    if ($method == 'POST' &&  $path == '/shop/PHP/stripe_subscription_checkout.php/create-checkout-session') {
        createCheckoutSession($input);
    } elseif ($method == 'GET' && $path == '/shop/PHP/stripe_subscription_checkout.php/subscription-status') {
        if (!isset($_GET['subscription_id'])) {
            throw new Exception("Eksik parametre: subscription_id");
        }
        getSubscriptionStatus($_GET['subscription_id']);
    } elseif ($method == 'POST' && $path == '/shop/PHP/stripe_subscription_checkout.php/cancel-subscription') {
        if (!isset($input['subscription_id'])) {
            throw new Exception("Eksik parametre: subscription_id");
        }
        cancelSubscription($input['subscription_id']);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint bulunamadı']);
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Stripe hatası: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Sunucu hatasi: ' . $e->getMessage()]);
}

/**
 * Stripe Checkout Session oluşturur.
 * Beklenen input:
 *  - price_id (string): Stripe fiyat ID'si (abonelik fiyatı)
 *  - success_url (string): Ödeme başarılıysa yönlendirme return URL'si
 */
function createCheckoutSession($input)
{
   

    if ($input['price_id'] === "1") {
        $price_id = 'price_1RPkv3L7zncsEWkGYMmhbSIg'; //STARTER PAKET  
    } elseif ($input['price_id'] === "2") {
        $price_id = 'price_1RPl6IL7zncsEWkGNaoxxNPG'; //GROWTH PAKET  
    } elseif ($input['price_id'] === "3") {
        $price_id = 'price_1RPlBYL7zncsEWkGAOWhO1uf'; //PRO PAKET  
    } elseif ($input['price_id'] === "4") {
        $price_id = 'price_1RPlS4L7zncsEWkGLfjr3vRU'; //ENTERPRİSE PAKET  
    }
     $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : "http://localhost/shop/PHP/"; // Tarayıcıdan gelen kaynak

    $session = Session::create([
        'ui_mode' => 'embedded',    // Embedded UI modu
        'mode' => 'subscription',  // Abonelik modu
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => $price_id, 
            'quantity' => 1,
        ]],
        'return_url' => $input['success_url'] . '?session_id={CHECKOUT_SESSION_ID}', // Ödeme başarılı dönüş URL'ine sesionid yi gönder. o sayfada da gelen sesion id ile sorgu yapıp gelen bilgileri abonelikId si v.b veritabanına kaydet.
        'subscription_data' => ['trial_period_days' => 14],     // Deneme süresi ne kadar sa buraya o yazılacak. yok ise kaldırılacak
       // subscription_data için daha fazla parametre vermek için 
        // Altta ki parametreler isteğe ögre veya iş kurallarına göre eklenilebilir.
        // 'subscription_data' => [
        //     'trial_period_days' => 14, // 14 günlük ücretsiz kullanım
        //     'payment_behavior' => 'allow_incomplete', // Ödeme teyit edilmese veya  tamamlanmasa bile test başlasın. dikkat deneme 
        //     'trial_settings' => ['end_behavior' => ['missing_payment_method' => 'cancel']] // Ödeme olmazsa aboneliği iptal et veya burada pause da kullanılabilir.
        // ]
     ]);
    // client tarafında ki embeddedProvider bizden stripe taraınfa oluşturduğumuz client_secret ı istiyor.
    // Bu şekilde client_secret a  göre ekranı otomatik olarak oluşturacak ve kullanıcıya otomatik bir şekilde ekranı açacak.
    echo json_encode(array('clientSecret' =>  $session->client_secret)); 
}

/**
 * Abonelik durumu sorgulama
 */
function getSubscriptionStatus($subscriptionId)
{
    // stripe in api'den Subscription objesini kullanrak abonelik bilgilerini alıyoruz.
    $subscription = Subscription::retrieve($subscriptionId);
    echo json_encode([
        'subscription_id' => $subscription->id,
        'status' => $subscription->status,
        'current_period_start' => $subscription->current_period_start,
        'current_period_end' => $subscription->current_period_end,
        'cancel_at_period_end' => $subscription->cancel_at_period_end,
    ]);
}

/**
 * Abonelik iptal et (dönem sonunda geçerli olacak)
 */
function cancelSubscription($subscriptionId)
{
    // stripe in api'den Subscription objeini kullanrak abonelik durumunu iptal ediyoruz.
    $subscription = Subscription::update($subscriptionId, [
        'cancel_at_period_end' => true,
    ]);
    echo json_encode([
        'message' => 'Abonelik iptal edildi. Dönem sonunda geçerli olacak.',
        'subscription_id' => $subscription->id,
        'cancel_at_period_end' => $subscription->cancel_at_period_end,
        'status' => $subscription->status,
    ]);
}
