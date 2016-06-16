<script type="text/javascript" src="<?php echo base_url('vendor/tinymce/tinymce/tinymce.min.js');?>"></script>
<div class="row">
	<div class="span4">
	  <h2>Nachricht <?php echo $message->message_id,': ',$message->subject; ?></h2>

Absender: <?php echo $message->person_id; ?><br/>
Betreff: <?php echo $message->subject; ?><br/>
Text: <?php echo $message->body; ?><br/>
<?php 
    // This is an example to show that you can load stuff from inside the template file
    echo $this->template->widget("organisationseinheit_widget", array('title' => 'Organisationseinheit', 'oe_kurzbz' => $message->oe_kurzbz));
?>
 <form method="post" action="system/Message/send">
<?php 
    // This is an example to show that you can load stuff from inside the template file
    echo $this->template->widget("tinymce_widget", array());
?>
	<input type="text" name="subject"></input>
    <textarea name="body" style="width:100%"></textarea>
 <button type="submit">send Message!</button> 
</form> 
</div>
