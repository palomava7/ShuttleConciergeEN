<div class="resbox">
	<form id="resboxForm" action="cotizacion.php" method="get">
		<div>
			<label for="type">Tipo de traslado</label>
			<select name="type" id="type">
				<option value="round trip">Viaje Redondo</option>
				<option value="arrival">Aeropuerto - Hotel</option>
				<option value="departure">Hotel - Aeropuerto</option>
			</select>
		</div>
		<div>
			<label for="destination_name">Hotel / Lugar</label>
			<input type="text" name="destination_name" id="destination_name" value="Escribe un hotel o lugar">
			<input type="hidden" name="destination_id" id="destination_id">
		</div>
		<div class="calendarBlock">
			<label for="start" class="calendarLabel">Llegada</label>
			<input type="text" name="start" id="start" class="calendar"><span class="calendar-icon" data-target="#start"></span>
		</div>
		<div class="calendarBlock">
			<label for="end" class="calendarLabel">Regreso</label>
			<input type="text" name="end" id="end" class="calendar"><span class="calendar-icon" data-target="#end"></span>
		</div>
		<div>
			<label for="pax">Pasajeros</label>
			<select name="pax" id="pax">
				<option value="1">1</option>
				<option value="2" selected="selected">2</option>
				<option value="3">3</option>
				<option value="4">4</option>
				<option value="5">5</option>
				<option value="6">6</option>
				<option value="7">7</option>
				<option value="8">8</option>
				<option value="9">9</option>
				<option value="10">10</option>
			</select>
		</div>
		<div class="submit-container">
			<img src="img/paypal.png" alt="Paypal" class="paypal">
			<img src="img/cash-icon.png" alt="Efectivo" class="cash">
			<input type="button" id="search" value="Cotizar">
		</div>
	</form>
</div>