<?php
 
include("login/db_connect.php");


$sql = "SELECT id, service_name FROM kt_carrier order by service_name "; 
f_select($sql,$conn); 
?> 
