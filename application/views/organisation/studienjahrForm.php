<?php
$bezeichnung = (isset($jahr->bezeichnung)) ? $jahr->bezeichnung : (isset($studienjahrkurzbz) ? "Studienjahr ".$studienjahrkurzbz : "");
?>

<tr>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="2">
		Bezeichnung:<br/><br/>
		<input type="text" name="studienjahrbz" value="<?php echo $bezeichnung; ?>"/><br/>
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
		<a href="<?php echo '../listStudienjahr'; ?>">
			<button type="button">Zur Übersicht</button>
		</a>
	</td>
</tr>

<tr>
	<td colspan="2">
		&nbsp;
	</td>
</tr>
<tr>
	<td colspan="2" align="center">
		<a href="<?php echo '../../studiensemester/listStudiensemester'; ?>">
			<button type="button">Zur Semesterübersicht</button>
		</a>
	</td>
</tr>


</table>
<br/>
<div class="input_ok"><?php if (isset($_GET['saved']) && $_GET['saved']) echo 'Studienjahr wurde gespeichert.'; ?></div>
<div class="input_error" id="errormessage"></div>