<?php
 
include("login/db_connect.php");
include("utils/jwt.php");

//token gecerli degilse hata ver ve cik
$ret = f_isTokenValid();
$token_user_id = (int) $ret['user_id'];
if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
    echo "invalidToken";  
    return;
}

//sadece depo bu verileri gorebilir
if (f_isWarehouseUser($token_user_id,$conn) == false){
    return;
}


//gelen degerleri al
$orders_id=$conn->escape_string($_GET["orders_id"]);

//ayni adrese gidecek orderlar
$sql = "Select so.id,
        so.you_buy,
        (CASE when so.consolidated_to = $orders_id then 1 else 0 end ) as secim,
        so.paid_date_additional,
        so.notes,
        so.product_total_price,  
        so.calculated_product_total_price,
        so.shipment_cost, so.custom_fee, so.calculated_shipment_cost, so.calculated_custom_fee,
        so.package_height, so.package_width,
        
        (select count(1)  from product where orders_id = so.id and return_status is not null and return_status !=2)  as not_yet_returned,
                                   
                                   
        
         (SELECT COALESCE(SUM(
                            CASE 
                                WHEN product_weight_units = 'kg' 
                                THEN product_weight * 2.20462 * product_quantity
                                WHEN product_weight_units = 'gr' 
                                THEN product_weight * 0.00220462 * product_quantity
                                WHEN product_weight_units = 'oz' 
                                THEN product_weight * 0.0625 * product_quantity
                                ELSE product_weight * product_quantity
                            END
                        ), 0)
                        FROM product
                    WHERE orders_id = so.id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and o.you_buy != 2) )) as package_weight,
                      
                      
            (SELECT COALESCE(SUM(
                        CASE 
                            WHEN product_length_units = 'cm' 
                            THEN (product_length / 2.54) * product_quantity
                            ELSE product_length * product_quantity
                        END), 0)
                    FROM product
                    WHERE orders_id = so.id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null || (return_status != 0 and o.you_buy != 2) )) as package_length ,         
          
          
        (select CASE WHEN COUNT(tracking_number) = 0 THEN 
              (select MAX(CASE WHEN arrive_date IS NULL THEN 1 ELSE 0 END) from product where orders_id = so.id and cancel_date is null)
        ELSE 
              MAX(CASE WHEN arrive_date IS NULL THEN 1 ELSE 0 END) END from product_tracking_numbers where orders_id=so.id ) as not_arrived

         
         
        From orders o, orders so
        where o.id = $orders_id and   so.user_id = o.user_id and 
        so.paid_date is not null and so.cancel_date is null and 
        (so.tracking_number is null or so.tracking_number = o.tracking_number) and
        so.to_alias = o.to_alias and so.to_address= o.to_address and so.to_city= o.to_city and so.to_state= o.to_state and so.to_zipcode= o.to_zipcode and so.to_country= o.to_country and 
          (select count(1) from product where orders_id = so.id and (return_status = 2 or return_status=1 or cancel_date is not null))  !=  (select count(*) from product where orders_id = so.id )   ";
       

echo f_select($sql,$conn); 
?> 


