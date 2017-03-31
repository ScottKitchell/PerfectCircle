<?php
	include('functions.php');
	include('PHPMailer/PHPMailerAutoload.php');
	
		
	// CRON JOBS
	//  - Email notifications once every 12 hours
	
	$users = database("SELECT u.ID, u.Email, u.FirstName, u.LastName, COUNT(n.ID) AS CountN
	FROM NOTIFICATIONS AS n LEFT JOIN USERS AS u ON n.UserID = u.ID
	WHERE n.Seen = '0' AND n.Date_Time >= now() - INTERVAL 12 HOUR
	GROUP BY n.UserID");
	
	for($i=0;$i<$users['numRows'];$i++){
		//Construct and send email
		
		$html_body = "<div style='text-align:center; width:80%; font-family:Arial, sans-serif; border:1px solid #ccc; padding:30px;'><h1 style='color:#cc3434;'>Perfect Circle</h1>
		<h2 style=''>".$users['FirstName_'.$i].", you have ".$users['CountN_'.$i]." new notifications!</h2>
		<p style=''>Check them out at <a href='http://www.perfectcircle.social'>www.perfectcircle.social</a> or click on the big button below.</p>
		<div style='text-align:center; background:#cc3434;'><a href='http://www.perfectcircle.social' style='font-size:1.2em; text-decoration:none; color:#FFFFFF;'>View my notifications</a></div>
		<p style='font-size:0.8em; color:#888;'>Best regards, the Perfect Circle team</p></div>";
		
		$html_body_plain = "Perfect Circle \n\r ".$users['FirstName_'.$i].", you have ".$users['CountN_'.$i]." new notifications! \n\r Check them out at http://www.perfectcircle.social \n\r Sinserly, the Perfect Circle team";
		
		$mail = new PHPMailer;
		
		//$mail->SMTPDebug = 3;                               // Enable verbose debug output
		
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'mail.perfectcircle.social ';  // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = 'notifications@perfectcircle.social';                 // SMTP username
		$mail->Password = 'Perfect2016';                           // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 25;                                    // TCP port to connect to
		
		$mail->setFrom('notifications@perfectcircle.social', 'Perfect Circle');
		$mail->addAddress($users['Email_'.$i], $users['FirstName_'.$i].' '.$users['LastName_'.$i]);     // Add a recipient
		
		$mail->isHTML(true);                                  // Set email format to HTML
		
		$mail->Subject = 'New notifications from your Perfect Circle friends';
		$mail->Body    = $html_body;
		$mail->AltBody = $html_body_plain;
		
		if(!$mail->send()) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		} else {
			echo 'Message has been sent';
		}
		
		
	}
	
?>