<?php
$this->load->view('templates/header', array('title' => 'StudiensemesterList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '7:{sorter:false}, 8:{sorter:false}', 'sortList' => '4,1],[0,0'));
?>
<body>
<div class="row">
	<div class="span4">
		<h2>Studiensemester</h2>
		<a href="newStudiensemester/">
			Neues Studiensemester anlegen
		</a>
		<table id="t1" class="tablesorter">
			<thead>
			<tr>
				<th>Kurzbez</th>
				<th>Bezeichnung</th>
				<th>Start</th>
				<th>Ende</th>
				<th>Studienjahr</th>
				<th>Beschreibung</th>
				<th>Onlinebewerbung</th>
				<th></th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($semester as $sem): ?>
				<tr>
					<td><?php echo $sem->studiensemester_kurzbz; ?></td>
					<td><?php echo $sem->bezeichnung; ?></td>
					<td><?php echo date_format(date_create($sem->start), "d.m.Y"); ?></td>
					<td><?php echo date_format(date_create($sem->ende), "d.m.Y"); ?></td>
					<td><?php echo $sem->studienjahr_kurzbz; ?></td>
					<td><?php echo $sem->beschreibung; ?></td>
					<td><?php echo ($sem->onlinebewerbung) ? "Ja" : "Nein"; ?></td>
					<td>
						<a href="editStudiensemester/<?php echo $sem->studiensemester_kurzbz; ?>">
							Bearbeiten
						</a>
					</td>
					<td>
						<a href="deleteStudiensemester/<?php echo $sem->studiensemester_kurzbz; ?>">
							Löschen
						</a>
					</td>
				</tr>
			<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
</body>
</html>



