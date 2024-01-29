<?php
if(isset($course['note']) && isset($grades[$course['note']]))
	$gradeclass = ($grades[$course['note']]['positiv']?'gradelist_row_grade_positiv':'gradelist_row_grade_negativ');
else
	$gradeclass = '';

// Only Display the Course if the person is assigned to the course
// (no additional elective courses are displayed)
if(isset($course['zugeordnet']) && $course['zugeordnet'] === true)
{
?>
	<tr class="gradelist_row_<?php echo $course['lehrtyp_kurzbz']; ?> gradelist_row_depth_<?php echo $depth; ?>">
		<td><?php echo $course['bezeichnung']; ?></td>
		<td><?php echo $course['kurzbz']; ?></td>
		<td align="center">
			<?php
				echo $course['semester'].'. Sem ';
				if(isset($course['studiengang_kurzbz']))
					echo '( '.$course['studiengang_kurzbz'].' )';
			?></td>
		<td><?php echo (isset($course['lehrform_kurzbz'])?$course['lehrform_kurzbz']:''); ?></td>
		<td align="right"><?php echo (isset($course['ects'])?$course['ects']:''); ?></td>
		<td align="right"><?php echo (isset($course['sws'])?$course['sws']:''); ?></td>

		<td align="right"><?php echo (isset($course['pflicht'])?($course['pflicht']?'Ja':'Nein'):''); ?></td>
		<td align="right"><?php echo (isset($course['zeugnis'])?($course['zeugnis']?'Ja':'Nein'):''); ?></td>
		<td class="<?php echo $gradeclass; ?>">
			<?php
			if (isset($course['note']) && isset($grades[$course['note']]['anmerkung']))
				echo $grades[$course['note']]['anmerkung'];
			?>
		</td>
	</tr>
<?php
}
?>
