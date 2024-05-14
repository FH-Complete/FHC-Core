<?php
$this->load->view(
    'templates/FHC-Header',
    array(
        'title' => 'Lehrauftrag bestellen',
        'jquery3' => true,
        'jqueryui1' => true,
        'bootstrap3' => true,
        'fontawesome4' => true,
        'sbadmintemplate3' => true,
        'ajaxlib' => true,
        'dialoglib' => true,
        'navigationwidget' => true,
		'addons' => true,
    )
);
?>

<body>
    <?php echo $this->widgetlib->widget('NavigationWidget'); ?>
    <div id="page-wrapper">
        <div class="container-fluid">

        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">
                    Lehrauftrag
                </h3>
            </div>
        </div>

        <div id="dashboard"></div>

        </div>
    </div>
</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
