<?php

require_once('phpmailer/PHPMailerAutoload.php');

$toemails = array();

$toemails[] = array(
				'email' => 'pneusapetitsprix@gmail.com', // Your Email Address
				'name' => 'Alexandre Lake' // Your Name
			);

// Form Processing Messages
$message_success = 'Nous avons <strong>bien</strong> reçu votre message et vous reviendrons dès que possible.';

// Add this only if you use reCaptcha with your Contact Forms
$recaptcha_secret = ''; // Your reCaptcha Secret

$mail = new PHPMailer();

// If you intend you use SMTP, add your SMTP Code after this Line


if( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
	if( $_POST['template-contactform-email'] != '' ) {

		$name = isset( $_POST['template-contactform-name'] ) ? $_POST['template-contactform-name'] : '';
		$email = isset( $_POST['template-contactform-email'] ) ? $_POST['template-contactform-email'] : '';
		$phone = isset( $_POST['template-contactform-phone'] ) ? $_POST['template-contactform-phone'] : '';
		$subject = isset( $_POST['template-contactform-subject'] ) ? $_POST['template-contactform-subject'] : '';
		$message = isset( $_POST['template-contactform-message'] ) ? $_POST['template-contactform-message'] : '';

		$subject = isset($subject) ? $subject : 'New Message From Contact Form';

		$botcheck = $_POST['template-contactform-botcheck'];

		if( $botcheck == '' ) {

			$mail->SetFrom( $email , $name );
			$mail->AddReplyTo( $email , $name );
			foreach( $toemails as $toemail ) {
				$mail->AddAddress( $toemail['email'] , $toemail['name'] );
			}
			$mail->Subject = $subject;

			$name = isset($name) ? "Nom: $name<br><br>" : '';
			$email = isset($email) ? "Courriel: $email<br><br>" : '';
			$phone = isset($phone) ? "Téléphone: $phone<br><br>" : '';
			$message = isset($message) ? "Message: $message<br><br>" : '';

			$referrer = $_SERVER['HTTP_REFERER'] ? '<br><br><br>This Form was submitted from: ' . $_SERVER['HTTP_REFERER'] : '';

			$body = "$name $email $phone $message $referrer";

			// Runs only when File Field is present in the Contact Form
			if ( isset( $_FILES['template-contactform-file'] ) && $_FILES['template-contactform-file']['Erreur'] == UPLOAD_ERR_OK ) {
				$mail->IsHTML(true);
				$mail->AddAttachment( $_FILES['template-contactform-file']['tmp_name'], $_FILES['template-contactform-file']['name'] );
			}

			// Runs only when reCaptcha is present in the Contact Form
			if( isset( $_POST['g-recaptcha-response'] ) ) {
				$recaptcha_response = $_POST['g-recaptcha-response'];
				$response = file_get_contents( "https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret . "&response=" . $recaptcha_response );

				$g_response = json_decode( $response );

				if ( $g_response->success !== true ) {
					echo '{ "alert": "Erreur", "message": "Captcha non validé! Réessayez plus tard." }';
					die;
				}
			}

			$mail->MsgHTML( $body );
			$sendEmail = $mail->Send();

			if( $sendEmail == true ):
				echo '{ "alert": "Succès", "message": "' . $message_success . '" }';
			else:
				echo '{ "alert": "Erreur", "message": "Le courriel <strong>n’a pas pu</strong> être envoyé pour une raison inconnue. Veuillez ressayer plus tard. <br /><br /><strong>Raison:</strong><br />' . $mail->ErrorInfo . '" }';
			endif;
		} else {
			echo '{ "alert": "Erreur", "message": "Bot <strong>Detected</strong>.! Clean yourself Botster.!" }';
		}
	} else {
		echo '{ "alert": "Erreur", "message": "Veuillez vous assurer d’avoir <strong>correctement rempli tous</strong> les champs et réessayez." }';
	}
} else {
	echo '{ "alert": "Erreur", "message": "Une <strong>erreur inconnue</strong> est survenue, veuillez réessayer plus tard." }';
}

?>