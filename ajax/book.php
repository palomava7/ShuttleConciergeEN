<?PHP
	header('Content-Type: application/json');
	require '../modules/config.php';

	$price_type;
	$price;
	if ($_GET["type"]=="round trip") $price_type = "round trip";
	if ($_GET["type"]=="arrival") $price_type = "one way";
	if ($_GET["type"]=="departure") $price_type = "one way";

	$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
	if ($mysqli->connect_errno) {
    	printf("Falló la conexión: %s\n", $mysqli->connect_error);
    	exit();
	}
	$mysqli->set_charset("utf8");

	$pax = $_GET["pax"];

	if ($_GET["service"] == "shared")
		if ($resultado = $mysqli->query("
				SELECT destination_name, zone_name, service, price
				FROM destinations
				JOIN zones ON destinations.zone_id = zones.zone_id
				JOIN prices ON destinations.zone_id = prices.zone_id
				WHERE type = '$price_type' AND destination_id = {$_GET["destination_id"]} AND service='shared'
				GROUP BY service
				HAVING  MIN(max_pax)
				ORDER BY service ASC
			")) {
			$service_data = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
	if ($_GET["service"] == "private")
		if ($resultado = $mysqli->query("
				SELECT destination_name, zone_name, service, price
				FROM destinations
				JOIN zones ON destinations.zone_id = zones.zone_id
				JOIN prices ON destinations.zone_id = prices.zone_id
				WHERE type = '$price_type' AND destination_id = {$_GET["destination_id"]} AND `max_pax` >= $pax AND service='private'
				GROUP BY service
				HAVING  MIN(max_pax)
				ORDER BY service ASC
			")) {
			$service_data = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
	if ($_GET["service"] == "premium")
		if ($resultado = $mysqli->query("
				SELECT destination_name, zone_name, service, price
				FROM destinations
				JOIN zones ON destinations.zone_id = zones.zone_id
				JOIN prices ON destinations.zone_id = prices.zone_id
				WHERE type = '$price_type' AND destination_id = {$_GET["destination_id"]} AND `max_pax` >= $pax AND service='premium'
				GROUP BY service
				HAVING  MIN(max_pax)
				ORDER BY service ASC
			")) {
			$service_data = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
	
	$arrivalairline = isset($_GET["arrival_airline"]) ? $_GET["arrival_airline"] : "";
	$arrivalflight = isset($_GET["arrival_flight"]) ? $_GET["arrival_flight"] : "";
	$arrivaltime = isset($_GET["arrival_time"]) ? $_GET["arrival_time"] : "";
	
	$departureairline = isset($_GET["departure_airline"]) ? $_GET["departure_airline"] : "";
	$departureflight = isset($_GET["departure_flight"]) ? $_GET["departure_flight"] : "";
	$departuretime = isset($_GET["departure_time"]) ? $_GET["departure_time"] : "";
		

	$price = $service_data[0]['price'];
	if ($_GET["service"] == "shared") $price = $price * $pax;
	$destination_name = $service_data[0]['destination_name'];
	$zone_name = $service_data[0]['zone_name'];
	$start_date = substr($_GET['start'],6,4)."/".substr($_GET['start'],3,2)."/".substr($_GET['start'],0,2);
	$end_date = substr($_GET['end'],6,4)."/".substr($_GET['end'],3,2)."/".substr($_GET['end'],0,2);
	
	$fecha_actual = new DateTime(date("Y-m-d H:i:s"));
	$fecha_actual->modify('-1 Hour');
	$fecha_actual = $fecha_actual->format("Y-m-d H:i:s");
	
	$query_string = "
		INSERT INTO bookings(
			type,
			destination,
			zone,
			price,
			start,
			end,
			pax,
			service,
			name,
			phone,
			email,
			address,
			country,
			state,
			city,
			zip_code,
			payment,
			arrival_airline,
			arrival_flight,
			arrival_time,
			departure_airline,
			departure_flight,
			departure_time,
			fecha_reserva
		) 
		VALUES(
			'{$_GET["type"]}',
			'{$destination_name}',
			'{$zone_name}',
			'{$price}',
			'{$start_date}',
			'{$end_date}',
			'{$_GET["pax"]}',
			'{$_GET["service"]}',
			'{$_GET["full_name"]}',
			'{$_GET["phone"]}',
			'{$_GET["email"]}',
			'{$_GET["address"]}',
			'{$_GET["country"]}',
			'{$_GET["state"]}',
			'{$_GET["city"]}',
			'{$_GET["zip_code"]}',
			'{$_GET["payment"]}',
			'{$arrivalairline}',
			'{$arrivalflight}',
			'{$arrivaltime}',
			'{$departureairline}',
			'{$departureflight}',
			'{$departuretime}',
			'{$fecha_actual}'
		)
	";
	$resultado = $mysqli->query($query_string);
	$book_id = $mysqli->insert_id;

	
	if ( $_GET["payment"] == "paypal" ) {
		require_once ("../modules/paypalfunctions.php");
		$shuttle_type;
		if ($_GET["type"]=="round trip") $shuttle_type="Round Trip";
		if ($_GET["type"]=="arrival") $shuttle_type="Arrival";
		if ($_GET["type"]=="departure") $shuttle_type="Departure";
		$service_name;
		if ($_GET["service"]=="shared") $service_name="Shared";
		if ($_GET["service"]=="private") $service_name="Private";
		if ($_GET["service"]=="premium") $service_name="Premium";

		$paymentAmount = $price;
		$description = "{$shuttle_type} bound for {$destination_name}({$zone_name}) with a {$service_name} service type for {$_GET["pax"]} passengers.";

		$returnURL = "http://www.shuttleconcierge.com/confirmacion.php?id=".$book_id;

		$resArray = CallExpressCheckout ($paymentAmount, $description, $returnURL);
		$ack = strtoupper($resArray["ACK"]);
		if($ack=="SUCCESS" || $ack=="SUCCESSWITHWARNING")
		{
			$response["uri"] = getPaypalRedirectURI($resArray["TOKEN"]);
		} 
		else  
		{
			//Display a user friendly Error on the page using any of the following error information returned by PayPal
			$ErrorCode = urldecode($resArray["L_ERRORCODE0"]);
			$ErrorShortMsg = urldecode($resArray["L_SHORTMESSAGE0"]);
			$ErrorLongMsg = urldecode($resArray["L_LONGMESSAGE0"]);
			$ErrorSeverityCode = urldecode($resArray["L_SEVERITYCODE0"]);
			
			echo "SetExpressCheckout API call failed. ";
			echo "Detailed Error Message: " . $ErrorLongMsg;
			echo "Short Error Message: " . $ErrorShortMsg;
			echo "Error Code: " . $ErrorCode;
			echo "Error Severity Code: " . $ErrorSeverityCode;

			$response["uri"] = "/confirmacion.php?id=".$book_id;
		}
	}
	else {
		$response["uri"] = "/confirmacion.php?id=".$book_id;
	}
	$confirm_link = "http:/www.shuttleconcierge.com/confirmacion.php?id=".$book_id;

	$mysqli->close();

	$to      = $_GET["email"] . ';' . $mail_notify;
	$subject = 'Reservations in ShuttleConcierge';
	
	$headers = 'From: ' . $mail_from . "\r\n" .
	    'Reply-To: ' . $mail_from . "\r\n" .
	    'X-Mailer: PHP/' . phpversion();

	$headers_cliente  = "MIME-Version: 1.0\r\n";
	$headers_cliente .= "Content-type: text/html; charset=UTF-8\r\n";
	$headers_cliente .= "Date: ". date('r'). " \r\n";
	$headers_cliente .= "From:Reservations ShuttleConcierge <reservaciones@shuttleconcierge.com>\r\n";
	$headers_cliente .= "Reply-to:reservaciones@shuttleconcierge.com \r\n";
	$headers_cliente .= "Organization: ShuttleConcierge \r\n";
	$headers_cliente .= "X-Sender:reservaciones@shuttleconcierge.com \r\n";
	$headers_cliente .= "X-Priority: 1\r\n";
	$headers_cliente .= "X-MSMail-Priority: High\r\n";
	$headers_cliente .= "X-Mailer: PHP/" . phpversion();
	
	
	$body =
	'
	<style type="text/css">
		.confirm h2 { text-align: left; }
		.confirm .book fieldset>div { display: block; }
		.confirm .label { color: #8C104F; margin-right: 10px; font-weight: bold; display: inline-block; width: 200px;}
		.confirm .value { color: #333; }
		.confirm .book fieldset { display: inline-block; }
		.confirm .amount { font-size: 30px; font-weight: bold; display: inline-block; background: #8C104F; padding: 10px; border-radius: 4px; color: #fff;}
		.confirm .currency { font-size: 15px; font-weight: bold; vertical-align: super;}
	</style>
	';

	$body .=
	'<div class="confirm">
		<h2>RESERVATION CONFIRMED</h2>
			<div class="book">
				<fieldset>
					<legend>Trip Information</legend>
					<div><span class="label">Trip Type </span><span class="value">'.$_GET["type"].'</span></div>
					<div><span class="label">Destination</span><span class="value">';
					
						$airport="Cancun Airport";
						if($_GET["type"]=="arrival"){
							$body .= $airport.'	&rarr;	'.$destination_name;
						}
						if($_GET["type"]=="departure"){
							$body .= $destination_name.' &rarr;	'.$airport;
						}
						if($_GET["type"]=="round trip"){
							$body .= $airport.'	&harr;	'.$destination_name;
						}
	$body .= '
					</span></div>
					<div><span class="label">Service Type </span><span class="value">'.$_GET["service"].'</span></div>
					<div><span class="label">Passengers </span><span class="value">'.$pax.'</span></div>';
					if ($_GET["type"]=="arrival" || $_GET["type"]=="round trip") {
	$body .= '
					<div><span class="label">Arrival </span><span class="value">'.$start_date.$arrivaltime.' [Aerolínea: '.$arrivalairline.' | Vuelo: '.$arrivalflight.']</span></div>';
					}
					if ($_GET["type"]=="departure" || $_GET["type"]=="round trip") {
	$body .= '
					<div><span class="label">Departure </span><span class="value">'.$end_date.$departuretime.' [Aerolínea: '.$departureairline.' | Vuelo: '.$departureflight.']</span></div>';
					}
	$body .= '
					<div><span class="label">Type of payment</span><span class="value">'.$_GET["payment"].'</span></div>
				</fieldset>
				<br>
				<fieldset>
					<legend>Contact information</legend>
					<div><span class="label">Name </span><span class="value">'.$_GET["full_name"].'</span></div>
					<div><span class="label">Phone Number </span><span class="value">+52 '.$_GET["phone"].'</span></div>
					<div><span class="label">Email </span><span style="text-transform: lowercase;" class="value">'.$_GET["email"].'</span></div>
					<div><span class="label">Address </span><span class="value">'.$_GET["address"].'</span></div>
					<div><span class="label">Country </span><span class="value">'.$_GET["country"].'</span></div>
					<div><span class="label">State </span><span class="value">'.$_GET["state"].'</span></div>
					<div><span class="label">City </span><span class="value">'.$_GET["city"].'</span></div>
					<div><span class="label">Zip Code </span><span class="value">'.$_GET["zip_code"].'</span></div>
				</fieldset>
			</div>
			<br>
			<div class="price">
				<span class="label">Service '; if($_GET["payment"] == "paypal") $body .= "Paid with Paypal"; else $body .= "Payable"; $body .= '</span>
				<span class="amount value">$'.$price.' <span class="currency">USD</span></span>
			</div>
	</div>';

		
	$res = mail($to, $subject, $body, $headers_cliente);	

	echo json_encode($response);
?>