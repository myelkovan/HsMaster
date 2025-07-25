<?php

include("login/db_connect.php");
//echo "<script>alert('Buradayım!');</script>";
//echo "<script>console.log('KEY SERVİS VERSİYON 2- Buradayım!');</script>";
//exit("Bu mesaj gösterilecek ve kod burada duracak.");


$webpage     =$conn->escape_string($_GET["webpage"]);

if ($webpage ==="amazon1"){
    $sql = "Select description1,description2,description3,image1,image2,image3,price1,price2,price3,price_cents1,price_cents2,price_cents3 from extension_keys where webpage = '$webpage'";
}
else{
   $sql = "Select webpage, main_type, sub_type, value from extension_keys_v2 where webpage = '$webpage'";
}

echo f_select($sql,$conn); 

?>