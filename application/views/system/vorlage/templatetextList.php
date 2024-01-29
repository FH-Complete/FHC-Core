<?php
	$this->load->view('templates/header', array('title' => 'VorlagetextList', 'tablesort' => true, 'tableid' => 't1', 'headers' => '7:{sorter:false},8:{sorter:false},9:{sorter:false}'));
?>

<div class="row">
  <div class="span4">
	<h2>Vorlagentext - <?php echo $vorlage_kurzbz; ?></h2>
	<form method="post" action="../newText" target="VorlageBottom">
	  <input type="hidden" name="vorlage_kurzbz" value="<?php echo $vorlage_kurzbz; ?>"/>
	  <button type="submit">Neu</button>
	</form>

	<table id="t1" class="tablesorter">
		<thead>
			<tr>
				<th>ID</th>
				<th>Vorlage</th>
				<th>Version</th>
				<th>OrgEinheit</th>
				<th>OrgForm</th>
				<th>Berechtigung</th>
				<th>Anmerkung</th>
				<th>Aktiv</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
	  <tbody>
		<?php foreach ($vorlagentext as $v): ?>
			<tr>
				<td><a href="../editText/<?php echo $v->vorlagestudiengang_id; ?>" target="VorlageBottom"><?php echo $v->vorlagestudiengang_id; ?></a></td>
				<td><a href="../editText/<?php echo $v->vorlagestudiengang_id; ?>" target="VorlageBottom"><?php echo $v->vorlage_kurzbz; ?></a></td>
				<td><?php echo $v->version; ?></td>
				<td><?php echo $v->oe_kurzbz; ?></td>
				<td></td>
				<td><?php echo implode(',', $v->berechtigung); ?></td>
				<td><?php echo $v->anmerkung_vorlagestudiengang; ?></td>
				<td><?php echo $v->aktiv; ?></td>
				<td><a href="../editText/<?php echo $v->vorlagestudiengang_id; ?>" target="VorlageBottom">Edit Text</a></td>
				<td><a href="../linkDocuments/<?php echo $v->vorlagestudiengang_id; ?>" target="VorlageBottom">Edit Documents</a></td>
			</tr>
		<?php endforeach ?>
	  </tbody>
	</table>
  </div>
</div>

<?php
	$this->load->view('templates/footer');
?>