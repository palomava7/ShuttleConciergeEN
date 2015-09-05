<style type="text/css">
		.confirm h2 { text-align: left; }
		.confirm .book fieldset>div { display: block; }
		.confirm .label { color: #8C104F; margin-right: 10px; font-weight: bold; display: inline-block; width: 200px;}
		.confirm .value { color: #333; }
		.confirm .book fieldset { display: inline-block; }
		.confirm .amount { font-size: 30px; font-weight: bold; display: inline-block; background: #8C104F; padding: 10px; border-radius: 4px; color: #fff;}
		.confirm .currency { font-size: 15px; font-weight: bold; vertical-align: super;}
	</style>
	<div class="confirm">
		<h2>RESERVACIÓN CONFIRMADA</h2>
			<div class="book">
				<fieldset>
					<legend>Información del traslado</legend>
					<div><span class="label">Tipo de traslado</span><span class="value">'.$_GET["type"].'</span></div>
					<div><span class="label">Destino</span><span class="value">';
						aeropuerto <->destino
					</span></div>
					<div><span class="label">Tipo de servicio</span><span class="value">'.$_GET["service"].'</span></div>
					<div><span class="label">Pasajeros</span><span class="value">'.$pax.'</span></div>
					<div><span class="label">Llegada</span><span class="value">'.$start_date.$arrivaltime.' [Aerolínea: '.$arrivalairline.' | Vuelo: '.$arrivalflight.']</span></div>
					<div><span class="label">Salida</span><span class="value">'.$end_date.$departuretime.' [Aerolínea: '.$departureairline.' | Vuelo: '.$departureflight.']</span></div>
					<div><span class="label">Tipo de pago</span><span class="value">'.$_GET["payment"].'</span></div>
				</fieldset>
				<br>
				<fieldset>
					<legend>Información de contacto</legend>
					<div><span class="label">Nombre</span><span class="value">'.$_GET["full_name"].'</span></div>
					<div><span class="label">Teléfono</span><span class="value">+52 '.$_GET["phone"].'</span></div>
					<div><span class="label">Email</span><span class="value">'.$_GET["email"].'</span></div>
					<div><span class="label">Dirección</span><span class="value">'.$_GET["address"].'</span></div>
					<div><span class="label">País</span><span class="value">'.$_GET["country"].'</span></div>
					<div><span class="label">Estado</span><span class="value">'.$_GET["state"].'</span></div>
					<div><span class="label">Ciudad</span><span class="value">'.$_GET["city"].'</span></div>
					<div><span class="label">Código Postal</span><span class="value">'.$_GET["zip_code"].'</span></div>
				</fieldset>
			</div>
			<div class="price">
				<span class="label">Servicio Pagado</span>
				<span class="amount value">$'.$price.' <span class="currency">USD</span></span>
			</div>
	</div>';
