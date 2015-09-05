<?php 
	require 'modules/config.php';
	if( !isset($_GET["type"],$_GET["destination_id"],$_GET["destination_name"],$_GET["start"],$_GET["end"],$_GET["pax"]) ) {
		exit("Faltan parámetros...");
	}
?>
<?php require 'modules/header.php'; ?>
	<?php require 'modules/resbox.php'; ?>
	<?php require 'modules/banners.php'; ?>
	<?php 
		$type_text = "";
		$price_type;
		$type = "";
		
		if ($_GET["type"]=="round trip") {
			$type_text="Traslado Redondo";
			$price_type = "round trip";
			$type = "round-trip";
		}
		if ($_GET["type"]=="arrival") {
			$type_text="Aeropuerto - Hotel";
			$price_type = "one way";
			$type = "arrival";
		}
		if ($_GET["type"]=="departure") {
			$type_text="Hotel - Aeropuerto";
			$price_type = "one way";
			$type = "departure";
		}

		$destination_id = intval($_GET["destination_id"]);
		$pax = intval($_GET["pax"]);
		$prices = null;
		$shared_includes = null;
		$private_includes = null;
		$premium_includes = null;

		$mysqli = new mysqli($db_host, $db_user, $db_password, $db_name);
		if ($mysqli->connect_errno) {
	    	printf("Falló la conexión: %s\n", $mysqli->connect_error);
	    	exit();
		}
		$mysqli->set_charset("utf8");
		if ($resultado = $mysqli->query("
				SELECT destination_name, zone_name, service, price
				FROM destinations
				JOIN zones ON destinations.zone_id = zones.zone_id
				JOIN prices ON destinations.zone_id = prices.zone_id
				WHERE type = '$price_type' AND destination_id = $destination_id AND service='shared'
				GROUP BY service
				HAVING  MIN(max_pax)
				ORDER BY service ASC
			")) {
			$prices_shared = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
		if ($resultado = $mysqli->query("
				SELECT destination_name, zone_name, service, price
				FROM destinations
				JOIN zones ON destinations.zone_id = zones.zone_id
				JOIN prices ON destinations.zone_id = prices.zone_id
				WHERE type = '$price_type' AND destination_id = $destination_id AND `max_pax` >= $pax AND service='private'
				GROUP BY service
				HAVING  MIN(max_pax)
				ORDER BY service ASC
			")) {
			$prices_private = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
		if ($resultado = $mysqli->query("
				SELECT destination_name, zone_name, service, price
				FROM destinations
				JOIN zones ON destinations.zone_id = zones.zone_id
				JOIN prices ON destinations.zone_id = prices.zone_id
				WHERE type = '$price_type' AND destination_id = $destination_id AND `max_pax` >= $pax AND service='premium'
				GROUP BY service
				HAVING  MIN(max_pax)
				ORDER BY service ASC
			")) {
			$prices_premium = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
		if ($resultado = $mysqli->query("
				SELECT text, visible
				FROM includes
				WHERE service = 'shared'
			")) {
			$shared_includes = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
		if ($resultado = $mysqli->query("
				SELECT text,visible
				FROM includes
				WHERE service = 'private'
			")) {
			$private_includes = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}
		if ($resultado = $mysqli->query("
				SELECT text,visible
				FROM includes
				WHERE service = 'premium'
			")) {
			$premium_includes = $resultado->fetch_all(MYSQLI_ASSOC);
		    $resultado->close();
		}				
		$mysqli->close();

		$shared_price = $prices_shared[0]['price'];
		$private_price = $prices_private[0]['price'];;
		if($prices_premium)
			$premium_price = $prices_premium[0]['price'];;
		$destination_name = $prices[0]['destination_name'];
		$zone_name = $prices_private[0]['zone_name'];
	?>	

	<h2>COTIZACIÓN DE TRASLADO</h2>
	<form action="ajax/book.php" class="book" id="bookForm">
		<input type="hidden" name="type" id="book_type">
		<input type="hidden" name="destination_id" id="book_destination_id">
		<input type="hidden" name="start" id="book_start">
		<input type="hidden" name="end" id="book_end">
		<input type="hidden" name="pax" id="book_pax">
		<fieldset>
			<legend>Elige el tipo de servicio</legend>
			
			<div class="option">
				<div class="service">
					<img src="img/servicio-colectivo.png" alt="Servicio Colectivo">
				</div>
				<!--div class="details">
					<h4>Este servcio incluye</h4>
					<ul>
						<?php foreach ($shared_includes as $element) { ?>
							<?php if($element['visible']=="1") {?>
								<li><?php echo $element['text']; ?></li>
							<?php } ?>
						<?php } ?>
					</ul>
					<a href="#shared_includes" class="includesLink">Ver todos</a>
				</div-->
				<div class="quote">
					<div class="type"><?php echo $type_text; ?></div>
					<div class="price">$<?php echo $shared_price*$pax; ?><span class="currency">USD</span></div>
					<div class="pax"><?php echo $pax; ?> <?php echo $pax>1?"pasajeros":"pasajero"; ?></div>
					<div class="details"><a href="#shared_includes" class="includesLink">Incluye...</a></div>
				</div>
				
				<div class="radiobutton">
					<input type="radio" name="service" value="shared">
					<div>Seleccionar</div>
				</div>
			</div>
			<div id="shared_includes" class="hidden_includes">
				<ul>
					<?php foreach ($shared_includes as $element) { ?>
						<li><?php echo $element['text']; ?></li>
					<?php } ?>
				</ul>
			</div>
			<div class="option active">
				<div class="service">
					<img src="img/servicio-privado.png" alt="Servicio Privado">
				</div>
				<!--div class="details">
					<h4>Este servcio incluye</h4>
					<ul>
						<?php foreach ($private_includes as $element) { ?>
							<?php if($element['visible']=="1") {?>
								<li><?php echo $element['text']; ?></li>
							<?php } ?>
						<?php } ?>
					</ul>
					<a href="#private_includes" class="includesLink">Ver todos</a>
				</div-->
				<div class="quote">
					<div class="type"><?php echo $type_text; ?></div>
					<div class="price">$<?php echo $private_price; ?><span class="currency">USD</span></div>
					<div class="pax"><?php echo $pax; ?> <?php echo $pax>1?"pasajeros":"pasajero"; ?></div>
					<div class="details"><a href="#private_includes" class="includesLink">Incluye...</a></div>
				</div>
				<div class="radiobutton">
					<input type="radio" name="service" value="private" checked="checked">
					<div>Seleccionar</div>
				</div>
			</div>
			<div id="private_includes" class="hidden_includes">
				<ul>
					<?php foreach ($private_includes as $element) { ?>
						<li><?php echo $element['text']; ?></li>
					<?php } ?>
				</ul>
			</div>
			<?php if($pax <= 6){?>
			<div class="option">
				<div class="service">
					<img src="img/servicio-premium.png" alt="Servicio Premium">
				</div>
				<!--div class="details">
					<h4>Este servcio incluye</h4>
					<ul>
						<?php foreach ($premium_includes as $element) { ?>
							<?php if($element['visible']=="1") {?>
								<li><?php echo $element['text']; ?></li>
							<?php } ?>
						<?php } ?>
					</ul>
					<a href="#premium_includes" class="includesLink">Ver todos</a>
				</div-->
				<div class="quote">
					<div class="type"><?php echo $type_text; ?></div>
					<div class="price">$<?php echo $premium_price; ?><span class="currency">USD</span></div>
					<div class="pax"><?php echo $pax; ?> <?php echo $pax>1?"pasajeros":"pasajero"; ?></div>
					<div class="details"><a href="#premium_includes" class="includesLink">Incluye...</a></div>
				</div>
				<div class="radiobutton">
					<input type="radio" name="service" value="premium">
					<div>Seleccionar</div>
				</div>
			</div>
			<div id="premium_includes" class="hidden_includes">
				<ul>
					<?php foreach ($premium_includes as $element) { ?>
						<li><?php echo $element['text']; ?></li>
					<?php } ?>
				</ul>
			</div>
			<?php }?>
						
		</fieldset>

		<fieldset>
			<legend>Ingresa tu información de contacto</legend>
			<div>
				<label for="full_name">Nombre Completo</label>
				<input type="text" name="full_name" id="full_name">
			</div>
			<div>
				<label for="phone">Teléfono</label>
				<input type="text" name="phone" id="phone">
			</div>
			<div>
				<label for="email">Correo Electrónico</label>
				<input type="text" name="email" id="email">
			</div>
			<div>
				<label for="address">Dirección</label>
				<textarea name="address" id="address"></textarea>
			</div>
			<div>
				<label for="country">País</label>
				<select name="country" id="country">
					<option value="AF">Afganistán</option><option value="AL">Albania</option><option value="DE">Alemania</option><option value="AD">Andorra</option><option value="AO">Angola</option><option value="AI">Anguilla</option><option value="AQ">Antártida</option><option value="AG">Antigua y Barbuda</option><option value="AN">Antillas Holandesas</option><option value="SA">Arabia Saudí</option><option value="DZ">Argelia</option><option value="AR">Argentina</option><option value="AM">Armenia</option><option value="AW">Aruba</option><option value="AU">Australia</option><option value="AT">Austria</option><option value="AZ">Azerbaiyán</option><option value="BS">Bahamas</option><option value="BH">Bahrein</option><option value="BD">Bangladesh</option><option value="BB">Barbados</option><option value="BE">Bélgica</option><option value="BZ">Belice</option><option value="BJ">Benin</option><option value="BM">Bermudas</option><option value="BY">Bielorrusia</option><option value="MM">Birmania</option><option value="BO">Bolivia</option><option value="BA">Bosnia y Herzegovina</option><option value="BW">Botswana</option><option value="BR">Brasil</option><option value="BN">Brunei</option><option value="BG">Bulgaria</option><option value="BF">Burkina Faso</option><option value="BI">Burundi</option><option value="BT">Bután</option><option value="CV">Cabo Verde</option><option value="KH">Camboya</option><option value="CM">Camerún</option><option value="CA">Canadá</option><option value="TD">Chad</option><option value="CL">Chile</option><option value="CN">China</option><option value="CY">Chipre</option><option value="VA">Ciudad del Vaticano(Santa Sede)</option><option value="CO">Colombia</option><option value="KM">Comores</option><option value="CG">Congo</option><option value="CD">Congo,República Democrática del</option><option value="KR">Corea</option><option value="KP">Corea del Norte</option><option value="CI">Costa de Marfíl</option><option value="CR">Costa Rica</option><option value="HR">Croacia(Hrvatska)</option><option value="CU">Cuba</option><option value="DK">Dinamarca</option><option value="DJ">Djibouti</option><option value="DM">Dominica</option><option value="EC">Ecuador</option><option value="EG">Egipto</option><option value="SV">El Salvador</option><option value="AE">EmiratosÁrabes Unidos</option><option value="ER">Eritrea</option><option value="SI">Eslovenia</option><option value="ES"selected>España</option><option value="US">Estados Unidos</option><option value="EE">Estonia</option><option value="ET">Etiopía</option><option value="FJ">Fiji</option><option value="PH">Filipinas</option><option value="FI">Finlandia</option><option value="FR">Francia</option><option value="GA">Gabón</option><option value="GM">Gambia</option><option value="GE">Georgia</option><option value="GH">Ghana</option><option value="GI">Gibraltar</option><option value="GD">Granada</option><option value="GR">Grecia</option><option value="GL">Groenlandia</option><option value="GP">Guadalupe</option><option value="GU">Guam</option><option value="GT">Guatemala</option><option value="GY">Guayana</option><option value="GF">Guayana Francesa</option><option value="GN">Guinea</option><option value="GQ">Guinea Ecuatorial</option><option value="GW">Guinea-Bissau</option><option value="HT">Haití</option><option value="HN">Honduras</option><option value="HU">Hungría</option><option value="IN">India</option><option value="ID">Indonesia</option><option value="IQ">Irak</option><option value="IR">Irán</option><option value="IE">Irlanda</option><option value="BV">Isla Bouvet</option><option value="CX">Isla de Christmas</option><option value="IS">Islandia</option><option value="KY">Islas Caimán</option><option value="CK">Islas Cook</option><option value="CC">Islas de Cocos o Keeling</option><option value="FO">Islas Faroe</option><option value="HM">Islas Heard y McDonald</option><option value="FK">Islas Malvinas</option><option value="MP">Islas Marianas del Norte</option><option value="MH">Islas Marshall</option><option value="UM">Islas menores de Estados Unidos</option><option value="PW">Islas Palau</option><option value="SB">Islas Salomón</option><option value="SJ">Islas Svalbard y Jan Mayen</option><option value="TK">Islas Tokelau</option><option value="TC">Islas Turks y Caicos</option><option value="VI">Islas Vírgenes(EEUU)</option><option value="VG">Islas Vírgenes(Reino Unido)</option><option value="WF">Islas Wallis y Futuna</option><option value="IL">Israel</option><option value="IT">Italia</option><option value="JM">Jamaica</option><option value="JP">Japón</option><option value="JO">Jordania</option><option value="KZ">Kazajistán</option><option value="KE">Kenia</option><option value="KG">Kirguizistán</option><option value="KI">Kiribati</option><option value="KW">Kuwait</option><option value="LA">Laos</option><option value="LS">Lesotho</option><option value="LV">Letonia</option><option value="LB">Líbano</option><option value="LR">Liberia</option><option value="LY">Libia</option><option value="LI">Liechtenstein</option><option value="LT">Lituania</option><option value="LU">Luxemburgo</option><option value="MK">Macedonia,Ex-República Yugoslava de</option><option value="MG">Madagascar</option><option value="MY">Malasia</option><option value="MW">Malawi</option><option value="MV">Maldivas</option><option value="ML">Malí</option><option value="MT">Malta</option><option value="MA">Marruecos</option><option value="MQ">Martinica</option><option value="MU">Mauricio</option><option value="MR">Mauritania</option><option value="YT">Mayotte</option><option value="MX" selected="selected">México</option><option value="FM">Micronesia</option><option value="MD">Moldavia</option><option value="MC">Mónaco</option><option value="MN">Mongolia</option><option value="MS">Montserrat</option><option value="MZ">Mozambique</option><option value="NA">Namibia</option><option value="NR">Nauru</option><option value="NP">Nepal</option><option value="NI">Nicaragua</option><option value="NE">Níger</option><option value="NG">Nigeria</option><option value="NU">Niue</option><option value="NF">Norfolk</option><option value="NO">Noruega</option><option value="NC">Nueva Caledonia</option><option value="NZ">Nueva Zelanda</option><option value="OM">Omán</option><option value="NL">Países Bajos</option><option value="PA">Panamá</option><option value="PG">Papúa Nueva Guinea</option><option value="PK">Paquistán</option><option value="PY">Paraguay</option><option value="PE">Perú</option><option value="PN">Pitcairn</option><option value="PF">Polinesia Francesa</option><option value="PL">Polonia</option><option value="PT">Portugal</option><option value="PR">Puerto Rico</option><option value="QA">Qatar</option><option value="UK">Reino Unido</option><option value="CF">República Centroafricana</option><option value="CZ">República Checa</option><option value="ZA">República de Sudáfrica</option><option value="DO">República Dominicana</option><option value="SK">República Eslovaca</option><option value="RE">Reunión</option><option value="RW">Ruanda</option><option value="RO">Rumania</option><option value="RU">Rusia</option><option value="EH">Sahara Occidental</option><option value="KN">Saint Kitts y Nevis</option><option value="WS">Samoa</option><option value="AS">Samoa Americana</option><option value="SM">San Marino</option><option value="VC">San Vicente y Granadinas</option><option value="SH">Santa Helena</option><option value="LC">Santa Lucía</option><option value="ST">Santo Toméy Príncipe</option><option value="SN">Senegal</option><option value="SC">Seychelles</option><option value="SL">Sierra Leona</option><option value="SG">Singapur</option><option value="SY">Siria</option><option value="SO">Somalia</option><option value="LK">Sri Lanka</option><option value="PM">St Pierre y Miquelon</option><option value="SZ">Suazilandia</option><option value="SD">Sudán</option><option value="SE">Suecia</option><option value="CH">Suiza</option><option value="SR">Surinam</option><option value="TH">Tailandia</option><option value="TW">Taiwán</option><option value="TZ">Tanzania</option><option value="TJ">Tayikistán</option><option value="TF">Territorios franceses del Sur</option><option value="TP">Timor Oriental</option><option value="TG">Togo</option><option value="TO">Tonga</option><option value="TT">Trinidad y Tobago</option><option value="TN">Túnez</option><option value="TM">Turkmenistán</option><option value="TR">Turquía</option><option value="TV">Tuvalu</option><option value="UA">Ucrania</option><option value="UG">Uganda</option><option value="UY">Uruguay</option><option value="UZ">Uzbekistán</option><option value="VU">Vanuatu</option><option value="VE">Venezuela</option><option value="VN">Vietnam</option><option value="YE">Yemen</option><option value="YU">Yugoslavia</option><option value="ZM">Zambia</option><option value="ZW">Zimbabue</option>
				</select>
			</div>
			<div>
				<label for="state">Estado</label>
				<input type="text" name="state" id="state">
			</div>
			<div>
				<label for="city">Ciudad</label>
				<input type="text" name="city" id="city">
			</div>
			<div>
				<label for="zip_code">Código Postal</label>
				<input type="text" name="zip_code" id="zip_code">
			</div>
		</fieldset>
		<fieldset>
			<legend>Elige un método de pago</legend>
			<div>
				<label>Método de pago</label>
				<input type="radio" name="payment" id="paypal" value="paypal"><label for="paypal" class="radioLabel">Paypal</label>
				<input type="radio" name="payment" id="cash" value="cash"><label for="cash" class="radioLabel">Efectivo</label>
			</div>
		</fieldset>
		<?php if ($type == "arrival" || $type == "round-trip") { ?>
		<fieldset class="flightFieldset">
			<legend>Ingresa la información de tu vuelo (llegada)</legend>
			<div>
				<label for="arrival_airline">Aerolínea</label>
				<input type="text" name="arrival_airline">
			</div>
			<div>
				<label for="arrival_flight_number">Número de vuelo</label>
				<input type="text" name="arrival_flight" id="arrival_flight">
			</div>
			<div>
				<label for="arrival_time">Hora de llegada</label>
				<select name="arrival_time" id="arrival_time">
					<option value="12:00am">12:00am</option><option value="12:05am">12:05am</option><option value="12:10am">12:10am</option><option value="12:15am">12:15am</option><option value="12:20am">12:20am</option><option value="12:25am">12:25am</option><option value="12:30am">12:30am</option><option value="12:35am">12:35am</option><option value="12:40am">12:40am</option><option value="12:45am">12:45am</option><option value="12:50am">12:50am</option><option value="12:55am">12:55am</option><option value="01:00am">01:00am</option><option value="01:05am">01:05am</option><option value="01:10am">01:10am</option><option value="01:15am">01:15am</option><option value="01:20am">01:20am</option><option value="01:25am">01:25am</option><option value="01:30am">01:30am</option><option value="01:35am">01:35am</option><option value="01:40am">01:40am</option><option value="01:45am">01:45am</option><option value="01:00am">01:00am</option><option value="01:50am">01:50am</option><option value="01:55am">01:55am</option><option value="02:00am">02:00am</option><option value="02:05am">02:05am</option><option value="02:10am">02:10am</option><option value="02:15am">02:15am</option><option value="02:20am">02:20am</option><option value="02:25am">02:25am</option><option value="02:30am">02:30am</option><option value="02:35am">02:35am</option><option value="02:40am">02:40am</option><option value="02:45am">02:45am</option><option value="02:50am">02:50am</option><option value="02:55am">02:55am</option><option value="03:00am">03:00am</option><option value="03:05am">03:05am</option><option value="03:10am">03:10am</option><option value="03:15am">03:15am</option><option value="03:20am">03:20am</option><option value="03:25am">03:25am</option><option value="03:30am">03:30am</option><option value="03:35am">03:35am</option><option value="03:40am">03:40am</option><option value="03:45am">03:45am</option><option value="03:50am">03:50am</option><option value="03:55am">03:55am</option><option value="04:00am">04:00am</option><option value="04:05am">04:05am</option><option value="04:10am">04:10am</option><option value="04:15am">04:15am</option><option value="04:20am">04:20am</option><option value="04:25am">04:25am</option><option value="04:30am">04:30am</option><option value="04:35am">04:35am</option><option value="04:40am">04:40am</option><option value="04:45am">04:45am</option><option value="04:50am">04:50am</option><option value="04:55m">04:55m</option><option value="05:00am">05:00am</option><option value="05:05am">05:05am</option><option value="05:10am">05:10am</option><option value="05:15am">05:15am</option><option value="05:20am">05:20am</option><option value="05:25am">05:25am</option><option value="05:30am">05:30am</option><option value="05:35am">05:35am</option><option value="05:40am">05:40am</option><option value="05:45am">05:45am</option><option value="05:50am">05:50am</option><option value="05:55am">05:55am</option><option value="06:00am">06:00am</option><option value="06:05am">06:05am</option><option value="06:10am">06:10am</option><option value="06:15am">06:15am</option><option value="06:20am">06:20am</option><option value="06:25am">06:25am</option><option value="06:30am">06:30am</option><option value="06:35am">06:35am</option><option value="06:40am">06:40am</option><option value="06:45am">06:45am</option><option value="06:50am">06:50am</option><option value="06:55am">06:55am</option><option value="07:00am">07:00am</option><option value="07:05am">07:05am</option><option value="07:10am">07:10am</option><option value="07:15am">07:15am</option><option value="07:20am">07:20am</option><option value="07:25am">07:25am</option><option value="07:30am">07:30am</option><option value="07:35am">07:35am</option><option value="07:40am">07:40am</option><option value="07:45am">07:45am</option><option value="07:50am">07:50am</option><option value="07:55am">07:55am</option><option value="08:00am">08:00am</option><option value="08:05am">08:05am</option><option value="08:10am">08:10am</option><option value="08:15am">08:15am</option><option value="08:20am">08:20am</option><option value="08:25am">08:25am</option><option value="08:30am">08:30am</option><option value="08:35am">08:35am</option><option value="08:40am">08:40am</option><option value="08:45am">08:45am</option><option value="08:50am">08:50am</option><option value="08:55am">08:55am</option><option value="09:00am">09:00am</option><option value="09:05am">09:05am</option><option value="09:10am">09:10am</option><option value="09:15am">09:15am</option><option value="08:50am">08:50am</option><option value="08:55am">08:55am</option><option value="09:00am">09:00am</option><option value="09:05am">09:05am</option><option value="09:10am">09:10am</option><option value="09:15am">09:15am</option><option value="09:20am">09:20am</option><option value="09:25am">09:25am</option><option value="09:30am">09:30am</option><option value="09:35am">09:35am</option><option value="09:40am">09:40am</option><option value="09:45am">09:45am</option><option value="09:50am">09:50am</option><option value="09:55am">09:55am</option><option value="10:00am">10:00am</option><option value="10:05am">10:05am</option><option value="10:10am">10:10am</option><option value="10:15am">10:15am</option><option value="10:20am">10:20am</option><option value="10:25am">10:25am</option><option value="10:30am">10:30am</option><option value="10:35am">10:35am</option><option value="10:40am">10:40am</option><option value="10:45am">10:45am</option><option value="10:50am">10:50am</option><option value="10:55am">10:55am</option><option value="11:00am">11:00am</option><option value="11:05am">11:05am</option><option value="11:10am">11:10am</option><option value="11:15am">11:15am</option><option value="11:20am">11:20am</option><option value="11:25am">11:25am</option><option value="11:30am">11:30am</option><option value="11:35am">11:35am</option><option value="11:40am">11:40am</option><option value="11:45am">11:45am</option><option value="11:50am">11:50am</option><option value="11:55am">11:55am</option><option selected="selected" value="12:00pm">12:00pm</option><option value="12:05pm">12:05pm</option><option value="12:10pm">12:10pm</option><option value="12:15pm">12:15pm</option><option value="12:20pm">12:20pm</option><option value="12:25pm">12:25pm</option><option value="12:30pm">12:30pm</option><option value="12:35pm">12:35pm</option><option value="12:40pm">12:40pm</option><option value="12:45pm">12:45pm</option><option value="12:50pm">12:50pm</option><option value="12:55pm">12:55pm</option><option value="01:00pm">01:00pm</option><option value="01:05pm">01:05pm</option><option value="01:10pm">01:10pm</option><option value="01:15pm">01:15pm</option><option value="01:20pm">01:20pm</option><option value="01:25pm">01:25pm</option><option value="01:30pm">01:30pm</option><option value="01:35pm">01:35pm</option><option value="01:40pm">01:40pm</option><option value="01:45pm">01:45pm</option><option value="01:50pm">01:50pm</option><option value="01:55pm">01:55pm</option><option value="02:00pm">02:00pm</option><option value="02:05pm">02:05pm</option><option value="02:10pm">02:10pm</option><option value="02:15pm">02:15pm</option><option value="02:20pm">02:20pm</option><option value="02:25pm">02:25pm</option><option value="02:30pm">02:30pm</option><option value="02:35pm">02:35pm</option><option value="02:40pm">02:40pm</option><option value="02:45pm">02:45pm</option><option value="02:50pm">02:50pm</option><option value="02:55pm">02:55pm</option><option value="03:00pm">03:00pm</option><option value="03:05pm">03:05pm</option><option value="03:10pm">03:10pm</option><option value="03:15pm">03:15pm</option><option value="03:20pm">03:20pm</option><option value="03:25pm">03:25pm</option><option value="03:30pm">03:30pm</option><option value="03:35pm">03:35pm</option><option value="03:40pm">03:40pm</option><option value="03:45pm">03:45pm</option><option value="03:50pm">03:50pm</option><option value="03:55pm">03:55pm</option><option value="04:00pm">04:00pm</option><option value="04:05pm">04:05pm</option><option value="04:10pm">04:10pm</option><option value="04:15pm">04:15pm</option><option value="04:20pm">04:20pm</option><option value="04:25pm">04:25pm</option><option value="04:30pm">04:30pm</option><option value="04:35pm">04:35pm</option><option value="04:40pm">04:40pm</option><option value="04:45pm">04:45pm</option><option value="04:50pm">04:50pm</option><option value="04:55pm">04:55pm</option><option value="05:00pm">05:00pm</option><option value="05:05pm">05:05pm</option><option value="05:10pm">05:10pm</option><option value="05:15pm">05:15pm</option><option value="05:20pm">05:20pm</option><option value="05:25pm">05:25pm</option><option value="05:30pm">05:30pm</option><option value="05:35pm">05:35pm</option><option value="05:40pm">05:40pm</option><option value="05:45pm">05:45pm</option><option value="05:50pm">05:50pm</option><option value="05:55pm">05:55pm</option><option value="06:00pm">06:00pm</option><option value="06:05pm">06:05pm</option><option value="06:10pm">06:10pm</option><option value="06:15pm">06:15pm</option><option value="06:20pm">06:20pm</option><option value="06:25pm">06:25pm</option><option value="06:30pm">06:30pm</option><option value="06:35pm">06:35pm</option><option value="06:40pm">06:40pm</option><option value="06:45pm">06:45pm</option><option value="06:50pm">06:50pm</option><option value="06:55pm">06:55pm</option><option value="07:00pm">07:00pm</option><option value="07:05pm">07:05pm</option><option value="07:10pm">07:10pm</option><option value="07:15pm">07:15pm</option><option value="07:20pm">07:20pm</option><option value="07:25pm">07:25pm</option><option value="07:30pm">07:30pm</option><option value="07:35pm">07:35pm</option><option value="07:40pm">07:40pm</option><option value="07:45pm">07:45pm</option><option value="07:50pm">07:50pm</option><option value="07:55pm">07:55pm</option><option value="08:00pm">08:00pm</option><option value="08:05pm">08:05pm</option><option value="08:10pm">08:10pm</option><option value="08:15pm">08:15pm</option><option value="08:20pm">08:20pm</option><option value="08:25pm">08:25pm</option><option value="08:30pm">08:30pm</option><option value="08:35pm">08:35pm</option><option value="08:40pm">08:40pm</option><option value="08:45pm">08:45pm</option><option value="08:50pm">08:50pm</option><option value="08:55pm">08:55pm</option><option value="09:00pm">09:00pm</option><option value="09:05pm">09:05pm</option><option value="09:10pm">09:10pm</option><option value="09:15pm">09:15pm</option><option value="09:20pm">09:20pm</option><option value="09:25pm">09:25pm</option><option value="09:30pm">09:30pm</option><option value="09:35pm">09:35pm</option><option value="09:40pm">09:40pm</option><option value="09:45pm">09:45pm</option><option value="09:50pm">09:50pm</option><option value="09:55pm">09:55pm</option><option value="10:00pm">10:00pm</option><option value="10:05pm">10:05pm</option><option value="10:10pm">10:10pm</option><option value="10:15pm">10:15pm</option><option value="10:20pm">10:20pm</option><option value="10:25pm">10:25pm</option><option value="10:30pm">10:30pm</option><option value="10:35pm">10:35pm</option><option value="10:40pm">10:40pm</option><option value="10:45pm">10:45pm</option><option value="10:50pm">10:50pm</option><option value="10:55pm">10:55pm</option><option value="11:00pm">11:00pm</option><option value="11:05pm">11:05pm</option><option value="11:10pm">11:10pm</option><option value="11:15pm">11:15pm</option><option value="11:20pm">11:20pm</option><option value="11:25pm">11:25pm</option><option value="11:30pm">11:30pm</option><option value="11:35pm">11:35pm</option><option value="11:40pm">11:40pm</option><option value="11:45pm">11:45pm</option><option value="11:50pm">11:50pm</option><option value="11:55pm">11:55pm</option>
				</select>
			</div>
		</fieldset>
			<?php 
				} 
				
				if ($type == "departure" || $type == "round-trip") 
				{
			?>
		<fieldset class="flightFieldset">
			<legend>Ingresa la información de tu vuelo (regreso)</legend>
			<div>
				<label for="departure_airline">Aerolínea</label>
				<input type="text" name="departure_airline">
			</div>
			<div>
				<label for="departure_flight_number">Número de vuelo</label>
				<input type="text" name="departure_flight" id="departure_flight">
			</div>
			<div>
				<label for="departure_time">Hora de partida</label>
				<select name="departure_time" id="departure_time">
					<option value="12:00am">12:00am</option><option value="12:05am">12:05am</option><option value="12:10am">12:10am</option><option value="12:15am">12:15am</option><option value="12:20am">12:20am</option><option value="12:25am">12:25am</option><option value="12:30am">12:30am</option><option value="12:35am">12:35am</option><option value="12:40am">12:40am</option><option value="12:45am">12:45am</option><option value="12:50am">12:50am</option><option value="12:55am">12:55am</option><option value="01:00am">01:00am</option><option value="01:05am">01:05am</option><option value="01:10am">01:10am</option><option value="01:15am">01:15am</option><option value="01:20am">01:20am</option><option value="01:25am">01:25am</option><option value="01:30am">01:30am</option><option value="01:35am">01:35am</option><option value="01:40am">01:40am</option><option value="01:45am">01:45am</option><option value="01:00am">01:00am</option><option value="01:50am">01:50am</option><option value="01:55am">01:55am</option><option value="02:00am">02:00am</option><option value="02:05am">02:05am</option><option value="02:10am">02:10am</option><option value="02:15am">02:15am</option><option value="02:20am">02:20am</option><option value="02:25am">02:25am</option><option value="02:30am">02:30am</option><option value="02:35am">02:35am</option><option value="02:40am">02:40am</option><option value="02:45am">02:45am</option><option value="02:50am">02:50am</option><option value="02:55am">02:55am</option><option value="03:00am">03:00am</option><option value="03:05am">03:05am</option><option value="03:10am">03:10am</option><option value="03:15am">03:15am</option><option value="03:20am">03:20am</option><option value="03:25am">03:25am</option><option value="03:30am">03:30am</option><option value="03:35am">03:35am</option><option value="03:40am">03:40am</option><option value="03:45am">03:45am</option><option value="03:50am">03:50am</option><option value="03:55am">03:55am</option><option value="04:00am">04:00am</option><option value="04:05am">04:05am</option><option value="04:10am">04:10am</option><option value="04:15am">04:15am</option><option value="04:20am">04:20am</option><option value="04:25am">04:25am</option><option value="04:30am">04:30am</option><option value="04:35am">04:35am</option><option value="04:40am">04:40am</option><option value="04:45am">04:45am</option><option value="04:50am">04:50am</option><option value="04:55m">04:55m</option><option value="05:00am">05:00am</option><option value="05:05am">05:05am</option><option value="05:10am">05:10am</option><option value="05:15am">05:15am</option><option value="05:20am">05:20am</option><option value="05:25am">05:25am</option><option value="05:30am">05:30am</option><option value="05:35am">05:35am</option><option value="05:40am">05:40am</option><option value="05:45am">05:45am</option><option value="05:50am">05:50am</option><option value="05:55am">05:55am</option><option value="06:00am">06:00am</option><option value="06:05am">06:05am</option><option value="06:10am">06:10am</option><option value="06:15am">06:15am</option><option value="06:20am">06:20am</option><option value="06:25am">06:25am</option><option value="06:30am">06:30am</option><option value="06:35am">06:35am</option><option value="06:40am">06:40am</option><option value="06:45am">06:45am</option><option value="06:50am">06:50am</option><option value="06:55am">06:55am</option><option value="07:00am">07:00am</option><option value="07:05am">07:05am</option><option value="07:10am">07:10am</option><option value="07:15am">07:15am</option><option value="07:20am">07:20am</option><option value="07:25am">07:25am</option><option value="07:30am">07:30am</option><option value="07:35am">07:35am</option><option value="07:40am">07:40am</option><option value="07:45am">07:45am</option><option value="07:50am">07:50am</option><option value="07:55am">07:55am</option><option value="08:00am">08:00am</option><option value="08:05am">08:05am</option><option value="08:10am">08:10am</option><option value="08:15am">08:15am</option><option value="08:20am">08:20am</option><option value="08:25am">08:25am</option><option value="08:30am">08:30am</option><option value="08:35am">08:35am</option><option value="08:40am">08:40am</option><option value="08:45am">08:45am</option><option value="08:50am">08:50am</option><option value="08:55am">08:55am</option><option value="09:00am">09:00am</option><option value="09:05am">09:05am</option><option value="09:10am">09:10am</option><option value="09:15am">09:15am</option><option value="08:50am">08:50am</option><option value="08:55am">08:55am</option><option value="09:00am">09:00am</option><option value="09:05am">09:05am</option><option value="09:10am">09:10am</option><option value="09:15am">09:15am</option><option value="09:20am">09:20am</option><option value="09:25am">09:25am</option><option value="09:30am">09:30am</option><option value="09:35am">09:35am</option><option value="09:40am">09:40am</option><option value="09:45am">09:45am</option><option value="09:50am">09:50am</option><option value="09:55am">09:55am</option><option value="10:00am">10:00am</option><option value="10:05am">10:05am</option><option value="10:10am">10:10am</option><option value="10:15am">10:15am</option><option value="10:20am">10:20am</option><option value="10:25am">10:25am</option><option value="10:30am">10:30am</option><option value="10:35am">10:35am</option><option value="10:40am">10:40am</option><option value="10:45am">10:45am</option><option value="10:50am">10:50am</option><option value="10:55am">10:55am</option><option value="11:00am">11:00am</option><option value="11:05am">11:05am</option><option value="11:10am">11:10am</option><option value="11:15am">11:15am</option><option value="11:20am">11:20am</option><option value="11:25am">11:25am</option><option value="11:30am">11:30am</option><option value="11:35am">11:35am</option><option value="11:40am">11:40am</option><option value="11:45am">11:45am</option><option value="11:50am">11:50am</option><option value="11:55am">11:55am</option><option selected="selected" value="12:00pm">12:00pm</option><option value="12:05pm">12:05pm</option><option value="12:10pm">12:10pm</option><option value="12:15pm">12:15pm</option><option value="12:20pm">12:20pm</option><option value="12:25pm">12:25pm</option><option value="12:30pm">12:30pm</option><option value="12:35pm">12:35pm</option><option value="12:40pm">12:40pm</option><option value="12:45pm">12:45pm</option><option value="12:50pm">12:50pm</option><option value="12:55pm">12:55pm</option><option value="01:00pm">01:00pm</option><option value="01:05pm">01:05pm</option><option value="01:10pm">01:10pm</option><option value="01:15pm">01:15pm</option><option value="01:20pm">01:20pm</option><option value="01:25pm">01:25pm</option><option value="01:30pm">01:30pm</option><option value="01:35pm">01:35pm</option><option value="01:40pm">01:40pm</option><option value="01:45pm">01:45pm</option><option value="01:50pm">01:50pm</option><option value="01:55pm">01:55pm</option><option value="02:00pm">02:00pm</option><option value="02:05pm">02:05pm</option><option value="02:10pm">02:10pm</option><option value="02:15pm">02:15pm</option><option value="02:20pm">02:20pm</option><option value="02:25pm">02:25pm</option><option value="02:30pm">02:30pm</option><option value="02:35pm">02:35pm</option><option value="02:40pm">02:40pm</option><option value="02:45pm">02:45pm</option><option value="02:50pm">02:50pm</option><option value="02:55pm">02:55pm</option><option value="03:00pm">03:00pm</option><option value="03:05pm">03:05pm</option><option value="03:10pm">03:10pm</option><option value="03:15pm">03:15pm</option><option value="03:20pm">03:20pm</option><option value="03:25pm">03:25pm</option><option value="03:30pm">03:30pm</option><option value="03:35pm">03:35pm</option><option value="03:40pm">03:40pm</option><option value="03:45pm">03:45pm</option><option value="03:50pm">03:50pm</option><option value="03:55pm">03:55pm</option><option value="04:00pm">04:00pm</option><option value="04:05pm">04:05pm</option><option value="04:10pm">04:10pm</option><option value="04:15pm">04:15pm</option><option value="04:20pm">04:20pm</option><option value="04:25pm">04:25pm</option><option value="04:30pm">04:30pm</option><option value="04:35pm">04:35pm</option><option value="04:40pm">04:40pm</option><option value="04:45pm">04:45pm</option><option value="04:50pm">04:50pm</option><option value="04:55pm">04:55pm</option><option value="05:00pm">05:00pm</option><option value="05:05pm">05:05pm</option><option value="05:10pm">05:10pm</option><option value="05:15pm">05:15pm</option><option value="05:20pm">05:20pm</option><option value="05:25pm">05:25pm</option><option value="05:30pm">05:30pm</option><option value="05:35pm">05:35pm</option><option value="05:40pm">05:40pm</option><option value="05:45pm">05:45pm</option><option value="05:50pm">05:50pm</option><option value="05:55pm">05:55pm</option><option value="06:00pm">06:00pm</option><option value="06:05pm">06:05pm</option><option value="06:10pm">06:10pm</option><option value="06:15pm">06:15pm</option><option value="06:20pm">06:20pm</option><option value="06:25pm">06:25pm</option><option value="06:30pm">06:30pm</option><option value="06:35pm">06:35pm</option><option value="06:40pm">06:40pm</option><option value="06:45pm">06:45pm</option><option value="06:50pm">06:50pm</option><option value="06:55pm">06:55pm</option><option value="07:00pm">07:00pm</option><option value="07:05pm">07:05pm</option><option value="07:10pm">07:10pm</option><option value="07:15pm">07:15pm</option><option value="07:20pm">07:20pm</option><option value="07:25pm">07:25pm</option><option value="07:30pm">07:30pm</option><option value="07:35pm">07:35pm</option><option value="07:40pm">07:40pm</option><option value="07:45pm">07:45pm</option><option value="07:50pm">07:50pm</option><option value="07:55pm">07:55pm</option><option value="08:00pm">08:00pm</option><option value="08:05pm">08:05pm</option><option value="08:10pm">08:10pm</option><option value="08:15pm">08:15pm</option><option value="08:20pm">08:20pm</option><option value="08:25pm">08:25pm</option><option value="08:30pm">08:30pm</option><option value="08:35pm">08:35pm</option><option value="08:40pm">08:40pm</option><option value="08:45pm">08:45pm</option><option value="08:50pm">08:50pm</option><option value="08:55pm">08:55pm</option><option value="09:00pm">09:00pm</option><option value="09:05pm">09:05pm</option><option value="09:10pm">09:10pm</option><option value="09:15pm">09:15pm</option><option value="09:20pm">09:20pm</option><option value="09:25pm">09:25pm</option><option value="09:30pm">09:30pm</option><option value="09:35pm">09:35pm</option><option value="09:40pm">09:40pm</option><option value="09:45pm">09:45pm</option><option value="09:50pm">09:50pm</option><option value="09:55pm">09:55pm</option><option value="10:00pm">10:00pm</option><option value="10:05pm">10:05pm</option><option value="10:10pm">10:10pm</option><option value="10:15pm">10:15pm</option><option value="10:20pm">10:20pm</option><option value="10:25pm">10:25pm</option><option value="10:30pm">10:30pm</option><option value="10:35pm">10:35pm</option><option value="10:40pm">10:40pm</option><option value="10:45pm">10:45pm</option><option value="10:50pm">10:50pm</option><option value="10:55pm">10:55pm</option><option value="11:00pm">11:00pm</option><option value="11:05pm">11:05pm</option><option value="11:10pm">11:10pm</option><option value="11:15pm">11:15pm</option><option value="11:20pm">11:20pm</option><option value="11:25pm">11:25pm</option><option value="11:30pm">11:30pm</option><option value="11:35pm">11:35pm</option><option value="11:40pm">11:40pm</option><option value="11:45pm">11:45pm</option><option value="11:50pm">11:50pm</option><option value="11:55pm">11:55pm</option>
				</select>
			</div>
		</fieldset>
			<?php } ?>
		<input type="checkbox" id="terms" name="terms"><label for="terms">He leído y acepto los <a href="politicas.php" class="terms-link">Terminos y Condiciones</a></label>
		<input type="button" id="bookButton" value="Reservar"><span class="loading-icon"></span>
		<fieldset id="formErrors" class="formErrors"><legend>Para poder reservar falta lo siguiente</legend></fieldset>
	</form>
<?php require 'modules/footer.php'; ?>