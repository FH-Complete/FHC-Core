<?php $this->load->view("templates/header", array("title" => "UDF")); ?>

	<body>
	
		<div>
			UDFWidget: 
		</div>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => $schema,
						UDFWidgetTpl::TABLE_ARG_NAME => $table,
						UDFWidgetTpl::FIELD_ARG_NAME => $field,
						DropdownWidget::SELECTED_ELEMENT => $selected
					),
					array('id' => 'schuhgroesseID', 'name' => 'schuhgroesseName', 'size' => '6')
				);
			?>
		</div>
	
	</body>
	
<?php $this->load->view("templates/footer"); ?>