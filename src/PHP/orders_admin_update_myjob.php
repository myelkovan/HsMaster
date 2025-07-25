
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


//gelen degerleri al
if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}

$orders_id           = $conn->escape_string($_POST["orders_id"]);
$user_id             = isset($_POST["user_id"]) ? $conn->escape_string($_POST["user_id"]) : 'null';
$now                 = $conn->escape_string($_POST["now"]);




//birisi isi iptral ediyorsa team_id yi null yap 
if ($user_id == 'null'){
    $sql_update = "Update orders Set team_id = null Where id = $orders_id";
    echo f_update($sql_update, $conn);

}else{
    //birisi isi aliyorsa isin ona ver ama kimseye ait olmadigindan emin ol
    $sql_update = "Update orders Set team_id = $user_id Where id = $orders_id and team_id is null";
    $ret = f_update($sql_update, $conn, false);
    if ($ret == 0) {
         echo "0";
         $conn->close();
         return;
     }

    //biri isi aldi ise in progres kaydi yoksa olustur
    $sql = "select 1 from order_status where orders_id = $orders_id and status_id=1";
    if ($result = $conn -> query($sql)) {
        if ($result->num_rows == 0) {
            $sql_status = "Insert into order_status(orders_id, status_id, date) Values($orders_id, 1, '$now')";
            echo f_update($sql_status, $conn);
        }else{
            $conn->close();
            echo $ret;
        }
    }
}


?> 



