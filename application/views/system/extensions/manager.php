<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			// Title
			'title' => 'Extensions manager',

			// JS & CSS includes
			'jquery' => true,
			'jqueryui' => true,
			'jquerycheckboxes' => true,
			'bootstrap' => true,

			// Styles includes
			'fontawesome' => true,
			'sbadmintemplate' => true,

			// FHC JS & CSS includes
			'ajaxlib' => true,
			'dialoglib' => true,
			'tabulator' => true,
			'tablewidget' => true,
			'phrases' => array(
				'extensions',
				'table',
				'ui'
			),
			'customJSs' => array('public/js/ExtensionsManager.js')
		)
	);
?>

	<body>
		<div class="container-fluid">
			<h3 class="page-header">
				<?php echo $this->p->t('extensions', 'title'); ?>
			</h3>

			<div>
				<?php $this->load->view('system/extensions/tableWidget.php'); ?>
			</div>

			<?php echo form_open_multipart(current_url().'/uploadExtension'); ?>
				<div class="row">
					<div class="col-xs-2">
						<input type="file" name="extension" />
					</div>
					<div>
						<input type="button" id="uploadExtension" value="<?php echo $this->p->t('extensions', 'uploadExtension'); ?>" />
					</div>
				</div>

				<br/>

				<div class="row">
					<div class="col-xs-2">
						<label for="notPerformSql"><?php echo $this->p->t('extensions', 'performSql'); ?></label>
					</div>
					<div>
						<input type="checkbox" class="checkbox" id="notPerformSql" name="notPerformSql"/>
					</div>
				</div>
			</form>
		</div>
	</body>

<?php $this->load->view('templates/FHC-Footer'); ?>
