<?php
include("db_connect.php");
include("../utils/mail.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}


$name = $conn->escape_string($_POST["name"]);
$email = $conn->escape_string($_POST["email"]);
$mobile = $conn->escape_string($_POST["mobile"]);
$password = $conn->escape_string($_POST["password"]);


$sql = "Select 1 From user where email = '$email'";
$result = $conn->query($sql);
if ($result) {
    if ($result->num_rows > 0) {
        echo "VAR";
        $conn->close();
        return;
    }
} else {
    echo "ERROR: Database query failed"; // Kullanıcı zaten var
    $conn->close();
    return;
}

$issuedAt   = new DateTimeImmutable();
$date = $issuedAt->modify('+10000 minutes')->format("Y-m-d H:i:s");
$token = bin2hex(random_bytes(16));


if ($name === "") {
    $conn->close();
    return;
}


$sql_insert = "Insert Into user(name, email, mobile, resettoken, resettokenexp, password ) 
                       Values('$name', '$email', '$mobile', '$token', '$date', AES_ENCRYPT('$password', 'şifrıç.ş,üğişç.çölşipi*-0.iııııı') )";

$id = f_update($sql_insert, $conn, false);
if ($id > 0) {

    $sql_insert = "Insert Into deposit(user_id, credit, paid_date )  Values($id, 5, '$date')";
    f_update($sql_insert, $conn, false);

    if (send_mail_activate($email, $name, $token) == true) {
        //info@jetbaskete yeni kullaniciyi haber ver
        send_mail_new_user($email, $name, $token);
        echo 'OK';
    } else {
        echo 'ME';
    };
} else {
    $conn->close();
    echo "NO";
}
