<?php
require 'res/mail/PHPMailerAutoload.php';
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->Port = 587;
$mail->SMTPSecure = 'tls';
$mail->SMTPAuth = true;
$mail->Username = "";
$mail->Password = "";
$mail->isHTML(true);
$mail->setFrom("","Support - Munish Inc.");
/*
    $to = $_POST['to'];
    $body = $_POST['body'];
    $from = $_POST['from'];
    $subject = $_POST['subject'];
    $mail->addAddress($to,"");
    $mail->setFrom("mk9569192204@gamil.com",$from);
    $mail->Subject = $subject;
    $mail->Body = $body;
    if(!$mail->send()){
        echo "Error!";
    }else{
        echo "Sent!";
    }
*/
?>
