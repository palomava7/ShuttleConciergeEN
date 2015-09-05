<?php require 'modules/header.php'; ?>
	<?php require 'modules/resbox.php'; ?>
	<form action="ajax/contact.php" class="contact" id="contactForm">
		<fieldset>
			<legend>Contacto</legend>
			<p>¡Gracias por contactar a ShuttleConcierge! Es un placer poder atenderte y responder a tus preguntas, dudas o sugerencias.</p>
			<p>Rellena este formulario y atenderemos tus solicitudes a la brevedad posible.</p>
			<div>
				<label for="full_name">Nombre Completo</label>
				<input type="text" name="full_name" id="full_name">
			</div>
			<div>
				<label for="email">Correo Electrónico</label>
				<input type="text" name="email" id="email">
			</div>
			<div>
				<label for="phone">Teléfono</label>
				<input type="text" name="phone" id="phone">
			</div>
			<div>
				<label for="message">Mensaje</label>
				<textarea name="message" id="message"></textarea>
			</div>
		</fieldset>
		<fieldset id="formErrors" class="formErrors"><legend>Para poder enviar un mensaje falta lo siguiente</legend></fieldset>
		<input type="button" id="contactButton" value="Enviar"><span class="loading-icon"></span>
	</form>
	
<?php require 'modules/footer.php'; ?>