<?php $this->load->view('templates/header', array('title' => 'Extensions manager', 'jqueryV1' => true, 'tablesort' => true)); ?>

	<script type="text/javascript">

		$(document).ready(function() {

			$(".tablesorter").each(function(i, v) {

				$("#" + v.id).tablesorter({
					widgets: ["zebra"],
					sortList: [[0, 0]],
					headers: {7: {sorter: false}}
				});

			});

			$(".chkbox").change(function() {

				var chkId = $(this).context.name;
				var checked = $(this).prop("checked");

				$.ajax({
					url: "<?php echo current_url(); ?>/toggleExtension",
					dataType: "json",
					type: "POST",
					data: {
						extension_id: chkId,
						enabled: checked
					},
					error: function(jqXHR, textStatus, errorThrown) {

						alert("Error while saving data");
					},
					success: function(response, textStatus, jqXHR) {
						if (response != true)
						{
							alert("Error while saving data");
						}
					},
					complete: function() {
						window.location.reload();
					}
				});

			});

			$(".lnkrm").click(function() {

				var lnkId = $(this).context.name;

				if (window.confirm('Are you really shure???') == true)
				{
					$.ajax({
						url: "<?php echo current_url(); ?>/delExtension",
						dataType: "json",
						type: "POST",
						data: {
							extension_id: lnkId
						},
						error: function(jqXHR, textStatus, errorThrown) {
							alert("Error while saving data");
						},
						success: function(response, textStatus, jqXHR) {
							if (response != true)
							{
								alert("Error while saving data");
							}
						},
						complete: function() {
							window.location.reload();
						}
					});
				}

			});

		});

	</script>

	<body>

		<?php
			if (!hasData($extensions))
			{
				echo 'No extension installed!<br>';
			}
			elseif (isError($extensions))
			{
				echo 'An error occurred while retriving extenions list.';
			}
			elseif (hasData($extensions))
			{
		?>
				<div>
					List of installed extensions
				</div>

				<br>

				<table class="tablesorter" id="t0">
					<thead>
						<tr>
							<th>Name</th>
							<th>Description</th>
							<th>Version</th>
							<th>Licence</th>
							<th>URL</th>
							<th>Minimum required Core version</th>
							<th>Dependes on (extensions)</th>
							<th>Enabled</th>
							<th>&nbsp;</th>
						</tr>
					</thead>
					<tbody>

						<?php
							$tableRow = '
							<tr>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>%s</td>
								<td>
									<input type="checkbox" class="chkbox" name="%s" %s>
								</td>
								<td>
									<a href="#" name="%s" class="lnkrm" >Remove</a>
								</td>
							</tr>';

							foreach ($extensions->retval as $key => $extension)
							{
								echo sprintf(
									$tableRow,
									$extension->name,
									$extension->description,
									$extension->version,
									$extension->license,
									$extension->url,
									$extension->core_version,
									count($extension->dependencies) == 0 ? 'None' : json_encode($extension->dependencies),
									$extension->extension_id,
									$extension->enabled === true ? 'checked' : '',
									$extension->extension_id
								);
							}
						?>

					</tbody>
				</table>
		<?php
			}
		?>

		<br>

		<?php echo form_open_multipart(current_url().'/uploadExtension'); ?>
			<input type="file" name="extension" />
			<input type="submit" value="Install/Update extension" />
		</form>

	</body>

<?php $this->load->view('templates/footer'); ?>
