<?php
    require 'PHPMailer/PHPMailerAutoload.php';
    $mail = new PHPMailer(true);
	$mail->IsSMTP();
try {
	$mail->Host = "smtp.gmail.com";
	$mail->SMTPAuth = true;
	$mail->Port = 465;
	$mail->SMTPSecure = "ssl";
	$mail->Username = "";
	$mail->Password ="";
	$mail->CharSet = 'utf-8'; 
	$mail->Encoding = "base64";

	$mail->From="";
	$mail->FromName= "";
	$mail->AddAddress("", ""); 

	$mail->Subject = "TEST 메일 제목";
	$mail->Body = "TESTESTESTSFD";
	$mail->Send();
	echo "메일을 전송하였습니다.";
} catch (phpmailerException $e) {
	echo $e->errorMessage();
} catch (Exception $e) {
	echo $e->getMessage();
}
//http://taeil83.tistory.com/1
?>
