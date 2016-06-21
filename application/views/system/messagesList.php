<?php
	$this->load->view('templates/header', array('title' => 'MessagesList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '4:{sorter:false}'));
?>
<div class="row">
	<div class="span4">
	  <h2>Vorlagen</h2>
<form method="post" action="">
Person
<input name="person_id"></input>
 <button type="submit">Filter</button> 
</form> 

<table id="t1" class="tablesorter">
	<thead>
		<tr><th class='table-sortable:default'>Vorlage</th>
			<th class='table-sortable:default'>Bezeichnung</th>
			<th>Anmerkung</th><th>MimeType</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($vorlage as $v): ?>
		<tr><td><a href="edit/<?php echo $v->vorlage_kurzbz; ?>" target="MessagesBottom"><?php echo $v->vorlage_kurzbz; ?></a></td>
			<td><?php echo $v->bezeichnung; ?></td>
			<td><?php echo $v->anmerkung; ?></td>
			<td><?php echo $v->mimetype; ?></td>
			<td><a href="view/<?php echo $v->vorlage_kurzbz; ?>">View</a></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
</div>
</div>
</body>
</html>
