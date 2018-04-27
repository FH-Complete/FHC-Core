<?php
	$this->load->view('templates/header', array('title' => 'PhrasesList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '3:{sorter:false}'));
?>
<div class="row">
	<div class="span4">
	  <h2>Phrasen</h2>
<table id="t1" class="tablesorter">
	<thead>
		<tr><th class='table-sortable:default'>ID</th>
			<th>App</th>
			<th class='table-sortable:default'>Phrase</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($phrases as $p): ?>
		<tr><td><a href="edit/<?php echo $p->phrase_id; ?>" target="PhrasesBottom"><?php echo $p->phrase_id; ?></a></td>
			<td><?php echo $p->app; ?></td>
			<td><a href="edit/<?php echo $p->phrase_id; ?>" target="PhrasesBottom"><?php echo $p->phrase; ?></a></td>
			<td><a href="view/<?php echo $p->phrase_id; ?>" target="PhrasesTop">Phrasentexte bearbeiten</a></td>
		</tr>
	<?php endforeach ?>
	</tbody>
</table>
</div>
</div>
</body>
</html>
