<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => $this->p->t('ui', 'antragStellen'),
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'ajaxlib' => true,
		'dialoglib' => true,
		'phrases' => array(
			'global' => array(
				'anerkennungNachgewiesenerKenntnisse',
				'antragStellen'
			),
			'ui' => array(
				'hilfeZuDieserSeite',
			)
		),
		'customJSs' => array(
			'public/js/bootstrapper.js',
			'public/js/lehre/anrechnung/Anrechnung.js')
	)
);
?>

<body>
<div id="page-wrapper">
	<div class="container-fluid">
        <!-- title -->
		<div class="row">
			<div class="col-lg-12 page-header">
				<a class="pull-right" data-toggle="collapse" href="#collapseHelp">
					<?php echo $this->p->t('ui', 'hilfeZuDieserSeite'); ?>
				</a>
				<h3>
					<?php echo $this->p->t('anrechnung', 'anerkennungNachgewiesenerKenntnisse'); ?>
                    <small>| <?php echo $this->p->t('anrechnung', 'antragStellen'); ?></small>
				</h3>
			</div>
		</div>
        <div class="row">
            <div class="col-xs-12">
                <div class="panel panel-default panel-body">
                    <!-- Antragsdaten, Dokument Upload, Notiz-->
                    <div class="row">
                        <div class="col-lg-6">
                            <!-- Antragsdaten -->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php echo $this->p->t('anrechnung', 'antragsdaten'); ?>
                                        </div>
                                        <table class="panel-body table table-bordered table-condensed">
                                            <tbody>
                                                <tr>
                                                    <td>StudentIn</td>
                                                    <td>Cristina Hainberger</td>
                                                </tr>
                                                <tr>
                                                    <td>StudentIn</td>
                                                    <td>Cristina Hainberger</td>
                                                </tr>
                                                <tr>
                                                    <td>StudentIn</td>
                                                    <td>Cristina Hainberger</td>
                                                </tr>
                                                <tr>
                                                    <td>StudentIn</td>
                                                    <td>Cristina Hainberger</td>
                                                </tr>
                                                <tr>
                                                    <td>StudentIn</td>
                                                    <td>Cristina Hainberger</td>
                                                </tr>
                                                <tr>
                                                    <td>StudentIn</td>
                                                    <td>Cristina Hainberger</td>
                                                </tr>
                                                <tr>
                                                    <td>StudentIn</td>
                                                    <td>Cristina Hainberger</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <!-- Dokument Upload-->
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php echo $this->p->t('anrechnung', 'dokumentZumNachweis'); ?>
                                        </div>
                                        <div class="form-inline panel-body">
                                            <?php echo form_open_multipart(current_url().'/uploadFile');?>
                                            <div class="form-group">
                                                <input type="file" class="" id="file" name="file" size="20" >
                                            </div>
                                            <button type="submit" class="btn btn-default pull-right" value="upload" />
                                                <?php echo $this->p->t('ui', 'hochladen'); ?>
                                            </button>
                                            <?php echo form_close();?>
                                        </div>
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <span class="pull-right"><i class="fa fa-times fa-lg" aria-hidden="true"></i></span>
                                                <a href="#">bla.pdf</a>
                                            </li>
                                            <li class="list-group-item">
                                                <span class="pull-right"><i class="fa fa-times fa-lg" aria-hidden="true"></i></span>
                                                <a href="#">bla.pdf</a>
                                            </li>                                </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Notiz -->
                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <?php echo $this->p->t('anrechnung', 'weitereInformationen'); ?>
                                        </div>
                                        <div class="panel-body">
                                            <textarea class="form-control" rows="15"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Antrag mit Checkboxen -->
                    <div class="row">
                            <div class="col-lg-6">
                                <div class="well">
                                    <p><b><?php echo $this->p->t('anrechnung', 'antragStellenText'); ?> </b></p>
                                    <div class="checkbox">
                                        <label>
                                            <input type="checkbox" value="">
                                            <b><?php echo $this->p->t('anrechnung', 'antragStellenWegenZeugnis'); ?></b>
                                        </label>
                                    </div>
                                    <div class="checkbox disabled">
                                        <label>
                                            <input type="checkbox" value="">
                                            <b><?php echo $this->p->t('anrechnung', 'antragStellenWegenPraxis'); ?></b>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
        </div>
        <!-- Submit button 'Anrechnung beantragen'-->
        <div class="row">
            <div class="col-xs-12">
                <button type="submit" role="button" class="btn btn-primary pull-right"
                        value=""><?php echo $this->p->t('anrechnung', 'anrechnungBeantragen'); ?>
            </div>
        </div>
    </div>
</div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
