<?php $this->load->view('templates/header', array('title' => 'MessagesList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '4:{sorter:false}')); ?>

		<div class="row">
			<div class="span4">
				<h2>
					Outbox <?php echo $person->vorname . " " . $person->nachname; ?>
					<br />
					<br />
					<button type="submit" onClick="parent.document.getElementById('MessagesTop').src = 'Messages/inbox/<?php echo $person->person_id; ?>'">
						Inbox
					</button>
					<br />
					<br />
				</h2>
				<form method="post" action="">
					Person <input name="person_id"></input>
					<button type="submit">show Mails</button> 
				</form>
				<table id="t1" class="tablesorter">
					<thead>
						<tr>
							<th>MessageID</th>
							<th class='table-sortable:default'>Sender</th>
							<th>Erstellt</th>
							<th>Priorität</th>
							<th>Status</th>
							<th>StatusInfo</th>
							<th>OE</th>
							<th>Relation</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($messages as $m): ?>
							<?php
								$href = str_replace("/system/Messages/outbox", "/system/Messages/view", $_SERVER["REQUEST_URI"]);
								$href = substr($href, 0, strrpos($href, "/") - strlen($href));
								$href .= "/" . $m->message_id . "/" . $person->person_id;
							?>
							<tr>
								<td><a href="<?php echo $href; ?>" target="MessagesBottom"><?php echo $m->message_id; ?></a></td>
								<td><?php echo $m->titelpost.' '.$m->vorname.' '.$m->nachname.' '.$m->titelpre; ?></td>
								<td><?php echo $m->insertamum; ?></td>
								<td><?php echo $m->priority; ?></td>
								<td><?php echo $m->status; ?></td>
								<td><?php echo $m->statusinfo; ?></td>
								<td><?php echo $m->oe_kurzbz; ?></td>
								<td><?php echo $m->relationmessage_id; ?></td>
								<td><a href="<?php echo $href; ?>" target="MessagesBottom">View</a></td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	</body>
</html>
