<?php 
	require 'modules/config.php';
	if( !isset($_GET["id"]) ) {
		exit("Faltan parámetros...");
	}
	require_once('pdf/tcpdf.php');
	
	$pdf = new tcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('shuttleconcierge');
	$pdf->SetTitle('confirmacion');
	$pdf->SetSubject('confirmacion');

	$pdf->SetHeaderData('logo.jpg', 100, 'Confirmación de Reservación', "www.shuttleconcierge.com\nTel. 01800 890 5878\nemail: contacto@shuttleconcierge.com", array(0,64,255), array(0,64,128));
	$pdf->setFooterData(array(0,64,0), array(0,64,128));

	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
	$pdf->setFontSubsetting(true);
	$pdf->SetFont('dejavusans', '', 14, '', true);


	$pdf->AddPage();

	$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
	if ($mysqli->connect_errno) {
		printf("Falló la conexión: %s\n", $mysqli->connect_error);
		exit();
	}
	$mysqli->set_charset("utf8");
	if ($resultado = $mysqli->query("
			SELECT *
			FROM bookings
			WHERE book_id = ".$_GET["id"].""
		)) {
		$data = $resultado->fetch_all(MYSQLI_ASSOC);
		$resultado->close();
	} 
	
	$airport="Aeropuerto Cancún";
	
	$html = '
	<style>
		body { font-family: "Open Sans", sans-serif; background: rgb(234, 244, 243); }
	
		.confirm h2 { text-align: center; }
		.confirm .book fieldset>div { display: block; }
		.confirm .label { color: #8C104F; margin-right: 10px; font-weight: bold; display: inline-block; width: 200px;}
		.confirm .value { color: #333; }
		.confirm .book fieldset { display: inline-block; }
		.confirm .amount { font-size: 30px; font-weight: bold; display: inline-block; background: #8C104F; padding: 10px; border-radius: 4px; color: #fff;}
		.confirm .currency { font-size: 15px; font-weight: bold; vertical-align: super;}
	</style>
	
	<div class="confirm">
		<h2>RESERVACIÓN CONFIRMADA</h2>
		';
		
		foreach ($data as $info){
			$html .= '
			<div class="book">
				<fieldset>
					<legend></legend>
					<div><span class="label">Tipo de traslado </span><span class="value">'.$info["type"].'</span></div>
					<div><span class="label">Destino </span><span class="value">';
					
						if($info["type"]=="arrival"){
							$html .= $airport.' -- &gt; '.$info["destination"];
						}
						if($info["type"]=="leaving"){
							$html .= $info["destination"].' -- &gt; '.$airport;
						}
						if($info["type"]=="round trip"){
							$html .= $airport.' &lt; -- &gt; '.$info["destination"];
						}
						
					$html .= '
					</span></div>
					<div><span class="label">Tipo de servicio </span><span class="value">'.$info["service"].'</span></div>
					<div><span class="label">Pasajeros </span><span class="value">'.$info["pax"].'</span></div>
					<div><span class="label">Llegada </span><span class="value">'.$info["start"].$info["arrival_time"].'[Aerolínea: '.$info["arrival_airline"].' | Vuelo: '.$info["arrival_flight"].']</span></div>
					<div><span class="label">Salida </span><span class="value">'.$info["end"].$info["departure_time"].'[Aerolínea: '.$info["departure_airline"].' | Vuelo: '.$info["departure_flight"].']</span></div>
					<div><span class="label">Tipo de pago </span><span class="value">'.$info["payment"].'</span></div>
				</fieldset>
				<fieldset>
					<legend></legend>
					<div><span class="label">Nombre </span><span class="value">'.$info["name"].'</span></div>
					<div><span class="label">Teléfono </span><span class="value">+52 '.$info["phone"].'</span></div>
					<div><span class="label">Email </span><span class="value">'.$info["email"].'</span></div>
					<div><span class="label">Dirección </span><span class="value">'.$info["address"].'</span></div>
					<div><span class="label">País </span><span class="value">'.$info["country"].'</span></div>
					<div><span class="label">Estado </span><span class="value">'.$info["state"].'</span></div>
					<div><span class="label">Ciudad </span><span class="value">'.$info["city"].'</span></div>
					<div><span class="label">Código Postal </span><span class="value">'.$info["zip_code"].'</span></div>
				</fieldset>
			</div>
			<div class="price">
				<span class="label">Servicio '; 
				if($info["payment"] == "paypal") $html .= 'Pagado por Paypal' ;
				else $html .= 'Por pagar' ;
				$html .= '</span>
				<span class="amount value">$'.$info["price"].' <span class="currency">USD</span></span>
			</div>';
						
		}
		
		$html .= '
	</div>';
	
	$pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
	$pdf->Output('confirmacion.pdf', 'I');
?>

	