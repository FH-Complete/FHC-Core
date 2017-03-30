<?php $this->load->view("templates/header", array("title" => "Message viewer")); ?>

	<body>
		<center>
		<br><br>
		<table width="70%;" style="border: solid 1px gray; background-color:white; padding:5px;">
			<tr style=''>
				<td style="width: 80px;">
					<b>From:</b>
				</td>
				<td>
					&nbsp;
				</td>
				<td>
					<?php echo $sender->vorname.' '.$sender->nachname; ?>
				</td>
			</tr>
			<tr>
				<td style="width: 80px;">
					<b>Subject:</b>
				</td>
				<td>
					&nbsp;
				</td>
				<td>
					<?php echo $message->subject; ?>
				</td>
			</tr>
			<tr>
				<td>
					<b>Message:</b>
				</td>
				<td>
					&nbsp;
				</td>
				<td>
					<?php echo $message->body; ?>
				</td>
			</tr>
			<tr>
				<td colspan="3" align="center" style="background-color:#dddddd; padding:5px;">
					<a href="<?php echo $href; ?>">Reply</a>
				</td>
			</tr>
		</table>
	</center>

	</body>

<?php $this->load->view("templates/footer"); ?>
