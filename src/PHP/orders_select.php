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


$user_id  = $conn->escape_string($_GET["user_id"]);

//baskasinin orderini goremesi
if ($token_user_id != $user_id) {
    return;
}


$sql = "Select id, you_buy, order_date, cancel_date,
            (SELECT status_id FROM order_status WHERE orders_id = o.id ORDER BY id DESC LIMIT 1 ) as status_id,
            
            CASE WHEN cancel_date IS NOT NULL 
            THEN 'Canceled'
            ELSE 
                (SELECT status FROM kt_status WHERE id = (SELECT status_id FROM order_status WHERE orders_id = o.id ORDER BY id DESC LIMIT 1 ))
            END AS status,
    
            tracking_number,  
            proof,
            stripe_product_id, 
            paid_date, paid_date_additional,paid_date_final,
            covered,
            product_total_price,
            calculated_product_total_price, 
            shipment_cost, calculated_shipment_cost, service_fee, calculated_service_fee, custom_fee, calculated_custom_fee, 
            service_code, service_name, carrier_id, carrier_code, 
            notes,
            deposit_used, deposit_remaining,
            (select sum(amount) from deposit where orders_id = o.id and user_id = o.user_id and shipment_refund = 1 ) as deposited,
            consolidated_to,
            to_alias, to_name, to_address, to_city, to_state, to_zipcode, to_country, to_phone, 
        
            (SELECT SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2)) FROM product WHERE orders_id = o.id ) as product_price_total,
            
            
             
            (SELECT sum(CASE when (((product_price * product_quantity) + product_shipment_cost) * 1.06625) > product_buy_price
                         then ROUND((((product_price * product_quantity) + product_shipment_cost) * 1.06625),2)
                         else (ROUND(product_buy_price,2))
                         End)
             FROM product WHERE orders_id = o.id)  as product_buy_price_total,
             
             
             
            
            (SELECT SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2)) FROM product WHERE orders_id = o.id and cancel_date is not null) as canceled_total,
            
            (SELECT CASE when SUM(((product_price * product_quantity) + product_shipment_cost) * 1.06625) > sum(product_buy_price)
                         then SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2))
                         else SUM(ROUND(product_buy_price,2))
                         End
             FROM product WHERE orders_id = o.id and return_status ='2')  as returned_total,
             
             (select (count(*) * 3) + COALESCE(SUM(return_cost), 0) from product WHERE orders_id = o.id and return_status = 2) as return_cost,
     
       
            (select JSON_ARRAYAGG(id) from product where orders_id = o.id order by id) as product_ids, 
            (select JSON_ARRAYAGG(product_picture_path) from product where orders_id = o.id order by id) as product_picture_paths, 
            (select JSON_ARRAYAGG(bought_date) from product where orders_id = o.id order by id) as product_bought_dates, 
            (select JSON_ARRAYAGG(product_link) from product where orders_id = o.id order by id) as product_links,
            (select JSON_ARRAYAGG(product_description) from product where orders_id = o.id order by id) as product_descriptions,
            (select JSON_ARRAYAGG(cancel_date) from product where orders_id = o.id order by id) as product_cancel_dates,
            (select JSON_ARRAYAGG(cancel_reason) from product where orders_id = o.id order by id) as product_cancel_reasons,
     
         (select JSON_ARRAYAGG(CASE WHEN return_status = 0 then 'Return requested' 
                                    WHEN return_status = 1 then 'Return confirmed'
                                    WHEN return_status = 2 then 'Returned'
                                    WHEN return_status = 3 then 'Not returnable' end ) from product where orders_id = o.id order by id) as product_return_status,

  
              (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                    'product_id', p.id,
                    'tracking_number', COALESCE(pt.tracking_number, NULL),  -- If no tracking number, return NULL
                    'arrive_date', COALESCE(pt.arrive_date, p.arrive_date)  -- If no arrive_date, return product's arrive_date
                    )) AS all_products_tracking_info
            
            FROM product p LEFT JOIN product_tracking_numbers pt ON pt.product_id = p.id AND pt.orders_id = o.id  
            WHERE p.orders_id = o.id) as product_tracking_numbers
     
         From orders o where user_id = $user_id order by id desc";



echo f_select($sql, $conn);
