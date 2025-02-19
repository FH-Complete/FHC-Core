<h4><?php echo $this->p->t('lehre', 'lehrauftragStandardBestellprozess'); ?></h4>
<div class="panel panel-body">
	<table>
		<tr class="text-center">
			<td><i class='fa fa-2x fa-user-tag'></i></td>
			<td><i class='fa fa-2x fa-long-arrow-right'></i></td>
			<td><i class='fa fa-2x fa-user-check'></i></td>
			<td><i class='fa fa-2x fa-long-arrow-right'></i></td>
			<td><i class='fa-regular fa-2x fa-handshake'></i></td>
		</tr>
		<tr class="text-center">
			<td class="text-muted"><?php echo $this->p->t('lehre', 'lehrauftragStandardBestellprozessBestellen'); ?></td>
			<td></td>
			<td><b><?php echo $this->p->t('lehre', 'lehrauftragStandardBestellprozessErteilen'); ?></b></td>
			<td></td>
			<td class="text-muted"><?php echo $this->p->t('lehre', 'lehrauftragStandardBestellprozessAnnehmen'); ?></td>
		</tr>
	</table>
</div>
<br>

<h4><?php echo $this->p->t('lehre', 'lehrauftraegeErteilen'); ?></h4>
<div class="panel panel-body">
	<?php echo $this->p->t('lehre', 'lehrauftraegeErteilenText'); ?>
	<ol>
		<li><?php echo $this->p->t('lehre', 'lehrauftraegeErteilenKlickStatusicon'); ?></li>
		<li><?php echo $this->p->t('lehre', 'lehrauftraegeErteilenLehrauftraegeWaehlen'); ?></li>
		<li><?php echo $this->p->t('lehre', 'lehrauftraegeErteilenMitKlickErteilen'); ?></li>
	</ol>
</div>
<br>

<h4><?php echo $this->p->t('lehre', 'geaenderteLehrauftraege'); ?></h4>
<div class="panel panel-body">
	<?php echo $this->p->t('lehre', 'geaenderteLehrauftraegeTextBeiErteilung'); ?>
</div>
<br>

<h4><?php echo $this->p->t('lehre', 'lehrauftraegeNichtAuswaehlbar'); ?></h4>
<div class="panel panel-body">
	<?php echo $this->p->t('lehre', 'lehrauftraegeNichtAuswaehlbarTextBeiErteilung'); ?>
</div>
<br>

<h4>Filter</h4>
<div class="panel panel-body">
	<table class="table table-bordered">
		<tr class="text-center">
			<td class="col-xs-1"><i class='fa fa-users'></i></td>
			<td class="col-xs-1"><i class='fa fa-user-plus'></i></td>
			<td class="col-xs-1"><i class='fa fa-user-tag'></i></td>
			<td class="col-xs-1"><i class='fa fa-user-check'></i></td>
			<td class="col-xs-1"><i class='fa-regular fa-handshake'></i></td>
			<td class="col-xs-1"><i class='fa fa-user-pen'></i></td>
			<td class="col-xs-1"><i class='fa fa-user-secret'></i></td>
		
		</tr>
		<tr class="text-center">
			<td><?php echo $this->p->t('lehre', 'filterAlle'); ?></td>
			<td><?php echo $this->p->t('lehre', 'filterNeu'); ?></td>
			<td><?php echo $this->p->t('lehre', 'filterBestellt'); ?></td>
			<td><?php echo $this->p->t('lehre', 'filterErteilt'); ?></td>
			<td><?php echo $this->p->t('lehre', 'filterAngenommen'); ?></td>
			<td><?php echo $this->p->t('lehre', 'filterGeaendert'); ?></td>
			<td><?php echo $this->p->t('lehre', 'filterDummies'); ?></td>
		</tr>
	</table>
</div>
<br>

<h4><?php echo $this->p->t('global', 'mehrHilfe'); ?></h4>
<div class="panel panel-info panel-body">
	<?php echo $this->p->t('global', 'weitereInformationenUnter'); ?><a href="https://wiki.fhcomplete.org/doku.php?id=fhc:lehrauftraege" target="_blank">FH Complete WIKI</a>
</div><br>