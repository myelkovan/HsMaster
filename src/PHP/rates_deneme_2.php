<?php
include("./login/db_connect.php");
include("byelabel.php");

// Example of how you'd calculate shipping costs, profit, and custom fees
//$fedexService = new FedexService(null, null);
$ByelabelService = new ByelabelService(null, null);
//$data = $ByelabelService->getSession();


$metod    = $conn->escape_string($_GET["metod"]);

if ($metod === "token") {
    echo ("ByeLabel APisini deniyorum. Token alma isteği gönderildi." . "\n");
    $data = $ByelabelService->getSession();
}
if ($metod === "getcarriersList") {
    echo ("ByeLabel APisini deniyorum. getcarriersList  isteği gönderildi." . "\n");
    $data = $ByelabelService->getcarriersList();
}

if ($metod === "getcountryList") {
    echo ("ByeLabel APisini deniyorum. getcountryList  isteği gönderildi." . "\n");
    $data = $ByelabelService->getcountryList();
}

if ($metod === "getcountryList") {
    echo ("ByeLabel APisini deniyorum. getcountryList  isteği gönderildi." . "\n");
    $data = $ByelabelService->getcountryList();
}

if ($metod === "getStateProvinceList") {
    echo ("ByeLabel APisini deniyorum. getStateProvinceList  isteği gönderildi." . "\n");
    $data = $ByelabelService->getStateProvinceList("US");
}
if ($metod === "getZipcodeToStateCity") {
    echo ("ByeLabel APisini deniyorum. getZipcodeToStateCity  isteği gönderildi." . "\n");
    $data = $ByelabelService->getZipcodeToStateCity('US', '07110');
}

if ($metod === "getRates_FromByeLabel") {
    echo ("ByeLabel APisini deniyorum. getRates_FromByeLabel  isteği gönderildi." . "\n");
    //$to_zipcode = 'V7V 2P6';    $to_country_code = 'CA';
    $to_zipcode = 'J2J 1V6';
    $to_country_code = 'ca';
    // array(5) {
    // ["zip_code"]=>
    // string(7) "J2J 1V6"
    // ["country_code"]=>
    // string(2) "ca"
    // ["state_code"]=>
    // string(2) "QC"
    // ["city"]=>
    // string(6) "Granby"
    // ["street"]=>
    // array(1) {
    //   [0]=>
    //   string(14) "363 Rue Massé"
    
    $from_zipcode = '08034';    $from_country_code = 'US';
    $weight = "5";    $length = "1";
    $width = "1";    $height = "1";
    $from_state="NJ";     $to_state= "QC";    //  $from_state="CA"; $to_state="BC";
    $from_city = "Cherry Hill";    $to_city = "Granby";    //  $from_city="Vancouver"; $to_city="Vancouver";
    $from_street_1="";  $from_street_2="";    $from_street_3 = "";    //  $from_street_1="123"; $from_street_2="456"; $from_street_3="678";
    $to_street_1 = "363 Rue Massé";    $to_street_2 = "";    $to_street_3 = "";    //  $to_street_1="789"; $to_street_2="012"; $to_street_3="345";
    
    $carrier_code = null;
    $carrier_service = null;
    $carrier_packaging = null;

    $carrier_code    = $conn->escape_string($_GET["param2"]);
    $data = $ByelabelService->getRates_FromByeLabel($from_country_code, $from_zipcode, $to_country_code, $to_zipcode, $from_state, $to_state, $from_city, $to_city,
                                                        $from_street_1,$from_street_2,$from_street_3,
                                                        $to_street_1,$to_street_2,$to_street_3,
                                                        $weight,$length,$width,$height,
                                                        $carrier_code,$carrier_service,$carrier_packaging);
    //var_dump($data);
    // Acaba  for döngüsü ile rate leri alabiliyormuyuz buna bi bakıyoruz 
    // foreach ($data as &$rate) {
    //     echo ("--FOR DÖNGÜSÜNDE Kİ var_dump(rate)  YAZIYORUM--" . "\n");
    //     var_dump($rate) ;
    //     $rate['calculated_shipment_cost'] = "calculated_price";
    //     $rate['calculated_custom_fee'] = "calculated_custom_fee";

    //     echo ("--FOR DÖNGÜSÜNDE Kİ RATE DEĞERLERİNİ YAZIYORUM--" . "\n");
    //     echo (" rate.serviceCode: " . $rate['serviceCode'] . "\n");
    //     echo (" rate.shipmentCost: " . $rate['shipmentCost'] . "\n");
    //     echo (" rate.otherCost: " . $rate['otherCost'] . "\n");
    //     echo (" rate.calculated_shipment_cost: " . $rate['calculated_shipment_cost'] . "\n");
    //     echo (" rate.calculated_custom_fee: " . $rate['calculated_custom_fee'] . "\n");
    //     }
}

if ($metod === "createLabel_FromByeLabel") {
    echo ("ByeLabel APisini deniyorum. createLabel_FromByeLabel  isteği gönderildi." . "\n");
   

    $to_name = 'Dayne Starkk';
    $to_email = 'daynestarkk@example.com';
    $to_phone = '4032624326';
    $to_zipcode = 'V6X 3S8';
    $to_country_code = 'CA';
    $to_state = "BC";    //   $to_state="BC";
    $to_city = "West Vancouver";    //  $from_city="Vancouver"; $to_city="Vancouver";
    $to_street_1 = "2139 Nelson Avenue";
    $to_street_2 = "";$to_street_3 = "";    //  $to_street_1="789"; $to_street_2="012"; $to_street_3="345";
    $from_zipcode = '08034';
    $from_country_code = 'US';
    $from_name = 'John Wick';
    $from_taxnumber = '100011';
    $from_email = 'johnwick@example.com';
    $from_phone = '1555055555';
    $from_state = "NJ";         //$from_state="CA";
    $from_city = "Cherry Hill";
    $from_street_1 = "1970 Old Cuthbert Rd STE 250";
    $from_street_2 = "";
    $from_street_3 = "";    //  $from_street_1="123"; $from_street_2="456"; $from_street_3="678";
    $weight = "1";
    $length = "1";
    $width = "1";
    $height = "1";
    $carrier_id = 'asendia';
    $carrier_code = 'asendia';
    $carrier_service = 'A180';
     $carrier_packaging = 'package';
    $invoice_number = 'AA00121';
     $invoice_date = '2025-01-15';
    $invoice_contents = 'merchandise';
     $invoice_description = 'NRI: 1111';
    $items_code = 'ASIN01';
    $items_description = 'Sport Shoes';
    $items_hts_code = '1220.01.1101';
    $items_country_of_origin = 'US';
    $items_quantity = 10;
    $items_currency_code = 'USD';
    $items_total_selling_price = 10;

    echo ('İLK BU BİZİM carrier_code' . $carrier_code);

    $data = $ByelabelService->createLabel_FromByeLabel(
                                                $from_name ,$from_taxnumber,$from_email,$from_phone,$from_country_code,$from_zipcode,
                                                $from_state,$from_city,$from_street_1,$from_street_2,$from_street_3,
                                                $to_name,$to_email,$to_phone,$to_country_code,$to_zipcode,
                                                $to_state,$to_city,$to_street_1,$to_street_2,$to_street_3 ,
                                                $weight,$length,$width,$height,
                                                $carrier_id, $carrier_code,$carrier_service,$carrier_packaging,
                                                $invoice_date,$invoice_number,$invoice_contents,$invoice_description,
                                                $items_code,$items_description,$items_hts_code,$items_country_of_origin,
                                                $items_quantity,$items_currency_code,$items_total_selling_price
                                                );

}

if ($metod === "voidLabel_FromByeLabel") {
    echo ("ByeLabel APisini deniyorum. voidLabel_FromByeLabel  isteği gönderildi." . "\n");
    $tracking_number    = $conn->escape_string($_GET["param2"]);
    if ($tracking_number ==="" ){
        $tracking_number= "AS002991152US";  //AS002989839US";  //"AS002989818US";  // "AS002989799US";  // "AS002989787US";  // "AS002989768US";  // "AS002989746US";  // "AS002989710US";// "AS002989655US";  // "AS002989641US";    //"AS002989598US";  // "AS002989571US";    //"AS002989535US";  // "AS002989494US";  // "AS002984275US";
    }
        $data = $ByelabelService->voidLabel_FromByeLabel($tracking_number , $labelId = '');
}

if ($metod === "trackShipment_FromByeLabel") {
    echo ("ByeLabel APisini deniyorum. trackShipment  isteği gönderildi." . "\n");
    $tracking_number= 'F653322F880147569A6D';  //'F653322F880147569A6D'; // 8045211460104EB6BD24 '';    //'E30111776AC54FF88E0A';
   
    $tracking_number    = $conn->escape_string($_GET["param2"]);
    if ($tracking_number === "") {
        $tracking_number = "AS002991152US";  //AS002989839US";  //"AS002989818US";  // "AS002989799US";  // "AS002989787US";  // "AS002989768US";  // "AS002989746US";  // "AS002989710US";// "AS002989655US";  // "AS002989641US";    //"AS002989598US";  // "AS002989571US";    //"AS002989535US";  // "AS002989494US";  // "AS002984275US";
    }
    echo "tracking number :" . $tracking_number;
    $carrierCode= 'asendia';    //'asendia_epaq_select_ddp';
    $data = $ByelabelService->trackShipment_FromByeLabel($carrierCode, $tracking_number);
}
var_dump($data);
echo "TEBRİKLER : " .  "\n" . json_encode($data,JSON_INVALID_UTF8_IGNORE + JSON_PRETTY_PRINT);



?>
