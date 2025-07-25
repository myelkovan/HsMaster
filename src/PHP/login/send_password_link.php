<?php


include("db_connect.php");
include("../utils/mail.php");

       
$email=$conn->escape_string($_GET["email"]);

//email gelmedi
if ($email == ''){
    echo 'NO';
    $conn -> close();
    return;
}


$sql = "Select id,name From user where email = '$email'";
if ($result = $conn -> query($sql)) {
	
	if ($row = $result -> fetch_assoc()){
        $id = $row['id'];  
        $name = $row['name'];  
        $issuedAt   = new DateTimeImmutable();
        $date = $issuedAt->modify('+20 minutes')->format("Y-m-d H:i:s");
        $token = bin2hex(random_bytes(16));
        
            
        $sql = "Update user set resettoken = '$token', resettokenexp = '$date' where id = $id";
        if (f_update($sql, $conn ) != -1){  

             if (send_mail_password_reset($email,$name, $token) == true){
                 echo '1';
                 return;
            };
        }
	}
	
    

    echo '-1';
    $conn -> close();



}    
 	
 	
	

?> 

