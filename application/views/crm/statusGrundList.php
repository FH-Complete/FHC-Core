<?php
	$this->load->view('templates/header', array('title' => 'StatusgrundList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '4:{sorter:false}'));
?>
		<div class="row">
			<div class="span4">
				<a href="../newGrund/<?php echo $status_kurzbz; ?>" target="StatusgrundBottom">+ Neu Grund</a>
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
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($statusGrund as $s): ?>
							<tr>
								<td><a href="../editGrund/<?php echo $s->statusgrund_kurzbz; ?>" target="StatusgrundBottom"><?php echo $s->status_kurzbz; ?></a></td>
								<td><?php echo $s->aktiv; ?></td>
								<td><?php echo $s->bezeichnung_mehrsprachig; ?></td>
								<td><?php echo $s->beschreibung; ?></td>
								<td><a href="../editGrund/<?php echo $s->statusgrund_kurzbz; ?>" target="StatusgrundBottom">Edit</a></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>
