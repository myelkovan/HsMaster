
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

//sadece depo ve agent user bu verileri gorebilir
if (f_isAgenOrWarehouseUser($token_user_id, $conn) == false) {
    f_log($token_user_id,  "order admin select YETKİSİZ ERİŞİM ", "user_ip_country_code :" .  $user_ip_country_code,  1, $conn, true);

    return;
}

//	f_log($token_user_id,  "order admin select ", "user_ip_country_code :" .  $user_ip_country_code,  1, $conn, false);


//gelen degerleri al
$team_id  = isset($_GET["team_id"]) ? $conn->escape_string($_GET["team_id"]) : 'NULL';
$buyer    = $conn->escape_string($_GET["buyer"]);


$sql = "Select id, 
        you_buy, 
        order_date, 
        user_id, team_id, (select name from user where id = team_id) as job_owner,

        (SELECT status_id FROM order_status WHERE orders_id = o.id ORDER BY id DESC LIMIT 1 ) as status_id,
        
        CASE WHEN cancel_date IS NOT NULL 
        THEN 'Canceled'
        ELSE 
            (SELECT status FROM kt_status WHERE id = (SELECT status_id FROM order_status WHERE orders_id = o.id ORDER BY id DESC LIMIT 1 ))
        END AS status,
       
        tracking_number,  
        shipment_cost, calculated_shipment_cost,
        service_fee, calculated_service_fee,
        custom_fee, calculated_custom_fee, 
        service_code, carrier_id,carrier_code, service_name,
        product_total_price,  
        calculated_product_total_price,
        deposit_used,
        package_length,
        package_width,
        package_height,
        package_weight,
        label_id,
        label_date,           
        label_data,
        stripe_product_id,
        paid_date, paid_date_additional, paid_date_final,cancel_date,
        proof,
        covered,
        notes, agent_notes,
        consolidated_to,
        (select name from user where id = user_id) as user_name,
        to_name, to_address, to_city, to_state, to_zipcode, to_phone, to_country, (select flag from kt_country where name = to_country) as flag,
        (select max(0) from product where orders_id = o.id and return_status = 0 ) as return_status,
        (select COALESCE(SUM(return_cost), 0) + (3 * count(1)) from product where orders_id = o.id and return_status > 0) as return_cost,
           
        (select JSON_ARRAYAGG(id) from product where orders_id = o.id order by id) as product_ids, 
        (select JSON_ARRAYAGG(product_picture_path) from product where orders_id = o.id order by id) as product_picture_paths, 
        (select JSON_ARRAYAGG(bought_date) from product where orders_id = o.id order by id) as product_bought_dates, 
        (select JSON_ARRAYAGG(product_link) from product where orders_id = o.id order by id) as product_links,
        (select JSON_ARRAYAGG(product_description) from product where orders_id = o.id order by id) as product_descriptions,
        (select JSON_ARRAYAGG(cancel_date) from product where orders_id = o.id order by id) as product_cancel_dates,
        (select JSON_ARRAYAGG(return_label_path) from product where orders_id = o.id order by id) as return_label_paths,
        (select JSON_ARRAYAGG(CASE WHEN return_status = 0 then 'Return requested' 
                                   WHEN return_status = 1 then 'Return confirmed'
                                   WHEN return_status = 2 then 'Returned'
                                   WHEN return_status = 3 then 'Not returnable' end ) from product where orders_id = o.id order by id) as product_return_status,
        
  
        (SELECT CASE when SUM(((product_price * product_quantity) + product_shipment_cost) * 1.06625) > sum(product_buy_price)
                     then SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2))
                     else SUM(ROUND(product_buy_price,2))
                     End
         FROM product WHERE orders_id = o.id and return_status ='2')  as returned_total,

    
      
        (SELECT sum(CASE when (((product_price * product_quantity) + product_shipment_cost) * 1.06625) > product_buy_price
                     then ROUND((((product_price * product_quantity) + product_shipment_cost) * 1.06625),2)
                     else (ROUND(product_buy_price,2))
                     End)
        FROM product WHERE orders_id = o.id)  as product_buy_price_total,
             
             
    
 
        (SELECT JSON_ARRAYAGG(JSON_OBJECT(
                'product_id', p.id,
                'tracking_number', COALESCE(pt.tracking_number, NULL),  -- If no tracking number, return NULL
                'arrive_date', COALESCE(pt.arrive_date, p.arrive_date),  -- If no arrive_date, return product's arrive_date
                'location', COALESCE(pt.location, p.location)
                )) AS all_products_tracking_info
        FROM product p LEFT JOIN product_tracking_numbers pt ON pt.product_id = p.id AND pt.orders_id = o.id  
        WHERE p.orders_id = o.id and p.cancel_date is null) as product_tracking_numbers
       
     From orders o  WHERE cancel_date is null and paid_date is not null";   
        
    if ($buyer == 1){
        $sql = $sql . " and you_buy = 2 and (team_id is null or team_id = $team_id) and (label_date is null or DATE(label_date) >= DATE_SUB(CURDATE(), INTERVAL 15 DAY))";
    }          
    
    $sql = $sql . " ORDER BY id DESC ";
//echo $sql;         
echo f_select($sql,$conn); 
?> 

        



        
        
