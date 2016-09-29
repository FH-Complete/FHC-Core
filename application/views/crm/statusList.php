<?php
	$this->load->view('templates/header', array('title' => 'StatusgrundList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '3:{sorter:false}'));
?>

		<div class="row">
			<div class="span4">
				<h2>Status</h2>
				<table id="t1" class="tablesorter">
					<thead>
						<tr>
							<th class='table-sortable:default'>Status</th>
							<th>beschreibung</th>
							<th>anmerkung</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($status as $s): ?>
							<tr>
								<td>
									<a href="listGrund/<?php echo $s->status_kurzbz; ?>" target="StatusgrundTop" onClick="parent.document.getElementById('StatusgrundBottom').src=''">
										<?php echo $s->status_kurzbz; ?>
									</a>
								</td>
								<td><?php echo $s->beschreibung; ?></td>
								<td><?php echo $s->anmerkung; ?></td>
								<td>
									<a href="editStatus/<?php echo $s->status_kurzbz; ?>" target="StatusgrundBottom" onClick="parent.document.getElementById('StatusgrundTop').src=''">
										Edit
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
