<script type="text/javascript" src="../../vendor/jquery/jqueryV1/jquery-1.12.4.min.js"></script>

<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
<script type="text/javascript" src="../../vendor/jquery/sizzle/sizzle.js"></script>

<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
<link href="../../skin/jquery-ui-1.9.2.custom.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>

<script>
	$(document).ready(function() {

		$("#t0").tablesorter({
			widgets: ["zebra"]
		});

	});
</script>

<div>
	<table class="tablesorter" id="t0">
		<thead>
			<tr>
				<th title="PersonId">PersonId</th>
				<th title="Nachname">Nachname</th>
				<th title="Vorname">Vorname</th>
				<th title="Email">Email</th>
				<th title="Aktiv">Aktiv</th>
			</tr>
		</thead>
		<tbody>
			<?php
				$result = $dataset->retval;
				foreach ($result as $key => $value)
				{
			?>
					<tr>
						<td><?php echo $value->PersonId; ?></td>
						<td><?php echo $value->Nachname; ?></td>
						<td><?php echo $value->Vorname; ?></td>
						<td><?php echo $value->Email; ?></td>
						<td><?php echo $value->Aktiv === true ? 'True' : 'False'; ?></td>
					</tr>
			<?php
				}
			?>
		</tbody>
	</table>
</div>
