<?php

include("login/db_connect.php");
include("byelabel.php");
include("utils/jwt.php");







// Validate the token
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
    echo json_encode(["error" => "invalidToken"]);
    return;
}



// Fetch input parameters
$orders_id          = $conn->escape_string($_GET["orders_id"]);
$price              = $conn->escape_string($_GET["price"]);
$service_code_param = $conn->escape_string($_GET["service_code"]);
$customTax = [];


// gonderilecek paketin agirlik ve adres bilgilerini al
if ($orders_id !== null) {
    $sql = "SELECT to_state, to_zipcode, to_city, to_address, package_weight, package_length, package_width, package_height, product_total_price, calculated_product_total_price,
                   (select id from kt_country where name = orders.to_country) as contry_id,
                   (select flag from kt_country where name = orders.to_country) as contry_code,
                   (select carrier_code from kt_carrier where service_code = '$service_code_param') as carrier_code,
                   (select carrier_id from kt_carrier where service_code = '$service_code_param') as carrier_id
            FROM orders WHERE id = $orders_id";


    if ($result = $conn->query($sql)) {
        $row = $result->fetch_object();
        $country_id =  $row->contry_id;
        $to_country_code =  $row->contry_code;
        $to_state =  $row->to_state;
        $to_city = $row->to_city;
        $to_address = $row->to_address;
        $to_zipcode =  $row->to_zipcode;
        $weight =  $row->package_weight;
        $length =  $row->package_length;
        $width =  $row->package_width;
        $height = $row->package_height;
        $product_total_price = $row->product_total_price;
        $calculated_product_total_price = $row->calculated_product_total_price;
        $carrier_code = $row->carrier_code;
        $carrier_id = $row->carrier_id;
    }
}



// o ulke ve paket agirligi icin kar oranlarini al
if ($weight != null) {
    $sql = "
        SELECT percentage_value, fixed_value,
               (SELECT carrier_code FROM kt_carrier WHERE id = profit_settings.carrier_id) AS carrier_code,
               (SELECT service_code FROM kt_carrier WHERE id = profit_settings.carrier_id) AS service_code,
               (SELECT service_name FROM kt_carrier WHERE id = profit_settings.carrier_id) AS service_name
        FROM profit_settings
        WHERE ((weight_1 <= $weight AND weight_2 >= $weight) OR (weight_1 IS NULL AND weight_2 IS NULL)) AND (country_id = $country_id OR country_id IS NULL)
        ORDER BY country_id DESC, carrier_id DESC, weight_1 ASC, weight_2 ASC";

    if ($result = $conn->query($sql)) {
        $profitValues = $result->fetch_all(MYSQLI_ASSOC);
    }
}

// o ulke ve fiat icin custom tax 
if ($price !== null) {
    $sql = "SELECT price, tax_percentage
            FROM custom_settings
            WHERE country_id = $country_id AND (price <= $price OR price IS NULL)
            ORDER BY price DESC
            LIMIT 1";

    // Returns custom tax
    if ($result = $conn->query($sql)) {
        $customTax =  $result->fetch_all(MYSQLI_ASSOC);
    }
}

// Şimdilik DEPO nun adresini from adresine set edelim(You Buy 1 seçince verilen adres). değerleri kontrol ettir
$from_countrycode = "US";
$from_zipcode = "08034";
$from_state = "NJ";
$from_city = 'Cherry Hill';
$from_street1 = null;
$from_street2 = null;
$from_street3 = null;
$to_street2 = null;
$to_street3 = null;


//bu kisim veritabanina tasinmali
//disaridan null geldi ise service de null olmali
$carrier_packaging = null;
if ($service_code_param === "null" || $service_code_param === "all") {
    $carrier_id = null;
    $carrier_code = null;
    $carrier_packaging = null;
    $service_code_param = null;
} else {
    if ($carrier_code == "ups") {
        $carrier_packaging = '02';
    } elseif ($carrier_code == "asendia") {
        $carrier_packaging = "package";
    } elseif ($carrier_code == "fedex") {
        $carrier_packaging = 'YOUR_PACKAGING';
    }
}

//echo $carrier_id;
//echo $carrier_code;
//$carrier_packaging = null;
//echo " carrier_packaging:" ;
//echo $service_code_param;

//$carrier_id = '3adf62b2-20af-4285-8301-0328a8b0d462';
//$carrier_code = 'fedex';
//$carrier_packaging = 'YOUR PACKAGING';
//$service_code_param = 'FEDEX_INTERNATIONAL_CONNECT_PLUS';






// Example of how you'd calculate shipping costs, profit, and custom fees
//$fedexService = new FedexService(null, null);
$ByelabelService = new ByelabelService(null, null);
$Rates = $ByelabelService->getRates_FromByeLabel(
    $from_countrycode,
    $from_zipcode,
    $to_country_code,
    $to_zipcode,
    $from_state,
    $to_state,
    $from_city,
    $to_city,
    $from_street1,
    $from_street2,
    $from_street3,
    $to_address,
    $to_street2,
    $to_street3,
    $weight,
    $length,
    $width,
    $height,
    $carrier_id,
    $carrier_code,
    $service_code_param,
    $carrier_packaging
);

//var_dump($Rates);
//  exit;



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

            $rate['custom_fee'] = (float) $calculated_custom_fee;
            break;
        }
    }

    if (!$find) {
        //$rate['error'] = "No profit value found for this carrier";
        // Profit tablosunda kayıt yok ise %50 kar ekle
        $calculated_price =  floatval($rate['shipment_cost']) + ((floatval($rate['shipment_cost']) * (floatval(50) / 100)));
        $rate['shipment_cost'] = $calculated_price;
    }
}



// Output the formatted array
echo json_encode($Rates, JSON_PRETTY_PRINT);

//echo json_encode($Rates,JSON_INVALID_UTF8_IGNORE + JSON_PRETTY_PRINT);

$conn->close();
