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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true); 
}


//consolidate arrayinde {id:1, target_id:2, id:5, target_id:2} 1 ve 5 nolu order 2 nolu order ile birlesecek
$ret = "1";
if (isset($_POST['consolidates']) && is_array($_POST['consolidates'])) {
    $consolidates = $_POST['consolidates'];
    $target_id_main = 0;
    
    //birlestirilecek olan orderlar array olarak geldi arrayi gez id ve target id leri al
    foreach ($consolidates as $consolidate) {
        if (isset($consolidate['id'])) {
            $id = $conn->escape_string($consolidate['id']);
            $target_id =  isset($consolidate["target_id"]) ? $conn->escape_string($consolidate['target_id']) : 'null';
            $target_id_main = $target_id;
            
            // veritabaninda consolidated_to alanina target order numarasini girecegiz. 
            $sql_update = "UPDATE orders SET consolidated_to = $target_id WHERE id = $id or id = $target_id ";
            $ret = f_update($sql_update, $conn, false);
            
        } else {
            $ret = -1;
        }
    }
    
}

$conn->close();
echo $ret;
?>

