<?php
	$this->load->view('templates/header', array('title' => 'StatusgrundEdit'));

	$sg = $statusgrund;
?>

		<div class="row">
			<div class="span4">
				<h2>Statusgrund: <?php echo $sg->status_kurzbz; ?></h2>
				<form method="post" action="<?php echo site_url("crm/Statusgrund/saveGrund"); ?>">
					<table>
						<tr>
							<td colspan="2">
								Bezeichnung mehrsprachig:<br/><br/>
								<?php
									$i = 0;
									$val = "";
								?>
								<?php foreach ($sprache as $s): ?>
									<?php echo $s->sprache; ?>:<br/>
									<?php
										if (!isset($sg->bezeichnung_mehrsprachig[$i]))
										{
											$val = "";
										}
										else
										{
											$val = $sg->bezeichnung_mehrsprachig[$i];
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
							<td colspan="2">
								Beschreibung:<br/><br/>
								<?php
									$i = 0;
									$val = "";
								?>
								<?php foreach ($sprache as $s): ?>
									<?php echo $s->sprache; ?>:<br/>
									<?php
										if (!isset($sg->beschreibung[$i]))
										{
											$val = "";
										}
										else
										{
											$val = $sg->beschreibung[$i];
										}
										$i++;
									?>
									<textarea name="beschreibung[]"><?php echo $val; ?></textarea><br/>
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
								<input type="checkbox" name="aktiv" <?php echo isset($sg->aktiv) && $sg->aktiv === true ? "checked" : ""; ?> />
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
								<input type="text" name="statusgrund_kurzbz" value="<?php echo $sg->statusgrund_kurzbz; ?>" /><br/>
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
					<input type="hidden" name="statusgrund_id" value="<?php echo isset($sg->statusgrund_id) ? $sg->statusgrund_id : ""; ?>" />
				</form>
			</div>
		</div>
	</body>

	<?php
		if (!is_null($update))
		{
	?>
			<script>
				parent.document.getElementById("StatusgrundTop").contentWindow.location.reload(true);
			</script>
	<?php
		}
	?>

</html>
