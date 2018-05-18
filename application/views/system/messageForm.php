<div class="row">
	<div class="form-group">
		<div class="col-lg-1">
			<label>To:</label>
		</div>
		<div class="col-lg-11">
			<?php
			for ($i = 0; $i < count($receivers); $i++)
			{
				$receiver = $receivers[$i];
				// Every 10 recipients a new line
				if ($i > 1 && $i % 10 == 0)
				{
					echo '<br>';
				}
				echo $receiver->Vorname." ".$receiver->Nachname."; ";
			}
			?>
		</div>
	</div>
</div>
<div class="row">
	<div class="form-group">
		<div class="col-lg-1 msgfield">
			<label>Subject:</label>
		</div>&nbsp;
		<?php
		$subject = '';
		if (isset($message))
		{
			$subject = 'Re: '.$message->subject;
		}
		?>
		<div class="col-lg-7">
			<input id="subject" class="form-control col-lg-10" type="text" value="<?php echo $subject; ?>"
				   name="subject">
		</div>
	</div>
</div>
<br>
<div class="row">
	<div class="col-lg-<?php echo isset($variables) ? 10 : 12 ?>">
		<label>Message:</label>
		<?php
		$body = '';
		if (isset($message) )
		{
			if (isset($receivers[0]))
				$body .= '<br><br><br><!--<hr style="color: #e6e6e6">--><blockquote><i>On '.date_format(date_create($message->sent), 'd.m.Y H:i').' '.$receivers[0]->Vorname.' '.$receivers[0]->Nachname.' wrote:'.'</i></blockquote>';
			$body .= '<blockquote style="border-left:2px solid; padding-left: 8px">';
			$body .= $message->body.'</blockquote>';
		}
		?>
		<textarea id="bodyTextArea" name="body"><?php echo $body; ?></textarea>
	</div>
	<?php
	if (isset($variables)):
		?>
		<div class="col-lg-2">
			<div class="form-group">
				<label>Variables:</label>
				<select id="variables" class="form-control" size="14" multiple="multiple">
					<?php
					foreach ($variables as $key => $val)
					{
						?>
						<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
						<?php
					}
					?>
				</select>
			</div>
		</div>
	<?php endif; ?>
</div>
<br>
<div class="row">
	<div class="col-lg-<?php echo isset($variables) ? 10 : 12 ?> text-right">
		<button id="sendButton" class="btn btn-default" type="button">Send</button>
	</div>
</div>