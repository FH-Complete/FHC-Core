<?php $this->load->view("templates/header", array("title" => "MessageReply", "jquery" => true, "tinymce" => true)); ?>

	<body>
		
		<div>
			<form id="usersFiltersForm" action="" method="post">
				<?php
					echo $this->templatelib->widget(
						'Usersfilters_widget',
						array(
							'studiengang' => $studiengang,
							'studiensemester' => $studiensemester,
							'gruppe' => $gruppe,
							'reihungstest' => $reihungstest,
							'stufe' => $stufe
						)
					);
				?>
			</form>
		</div>
		
		<form id="linkUsersForm" action="" method="post">
			<div>
				Stufe: <input type="text" name="stufe" value=""> <button type="button" id="linkToStufe">To step</button>
			</div>
			<div>
				Gruppe: <input type="text" name="aufnahmegruppe" value=""> <button type="button" id="linkToGruppe">To group</button>
			</div>
			
			<br>
			<br>
			
			<div>
				<?php
					if ($users != null)
					{
						for ($i = 0; $i < count($users); $i++)
						{
							$user = $users[$i];
							
							echo '<input type="checkbox" name="prestudent_id[]" value="' . $user->prestudent_id . '"> - ';
							echo $user->prestudent_id . ' - ';
							echo $user->vorname . ' - ';
							echo $user->nachname;
							echo '<br>';
						}
					}
					else
					{
						echo 'No data found!!!';
					}
				?>
			</div>
		</form>
		
	</body>
	
	<?php
		$hrefLinkToStufe = str_replace("/system/Users", "/system/Users/linkToStufe", $_SERVER["REQUEST_URI"]);
		$hrefLinkToAufnahmegruppe = str_replace("/system/Users", "/system/Users/linkToAufnahmegruppe", $_SERVER["REQUEST_URI"]);
	?>
	
	<script>
		
		$(document).ready(function() {
			if ($("#linkToStufe"))
			{
				$("#linkToStufe").click(function() {
					if ($("#linkUsersForm"))
					{
						$("#linkUsersForm").attr("action", "<?php echo $hrefLinkToStufe; ?>");
						$("#linkUsersForm").submit();
					}
				});
			}
			
			if ($("#linkToGruppe"))
			{
				$("#linkToGruppe").click(function() {
					if ($("#linkUsersForm"))
					{
						$("#linkUsersForm").attr("action", "<?php echo $hrefLinkToAufnahmegruppe; ?>");
						$("#linkUsersForm").submit();
					}
				});
			}
		});
		
		function studiengangSelected(value)
		{
			submitUsersFiltersForm();
		}
		
		function studiensemesterSelected(value)
		{
			submitUsersFiltersForm();
		}
		
		function reihungstestSelected(value)
		{
			submitUsersFiltersForm();
		}
		
		function gruppeSelected(value)
		{
			submitUsersFiltersForm();
		}
		
		function stufeSelected(value)
		{
			submitUsersFiltersForm();
		}
		
		function submitUsersFiltersForm()
		{
			document.getElementById("usersFiltersForm").submit();
		}
	</script>

</html>