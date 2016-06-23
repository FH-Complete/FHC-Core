<?php
	$this->load->view('templates/header', array('title' => 'MessagesList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '4:{sorter:false}'));
?>
<div class="row">
  <div class="span4">
	<h2>Outbox <?php if (empty($person))
						echo $uid;
					else
						echo ' -> Person '.$person->person_id.' ('.$person->vorname.')';
		 ?><br /><br />
	<form method="post" action="inbox">
		<button type="submit">Inbox</button>
	</form></h2>

<form method="post" action="">
	Person <input name="person_id"></input>
	<button type="submit">show Mails</button> 
</form> 

<table id="t1" class="tablesorter">
	<thead>
		<tr><th>MessageID</th>
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
		<tr><td><a href="view/<?php echo $m->message_id; ?>" target="MessagesBottom"><?php echo $m->message_id; ?></a></td>
			<td><?php echo $m->titelpost.' '.$m->vorname.' '.$m->nachname.' '.$m->titelpre; ?></td>
			<td><?php echo $m->insertamum; ?></td>
			<td><?php echo $m->priority; ?></td>
			<td><?php echo $m->status; ?></td>
			<td><?php echo $m->statusinfo; ?></td>
			<td><?php echo $m->oe_kurzbz; ?></td>
			<td><?php echo $m->relationmessage_id; ?></td>
			<td><a href="view/<?php echo $m->message_id; ?>">View</a></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
</div>
</div>
</body>
</html>
