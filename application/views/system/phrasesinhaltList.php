<?php
	$this->load->view('templates/header', array('title' => 'PhrasenInhaltList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '5:{sorter:false}'));
?>

<div class="row">
  <div class="span4">
	<h2>Phrase Inhalt - <?php echo $phrase_id; ?></h2>
	<form method="post" action="../newtext" target="TemplatesBottom">
	  <input type="hidden" name="phrase_id" value="<?php echo $phrase_id; ?>"/>
	  <button type="submit">Neu</button>
	</form>

	<table id="t1" class="tablesorter">
	  <thead>
		<tr><th class='table-sortable:default'>ID</th>
			<th class='table-sortable:default'>Sprache</th>
			<th class='table-sortable:default'>OrgEinheit</th>
			<th class='table-sortable:default'>OrgForm</th>
			<th class='table-sortable:default'>Text</th>
			<th>Beschreibung</th>
			<th></th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach ($phrase_inhalt as $v): ?>
			<tr><td><a href="../edittext/<?php echo $v->phrase_inhalt_id; ?>" target="PhrasesBottom"><?php echo $v->phrase_inhalt_id; ?></a></td>
				<td><?php echo $v->sprache; ?></td>
				<td><?php echo $v->orgeinheit_kurzbz; ?></td>
				<td><?php echo $v->orgform_kurzbz; ?></td>
				<td><?php echo $v->text; ?></td>
				<td><?php echo $v->description; ?></td>
				<td><a href="../edittext/<?php echo $v->phrase_inhalt_id; ?>" target="PhrasesBottom">edit</a></td>
			</tr>
		<?php endforeach ?>
	  </tbody>
	</table>
  </div>
</div>

<?php
	$this->load->view('templates/footer');
?>
