<?php $this->load->view("templates/header", array("title" => "UDF", "widgetsCSS" => true)); ?>
	
	<body style="background-color: #eff0f1;">
	
<?php
	if ($result != null)
	{
		if (isSuccess($result))
		{
?>
		<div style="color: black;">
			Saved!
		</div>
		
		<br>
<?php
		}
		else
		{
?>
		<div style="color: red;">
			Error while saving!
		</div>
		<br>
		<div style="color: red;">
<?php
		$errors = $result->retval;
		foreach ($errors as $error)
		{
			foreach ($error as $fieldError)
			{
				echo $fieldError->msg . ' -> ' . $fieldError->retval . '<br>';
			}
		}
?>
		</div>
		
		<br>
		<br>
		<br>
<?php
		}
	}
?>
		<form action="<?php echo APP_ROOT; ?>index.ci.php/system/UDF/saveUDF" method="POST">
		
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
							echo $this->widgetlib->UDFWidget(
								array(
									UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
									UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
									UDFWidgetTpl::UDFS_ARG_NAME => $personUdfs
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
							echo $this->widgetlib->UDFWidget(
								array(
									UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
									UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_prestudent',
									UDFWidgetTpl::UDFS_ARG_NAME => $prestudentUdfs
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
					<div class="div-cell halign-right">
						<input type="submit" value="&nbsp;Speichern&nbsp;">
					</div>
				</div>
			</div>
			
		<?php
			if (isset($personUdfs))
			{
		?>
			<input type="hidden" name="person_id" value="<?php echo $personUdfs['person_id']; ?>">
		<?php
			}
		?>
		<?php
			if (isset($prestudentUdfs))
			{
		?>
			<input type="hidden" name="prestudent_id" value="<?php echo $prestudentUdfs['prestudent_id']; ?>">
		<?php
			}
		?>
		
		</form>
		
	</body>
	
<?php $this->load->view("templates/footer"); ?>