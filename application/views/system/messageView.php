<script type="text/javascript" src="<?php echo base_url('vendor/tinymce/tinymce/tinymce.min.js');?>"></script>

<div class="row">
	<div class="span4">
		<h2>
			Subject: <?php echo $message->subject; ?>
		</h2>
		Sender: <?php echo $message->person_id; ?><br/>
		Body: <?php echo $message->body; ?><br/>
		<?php
			echo $this->templatelib->widget("organisationseinheit_widget", array('title' => 'Organisationseinheit', 'oe_kurzbz' => $message->oe_kurzbz));
		?>
	</div>
</div>
