<?php
	$this->load->view('templates/header', array('title' => 'StatusgrundList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '4:{sorter:false}'));
?>
		<div class="row">
			<div class="span4">
				<a href="../newGrund/<?php echo $status_kurzbz; ?>" target="StatusgrundBottom">+ Neuen Statusgrund hinzufügen</a>
			</div>
		</div>
		<div class="row">
			<div class="span4">
				<table id="t1" class="tablesorter">
					<thead>
						<tr>
							<th class='table-sortable:default'>ID</th>
							<th>Aktiv</th>
							<th>Bezeichnung mehrsprachig</th>
							<th>Beschreibung</th>
							<th>Statusgrund</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($statusGrund as $s): ?>
							<tr>
								<td><a href="../editGrund/<?php echo $s->statusgrund_id; ?>" target="StatusgrundBottom"><?php echo $s->status_kurzbz; ?></a></td>
								<td><?php echo json_encode($s->aktiv); ?></td>
								<td><?php echo json_encode($s->bezeichnung_mehrsprachig); ?></td>
								<td><?php echo json_encode($s->beschreibung); ?></td>
								<td><?php echo json_encode($s->statusgrund_kurzbz); ?></td>
								<td><a href="../editGrund/<?php echo $s->statusgrund_id; ?>" target="StatusgrundBottom">Edit</a></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>
