<?php
include("db_connect.php");
include("../utils/mail.php");

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods:  POST');
header("Access-Control-Allow-Headers: Content-Type");


$email = "muratyelkovan@yahoo.com";
$name = 'Murat Yelkovan';
$title = 'Activate your JetBasket Account';
$body  = '<p>To activate your account please click the link below!<br />
          <p><a href="https://jetbasket.com/activate/muratyelkovan@yahoo.com/token">Activate Account</a></p>';
    

//direk mail adresine at
if (send_mail($email, $name, $title, $body) == true){
     echo 'OK';
}else{
    echo 'ME';
};

  
//order sahibine mail at  
if (send_mail_by_orders_id($conn, 8, $title, $body) == true){
     echo 'OK';
}else{
    echo 'ME';
};
                   

?>





