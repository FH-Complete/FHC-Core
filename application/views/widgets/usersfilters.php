<?php
	if (isset($errors) && is_array($errors) && count($errors) > 0)
	{
?>
		<div class="errors">
			<?php foreach($errors as $e): ?>
				<?php echo $e->retval; ?><br>
			<?php endforeach; ?>
		</div>
<?php
	}
?>
<div>
	<div>
		<select id="studiengang" name="studiengang">
			<?php foreach($studiengaenge as $v): ?>
				<?php
					$selected = '';
					if ($v->studiengang_kz == $selectedStudiengang)
					{
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v->studiengang_kz; ?>" <?php echo $selected; ?> onClick="studiengangSelected(this.value)">
					<?php
						if ($v->studiengang_kz == '-1')
						{	
							echo $v->kurzbzlang;
						}
						else
						{
							echo $v->kurzbzlang . ' (' . $v->bezeichnung . ')';
						}
					?>
				</option>
			<?php endforeach; ?>
		</select>
		AND
		<select id="studiensemester" name="studiensemester">
			<?php foreach($studiensemester as $v): ?>
				<?php
					$selected = '';
					if ($v->studiensemester_kurzbz == $selectedStudiensemester)
					{
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v->studiensemester_kurzbz; ?>" <?php echo $selected; ?> onClick="studiensemesterSelected(this.value)">
					<?php echo $v->beschreibung; ?>
				</option>
			<?php endforeach; ?>
		</select>
		->
		<select id="reihungstest" name="reihungstest">
			<?php foreach($reihungstest as $v): ?>
				<?php
					$selected = '';
					if ($v->reihungstest_id == $selectedReihungstest)
					{
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v->reihungstest_id; ?>" <?php echo $selected; ?> onClick="reihungstestSelected(this.value)">
					<?php echo $v->beschreibung; ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
	<div>
		AND
	</div>
	<div>
		<select id="gruppe" name="gruppe">
			<?php foreach($gruppen as $v): ?>
				<?php
					$selected = '';
					if ($v->gruppe_kurzbz == $selectedGruppe)
					{
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v->gruppe_kurzbz; ?>" <?php echo $selected; ?> onClick="gruppeSelected(this.value)">
					<?php echo $v->beschreibung; ?>
				</option>
			<?php endforeach; ?>
		</select>
		AND
		<select id="stufe" name="stufe">
			<?php foreach($stufen as $v): ?>
				<?php
					$selected = '';
					if ($v->stufe == $selectedStufe)
					{
						$selected = 'selected';
					}
				?>
				<option value="<?php echo $v->stufe; ?>" <?php echo $selected; ?> onClick="stufeSelected(this.value)">
					<?php echo $v->beschreibung; ?>
				</option>
			<?php endforeach; ?>
		</select>
	</div>
</div>