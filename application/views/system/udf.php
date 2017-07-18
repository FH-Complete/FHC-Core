<?php $this->load->view("templates/header", array("title" => "UDF")); ?>
	
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
		foreach($errors as $error)
		{
			foreach($error as $fieldError)
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
		
		<form action="/core/index.ci.php/system/UDF/saveUDF" method="POST">
		
			<table>
				<tr>
					<td>
						Person
					</td>
					<td width="30px">
						&nbsp;
					</td>
					<td>
						Prestudent
					</td>
				</tr>
				<tr>
					<td colspan="3">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td>
						<?php
							echo $this->widgetlib->UDFWidget(
								array(
									UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
									UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_person',
									UDFWidgetTpl::UDFS_ARG_NAME => $personUdfs
								)
							);
						?>
					</td>
					<td width="30px">
						&nbsp;
					</td>
					<td>
						<?php
							echo $this->widgetlib->UDFWidget(
								array(
									UDFWidgetTpl::SCHEMA_ARG_NAME => 'public',
									UDFWidgetTpl::TABLE_ARG_NAME => 'tbl_prestudent',
									UDFWidgetTpl::UDFS_ARG_NAME => $prestudentUdfs
								)
							);
						?>
					</td>
				</tr>
				<tr>
					<td colspan="3">
						&nbsp;
					</td>
				</tr>
				<tr>
					<td colspan="3" align="center">
						<input type="submit" value="Save">
					</td>
				</tr>
			</table>
			
			<input type="hidden" name="person_id" value="<?php echo $personUdfs['person_id']; ?>">
			<input type="hidden" name="prestudent_id" value="<?php echo $prestudentUdfs['prestudent_id']; ?>">
		
		</form>
		
	</body>
	
<?php $this->load->view("templates/footer"); ?>