<div class="row">
	<div class="span4">
	  <h2>Nachricht <?php echo $message->message_id; ?></h2>

Absender: <?php echo $message->person_id; ?><br/>
Betreff: <?php echo $message->subject; ?><br/>
Text: <?php echo $message->body; ?><br/>
<?php 
    // This is an example to show that you can load stuff from inside the template file
    echo $this->template->widget("organisationseinheit_widget", array('title' => 'Organisationseinheit', 'oe_kurzbz' => $message->oe_kurzbz));
?>
	  
</div>
