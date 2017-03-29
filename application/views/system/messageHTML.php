<?php $this->load->view("templates/header", array("title" => "Message viewer")); ?>

	<body>
		
		<table widht="70%">
			<tr>
				<td>
					Subject:
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
					Message: 
				</td>
				<td>
					&nbsp;
				</td>
				<td>
					<?php echo $message->body; ?>
				</td>
			</tr>
			<tr>
				<td></td>
				<td colspan="2" align="center">
					<a href="<?php echo $href; ?>">Reply</a>
				</td>
			</tr>
		</table>
	
	</body>

<?php $this->load->view("templates/footer"); ?>