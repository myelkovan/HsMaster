
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

//sadece depo ve agent user bu verileri gorebilir
if (f_isAgenOrWarehouseUser($token_user_id,$conn) == false){
    return;
}


if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}

$orders_id          = $conn->escape_string($_POST["orders_id"]);
$product_id         = $conn->escape_string($_POST["product_id"]);
$return_status      = $conn->escape_string($_POST["return_status"]);


$sql_update = "Update product Set return_status=$return_status Where id = $product_id";
echo f_update($sql_update, $conn, false);






        $sql_update = "update orders set   
               product_total_price = (
                    SELECT COALESCE(SUM(ROUND(((product_price * product_quantity) + product_shipment_cost) * 1.06625,2)), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null )
                ),
                
                calculated_product_total_price = (
                    SELECT COALESCE(SUM(
                        CASE
                            WHEN (((product_price * product_quantity) + product_shipment_cost) * 1.06625) > COALESCE(product_buy_price,0)
                            THEN ROUND((((product_price * product_quantity) + product_shipment_cost) * 1.06625),2)
                            ELSE COALESCE(ROUND(product_buy_price,2),0)
                        END
                    ), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null )
                ),
                
         
          
                package_weight = (
                    SELECT COALESCE(SUM(
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
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null  )
                ),
                
                package_length = (
                    SELECT COALESCE(SUM(
                        CASE 
                            WHEN product_length_units = 'cm' 
                            THEN (product_length / 2.54) * product_quantity
                            ELSE product_length * product_quantity
                        END), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null  )
                ),
                
                package_width = (
                    SELECT COALESCE(MAX(
                        CASE 
                            WHEN product_length_units = 'cm' 
                            THEN (product_width / 2.54)
                            ELSE product_width
                        END), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null )
                ),
                
                package_height = (
                    SELECT COALESCE(MAX(
                        CASE 
                            WHEN product_length_units = 'cm' 
                            THEN (product_height / 2.54)
                            ELSE product_height
                        END), 0)
                    FROM product
                    WHERE orders_id = $orders_id
                      AND cancel_date IS NULL and (return_status != 2 || return_status is null  )
                ) 
            WHERE id = $orders_id";
            
       echo f_update($sql_update, $conn);
        
        
        
        

?> 






           
           
           


