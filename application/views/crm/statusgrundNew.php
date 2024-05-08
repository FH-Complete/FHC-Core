<?php
	$this->load->view('templates/header', array('title' => 'StatusgrundNew'));
?>

		<div class="row">
			<div class="span4">
				<h2>Neuer Statusgrund</h2>
				<form method="post" action="<?php echo site_url("crm/Statusgrund/insGrund"); ?>">
					<table>
						<tr>
							<td colspan="2">
								Bezeichnung mehrsprachig:<br/><br/>

								<?php foreach ($sprache as $s): ?>
									<?php echo $s->sprache; ?>:<br/>
									<input type="text" name="bezeichnung_mehrsprachig[]" value="" /><br/>
								<?php endforeach ?>

							</td>
						</tr>
						<tr>
							<td colspan="2">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td colspan="2">
								Beschreibung:<br/><br/>

								<?php foreach ($sprache as $s): ?>
									<?php echo $s->sprache; ?>:<br/>
									<textarea name="beschreibung[]"></textarea><br/>
								<?php endforeach ?>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td>
								Aktiv:
							</td>
							<td>
								<input type="checkbox" name="aktiv" />
							</td>
						</tr>
						<tr>
							<td colspan="2">
								&nbsp;
							</td>
						</tr>
						<tr>
							<td>
								StatusGrund:
							</td>
						<tr>
						</tr>
							<td>
								<input type="text" name="statusgrund_kurzbz" value="" /><br/>
							</td>
						</tr>
						<tr>
							<td colspan="2" align="center">
								<button type="submit">Save</button>
							</td>
						</tr>
					</table>
					<input type="hidden" name="status_kurzbz" value="<?php echo $status_kurzbz; ?>" />
				</form>
			</div>
		</div>
	</body>

</html>
