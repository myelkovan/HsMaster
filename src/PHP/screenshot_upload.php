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

//Bu islemi warehouse kullanicilari yapabilir. Kullanicinin yetkisi yoksa cik
if (f_isWarehouseUser($token_user_id, $conn) == false){
    echo -1;
    return;
}


if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POST)) {
   $_POST = json_decode(file_get_contents('php://input'),true); 
}


$id =$conn->escape_string($_POST["id"]);


if(isset($_POST['image'])) {
    $img = $_POST['image'];
    $file = $id . "_screenshot.png";
    $file_path = "../images/" . $file;
    
    $img = str_replace('data:image/png;base64,', '', $img);
    $img = str_replace(' ', '+', $img);
    $data = base64_decode($img);

    if (file_put_contents($file_path, $data)) {
         $sql = "Insert into order_files (orders_id, picture_path) Values( $id, '$file')";
         echo f_update($sql, $conn);
    } else {
        echo '-1';
    }
    

} else {
    echo '-1';
}
?>

 
    