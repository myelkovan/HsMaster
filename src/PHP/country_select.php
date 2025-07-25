<?php
 
include("login/db_connect.php");


$sql = "SELECT id, name, flag FROM kt_country order by name "; 
f_select($sql,$conn); 
?> 
