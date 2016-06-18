<?php
	$this->load->view('templates/header', array('title' => 'TemplatetextList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '7:{sorter:false}'));
?>

<div class="row">
  <div class="span4">
	<h2>Vorlagentext - <?php echo $vorlage_kurzbz; ?></h2>
	<form method="post" action="../newtext" target="TemplatesBottom">
	  <input type="hidden" name="vorlage_kurzbz" value="<?php echo $vorlage_kurzbz; ?>"/>
	  <button type="submit">Neu</button> 
	</form> 

	<table id="t1" class="tablesorter">
	  <thead>
		<tr><th class='table-sortable:default'>ID</th>
			<th class='table-sortable:default'>Vorlage</th>
			<th class='table-sortable:default'>Version</th>
			<th class='table-sortable:default'>OrgEinheit</th>
			<th class='table-sortable:default'>OrgForm</th>
			<th class='table-sortable:default'>Berechtigung</th>
			<th>Anmerkung</th><th>Aktiv</th>
			<th></th>
		</tr>
	  </thead>
	  <tbody>
		<?php foreach ($vorlagentext as $v): ?>
			<tr><td><a href="../edittext/<?php echo $v->vorlagestudiengang_id; ?>" target="TemplatesBottom"><?php echo $v->vorlagestudiengang_id; ?></a></td>
				<td><a href="../edittext/<?php echo $v->vorlagestudiengang_id; ?>" target="TemplatesBottom"><?php echo $v->vorlage_kurzbz; ?></a></td>
				<td><?php echo $v->version; ?></td>
				<td><?php echo $v->oe_kurzbz; ?></td>
				<td></td>
				<td><?php echo $v->berechtigung; ?></td>
				<td><?php echo $v->anmerkung_vorlagestudiengang; ?></td>
				<td><?php echo $v->aktiv; ?></td>
				<td><a href="../editText/<?php echo $v->vorlagestudiengang_id; ?>" target="TemplatesBottom">Edit</a></td>
			</tr>
		<?php endforeach ?>
	  </tbody>
	</table>
  </div>
</div>

<?php
	$this->load->view('templates/footer');
?>
