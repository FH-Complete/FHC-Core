<?php
	$this->load->view('templates/header', array('title' => 'PhrasesEdit'));
?>
<div class="row">
	<div class="span4">
	  <h2>Phrase: <?php echo $phrase->phrase_id; ?></h2>
<form method="post" action="../save">
	Bezeichnung: <input type="text" name="phrase" value="<?php echo $phrase->phrase; ?>" />
	<input type="hidden" name="phrase_id" value="<?php echo $phrase->phrase_id; ?>" />
	<button type="submit">Save</button>
</form>

</div>
</div>

</body>
</html>
