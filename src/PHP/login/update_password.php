<?php


include("db_connect.php");
include("../utils/jwt.php");

$email=$conn->escape_string($_GET["email"]);
$password=$conn->escape_string($_GET["password"]);
$reset_token=$conn->escape_string($_GET["reset_token"]);


//reset token gonderilmediyse kullanici login olmus ve sifre degistiriyordur, email yerine user_id gonderecek
if ($reset_token == ""){
    if (f_isTokenValid() == false){
        echo "invalidToken"; 
        $conn -> close();
        return;
    }
    
    $sql = "Update user set password = AES_ENCRYPT('$password', 'şifrıç.ş,üğişç.çölşipi*-0.iııııı') where id = '$email'"; //aciklama yukarida
    echo f_update($sql, $conn );  


}else{
//reset token varsa kullaniciya token'li link gonderilmistir


    $server_date   = new DateTimeImmutable();
    $server_date = $server_date ->format("Y-m-d H:i:s");
    
    $sql = "Select 1 From user where email = '$email' AND resettoken = '$reset_token'";// AND resettokenexp > '$server_date'";
    
    $result = $conn->query($sql);
    if ($result) {
       
        if ($result->num_rows == 1) {
             $sql = "Update user set password = AES_ENCRYPT('$password', 'şifrıç.ş,üğişç.çölşipi*-0.iııııı') where email = '$email'";
             echo f_update($sql, $conn );  
    
        }else{
            echo -1;
            $conn -> close();
        }
    }else{
        echo -1;
        $conn -> close();
    } 
} 


?> 
