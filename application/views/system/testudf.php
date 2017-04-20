<?php $this->load->view("templates/header", array("title" => "UDF")); ?>

	<body>
	
		<div>
			UDFWidget: 
		</div>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDF_widget_tpl::SCHEMA_ARG_NAME => $schema,
						UDF_widget_tpl::TABLE_ARG_NAME => $table,
						UDF_widget_tpl::FIELD_ARG_NAME => $field
					),
					array('name' => 'schuhgroesse', 'id' => 'schuhgroesseUDF')
				);
			?>
		</div>
	
	</body>
	
<?php $this->load->view("templates/footer"); ?>