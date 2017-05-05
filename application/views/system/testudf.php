<?php $this->load->view("templates/header", array("title" => "UDF")); ?>

	<body>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::FIELD_ARG_NAME => 'schuhgroesse',
						DropdownWidget::SELECTED_ELEMENT => array(42, 44)
					),
					array('id' => 'schuhgroesseID', 'name' => 'schuhgroesseName', 'size' => '9')
				);
			?>
		</div>
		
		<br/>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::FIELD_ARG_NAME => 'headSize'
					),
					array('id' => 'headSizeID', 'name' => 'headSizeName')
				);
			?>
		</div>
		
		<br/>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::FIELD_ARG_NAME => 'bellySize'
					),
					array('id' => 'bellySizeID', 'name' => 'bellySizeName')
				);
			?>
		</div>
		
		<br/>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::FIELD_ARG_NAME => 'nickname'
					),
					array('id' => 'nicknameID', 'name' => 'nicknameName')
				);
			?>
		</div>
		
		<br/>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::FIELD_ARG_NAME => 'age'
					),
					array('id' => 'ageID', 'name' => 'ageName')
				);
			?>
		</div>
		
		<br/>
		
		<div>
			<?php
				echo $this->widgetlib->UDFWidget(
					array(
						UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
						UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
						UDFWidgetTpl::FIELD_ARG_NAME => 'agree'
					),
					array('id' => 'agreeID', 'name' => 'agreeName')
				);
			?>
		</div>
		
	</body>
	
<?php $this->load->view("templates/footer"); ?>