<?php 
	require 'modules/config.php';
	if( !isset($_GET["id"]) ) {
		exit("Faltan parámetros...");
	}
?>

<?php require 'modules/header.php'; ?>
<?php require 'modules/resbox.php'; ?>
<?php require 'modules/banners.php'; ?>
<?php
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
?>
<script type="text/javascript">
	function imprimir(){
		window.location="imprimirConfirmacion.php?id=<?php echo $_GET["id"]?>";
	}
</script>
	<div class="confirm">
		<h2>RESERVACIÓN CONFIRMADA</h2>
		<?php foreach ($data as $info): ?>
			<div class="book">
				<fieldset>
					<legend>Información del traslado</legend>
					<div><span class="label">Tipo de traslado</span><span class="value"><?php echo $info["type"]?></span></div>
					<div><span class="label">Destino</span><span class="value">
						<?php
							$airport="Aeropuerto Cancún";
						if($info["type"]=="arrival"){
							echo $airport;?>	&rarr;	<?php echo $info["destination"];
						}
						if($info["type"]=="leaving"){
							echo $info["destination"];?>	&rarr;	<?php echo	$airport;
						}
						if($info["type"]=="round trip"){
							echo $airport;?>	&harr;		<?php echo $info["destination"];
						}
						?>
					</span></div>
					<div><span class="label">Tipo de servicio</span><span class="value"><?php echo $info["service"]?></span></div>
					<div><span class="label">Pasajeros</span><span class="value"><?php echo $info["pax"]?></span></div>
					<?php if ($info["type"] == "arrival" || $info["type"] == "round trip") { ?>
					<div><span class="label">Llegada</span><span class="value"><?php echo $info["start"]?> <?php echo $info["arrival_time"]?> [Aerolínea: <?php echo $info["arrival_airline"]?> | Vuelo: <?php echo $info["arrival_flight"]?>]</span></div>
					<?php } 
					if ($info["type"] == "departure" || $info["type"] == "round trip") { ?>
					<div><span class="label">Salida</span><span class="value"><?php echo $info["end"]?> <?php echo $info["departure_time"]?> [Aerolínea: <?php echo $info["departure_airline"]?> | Vuelo: <?php echo $info["departure_flight"]?>]</span></div>
					<?php } ?>
					<div><span class="label">Tipo de pago</span><span class="value"><?php echo $info["payment"]?></span></div>
				</fieldset>
				<fieldset>
					<legend>Información de contacto</legend>
					<div><span class="label">Nombre</span><span class="value"><?php echo $info["name"]?></span></div>
					<div><span class="label">Teléfono</span><span class="value">+52 <?php echo $info["phone"]?></span></div>
					<div><span class="label">Email</span><span style="text-transform: lowercase;" class="value"><?php echo $info["email"]?></span></div>
					<div><span class="label">Dirección</span><span class="value"><?php echo $info["address"]?></span></div>
					<div><span class="label">País</span><span class="value"><?php echo $info["country"]?></span></div>
					<div><span class="label">Estado</span><span class="value"><?php echo $info["state"]?></span></div>
					<div><span class="label">Ciudad</span><span class="value"><?php echo $info["city"]?></span></div>
					<div><span class="label">Código Postal</span><span class="value"><?php echo $info["zip_code"]?></span></div>
				</fieldset>
			</div>
			<div class="price">
				<span class="label">Servicio <?php if($info["payment"] == "paypal") echo "Pagado por Paypal"; else echo "Por pagar";?></span>
				<span class="amount value">$<?php echo $info["price"]?> <span class="currency">USD</span></span>
			</div>
		<?php endforeach ?>
		<div>
			<input style="width: 200px; margin-top: 30px; height: 30px;" onclick="imprimir()" id="imprimir" type="button" value="Imprimir Reservaci&oacute;n"/>
		</div>
	</div>
<?php require 'modules/footer.php'; ?>