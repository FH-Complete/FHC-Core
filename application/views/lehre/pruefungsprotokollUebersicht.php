<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Prüfungsprotokoll',
			'jquery' => true,
			'jqueryui' => true,
			'jquerycheckboxes' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'tablesorter' => true,
			'ajaxlib' => true,
			'dialoglib' => true,
			'tablewidget' => true,
			'phrases' => array(
				'ui' => array(
					'keineDatenVorhanden',
                    'heute',
                    'letzteWoche',
                    'alle',
                    'zeitraum'
					)
			),
			'customCSSs' => array('public/css/sbadmin2/tablesort_bootstrap.css'),
			'customJSs' => array('public/js/bootstrapper.js')
		)
	);
?>

<body>
<div id="page-wrapper">
	<div class="container-fluid">
		<div class="row">
			<div class="col-lg-12">
				<h3 class="page-header">
					<?php echo $this->p->t('abschlusspruefung','pruefungsprotokoll'); ?>
				</h3>
			</div>
		</div>
		<?php echo $this->p->t('abschlusspruefung','einfuehrungstext'); ?>
        <br><br>
        <div class="row">
            <div class="col-lg-12">
                <form action="" method="post">
                    <label><?php echo $this->p->t('ui','zeitraum'); ?>:&nbsp;&nbsp;</label>
                    <div class="btn-group" role="group">
                        <button type="submit" class="btn btn-default <?php echo $period == 'today' ? 'active' : ''?>"
                                name="period" value="today"><?php echo $this->p->t('ui','heute'); ?></button>
                        <button type="submit" class="btn btn-default <?php echo $period == 'lastWeek' ? 'active' : ''?>"
                                name="period" value="lastWeek"><?php echo $this->p->t('ui','letzteWoche'); ?></button>
                        <button type="submit" class="btn btn-default <?php echo $period == 'upcoming' ? 'active' : ''?>"
                                name="period" value="upcoming"><?php echo $this->p->t('ui','zukuenftige'); ?></button>
                        <button type="submit" class="btn btn-default <?php echo $period == 'all' ? 'active' : ''?>"
                                name="period" value="all"><?php echo $this->p->t('ui','alle'); ?></button>
                    </div>
                </form>
            </div>
        </div>
		<div class="row">
			<div class="col-lg-12">
			<?php $this->load->view('lehre/pruefungsprotokollUebersichtData.php'); ?>
			</div>
		</div>
	</div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
