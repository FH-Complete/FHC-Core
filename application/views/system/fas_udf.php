<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'InfocenterDetails',
			'jquery' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'jqueryui' => true,
			'dialoglib' => true,
			'ajaxlib' => true,
			'udfs' => true,
			'widgets' => true,
			'sbadmintemplate' => true,
			'customCSSs' => array(
				'public/css/sbadmin2/admintemplate.css'
			),
			'customJSs' => array(
				'public/js/bootstrapper.js'
			)
		)
	);
?>

	<body style="background-color: #eff0f1;">

			<div class="div-table">
				<div class="div-row">
					<div class="div-cell" style="font-size: 20px; font-weight: bold;">
						Zusatzfelder
					</div>
				</div>
				<div class="div-row">
					<div class="div-cell">
						&nbsp;
					</div>
				</div>
				<div class="div-row">
				<?php
					if (isset($personUdfs))
					{
				?>
					<div class="div-cell">
						<?php
							echo $this->udflib->UDFWidget(
								array(
									UDFLib::UDF_UNIQUE_ID => 'fasPersonUDFs',
									UDFLib::REQUIRED_PERMISSIONS_PARAMETER => 'basis/person',
									UDFLib::SCHEMA_ARG_NAME => 'public',
									UDFLib::TABLE_ARG_NAME => 'tbl_person',
									UDFLib::PRIMARY_KEY_NAME => 'person_id',
									UDFLib::PRIMARY_KEY_VALUE => $person_id,
									UDFLib::UDFS_ARG_NAME => $personUdfs
								)
							);
						?>
					</div>
					<div class="div-cell" style="width: 40px;">
						&nbsp;
					</div>
				<?php
					}
				?>
				<?php
					if (isset($prestudentUdfs))
					{
				?>
					<div class="div-cell">
						<?php
							echo $this->udflib->UDFWidget(
								array(
									UDFLib::UDF_UNIQUE_ID => 'fasPrestudentUDFs',
									UDFLib::REQUIRED_PERMISSIONS_PARAMETER => 'basis/person',
									UDFLib::SCHEMA_ARG_NAME => 'public',
									UDFLib::TABLE_ARG_NAME => 'tbl_prestudent',
									UDFLib::PRIMARY_KEY_NAME => 'prestudent_id',
									UDFLib::PRIMARY_KEY_VALUE => $prestudent_id,
									UDFLib::UDFS_ARG_NAME => $prestudentUdfs
								)
							);
						?>
					</div>
				<?php
					}
				?>
				</div>
				<div class="div-row">
					<div class="div-cell">
						&nbsp;
					</div>
				</div>
				<div class="div-row halign-right">
				<?php
					if (isset($personUdfs) && isset($prestudentUdfs))
					{
				?>
					<div class="div-cell">
						&nbsp;
					</div>
					<div class="div-cell">
						&nbsp;
					</div>
				<?php
					}
				?>
				</div>
			</div>

	</body>

<?php $this->load->view("templates/footer"); ?>
