<script type="text/javascript" src="<?php echo base_url('vendor/tinymce/tinymce/tinymce.min.js');?>"></script>

<div class="row">
	<div class="span4">
		<h2>Neue Nachricht</h2>
		<form method="post" action="send">
			Absender: <?php //echo $message->person_id; ?><br/>
			<?php
				// This is an example to show that you can load stuff from inside the template file
				//echo $this->template->widget("organisationseinheit_widget", array('title' => 'Organisationseinheit', 'oe_kurzbz' => $message->oe_kurzbz));
			?>
			Betreff: <input type="text" name="subject" value="<?php echo $subject; ?>" /></input>
			<?php
				// This is an example to show that you can load stuff from inside the template file
				echo $this->template->widget("tinymce_widget", array());
			?>
			<textarea name="body" style="width:100%"><?php echo $body; ?></textarea>
			<button type="submit">send Message!</button>
		</form> 
	</div>
</div>
