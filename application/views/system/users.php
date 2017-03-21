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
			
			<br>
			<br>
			<br>
			
			<?php
				if ($users != null)
				{
			?>
				<div>
					Assign to:<br>
					<select id="linkToGruppe" name="aufnahmegruppe">
						<?php foreach($gruppen as $v): ?>
							<?php
								$selected = '';
								if ($v->gruppe_kurzbz == $selectedGruppe)
								{
									$selected = 'selected';
								}
							?>
							<option value="<?php echo $v->gruppe_kurzbz; ?>" <?php echo $selected; ?>>
								<?php echo $v->beschreibung; ?>
							</option>
						<?php endforeach; ?>
					</select>
					<select id="linkToStufe" name="stufe">
						<?php foreach($stufen as $v): ?>
							<?php
								$selected = '';
								if ($v->stufe == $selectedStufe)
								{
									$selected = 'selected';
								}
							?>
							<option value="<?php echo $v->stufe; ?>" <?php echo $selected; ?>>
								<?php echo $v->beschreibung; ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>
			<?php
				}
			?>
			<br>
			<br>

			<div>
				<?php
					if ($users != null)
					{
				?>
						<table>
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th>Prestudent ID</th>
									<th>Person ID</th>
									<th>Vorname</th>
									<th>Nachname</th>
									<th>Geschlecht</th>
									<th>Studiengang</th>
									<th>OrgForm</th>
									<th>Studienplan</th>
									<th>Geburtsdatum</th>
									<th>Email</th>
									<th>Stufe</th>
									<th>Gruppe</th>
									<th>Punkte</th>
								</tr>
							</thead>
							<tbody>
				<?php
						for ($i = 0; $i < count($users); $i++)
						{
							$user = $users[$i];
							
							echo "<tr>";
							
							echo "<td>";
							echo '<input type="checkbox" name="prestudent_id[]" value="' . $user->prestudent_id . '">';
							echo "</td>";
							
							echo "<td>";
							echo $user->prestudent_id;
							echo "</td>";
							
							echo "<td>";
							echo $user->person_id;
							echo "</td>";
							
							echo "<td>";
							echo $user->vorname;
							echo "</td>";
							
							echo "<td>";
							echo $user->nachname;
							echo "</td>";
							
							echo "<td>";
							echo $user->geschlecht;
							echo "</td>";
							
							echo "<td>";
							echo $user->kurzbzlang;
							echo "</td>";
							
							echo "<td>";
							echo $user->orgform_kurzbz;
							echo "</td>";
							
							echo "<td>";
							echo $user->studienplan;
							echo "</td>";
							
							echo "<td>";
							echo $user->gebdatum;
							echo "</td>";
							
							echo "<td>";
							echo $user->email;
							echo "</td>";
							
							echo "<td>";
							echo $user->rt_stufe;
							echo "</td>";
							
							echo "<td>";
							echo $user->aufnahmegruppe_kurzbz;
							echo "</td>";
							
							echo "<td>";
							echo $user->punkte;
							echo "</td>";
							
							echo "</tr>";
						}
				?>
							</tbody>
						</table>
				<?php
					}
					else
					{
						echo 'No users found.';
					}
				?>
			</div>
		</form>

	<?php
		$hrefLinkToStufe = str_replace("/system/Users", "/system/Users/linkToStufe", $_SERVER["REQUEST_URI"]);
		$hrefLinkToAufnahmegruppe = str_replace("/system/Users", "/system/Users/linkToAufnahmegruppe", $_SERVER["REQUEST_URI"]);
	?>

	<script>

		$(document).ready(function() {
			if ($("#linkToStufe"))
			{
				$("#linkToStufe").change(function() {
					if ($("#linkUsersForm"))
					{
						$("#linkUsersForm").attr("action", "<?php echo $hrefLinkToStufe; ?>");
						$("#linkUsersForm").submit();
					}
				});
			}

			if ($("#linkToGruppe"))
			{
				$("#linkToGruppe").change(function() {
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
	</body>
<?php $this->load->view("templates/footer"); ?>