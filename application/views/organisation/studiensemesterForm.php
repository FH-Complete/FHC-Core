<?php
$bezeichnung = (isset($sem->bezeichnung) ? $sem->bezeichnung : "");
$start = (isset($sem->start) ? date_format(date_create($sem->start), "d.m.Y") : "");
$ende = (isset($sem->ende) ? date_format(date_create($sem->ende), "d.m.Y") : "");
$studienjahr_kurzbz = (isset($sem->studienjahr_kurzbz) ? $sem->studienjahr_kurzbz : "");
$beschreibung = (isset($sem->beschreibung) ? $sem->beschreibung : "");
$onlinebewerbung = (isset($sem->onlinebewerbung) ? $sem->onlinebewerbung : "");
?>

<tr>
	<td colspan="2">
		Bezeichnung:<br/><br/>
		<input type="text" name="sembz" value="<?php echo $bezeichnung; ?>"/><br/>
	</td>
</tr>
<tr>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="2">
		Datum start:<br/><br/>
		<input type="text" class="dateinput" name="semstart" value="<?php echo $start; ?>"/><br/>
	</td>
</tr>
<tr>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="2">
		Datum ende:<br/><br/>
		<input type="text" class="dateinput" name="semende" value="<?php echo $ende; ?>"/><br/>
	</td>
</tr>
<tr>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="2">
		Studienjahr:<br/><br/>
		<select name="studienjahrkurzbz">
			<?php foreach ($allstudienjahre as $jahr): ?>
				<option <?php if ($studienjahr_kurzbz == $jahr->studienjahr_kurzbz) echo 'selected' ?>
						value="<?php echo $jahr->studienjahr_kurzbz ?>">
					<?php echo $jahr->bezeichnung ?>
				</option>
			<?php endforeach ?>
		</select>
		<a href="<?php echo '../../studienjahr/newStudienjahr/'; ?>">
			<button type="button">Neues Studienjahr</button>
		</a>
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
		<textarea name="beschreibung" rows="5" cols="33"><?php echo $beschreibung; ?></textarea>
		<br/>
	</td>
</tr>
<tr>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td>
		Onlinebewerbung
		<br/>
	</td>
	<td>
		<input type="checkbox" name="onlinebewerbung" <?php if ($onlinebewerbung) echo 'checked' ?>/>
	</td>
</tr>
<tr>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td align="center">
		<button type="submit">Speichern</button>
	</td>
	<td align="center">
		<a href="<?php echo '../listStudiensemester'; ?>">
			<button type="button">Zur Übersicht</button>
		</a>
	</td>
</tr>
</table>
<br/>
<div class="input_ok"><?php if (isset($_GET['saved']) && $_GET['saved']) echo 'Studiensemester wurde gespeichert.'; ?></div>
<div class="input_error" id="errormessage"></div>