<?php
$allowed_referers = [
    'https://jetbasket.us',
    'https://login.hsmaster.ai'
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

include("login/db_connect.php");
include("byelabel.php");
include("utils/jwt.php");

// // Gelen isteğin referer bilgisi
// $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

// // Sunucunuzun domaini (referer kontrolü için)
// $allowed_domain = 'jetbasket.us'; // Buraya kendi domaininizi yazın

// // Eğer referer boşsa veya izin verilen domain içermiyorsa erişimi engelle
// if (empty($referer) || strpos($referer, $allowed_domain) === false) {
//     header('HTTP/1.0 403 Forbidden'); // 403 yasaklama kodu
//     echo "Bu sayfaya doğrudan erişim izni yok.";
//     exit; // İşlemi sonlandır
// }


// // Validate the token
// $ret = f_isTokenValid();
// $token_user_id = (int) $ret['user_id'];
// if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
//     echo json_encode(["error" => "invalidToken"]);
//     return;
// }



// Fetch input parameters
//$orders_id          = $conn->escape_string($_GET["orders_id"]);
$price              =  $conn->escape_string($_GET["price"]);
//echo "m-eldim 1.1";
$service_code_param = null; //$conn->escape_string($_GET["service_code"]);
$customTax = [];

$country_id = "36";  // $conn->escape_string($_GET["country_id"]);

$to_country_code = $conn->escape_string($_GET["toCountry"]);

$to_zipcode = $conn->escape_string($_GET["toZipcode"]);
$to_state =   $conn->escape_string($_GET["toState"]);   //  "";    // "BC"; //$_GET["state"];
$to_city =    $conn->escape_string($_GET["toCity"]); //  "";  //"DORMAGEN";   //"West Vancouver"; //$_GET["city"];
$to_street_1 =  $conn->escape_string($_GET["toStreet"]); //  "";  //  "Zonser Str. 64";   //"2139 Nelson Avenue";
$to_street_2 = "";
$to_street_3 = "";    //  $to_street_1="789"; $to_street_2="012"; $to_street_3="345";

$weight = $conn->escape_string($_GET["weight"]);
$length = $conn->escape_string($_GET["length"]);
$width = $conn->escape_string($_GET["width"]);
$height = $conn->escape_string($_GET["height"]);


$from_country_code = $conn->escape_string($_GET["fromCountry"]);
$from_zipcode = $conn->escape_string($_GET["fromZipcode"]);
$from_state =  $conn->escape_string($_GET["fromState"]);
$from_city =  $conn->escape_string($_GET["fromCity"]);
$from_street_1 =  $conn->escape_string($_GET["fromStreet"]);
$from_street_2 = "";
$from_street_3 = "";    //  $from_street_1="123"; $from_street_2="456"; $from_street_3="678";

$carrier_id = null;
$carrier_code = null;
$carrier_service = null;
$carrier_packaging = null;

$product_total_price = null;
$calculated_product_total_price = null;



// o ulke ve paket agirligi icin kar oranlarini al
if ($weight != null) {
    $sql = "
        SELECT percentage_value, fixed_value,
               (SELECT carrier_code FROM kt_carrier WHERE id = profit_settings.carrier_id) AS carrier_code,
               (SELECT service_code FROM kt_carrier WHERE id = profit_settings.carrier_id) AS service_code,
               (SELECT service_name FROM kt_carrier WHERE id = profit_settings.carrier_id) AS service_name
        FROM profit_settings
        LEFT OUTER JOIN kt_country on profit_settings.country_id= kt_country.id
        WHERE ((weight_1 <= $weight AND weight_2 >= $weight) OR (weight_1 IS NULL AND weight_2 IS NULL)) 
         AND (kt_country.flag = '$to_country_code' OR country_id IS NULL)
         ORDER BY country_id DESC, carrier_id DESC, weight_1 ASC, weight_2 ASC";

    if ($result = $conn->query($sql)) {
        $profitValues = $result->fetch_all(MYSQLI_ASSOC);
    }
}


// o ulke ve fiat icin custom tax 
if ($price !== null) {
    $sql = "SELECT price, tax_percentage
            FROM custom_settings
            LEFT OUTER JOIN kt_country on custom_settings.country_id= kt_country.id
            WHERE kt_country.flag=  '$to_country_code' AND (price <= $price OR price IS NULL)
            ORDER BY price DESC
            LIMIT 1";


    // Returns custom tax
    if ($result = $conn->query($sql)) {
        $customTax =  $result->fetch_all(MYSQLI_ASSOC);
    }
}



$ByelabelService = new ByelabelService(null, null);
$Rates = $ByelabelService->getRates_FromByeLabel(
    $from_country_code,
    $from_zipcode,
    $to_country_code,
    $to_zipcode,
    $from_state,
    $to_state,
    $from_city,
    $to_city,
    $from_street_1,
    $from_street_2,
    $from_street_3,
    $to_street_1,
    $to_street_2,
    $to_street_3,
    $weight,
    $length,
    $width,
    $height,
    $carrier_id,
    $carrier_code,
    $carrier_service,
    $carrier_packaging
);

//var_dump($Rates);



//Bylabeldan gelen data uzerine kar oranlarini ve custem fee yi ekler
foreach ($Rates as &$rate) {

    $find = false;



    foreach ($profitValues as $profitValue) {

        if ($profitValue['service_code'] == null || $rate['service_code'] == $profitValue['service_code']) {
            $find = true;

            // Calculate the shipment cost
            $calculated_price =  floatval($rate['shipment_cost']) + ((floatval($rate['shipment_cost']) * (floatval($profitValue['percentage_value']) / 100)) + floatval($profitValue['fixed_value']));
            $rate['shipment_cost'] = $calculated_price;

            //carrierCode null ise tum carrierler isteniyor yani priceden ekraninda geliyoruz, calculated degil customer fiyat hesapliyor
            if ($service_code_param == null) {
                $product_price = $product_total_price;
            } else {
                $product_price = $calculated_product_total_price;
            }


            $calculated_custom_fee = 0;
            if (count($customTax) > 0) {

                $product_price = floatval($product_price) / 4;
                if ($product_price < 10) {
                    $product_price = 10;
                }

                $calculated_custom_fee =  ($product_price / 100) * floatval($customTax[0]['tax_percentage']);
            }

            $rate['custom_fee'] = $calculated_custom_fee;

            break;
        }
    }

    if (!$find) {
        $rate['error'] = "No profit value found for this carrier";
    }
}

//echo $Rates;

// Output the formatted array
echo json_encode($Rates, JSON_PRETTY_PRINT);

//echo json_encode($Rates,JSON_INVALID_UTF8_IGNORE + JSON_PRETTY_PRINT);

$conn->close();
