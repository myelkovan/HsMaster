
<?php
include("login/db_connect.php");
include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id == null || $token_user_id == 0) {
    echo "invalidToken";
    return;
}


//gelen degerleri al
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}



$id                      = $conn->escape_string($_POST["id"]);
$user_id                 = $conn->escape_string($_POST["user_id"]);
$you_buy                 = $conn->escape_string($_POST["you_buy"]);
$product_total_price     = $conn->escape_string($_POST["product_total_price"]);
$shipment_cost           = isset($_POST["shipment_cost"]) ? $conn->escape_string($_POST["shipment_cost"]) : 'null';
$service_fee             = isset($_POST["service_fee"]) ? $conn->escape_string($_POST["service_fee"]) : 'null';
$custom_fee              = isset($_POST["custom_fee"]) ? $conn->escape_string($_POST["custom_fee"]) : 'null';
$deposit_used            = isset($_POST["deposit_used"]) ? $conn->escape_string($_POST["deposit_used"]) : 'null';
$deposit_remaining       = isset($_POST["deposit_remaining"]) ? $conn->escape_string($_POST["deposit_remaining"]) : 'null';
$service_code            = isset($_POST["service_code"]) ? $conn->escape_string($_POST["service_code"]) : 'null';
$carrier_id              = isset($_POST["carrier_id"]) ? $conn->escape_string($_POST["carrier_id"]) : 'null';
$carrier_code            = isset($_POST["carrier_code"]) ? $conn->escape_string($_POST["carrier_code"]) : 'null';
$service_name            = isset($_POST["service_name"]) ? $conn->escape_string($_POST["service_name"]) : 'null';
$package_height          = isset($_POST["package_height"]) ? $conn->escape_string($_POST["package_height"]) : 'null';
$package_length          = isset($_POST["package_length"]) ? $conn->escape_string($_POST["package_length"]) : 'null';
$package_width           = isset($_POST["package_width"]) ? $conn->escape_string($_POST["package_width"]) : 'null';
$package_weight          = isset($_POST["package_weight"]) ? $conn->escape_string($_POST["package_weight"]) : 'null';
$now                     = $conn->escape_string($_POST["now"]);
$to_alias                = $conn->escape_string($_POST["to_alias"]);
$to_name                 = $conn->escape_string($_POST["to_name"]);
$to_address              = $conn->escape_string($_POST["to_address"]);
$to_city                 = $conn->escape_string($_POST["to_city"]);
$to_state                = isset($_POST["to_state"]) ? $conn->escape_string($_POST["to_state"]) : 'null';
$to_zipcode              = $conn->escape_string($_POST["to_zipcode"]);
$to_country              = $conn->escape_string($_POST["to_country"]);
$to_phone                = $conn->escape_string($_POST["to_phone"]);





$sql_insert = "Insert Into orders(user_id, you_buy, product_total_price, calculated_product_total_price, 
                                    shipment_cost, calculated_shipment_cost, 
                                    service_fee, calculated_service_fee, 
                                    custom_fee, calculated_custom_fee, 
                                    deposit_used, deposit_remaining,
                                    service_code,  carrier_id, carrier_code,  service_name, 
                                    package_length, package_width, package_height, package_weight, order_date,
                                    to_alias, to_name, to_address, to_city, to_state, to_zipcode, to_country, to_phone ) 
                            Values($user_id, $you_buy, $product_total_price, $product_total_price, 
                                    $shipment_cost, $shipment_cost, 
                                    $service_fee, $service_fee, 
                                    $custom_fee, $custom_fee, 
                                    $deposit_used, $deposit_remaining,
                                    '$service_code', '$carrier_id', '$carrier_code', '$service_name',
                                    $package_length, $package_width,$package_height, $package_weight, '$now',
                                    '$to_alias', '$to_name', '$to_address', '$to_city', '$to_state', '$to_zipcode', '$to_country', '$to_phone' )";

$sql_update = "Update orders Set user_id = $user_id, you_buy = $you_buy, 
                                 product_total_price = $product_total_price, calculated_product_total_price = $product_total_price,
                                 shipment_cost = $shipment_cost, calculated_shipment_cost = $shipment_cost, 
                                 service_fee = $service_fee, calculated_service_fee = $service_fee, 
                                 custom_fee = $custom_fee, calculated_custom_fee = $custom_fee,
                                 deposit_used = $deposit_used, deposit_remaining = $deposit_remaining,
                                 service_code = '$service_code', carrier_id = '$carrier_id',carrier_code = '$carrier_code', service_name = '$service_name',
                                 package_length = $package_length, package_width = $package_width, package_height = $package_height, package_weight = $package_weight,
                                 to_alias = '$to_alias', to_name = '$to_name', to_address = '$to_address', to_city = '$to_city', to_state = '$to_state', 
                                 to_zipcode = '$to_zipcode', to_country = '$to_country', to_phone = '$to_phone' Where id = $id";




if ($id == "" or $id == 0) {
    $id =  f_update($sql_insert, $conn, false);

    //Yeni order girildiyse odeme bekleniyor statusunu ata
    $sql_insert = "insert into order_status (orders_id, status_id, date) values($id, 0, '$now')";
    f_update($sql_insert, $conn, false);
} else {
    f_update($sql_update, $conn, false);
}


//order ile birlikte product datasida geliyor her defasinda tum productlari silip yeniden olusturuyoruz
//burada product kendi basina ayri update edilmeli
if ($id != "" and $id > 0) {

    f_update("Delete from product where orders_id = $id", $conn, false);
    f_update("Delete from product_tracking_numbers where orders_id = $id", $conn, false);

    // Check if 'products' array is set in POST data
    if (isset($_POST['products']) && is_array($_POST['products'])) {
        // Loop through each product
        foreach ($_POST['products'] as $product) {
            // Escape and sanitize each product field
            $product_link = $conn->escape_string($product['product_link']);
            $product_description = $conn->escape_string($product['product_description']);
            $product_description_label = $conn->escape_string($product['product_description']);
            $product_additional_description = $conn->escape_string($product['product_additional_description']);
            $product_price = $conn->escape_string($product['product_price']);
            $product_shipment_cost = $conn->escape_string($product['product_shipment_cost']);
            $product_quantity = $conn->escape_string($product['product_quantity']);
            $product_length = $conn->escape_string($product['product_length']);
            $product_width = $conn->escape_string($product['product_width']);
            $product_height = $conn->escape_string($product['product_height']);
            $product_length_units = $conn->escape_string($product['product_length_units']);
            $product_weight = $conn->escape_string($product['product_weight']);
            $product_weight_units = $conn->escape_string($product['product_weight_units']);
            $product_picture_path = $conn->escape_string($product['product_picture_path']);
            $hscode = $conn->escape_string($product['hscode']);

            // Prepare and execute SQL query to insert or update each product
            $sql = "INSERT INTO product (orders_id, product_link, product_description, product_description_label, product_additional_description, product_price, product_shipment_cost, product_quantity, product_length, product_width, product_height, product_length_units, product_weight, product_weight_units, product_picture_path,hscode) 
                    VALUES ($id , '$product_link', '$product_description', '$product_description_label','$product_additional_description', '$product_price', '$product_shipment_cost', '$product_quantity', '$product_length', '$product_width', '$product_height',  '$product_length_units', '$product_weight', '$product_weight_units', '$product_picture_path','$hscode')
                    ON DUPLICATE KEY UPDATE
                    orders_id = $id, 
                    product_link = VALUES(product_link),
                    product_description = VALUES(product_description),
                    product_description_label = VALUES(product_description),
                    product_additional_description = VALUES(product_additional_description),
                    product_price = VALUES(product_price),
                    product_shipment_cost = VALUES(product_shipment_cost),
                    product_quantity = VALUES(product_quantity),
                    product_length = VALUES(product_length),
                    product_width = VALUES(product_width),
                    product_height = VALUES(product_height),
                    product_length_units = VALUES(product_length_units),
                    product_weight = VALUES(product_weight),
                    product_weight_units = VALUES(product_weight_units),
                    product_picture_path = VALUES(product_picture_path),
                    hscode = VALUES(hscode)";
            //echo $sql;


            $result = $conn->query($sql);
            if ($result) {
                $tracking_numbers = [];

                // Get the last inserted or updated product ID
                $product_id = $conn->insert_id;

                if (isset($product['product_tracking_numbers'])) {
                    if (is_string($product['product_tracking_numbers'])) {
                        $tracking_numbers = json_decode($product['product_tracking_numbers'], true);
                    } else {
                        // If it's already an array, no need to decode
                        $tracking_numbers = $product['product_tracking_numbers'];
                    }


                    // Now insert tracking numbers into product_tracking_numbers
                    if (!empty($tracking_numbers)) {
                        foreach ($tracking_numbers as $row) {
                            // Assuming $tracking is an associative array with 'value' and 'label'
                            $tracking_number = $conn->escape_string($row['tracking_number']);
                            $tracking_sql = "INSERT INTO product_tracking_numbers (orders_id, product_id, tracking_number) 
                                             VALUES ($id, $product_id, '$tracking_number')
                                             ON DUPLICATE KEY UPDATE tracking_number = VALUES(tracking_number)";
                            $conn->query($tracking_sql);
                            // echo $tracking_sql;
                        }
                    }
                }
            } else {
                echo "Error: " . $conn->error;
            }

            // Check if the query was successful
            if ($result) {
            } else {
                echo "Error adding/updating products: " . $conn->error;
            }
        }
    } else {
        echo "No products found in the request.";
    }
}


$conn->close();
echo $id;

?> 






