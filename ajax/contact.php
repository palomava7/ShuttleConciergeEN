<?PHP
	header('Content-Type: application/json');
	require '../modules/config.php';

	$to      = $mail_contact;
	$subject = "Nuevo Mensaje de Contacto";
	$message = "Nombre: {$_GET['full_name']}\r\nEmail: {$_GET['email']}\r\nTeléfono: {$_GET['phone']}\r\nMensaje: {$_GET['message']}";
	$headers = 'From: ' . $mail_from . "\r\n" .
	    'Reply-To: ' . $mail_from . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	$res = mail($to, $subject, $message, $headers);	

	echo json_encode($res);
?>