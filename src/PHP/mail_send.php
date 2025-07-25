
<?php

    include("login/db_connect.php");
    include("utils/jwt.php");
    include("utils/mail.php");
 
    //token gecerli degilse hata ver ve cik
    $ret = f_isTokenValid();
    $token_user_id = (int) $ret['user_id'];
    if ($ret['success'] == false || $token_user_id==null || $token_user_id == 0) {
        echo "invalidToken";  
        return;
    }


    //gelen degerleri al
    if($_SERVER['REQUEST_METHOD']==='POST' && empty($_POSnT)) {
       $_POST = json_decode(file_get_contents('php://input'),true); 
    }
    $subject         = $conn->escape_string($_POST["subject"]);
    $body            = $conn->escape_string($_POST["body"]);
    $user_id         = $conn->escape_string($_POST["user_id"]);
    $order_id        = $conn->escape_string($_POST["order_id"]);
    $email           = $conn->escape_string($_POST["email"]); 
    $name            = $conn->escape_string($_POST["name"]); 
    
    // admin ekranından kullanıcıya direk email göndermek için 
    if (isset($_POST["directToUser"])) {
        $directToUser = $conn->escape_string($_POST["directToUser"]);
        echo   send_mail($email, $name, $subject, $body, false);
    } else {
        echo send_mail_to_support($subject, $body, $order_id, $user_id, $email, $name);
    }
  
    //echo send_mail_to_support($subject, $body, $order_id, $user_id, $email, $name);
    $conn->close();           

?> 



