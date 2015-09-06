<?php require 'modules/header.php'; ?>
	<?php require 'modules/resbox.php'; ?>
	<form action="ajax/contact.php" class="contact" id="contactForm">
		<fieldset>
			<legend>Contact</legend>
			<p>Thank you for contacting ShuttleConsierge! It is a pleasure to assist you and answer your questions, queries or suggestions.</p>
			<p>Fill out this form and we will answer your requests as soon as possible.</p>
			<div>
				<label for="full_name">Full Name</label>
				<input type="text" name="full_name" id="full_name">
			</div>
			<div>
				<label for="email">Email</label>
				<input type="text" name="email" id="email">
			</div>
			<div>
				<label for="phone">Phone number</label>
				<input type="text" name="phone" id="phone">
			</div>
			<div>
				<label for="message">Message</label>
				<textarea name="message" id="message"></textarea>
			</div>
		</fieldset>
		<fieldset id="formErrors" class="formErrors"><legend>In order to send a message fill the following</legend></fieldset>
		<input type="button" id="contactButton" value="Send"><span class="loading-icon"></span>
	</form>
	
<?php require 'modules/footer.php'; ?>