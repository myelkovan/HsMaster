<?php
// subscription_api.php
// Stripe Abonelik Yönetim API'si - PHP ile
// Bu API ile Stripe üzerinden abonelik oluşturabilir, sorgulayabilir ve iptal edebilirsiniz.

// Stripe PHP SDK'sını yükleyin:
// composer require stripe/stripe-php

require '../PHP/utils/GuzzleHttp/vendor/autoload.php';

// Stripe API anahtarınızı buraya yazın (Test anahtarı kullanmanız tavsiye edilir)
\Stripe\Stripe::setApiKey('sk_test_YOUR_SECRET_KEY_HERE');

// Kullanılan Stripe sınıflarını import ediyoruz
use Stripe\Customer;
use Stripe\Subscription;

// Basit yönlendirme ve istek alma (GET ve POST)
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// JSON input alma
$input = json_decode(file_get_contents('php://input'), true);

// CORS ayarları (isteğe bağlı, testte kullanışlı)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
if ($method == "OPTIONS") {
    header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    exit(0);
}

try {
    getSubscriptionStatus($_GET['session_id']);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

// Abonelik oluşturma fonksiyonu (PaymentMethod attach edilmeden, ödeme yöntemi frontend veya farklı yöntemle ilişkilendirilmiş olarak)

// Abonelik durumu sorgulama fonksiyonu
function getSubscriptionStatus($sessionId)
{
    $session = \Stripe\Checkout\Session::retrieve($sessionId);

    echo "Customer ID: " . $session->customer;
    echo "Subscription ID: " . $session->subscription; // ✅ Ödeme tamamlandıysa artık bu dolu olmalı!
    // Ödeme durumu kontrol et
    if ($session->payment_status === 'paid') {
        echo "Ödeme başarılı! DEVAM...";
    } elseif ($session->payment_status === 'unpaid') {
        echo " Ödeme yapılmamış!";
        die("Ödeme yapılmadı! Abonelik başlatılmadı.");
    } elseif ($session->payment_status === 'no_payment_required') {
        echo "Ödeme gereksiz! DEVAM...";
    }


    $subscriptionId = $session->subscription; // ✅ Session'dan gelen Subscription ID

    if (empty($subscriptionId)) {
        echo "Hata: Subscription ID boş! Ödeme tamamlanmamış olabilir.";
    } else {
        $subscription = \Stripe\Subscription::retrieve($subscriptionId);

        if ($subscription->status === 'active') {
            echo " Abonelik devam ediyor!";
        } elseif ($subscription->status === 'canceled') {
            echo " Abonelik iptal edildi!";
        } elseif ($subscription->status === 'past_due') {
            echo "Ödeme gecikmiş!";
        } elseif ($subscription->status === 'incomplete') {
            echo "Ödeme Başlamış ama Tamamlanmamış!";
        }

        // BU aşamda  echo yapmak yerine veya öncesinde bilgileri 
        // bir veritabanına kayıt etmek iyi olur.
        echo json_encode([
            'session_id' => $session->id,
            'status' => $session->payment_status,
            'subscription_id' => $subscription->id,
            'status' => $subscription->status,
            'current_period_start' => $subscription->current_period_start,
            'current_period_end' => $subscription->current_period_end,
            'cancel_at_period_end' => $subscription->cancel_at_period_end,
        ]);

    }
}

