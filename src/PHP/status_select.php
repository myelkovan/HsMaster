<?php
 
include("login/db_connect.php");
include("utils/jwt.php");



$sql = "SELECT id, status, status_short FROM kt_status "; 

f_select($sql,$conn); 
?> 
