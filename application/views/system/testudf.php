<?php $this->load->view("templates/header", array("title" => "UDF")); ?>

	<body>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::FIELD_ARG_NAME => 'udf_schuhgroesse',
						DropdownWidget::SELECTED_ELEMENT => $udfs['udf_schuhgroesse']
					),
					array('name' => 'schuhgroesseName', 'id' => 'schuhgroesseId')
				);
			?>
		</div>
		
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