<?php
	$this->load->view('templates/header', array('title' => 'StatusEdit'));
	
	$s = $status;
?>

		<div class="row">
			<div class="span4">
				<h2>Status: <?php echo $s->status_kurzbz; ?></h2>
				<form method="post" action="../saveStatus">
					<table>
						<tr>
							<td colspan="2">
								beschreibung:<br/><br/>
								<input type="text" name="beschreibung" value="<?php echo $s->beschreibung; ?>" /><br/>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="2">
								Anmerkung:<br/><br/>
								<textarea name="anmerkung"><?php echo $s->anmerkung; ?></textarea><br/>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="2">
								Bezeichnung mehrsprachig:<br/><br/>
								
								<?php
									$val = '';
									$i = 0;
								?>
								
								<?php foreach ($sprache as $sp): ?>
									<?php echo $sp->sprache; ?>:<br/>
									<?php
										if (!isset($s->bezeichnung_mehrsprachig[$i]))
										{
											$val = '';
										}
										else
										{
											$val = $s->bezeichnung_mehrsprachig[$i];
										}
										$i++;
									?>
									<input type="text" name="bezeichnung_mehrsprachig[]" value="<?php echo $val; ?>" /><br/>
								<?php endforeach ?>
								
							</td>
						</tr>
						<tr>
							<td colspan="2">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<button type="submit">Save</button>
							</td>
						</tr>
					</table>
					<input type="hidden" name="status_kurzbz" value="<?php echo $s->status_kurzbz; ?>" />
				</form>
			</div>
		</div>
	</body>
	
	<?php
		if (!is_null($update))
		{
	?>
			<script>
				parent.document.getElementById("StatusgrundLeft").contentWindow.location.reload(true);
			</script>
	<?php
		}
	?>
	
</html>
