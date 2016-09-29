<?php
	$this->load->view('templates/header', array('title' => 'StatusgrundEdit'));
	
	$sg = $statusgrund;
?>

		<div class="row">
			<div class="span4">
				<h2>Status grund: <?php echo $sg->status_kurzbz; ?></h2>
				<form method="post" action="<?php echo APP_ROOT . "index.ci.php/crm/Statusgrund/saveGrund";?>">
					<table>
						<tr>
							<td colspan="2">
								Bezeichnung mehrsprachig:<br/><br/>
								
								<?php
									if (isset($sg->bezeichnung_mehrsprachig))
									{
										$val = str_replace("{", "", $sg->bezeichnung_mehrsprachig);
										$val = str_replace("}", "", $val);
										$val = str_replace("\"", "", $val);
										$val = explode(",", $val);
									}
									else
									{
										$val = array();
									}
									
									$i = 0;
								?>
								
								<?php foreach ($sprache as $s): ?>
									<?php echo $s->sprache; ?>:<br/>
									<?php
										if (!isset($val[$i]))
										{
											$val[$i] = "";
										}
										else
										{
											$val = str_replace("|", ",", $val);
										}
									?>
									<input type="text" name="bezeichnung_mehrsprachig[]" value="<?php echo $val[$i++]; ?>" /><br/>
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
									if (isset($sg->beschreibung))
									{
										$val = str_replace("{", "", $sg->beschreibung);
										$val = str_replace("}", "", $val);
										$val = str_replace("\"", "", $val);
										$val = explode(",", $val);
									}
									else
									{
										$val = array();
									}
									
									$i = 0;
								?>
								
								<?php foreach ($sprache as $s): ?>
									<?php echo $s->sprache; ?>:<br/>
									<?php
										if (!isset($val[$i]))
										{
											$val[$i] = "";
										}
										else
										{
											$val = str_replace("|", ",", $val);
										}
									?>
									<textarea name="beschreibung[]"><?php echo $val[$i++]; ?></textarea><br/>
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
								<input type="checkbox" name="aktiv" <?php echo isset($sg->aktiv) && $sg->aktiv == "t" ? "checked" : ""; ?> />
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
					<input type="hidden" name="statusgrund_kurzbz" value="<?php echo isset($sg->statusgrund_kurzbz) ? $sg->statusgrund_kurzbz : ""; ?>" />
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
