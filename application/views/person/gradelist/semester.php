<?php
	$semesterdata = $data;
	$tableid='table-semester-'.$semesterdata['studiensemester_kurzbz'];
?>
<div id="accordion_<?php echo $tableid;?>">
	<div class="panel-group">
		<div class="panel panel-default">
		<div class="panel-heading" id="headingcollapse_<?php echo $tableid;?>">
					<a class="btn btn-link"
						data-toggle="collapse"
						data-target="#collapse_<?php echo $tableid;?>"
						aria-expanded="true"
						aria-controls="collapse_<?php echo $tableid;?>"
						>
						<?php
						echo $semesterdata['studiensemester_kurzbz'].' - '.
							$semesterdata['ausbildungssemester'].'. '.$this->p->t('lehre','ausbildungssemester').' - '.
							$semesterdata['status'].' | '.
							$semesterdata['ectssumme_positiv'];
						if(isset($semesterdata['ectssumme_nonstpl']))
							echo ' + '.$semesterdata['ectssumme_nonstpl'];
						echo ' '.$this->p->t('lehre','ects');
							?>
					</a>
		</div>
		<div id="collapse_<?php echo $tableid;?>"
			class="panel-collapse collapse"
			aria-labelledby="headingcollapse_<?php echo $tableid;?>"
			data-parent="#accordion_<?php echo $tableid;?>"
			>
			<div class="panel-body">
				<?php
				if (is_array($lvs) && count($lvs) > 0):

				echo '<h4>Lehrveranstaltungen laut Studienplan '.$semesterdata['studienplan_bezeichnung'].'</h4>';
				?>
				<table id="<?php echo $tableid;?>" class="gradetable">
					<thead>
						<tr>
							<th><?php echo $this->p->t('lehre','lehrveranstaltung');?></th>
							<th><?php echo $this->p->t('lehre','kurzbz');?></th>
							<th><?php echo $this->p->t('lehre','semester');?></th>
							<th><?php echo $this->p->t('lehre','lehrform');?></th>
							<th style="text-align: right"><?php echo $this->p->t('lehre','ects');?></th>
							<th style="text-align: right"><?php echo $this->p->t('lehre','sws');?></th>
							<th><?php echo $this->p->t('lehre','pflichtfach');?></th>
							<th><?php echo $this->p->t('lehre','zeugnis');?></th>
							<th><?php echo $this->p->t('lehre','note');?></th>
						</tr>
					</thead>
					<tbody>
						<?php
							foreach ($lvs as $row_course)
							{
								Gradelist::printRow($row_course, 0);
							}
						?>
					</tbody>
					<tfoot>
						<tr>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th style="text-align: right">
								<?php
								echo (isset($semesterdata['ectssumme_positiv'])?$semesterdata['ectssumme_positiv']:'');

								if (isset($semesterdata['ectssumme'])
								 && isset($semesterdata['ectssumme_positiv'])
								 && $semesterdata['ectssumme'] != $semesterdata['ectssumme_positiv'])
								{
									echo ' ('.$semesterdata['ectssumme'].')';
								}
								?>
							</th>
							<th style="text-align: right">
								<?php
								echo (isset($semesterdata['swssumme_positiv'])?$semesterdata['swssumme_positiv']:'');

								if (isset($semesterdata['swssumme'])
								 && isset($semesterdata['swssumme_positiv'])
								 && $semesterdata['swssumme'] != $semesterdata['swssumme_positiv'])
								{
									echo ' ('.$semesterdata['swssumme'].')';
								}
								?>
							</th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</tfoot>
				</table>
				<?php
			endif;
			if (isset($lvs_nonstpl) && count($lvs_nonstpl) > 0):
				?>
				<h2><?php echo $this->p->t('lehre','nichtstudienplanrelevanteKurse'); ?></h2>
				<table id="<?php echo $tableid;?>_nonstpl" class="gradetable">
					<thead>
						<tr>
							<th><?php echo $this->p->t('lehre','lehrveranstaltung');?></th>
							<th><?php echo $this->p->t('lehre','kurzbz');?></th>
							<th><?php echo $this->p->t('lehre','semester');?></th>
							<th><?php echo $this->p->t('lehre','lehrform');?></th>
							<th><?php echo $this->p->t('lehre','ects');?></th>
							<th><?php echo $this->p->t('lehre','sws');?></th>
							<th><?php echo $this->p->t('lehre','pflichtfach');?></th>
							<th><?php echo $this->p->t('lehre','zeugnis');?></th>
							<th><?php echo $this->p->t('lehre','note');?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						if(is_array($lvs_nonstpl))
						{
							foreach ($lvs_nonstpl as $row_course)
							{
								Gradelist::printRow($row_course, 0);
							}
						}
						?>
					</tbody>
					<tfoot>
						<tr>
							<th></th>
							<th></th>
							<th></th>
							<th></th>
							<th style="text-align: right"><?php echo (isset($semesterdata['ectssumme_nonstpl'])?number_format($semesterdata['ectssumme_nonstpl'],2):''); ?></th>
							<th style="text-align: right"><?php echo (isset($semesterdata['swssumme_nonstpl'])?number_format($semesterdata['swssumme_nonstpl'],2):''); ?></th>
							<th></th>
							<th></th>
							<th></th>
						</tr>
					</tfoot>
				</table>
				<?php
				endif;
			?>
			</div>
				<div class="panel-footer">
					<b><?php echo $this->p->t('lehre','notendurchschnitt');?>:</b>
					<?php echo (isset($semesterdata['notendurchschnitt'])?$semesterdata['notendurchschnitt']:'');?>
					<img src="../../../../skin/images/information.png" title="<?php echo htmlentities($this->p->t('lehre', 'info_notendurchschnitt')); ?>" />
					<b><?php echo $this->p->t('lehre','gewichteternotendurchschnitt');?>:</b>
					<?php echo (isset($semesterdata['notendurchschnittgewichtet'])?$semesterdata['notendurchschnittgewichtet']:'');?>
					<img src="../../../../skin/images/information.png" title="<?php echo htmlentities($this->p->t('lehre', 'info_notendurchschnitt_gewichtet')); ?>" />
				</div>
		</div>
		</div>
	</div>
</div>
