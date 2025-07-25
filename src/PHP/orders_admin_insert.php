
<?php
include("login/db_connect.php");
include("utils/jwt.php");
include("utils/mail.php");


//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
    echo "invalidToken";
    return;
}

//sadece depo ve agent user bu verileri gorebilir
if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false) {
    return;
}


//gelen degerleri al
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POSnT)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}

$id                              = $conn->escape_string($_POST["id"]);
$product_total_price             = $conn->escape_string($_POST["product_total_price"]);
$calculated_product_total_price  = $conn->escape_string($_POST["calculated_product_total_price"]);
$calculated_shipment_cost        = $conn->escape_string($_POST["calculated_shipment_cost"]);
$calculated_custom_fee           = $conn->escape_string($_POST["calculated_custom_fee"]);
$calculated_service_fee          = $conn->escape_string($_POST["calculated_service_fee"]);
$deposit_used                    = isset($_POST["deposit_used"]) ? $conn->escape_string($_POST["deposit_used"]) : 'null';
$covered                         = $conn->escape_string($_POST["covered"]);
$package_height                  = $conn->escape_string($_POST["package_height"]);
$package_length                  = $conn->escape_string($_POST["package_length"]);
$package_width                   = $conn->escape_string($_POST["package_width"]);
$package_weight                  = $conn->escape_string($_POST["package_weight"]);
$shipment_difference             = $conn->escape_string($_POST["shipment_difference"]);
$now                             = $conn->escape_string($_POST["now"]);
$service_code                    = $conn->escape_string($_POST["service_code"]);
$carrier_id                    = $conn->escape_string($_POST["carrier_id"]);
$carrier_code                    = $conn->escape_string($_POST["carrier_code"]);
$service_name                    = $conn->escape_string($_POST["service_name"]);



//normal order ile ilgili girilen verileri kaydet
$sql = "Update orders Set calculated_product_total_price = $calculated_product_total_price, 
                              calculated_shipment_cost = $calculated_shipment_cost, 
                              calculated_custom_fee = $calculated_custom_fee, 
                              calculated_service_fee = $calculated_service_fee, 
                              covered = $covered,
                              service_code = '$service_code', carrier_id = '$carrier_id', carrier_code = '$carrier_code', service_name = '$service_name',
                              package_length = $package_length, package_width = $package_width, package_height = $package_height, package_weight = $package_weight
                              Where id = $id";
echo f_update($sql, $conn, false);



//labelmodal da save tusuna basildi working on item olmasi lazim veya odeme yapilmasi gerekiyorsa payment waiting
if (floatval($calculated_product_total_price) > 0) {

    //odenmemis veya cover edilmemis ve consolide degilse is payment waiting yap 
    if (floatval($shipment_difference) > 0 && ($covered == null || $covered == 0)) {
        $status_id = 0;
    } else {
        $status_id = 4; //working on the item
    }

    //Asil order ve varsa consolidated kayitlarini al 
    $sql = "SELECT id, consolidated_to FROM orders WHERE consolidated_to = $id or id = $id";
    $result = $conn->query($sql);

    //statulerini 4 working on item veya para odenmemisse 0-waiting for payment yap
    //ana kayit working de olabilir payment waiting de olabilir. Consolide olan diger tum kayitlar working olur cunku sadece ana kayita fark cikar
    //normalde consolide ile fiyat artmamis olmasi gerekir zaten amacimiz fiyati dusurmek
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $orders_id = $row['id'];
            $consolidated_to = $row['consolidated_to'];

            //consolide ise working yap para odenecekse consolidenin anlami yok
            if ($consolidated_to != null) {
                $status_id = 4; //working on the item
            }

            $insert_sql = "INSERT INTO order_status (orders_id, status_id, date) VALUES ($orders_id, $status_id, '$now')";
            f_update($insert_sql, $conn, false);

            //4 working on the item ise mail
            if ($status_id == 4) {
                send_mail_working($conn, $orders_id);
            }

            //0 odeme bekleniyor ise mail
            if ($status_id == 0) {
                send_mail_price_increased($conn, $orders_id);
            }
        }
    }
}




$conn->close();


?> 



