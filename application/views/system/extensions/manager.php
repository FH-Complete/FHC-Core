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
				'extensions'
			),
			'customJSs' => array('public/js/ExtensionsManager.js')
		)
	);
?>

	<body>
		<div>
			<h3 class="page-header">
				<?php echo $this->p->t('extensions', 'title'); ?>
			</h3>

			<div>
				<?php $this->load->view('system/extensions/tableWidget.php'); ?>
			</div>

			<?php echo form_open_multipart(current_url().'/uploadExtension'); ?>
				<div>
					<input type="file" name="extension" />
					<input type="button" id="uploadExtension" value="<?php echo $this->p->t('extensions', 'uploadExtension'); ?>" />
				</div>

				<br/>

				<div>
					<label for="performSql"><?php echo $this->p->t('extensions', 'performSql'); ?></label>
					<input type="checkbox" class="checkbox" id="performSql" name="performSql"/>
				</div>
			</form>
		</div>
	</body>

<?php $this->load->view('templates/FHC-Footer'); ?>

