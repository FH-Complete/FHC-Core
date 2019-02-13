<?php
if(isset($course['note']) && isset($grades[$course['note']]))
	$gradeclass = ($grades[$course['note']]['positiv']?'gradelist_row_grade_positiv':'gradelist_row_grade_negativ');
else
	$gradeclass = '';
?>
	<tr class="gradelist_row_<?php echo $course['lehrtyp_kurzbz']; ?> gradelist_row_depth_<?php echo $depth; ?>">
		<td><?php echo $course['bezeichnung']; ?></td>
		<td align="right"><?php echo (isset($course['ects'])?$course['ects']:''); ?></td>
		<td class="<?php echo $gradeclass; ?>">
			<?php
			if (isset($course['note']) && isset($grades[$course['note']]['anmerkung']))
				echo $grades[$course['note']]['anmerkung'];
			?>
		</td>
	</tr>
