<?php
$this->load->view('templates/header', array('title' => 'StudienjahrList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '2:{sorter:false}, 3:{sorter:false}', 'sortList' => '0,1'));
?>
<body>
<div class="row">
	<div class="span4">
		<h2>Studienjahr</h2>
		<a href="newStudienjahr/">
			Neues Studienjahr anlegen
		</a>
		<table id="t1" class="tablesorter">
			<thead>
			<tr>
				<th>Kurzbez</th>
				<th>Bezeichnung</th>
				<th></th>
				<th></th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($studienjahr as $jahr):
				$escapedstudienjahrkurzbz = str_replace("/", "_", $jahr->studienjahr_kurzbz);
				?>
				<tr>
					<td><?php echo $jahr->studienjahr_kurzbz; ?></td>
					<td><?php echo $jahr->bezeichnung; ?></td>
					<td>
						<a href="editStudienjahr/<?php echo $escapedstudienjahrkurzbz; ?>">
							Bearbeiten
						</a>
					</td>
					<td>
						<a href="deleteStudienjahr/<?php echo $escapedstudienjahrkurzbz; ?>">
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



