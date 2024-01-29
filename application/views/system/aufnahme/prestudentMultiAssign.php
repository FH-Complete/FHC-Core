<?php $this->load->view("templates/header", array("title" => "Users manager", "jqueryV1" => true, "tablesort" => true, "jquery_checkboxes" => true, "jquery_custom" => true)); ?>

	<body>
		<form id="usersFiltersForm" action="" method="post">
			<table>
				<tr>
					<td>
						<?php
							echo $this->widgetlib->widget(
								'Studiengang_widget',
								array(DropdownWidget::SELECTED_ELEMENT => $studiengang),
								array('name' => 'studiengang', 'id' => 'studiengangFilter')
							);
						?>
					</td>
					<td>
						<?php
							echo $this->widgetlib->widget(
								'Studiensemester_widget',
								array(DropdownWidget::SELECTED_ELEMENT => $studiensemester),
								array('name' => 'studiensemester', 'id' => 'studiensemesterFilter')
							);
						?>
					</td>
					<td>
						<?php
							echo $this->widgetlib->widget(
								'Reihungstest_widget',
								array(
									DropdownWidget::SELECTED_ELEMENT => $reihungstest,
									'studiengang' => $studiengang,
									'studiensemester' => $studiensemester
								),
								array('name' => 'reihungstest', 'id' => 'reihungstestFilter')
							);
						?>
					</td>
					<td>
						<?php
							echo $this->widgetlib->widget(
								'Aufnahmegruppe_widget',
								array(DropdownWidget::SELECTED_ELEMENT => $aufnahmegruppe),
								array('name' => 'aufnahmegruppe', 'id' => 'aufnahmegruppeFilter')
							);
						?>
					</td>
					<td>
						<?php
							echo $this->widgetlib->widget(
								'Stufe_widget',
								array(DropdownWidget::SELECTED_ELEMENT => $stufe),
								array('name' => 'stufe', 'id' => 'stufeFilter')
							);
						?>
					</td>
				</tr>
			</table>
		</form>

		<br>

		<form id="linkUsersForm" action="" method="post">
			<?php
				if ($users != null)
				{
			?>
				<table>
					<tr>
						<td colspan="2">
							<strong>Assign to:</strong>
						</td>
					</tr>
					<tr>
						<td height="3px" colspan="2"></td>
					</tr>
					<tr>
						<td>
							<?php
								echo $this->widgetlib->widget(
									'Stufe_widget',
									array('stufe' => $stufe),
									array('name' => 'stufe', 'id' => 'stufeAssign')
								);
							?>
						</td>
						<td>&nbsp;</td>
						<td>
							<input type="button" id="linkToStufe" value="Assign this stufe">
						</td>
					</tr>
					<tr>
						<td>
							<?php
								echo $this->widgetlib->widget(
									'Aufnahmegruppe_widget',
									array('aufnahmegruppe' => $aufnahmegruppe),
									array('name' => 'aufnahmegruppe', 'id' => 'aufnahmegruppeAssign')
								);
							?>
						<td>&nbsp;</td>
						<td>
							<input type="button" id="linkToGruppe" value="Assign to this group">
						</td>
					</tr>
				</table>
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
						<table id="t0" class="tablesorter">
							<thead>
								<tr>
									<th>&nbsp;</th>
									<th class="clm_prestudent_id header">Prestudent ID</th>
									<th class="clm_person_id header">Person ID</th>
									<th class="header headerSortDown">Vorname</th>
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
				?>
							<tr>
								<td>
									<input type="checkbox" name="prestudent_id[]" value="<?php echo $user->prestudent_id ?>">
								</td>
								<td>
									<?php echo $user->prestudent_id; ?>
								</td>
								<td>
									<?php echo $user->person_id; ?>
								</td>
								<td>
									<?php echo $user->vorname; ?>
								</td>
								<td>
									<?php echo $user->nachname; ?>
								</td>
								<td>
									<?php echo $user->geschlecht; ?>
								</td>
								<td>
									<?php echo $user->kurzbzlang; ?>
								</td>
								<td>
									<?php echo $user->orgform_kurzbz; ?>
								</td>
								<td>
									<?php echo $user->studienplan; ?>
								</td>
								<td>
									<?php echo $user->gebdatum; ?>
								</td>
								<td>
									<?php echo $user->email; ?>
								</td>
								<td>
									<?php echo $user->rt_stufe; ?>
								</td>
								<td>
									<?php echo $user->aufnahmegruppe_kurzbz; ?>
								</td>
								<td>
									<?php echo $user->punkte; ?>
								</td>
							</tr>
				<?php
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
			$hrefLinkToStufe = str_replace("/system/aufnahme/PrestudentMultiAssign", "/system/aufnahme/PrestudentMultiAssign/linkToStufe", $_SERVER["REQUEST_URI"]);
			$hrefLinkToAufnahmegruppe = str_replace("/system/aufnahme/PrestudentMultiAssign", "/system/aufnahme/PrestudentMultiAssign/linkToAufnahmegruppe", $_SERVER["REQUEST_URI"]);
		?>

		<script>

			$(document).ready(function() {
				if ($("#linkToStufe"))
				{
					$("#linkToStufe").click(function() {
						$.ajax({
							type: "POST",
							dataType: "json",
							url: "<?php echo $hrefLinkToStufe; ?>",
							data: $("#linkUsersForm").serialize(),
							success: function(data, textStatus, jqXHR) {
								alert(data.msg);
								$("#usersFiltersForm").submit();
							},
							error: function(jqXHR, textStatus, errorThrown) {
								alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
							}
						});
					});
				}

				if ($("#linkToGruppe"))
				{
					$("#linkToGruppe").click(function() {
						$.ajax({
							type: "POST",
							dataType: "json",
							url: "<?php echo $hrefLinkToAufnahmegruppe; ?>",
							data: $("#linkUsersForm").serialize(),
							success: function(data, textStatus, jqXHR) {
								alert(data.msg);
								$("#usersFiltersForm").submit();
							},
							error: function(jqXHR, textStatus, errorThrown) {
								alert(textStatus + " - " + errorThrown + " - " + jqXHR.responseText);
							}
						});
					});
				}

				if ($('#studiengangFilter'))
				{
					$('#studiengangFilter').change(function() {
						$('#usersFiltersForm').submit();
					});
				}

				if ($('#studiensemesterFilter'))
				{
					$('#studiensemesterFilter').change(function() {
						$('#usersFiltersForm').submit();
					});
				}

				if ($('#aufnahmegruppeFilter'))
				{
					$('#aufnahmegruppeFilter').change(function() {
						$('#usersFiltersForm').submit();
					});
				}

				if ($('#stufeFilter'))
				{
					$('#stufeFilter').change(function() {
						$('#usersFiltersForm').submit();
					});
				}

				if ($('#reihungstestFilter'))
				{
					$('#reihungstestFilter').change(function() {
						$('#usersFiltersForm').submit();
					});
				}

				$(".tablesorter").each(function(i, v) {
					$("#"+v.id).tablesorter(
					{
						widgets: ["zebra"],
						sortList: [[3,0],[4,0]],
						headers: {0: { sorter: false}}
					});

					$("#toggle_"+v.id).on('click', function(e) {
						$("#"+v.id).checkboxes('toggle');
						e.preventDefault();
						if ($("input.chkbox:checked").size() > 0)
							$("#mailSendButton").html('Mail an markierte Personen senden');
						else
							$("#mailSendButton").html('Mail an alle senden');
					});

					$("#uncheck_"+v.id).on('click', function(e) {
						$("#"+v.id).checkboxes('uncheck');
						e.preventDefault();
						if ($("input.chkbox:checked").size() > 0)
							$("#mailSendButton").html('Mail an markierte Personen senden');
						else
							$("#mailSendButton").html('Mail an alle senden');
					});

					$("#"+v.id).checkboxes('range', true);
				});

				$('.chkbox').change(function()
				{
					if ($("input.chkbox:checked").size() > 0)
						$("#mailSendButton").html('Mail an markierte Personen senden');
					else
						$("#mailSendButton").html('Mail an alle senden');
				});
			});

		</script>

	</body>
<?php $this->load->view("templates/footer"); ?>
