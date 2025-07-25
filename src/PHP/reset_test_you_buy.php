<?php

include("login/db_connect.php");




$sql ="delete from product where orders_id in (select id from orders where user_id = 109)";
echo f_delete($sql, $conn, false);


$sql ="delete from order_status where orders_id in (select id from orders where user_id = 109)";
echo f_delete($sql, $conn, false);

$sql ="delete from product_tracking_numbers where orders_id in (select id from orders where user_id = 109)";
echo f_delete($sql, $conn, false);

$sql ="delete from orders where id in (select id from orders where user_id = 109)";
echo f_delete($sql, $conn, false);

$sql ="delete from deposit where user_id = 109";
echo f_delete($sql, $conn, false);


$sql ="INSERT INTO `u242434967_jetbasket1`.`orders`
    (`user_id`, `team_id`, `you_buy`, `order_date`, `cancel_date`, `service_code`, `service_name`, `shipment_cost`, `calculated_shipment_cost`, `service_fee`, `calculated_service_fee`, `custom_fee`, `product_total_price`, `calculated_product_total_price`, `calculated_custom_fee`, `deposit_used`, `deposit_remaining`, `package_length`, `package_width`, `package_height`, `package_weight`, `tracking_number`, `label_id`, `label_date`, `label_data`, `stripe_product_id`, `paid_date`, `paid_date_additional`, `paid_date_final`, `proof`, `covered`, `notes`, `agent_notes`, `to_alias`, `to_name`, `to_address`, `to_city`, `to_state`, `to_zipcode`, `to_country`, `to_phone`, `consolidated_to`, `consolidated_price`)
    VALUES
    (109, 22, 2 , NOW(), NULL, 'A180', 'e-PAQ Select Direct Access Canada DDP', 11.78, 11.78, 5.00, 5.00, 0.00, 8.53, 8.53, 0.00, 25.31, 0.33, 5.00, 2.00, 2.00, 5.00, NULL,  NULL, NULL, NULL, 'prod_RbwqGxk1U7kbXQ', 
    NOW(), NULL, NULL, NULL, NULL,NULL, NULL, 'Ablam', 'Ozlem Ucar', '363 Rue Massé', 'Granby', 'QC', 'J2J 1V6', 'Canada', '18566569294', NULL,NULL)";
    
$orders_id = f_update($sql, $conn, false);



$sql ="INSERT INTO `u242434967_jetbasket1`.`product` 
(`orders_id`, `product_link`, `product_buy_link`, `product_description`, `product_additional_description`, `product_description_label`, `product_price`, `product_quantity`, `product_shipment_cost`, `product_buy_price`, `product_length`, `product_width`, `product_height`, `product_length_units`, `product_weight`, `product_weight_units`, `product_picture_path`, `bought_date`, `arrive_date`, `cancel_date`, `cancel_reason`, `return_status`, `return_cost`, `return_label_path`, `tracking_number`, `location`)
VALUES
($orders_id, '1', NULL,'1', '1', '1','1.00', 1, 1.00, NULL, 1.00, 1.00, 1.00, 'in', 1.00, 'lb', 'https://login.hsmaster.ai/images/box6.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
($orders_id, '2', NULL, '2', '2', '2', '2.00', 2, 2.00, NULL, 2.00, 2.00, 2.00, 'in', 2.00, 'lb','https://login.hsmaster.ai/images/box6.png', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";

echo f_update($sql, $conn, false);
  


  
$sql ="Insert into order_status(orders_id, status_id, date) Values($orders_id, 1, NOW())";
echo f_update($sql, $conn, false); 

/* musteri odedi ise calistir */
$sql = "INSERT INTO `u242434967_jetbasket1`.`deposit`
(`user_id`,`date`,`amount`,`description`,`orders_id`,`stripe_product_id`,`shipment_refund`)
SELECT 109, NOW(), 100, NULL, NULL, NULL, NULL";
echo f_update($sql, $conn, false); 

/* musteri odedi ise calistir */
$sql ="INSERT INTO `u242434967_jetbasket1`.`deposit`
(`user_id`,`date`,`amount`,`description`,`orders_id`,`stripe_product_id`,`shipment_refund`)
SELECT 109, NOW(), -25.31, NULL, NULL, NULL, NULL";
echo f_update($sql, $conn, false);  


?>
 