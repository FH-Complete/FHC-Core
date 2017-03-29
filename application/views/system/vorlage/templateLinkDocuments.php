<?php
	$this->load->view('templates/header', array('title' => 'TemplateLinkDocuments', 'tablesort' => true, 'tableid' => 't1', 'sortList' => '2,1', 'headers' => '3:{sorter:false},4:{sorter:false}'));
?>



	<script>
		function addDocument(dokument_kurzbz)
		{
			var addDocumentDefault = document.getElementById("addDocumentDefault");
			addDocumentDefault.selected = true;


			$.post("../saveDocuments/"+<?=$vorlagestudiengang_id?>+"/"+dokument_kurzbz+"/0", function(answer)
			{
				window.location.href=window.location.href;
			});
		}
		function delDocument(vorlagedokument_id)
		{
			$.post("../deleteDocumentLink/"+vorlagedokument_id, function(answer)
			{
				window.location.href=window.location.href;
			});
		}
		function changeSort(vorlagedokument_id, sortnum)
		{
			$.post("../changeSort/"+vorlagedokument_id+"/"+sortnum, function(answer)
			{
				window.location.href=window.location.href;
			});
		}
	</script>
	<h2><?=$vorlagestudiengang_id?></h2>
	<table id="t1" class="tablesorter">
		<thead>
			<tr>
				<th>ID</th>
				<th>Bezeichnung</th>
				<th>Sortierung</th>
				<th></th>
				<th></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach ($documents as $d): ?>
			<tr>
				<td><?=$d->vorlagedokument_id?></td>
				<td><?=$d->bezeichnung?></td>
				<td> <?=$d->sort?></td>
				<td><a onclick="changeSort('<?=$d->vorlagedokument_id?>', <?=$d->sort?>+1)"><img src="<?php echo APP_ROOT?>/skin/images/up.png"/></a> <a onclick="changeSort('<?=$d->vorlagedokument_id?>', <?=$d->sort?>-1)"><img src="<?php echo APP_ROOT?>/skin/images/down.png"/></a></td>
				<td><a onclick="delDocument('<?=$d->vorlagedokument_id?>')">löschen</a></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<select>
		<option selected="true" id="addDocumentDefault" disabled>Dokument hinzufuegen</option>
		<?php foreach($allDocuments as $d): ?>
			<option onclick="addDocument('<?=$d->dokument_kurzbz?>');"><?=$d->bezeichnung?></option>
		<?php endforeach ;?>
	</select>


</body>
</html>
