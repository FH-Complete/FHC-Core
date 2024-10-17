<?php
	$this->load->view('templates/header', array('title' => 'PhrasenInhaltList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '5:{sorter:false}'));
?>

<div class="row">
  <div class="span4">
	<h2>Phrase Inhalt - <?php echo $phrase; ?></h2>
	<form method="post" action="../newText" target="PhrasesBottom">
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
			<th></th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach ($phrase_inhalt as $v): ?>
			<tr><td><a href="../editText/<?php echo $v->phrasentext_id; ?>" target="PhrasesBottom"><?php echo $v->phrasentext_id; ?></a></td>
				<td><?php echo $v->sprache; ?></td>
				<td><?php echo $v->orgeinheit_kurzbz; ?></td>
				<td><?php echo $v->orgform_kurzbz; ?></td>
				<td><?php echo $v->text; ?></td>
				<td><?php echo $v->description; ?></td>
				<td><a href="../editText/<?php echo $v->phrasentext_id; ?>" target="PhrasesBottom">edit</a></td>
				<td>
					<a href="javascript:void(0);" onclick="delPhrasentext(<?php echo $v->phrasentext_id; ?>, <?php echo $phrase_id; ?>)">delete</a>
				</td>
			</tr>
		<?php endforeach ?>
	  </tbody>
	</table>
  </div>
</div>
<script>
function delPhrasentext(id,pid)
{
	var c = confirm("Wirklich löschen?");
	if (c == true)
		window.location.href = "../deltext/"+id+"/"+pid;
}
</script>

<?php
	$this->load->view('templates/footer');
?>
