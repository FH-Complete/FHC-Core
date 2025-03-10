<br>
<div class="row"  id="divTableWidgetDataset" tableUniqueId="<?php echo $tableUniqueId; ?>">
	<div  class="col-lg-12">
	
		<!-- Table widget header -->
		<div id="tableWidgetHeader"></div>

		<!-- TableWidget help site ( only rendered if widget is Tabulator )-->
		<?php $this->load->view('widgets/table/tableHelpsite') ?>

		<!-- Table info top -->
		<div id="tableDatasetActionsTop"></div>

		<!-- TableWidget table -->
		<div style="border:1px solid lightgrey; border-bottom:none; ">
		<?php TableWidget::loadViewDataset(); ?>
		</div>

		<!-- Table info bottom -->
		<div id="tableDatasetActionsBottom"></div>

		<!-- Table widget footer  -->
		<div id="tableWidgetFooter"></div>
		</div>
	</div>