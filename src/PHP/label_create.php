<?php

use GuzzleHttp\Psr7\Message;

include("login/db_connect.php");
include("byelabel.php");
include("utils/jwt.php");
include("utils/mail.php");



// Validate the token
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
  echo "invalidToken"; //json_encode(["error" => "invalidToken"]);
  return;
}



try {
  $orders_id = $conn->escape_string($_GET["orders_id"]);
  $now  = $conn->escape_string($_GET["now"]);



  $sql = "SELECT user_id,to_name,to_phone,to_zipcode,to_state,to_city,to_address, package_weight, package_length, package_width, package_height, 
                   service_code, 
                   (select carrier_code from kt_carrier where service_code = orders.service_code) as carrier_code,
                   (select carrier_id from kt_carrier where service_code = orders.service_code) as carrier_id,
                   (select flag from kt_country where name = orders.to_country) as to_country_code
            FROM orders WHERE id = $orders_id";

  if ($result = $conn->query($sql)) {
    $row = $result->fetch_object();
    $user_id =  $row->user_id;
    $to_name =  $row->to_name;
    $to_email =  null;
    $to_phone =  $row->to_phone;
    $to_zipcode =  $row->to_zipcode;
    $to_country_code =  $row->to_country_code;
    $to_state =  $row->to_state;
    $to_city = $row->to_city;
    $to_street_1 = $row->to_address;
    $to_street_2 = '';
    $to_street_3 = '';
    $from_name = "JetBasket";
    $from_taxnumber = null;
    $from_email = 'info@jetbasket.us';
    $from_phone = '+18562138652';
    $from_zipcode = '08034';
    $from_country_code = 'US';
    $from_countrycode = "US";
    $from_zipcode = "08034";
    $from_state = "NJ";
    $from_city = 'Cherry Hill';
    $from_street_1 = '1970 Old Cuthbert Rd Suite 250';
    $from_street_2 = '';
    $from_street_3 = '';
    $invoice_number = null;
    $invoice_date = null;
    $invoice_contents = 'merchandise';
    $invoice_description = null;
    $weight =  $row->package_weight;
    $length =  $row->package_length;
    $width =  $row->package_width;
    $height = $row->package_height;
    $service_code =  $row->service_code;
    $carrier_code = $row->carrier_code;
    $carrier_id = $row->carrier_id;


    if ($carrier_code == "ups") {
      $carrier_packaging = '02';
    } elseif ($carrier_code == "asendia") {
      $carrier_packaging = "package";
    } elseif ($carrier_code == "dhl") {
      $carrier_packaging = "package";
    } elseif ($carrier_code == "fedex") {
      $carrier_packaging = 'YOUR_PACKAGING';
    } else {
      $carrier_packaging = "package";
    }
  }



//token icine gizlemen user_id ile sayfadan gonderilen user_id farkli, bir guvenlik acigi olabilir, cik -- kendisi degilse
if ($token_user_id != $user_id){
    //Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
    if (f_isWarehouseUser($token_user_id, $conn) == false){
        echo -1;
        return;
    }
}



  /*
{
  ["carrier"]=>
  array(4) {
    ["id"]=>
    string(36) "3adf62b2-20af-4285-8301-0328a8b0d462"
    ["code"]=>
    string(5) "fedex"
    ["service"]=>
    string(32) "FEDEX_INTERNATIONAL_CONNECT_PLUS"
    ["packaging"]=>
    string(14) "YOUR_PACKAGING"
  }
  ["invoice"]=>
  array(4) {
    ["date"]=>
    string(10) "2025-01-15"
    ["number"]=>
    string(7) "AA00121"
    ["contents"]=>
    string(11) "merchandise"
    ["description"]=>
    string(9) "NRI: 1111"
  }
  ["from"]=>
  array(10) {
    ["name"]=>
    string(9) "John Wick"
    ["company"]=>
    string(0) ""
    ["tax_number"]=>
    string(6) "100011"
    ["email"]=>
    string(20) "johnwick@example.com"
    ["phone"]=>
    string(10) "1555055555"
    ["country_code"]=>
    string(2) "US"
    ["zip_code"]=>
    string(5) "08034"
    ["state_code"]=>
    string(2) "NJ"
    ["city"]=>
    string(11) "Cherry Hill"
    ["street"]=>
    array(1) {
      [0]=>
      string(28) "1970 Old Cuthbert Rd STE 250"
    }
  }
  ["to"]=>
  array(10) {
    ["name"]=>
    string(12) "Dayne Starkk"
    ["company"]=>
    string(0) ""
    ["sold_to"]=>
    string(0) ""
    ["email"]=>
    string(23) "daynestarkk@example.com"
    ["phone"]=>
    string(10) "4032624326"
    ["country_code"]=>
    string(2) "CA"
    ["zip_code"]=>
    string(7) "V6X 3S8"
    ["state_code"]=>
    string(2) "BC"
    ["city"]=>
    string(14) "West Vancouver"
    ["street"]=>
    array(1) {
      [0]=>
      string(18) "2139 Nelson Avenue"
    }
  }
  ["package"]=>
  array(1) {
    [0]=>
    array(3) {
      ["description"]=>
      string(9) "Package 1"
      ["weight"]=>
      array(1) {
        ["value"]=>
        string(1) "1"
      }
      ["dimensions"]=>
      array(4) {
        ["unit"]=>
        string(2) "in"
        ["length"]=>
        string(1) "1"
        ["width"]=>
        string(1) "1"
        ["height"]=>
        string(1) "1"
      }
    }
  }
  ["items"]=>
  array(1) {
    [0]=>
    array(7) {
      ["code"]=>
      string(6) "ASIN01"
      ["description"]=>
      string(11) "Sport Shoes"
      ["hts_code"]=>
      string(12) "1220.01.1101"
      ["country_of_origin"]=>
      string(2) "US"
      ["quantity"]=>
      int(10)
      ["currency_code"]=>
      string(3) "USD"
      ["total_selling_price"]=>
      int(10)
    }
  }
}


*/

  //ordera eklenen consolide order varsa onlarinda productlarini al ama icinde cancel veya return varsa dus
  $sql = "SELECT product_description_label, product_price, product_quantity, product_buy_price, hscode
            FROM product WHERE (orders_id = $orders_id or orders_id in (select id from orders where consolidated_to = $orders_id )) 
            and cancel_date is null and (return_status != 2 or return_status is null) ";

  // Initialize an empty array to store the items
  $items = [];

  if ($result = $conn->query($sql)) {
    // Loop through each row
    while ($row = $result->fetch_object()) {
      $product_description = explode(' ', $row->product_description_label);
      $sku = implode(' ', array_slice($product_description, 0, 10));
      $sku = substr($sku, 0, 40);

      $product_price = floatval($row->product_price) / 4;
      if ($product_price < 10) {
        $product_price = 10;
      }


      $items[] = [
        "code" => $sku,
        "description" => $row->product_description_label,
        "hts_code" => $row->hscode,
        "country_of_origin" => 'US',
        "quantity" =>  $row->product_quantity,
        "currency_code" => "USD",
        "total_selling_price" => $product_price,

      ];
    }
  } else {
    // Handle query error
    echo "Error: " . $conn->error;
  }


  //get usa time php server avrupada olabilir
  $timezone = new DateTimeZone('America/New_York');
  $date = new DateTime('now', $timezone);

  $invoice_date = $date->format('Y-m-d');
  $invoice_number = "JB-" . $orders_id;
  $invoice_contents = "merchandise";
  $invoice_description = $to_name . " " . $to_phone . " " . $to_country_code . " " . $to_street_1;



  //echo ('İLK BU BİZİM carrier_code' . $carrier_code);
  $ByelabelService = new ByelabelService(null, null);
  $data = $ByelabelService->createLabel_FromByeLabel(
    $from_name,
    $from_taxnumber,
    $from_email,
    $from_phone,
    $from_country_code,
    $from_zipcode,
    $from_state,
    $from_city,
    $from_street_1,
    $from_street_2,
    $from_street_3,
    $to_name,
    $to_email,
    $to_phone,
    $to_country_code,
    $to_zipcode,
    $to_state,
    $to_city,
    $to_street_1,
    $to_street_2,
    $to_street_3,
    $weight,
    $length,
    $width,
    $height,
    $carrier_id,
    $carrier_code,
    $service_code,
    $carrier_packaging,
    $invoice_date,
    $invoice_number,
    $invoice_contents,
    $invoice_description,
    $items
  );




  if ($data['id'] != null) {
    $tracking_number = $data['tracking_number'];
    $id = $data['id'];
    $label_date = $data['created_at'];
    $label_data = $data['label_data'];
    $label_cost = $data['shipment_cost'];

    $sql = "Update orders Set tracking_number = '$tracking_number', 
                                  label_id = '$id', 
                                  label_date = '$label_date',
                                  label_data =  '$label_data',
                                  label_cost = $label_cost
                                  Where id = $orders_id or consolidated_to = $orders_id";

    f_update($sql, $conn, false);

    //Asil order kaydinin statusunu shipped yap
    $sql = "UPDATE order_status SET date = '$now' WHERE orders_id = $orders_id AND status_id = 5";
    if ($conn->query($sql) === TRUE) {
      if ($conn->affected_rows == 0) {
        $sql = "Insert into order_status(orders_id, status_id, date) Values($orders_id, 5, '$now')";
        f_update($sql, $conn, false);
      }
    }

    //consolidated order varsa onlari da shipped yapacagiz once listeyi al          
    $sql = "SELECT id FROM orders WHERE consolidated_to = $orders_id";
    $result = $conn->query($sql);

    $consolidated_order_ids = "";
    if ($result && $result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $orders_id = $row['id'];

        //birlestirilenleri JB-1, JB-2 seklinde birlestir mailde ullanacagiz
        if ($consolidated_order_ids != "") {
          $consolidated_order_ids = $consolidated_order_ids . ", ";
        }
        $consolidated_order_ids = "JB-" . $orders_id;

        // order kaydinin statusunu shipped yap
        $sql = "UPDATE order_status SET date = '$now' WHERE orders_id = $orders_id  AND status_id = 5";
        if ($conn->query($sql) === TRUE) {
          if ($conn->affected_rows == 0) {
            $insert_sql = "INSERT INTO order_status (orders_id, status_id, date) VALUES ($orders_id, 5, '$now')";
            f_update($insert_sql, $conn, false);
          }
        }
      }
    }


    send_mail_shipped($conn, $orders_id, $tracking_number, $consolidated_order_ids);
  }


  echo json_encode($data, JSON_INVALID_UTF8_IGNORE + JSON_PRETTY_PRINT);
} catch (Exception $e) {
  echo "Failed to create label " . $e->getMessage();
}

$conn->close();
