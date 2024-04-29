<h4><?php echo $this->p->t('lehre', 'lehrauftraegeAnnehmen'); ?></h4>

<div class="card-body">
	<?php echo $this->p->t('lehre', 'lehrauftraegeAnnehmenText'); ?>
	<ol>
		<li><?php echo $this->p->t('lehre', 'lehrauftraegeAnnehmenKlickStatusicon'); ?></li>
		<li><?php echo $this->p->t('lehre', 'lehrauftraegeAnnehmenLehrauftraegeWaehlen'); ?></li>
		<li><?php echo $this->p->t('lehre', 'lehrauftraegeAnnehmenMitKlickAnnehmen'); ?></li>
	</ol>
</div>

<br>

<h4><?php echo $this->p->t('lehre', 'lehrauftraegeNichtAuswaehlbar'); ?></h4>

<div class="card-body">
	<?php echo $this->p->t('lehre', 'lehrauftraegeNichtAuswaehlbarTextBeiAnnahme'); ?>

</div>
<br>

<h4>Filter</h4>

<div class="card-body">
	<div class="col-12 col-md-8 col-lg-6">
		<table class="table table-bordered">
			<tr class="text-center">
				<td class="col-1"><i class='fa fa-users'></i></td>
				<td class="col-1"><i class='fa fa-user-check'></i></td>
				<td class="col-1"><i class='fa-regular fa-handshake'></i></td>
			</tr>
			<tr class="text-center">
				<td><?php echo $this->p->t('lehre', 'filterAlleBeiAnnahme'); ?></td>
				<td><?php echo $this->p->t('lehre', 'filterErteiltBeiAnnahme'); ?></td>
				<td><?php echo $this->p->t('lehre', 'filterAngenommen'); ?></td>
			</tr>
		</table>
	</div>

</div>
<br>