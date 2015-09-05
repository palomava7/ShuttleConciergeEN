<?php
	header('Content-Type: application/json');
	require '../modules/config.php';
	
	$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
	if ($mysqli->connect_errno) {
    	printf("Falló la conexión: %s\n", $mysqli->connect_error);
    	exit();
	}
	$mysqli->set_charset("utf8");
	if ($resultado = $mysqli->query("SELECT destination_id, destination_name as value FROM destinations")) {
		$data = $resultado->fetch_all(MYSQLI_ASSOC);	
	    $resultado->close();
	}
	$mysqli->close();
	
	echo json_encode($data);
?>