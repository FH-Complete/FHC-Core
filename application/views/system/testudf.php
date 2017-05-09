<?php $this->load->view("templates/header", array("title" => "UDF")); ?>

	<body>
		
		
		<br/>
		<br/>
		<br/>
		<br/>
		<br/>
		<br/>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::UDFS_ARG_NAME => $udfs
					)
				);
			?>
		</div>
		
	</body>
	
<?php $this->load->view("templates/footer"); ?>