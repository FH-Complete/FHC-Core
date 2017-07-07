<?php $this->load->view("templates/header", array("title" => "UDF")); ?>
	
	<body>
		
		<form action="/core/index.ci.php/api/v1/system/UDF/UDF" method="POST">
		
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
			
			<div>
				<input type="submit" value="Save">
			</div>
			
			<input type="hidden" name="person_id" value="<?php echo $udfs['person_id']; ?>">
			<input type="hidden" name="caller" value="<?php echo $udfs['caller']; ?>">
<!-- 			<input type="hidden" name="prestudent_id" value="<?php echo $udfs['prestudent_id']; ?>"> -->
		</form>
		
	</body>
	
<?php $this->load->view("templates/footer"); ?>