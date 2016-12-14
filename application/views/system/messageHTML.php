<div>
	S: <?php echo $message->subject; ?>
</div>
<div>
	B: <?php echo $message->body; ?>
</div>
<div>
	<a href="<?php echo $href.$message->token; ?>">Reply</a>
</div>