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
		<h2>RESERVATION CONFIRMED</h2>
			<div class="book">
				<fieldset>
					<legend>Transfer information</legend>
					<div><span class="label">Type of transfer</span><span class="value">'.$_GET["type"].'</span></div>
					<div><span class="label">Destination</span><span class="value">';
						airport <->destination
					</span></div>
					<div><span class="label">Type of service</span><span class="value">'.$_GET["service"].'</span></div>
					<div><span class="label">Passengers</span><span class="value">'.$pax.'</span></div>
					<div><span class="label">Arrival</span><span class="value">'.$start_date.$arrivaltime.' [Aerolínea: '.$arrivalairline.' | Vuelo: '.$arrivalflight.']</span></div>
					<div><span class="label">Departure</span><span class="value">'.$end_date.$departuretime.' [Aerolínea: '.$departureairline.' | Vuelo: '.$departureflight.']</span></div>
					<div><span class="label">Payment Type</span><span class="value">'.$_GET["payment"].'</span></div>
				</fieldset>
				<br>
				<fieldset>
					<legend>Contact information</legend>
					<div><span class="label">Name</span><span class="value">'.$_GET["full_name"].'</span></div>
					<div><span class="label">Phone</span><span class="value">+52 '.$_GET["phone"].'</span></div>
					<div><span class="label">Email</span><span class="value">'.$_GET["email"].'</span></div>
					<div><span class="label">Address</span><span class="value">'.$_GET["address"].'</span></div>
					<div><span class="label">Country</span><span class="value">'.$_GET["country"].'</span></div>
					<div><span class="label">State</span><span class="value">'.$_GET["state"].'</span></div>
					<div><span class="label">City</span><span class="value">'.$_GET["city"].'</span></div>
					<div><span class="label">Zip Code</span><span class="value">'.$_GET["zip_code"].'</span></div>
				</fieldset>
			</div>
			<div class="price">
				<span class="label">Service Payed</span>
				<span class="amount value">$'.$price.' <span class="currency">USD</span></span>
			</div>
	</div>';
