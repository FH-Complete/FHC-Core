<?php

require_once('../../config/vilesci.config.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/phrasen.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/lehrveranstaltung_faktor.class.php');
require_once('../../include/studiensemester.class.php');

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$sprache = getSprache();
$p = new phrasen($sprache);

if(!$rechte->isBerechtigt('basis/person', 'suid'))
	die('Sie haben keine Berechtigung für diese Seite');

echo '<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
';
?>
<script>
	$(document).ready(function () {

		$('#faktorTable').on('click', '.edit', function() {
			var row = $(this).closest('tr');
			var id = row.data('id');
			var faktor = row.find('.faktor').text();
			var von = row.find('.von').text();
			var bis = row.find('.bis').text();

			$('#action').val('edit');
			$('#id').val(id);
			$('#faktor').val(faktor);
			$('#von').val(von);
			$('#bis').val(bis);
		});

		$('#faktorTable').on('click', '.delete', function() {
			var id = $(this).closest('tr').data('id');

			var formData = {
				id: id
			}
			deleteFaktor(formData);
		});

		$('#faktorForm').on('submit', function(event) {
			event.preventDefault();
			var action = $('#action').val();
			var id = $('#id').val();

			var faktor = $('#faktor').val();
			var von = $('#von').val();
			var bis = $('#bis').val();
			var lv_id = $('#lv_id').val();

			var formData = {
				faktor: faktor,
				von: von,
				bis: bis,
				lv_id: lv_id
			};

			if(action === 'add')
			{
				addFaktor(formData);
			}
			else
			{
				formData.id = id;
				updateFaktor(formData);
			}
		});
	});

	function addFaktor(formData)
	{
		$.ajax({
			dataType: "json",
			type: 'POST',
			url: "../../soap/lehrveranstaltung_faktor.json.php",
			data: {
				method: 'addFaktor',
				faktor: formData
			},
			success: function(data)
			{
				if (data.status === 'error')
					return alert(data.message);
				else
				{
					addRow(data);
					handleResponse('save')
					sortTable();
				}
			},
			error: function(xhr, status, error)
			{
				alert('Fehler beim Laden der Daten');
			}
		});
	}

	function updateFaktor(formData)
	{
		$.ajax({
			dataType: "json",
			type: 'POST',
			url: "../../soap/lehrveranstaltung_faktor.json.php",
			data: {
				method: 'updateFaktor',
				faktor: formData
			},
			success: function(data)
			{
				if (data.status === 'error')
					return alert(data.message);
				else
				{
					var row = $('#faktorTable tbody tr[data-id="' + formData.id + '"]');
					row.find('.faktor').text(formData.faktor);
					row.find('.von').text(formData.von);
					row.find('.bis').text(formData.bis);

					handleResponse('save');
					sortTable();
				}
			},
			error: function(xhr, status, error)
			{
				alert('Fehler beim Laden der Daten');
			}
		});
	}

	function deleteFaktor(formData)
	{
		$.ajax({
			dataType: "json",
			type: 'POST',
			url: "../../soap/lehrveranstaltung_faktor.json.php",
			data: {
				method: 'deleteFaktor',
				faktor: formData
			},
			success: function (data) {
				if (data.status === 'error')
					return alert(data.message);
				else
				{
					var row = $('#faktorTable tbody tr[data-id="' + formData.id + '"]');
					row.remove();
					$("#faktorTable").trigger("update").trigger("applyWidgets");
					handleResponse('delete');
				}
			},
			error: function (xhr, status, error) {
				alert('Fehler beim Laden der Daten');
			}
		})
	}

	function addRow(faktor)
	{
		var tr = $('<tr>')
			.attr('data-id', faktor.id);

		var editButton = $('<button>')
			.text('Bearbeiten')
			.addClass('edit')

		var deleteButton = $('<button>')
			.text('Loeschen')
			.addClass('delete')

		var row = tr
			.append(
				$('<td>').text(faktor.faktor).addClass('faktor'),
				$('<td>').text(faktor.von).addClass('von'),
				$('<td>').text(faktor.bis).addClass('bis'),
				$('<td>').append(editButton).addClass('edit'),
				$('<td>').append(deleteButton).addClass('delete')
			);

		$('#faktorTable tbody').append(row);
	}

	function handleResponse(type)
	{
		$('#faktorForm')[0].reset();
		$('#action').val('add');
		$('#id').val('');

		let successMessage = document.getElementById('success_message_' +  type);
		successMessage.style.display = 'block';
		setTimeout(() => successMessage.style.display = 'none', 1000);
	}

	function sortTable()
	{
		if ($("#faktorTable tbody tr").length > 0)
		{
			$("#faktorTable").tablesorter({
				sortList: [[1,1], [0,0], [2,0]],
				widgets: ["zebra"]
			});
		}
	}
</script>
<?php

$lehrveranstaltung_id = $_GET["lehrveranstaltung_id"];
$lv = new lehrveranstaltung();
$lv->load($lehrveranstaltung_id);

$faktor = new lehrveranstaltung_faktor();
$faktor->loadByLV($lv->lehrveranstaltung_id);

$studiensemester = new studiensemester();
$studiensemester->getAll('desc');


echo '
</head>
<body class="Background_main">
<h2>Faktor - '. $lv->bezeichnung . ' - ' . $lv->lehrveranstaltung_id . '</h2>';

echo '
	<form id="faktorForm">
		<input type="hidden" id="action" value="add" />
		<input type="hidden" id="id"/>
		<input type="hidden" id="lv_id" value="'. $lv->lehrveranstaltung_id .'"/>
		<label for="faktor">Faktor</label>
		<input type="number" id="faktor" name="faktor" required />
		<label for="von">Von</label>
		<select id="von" name="von" required>
			<option value="">---keine Auswahl---</option>';

			foreach ($studiensemester->studiensemester as $sem)
			{
				echo '<option value="'.$sem->studiensemester_kurzbz.'">'.$sem->studiensemester_kurzbz.'</option>';
			}

echo	'
		</select>
		<label for="bis">Bis</label>
		<select id="bis" name="bis">
			<option value="">---keine Auswahl---</option>';

			foreach ($studiensemester->studiensemester as $sem)
			{
				echo '<option value="'.$sem->studiensemester_kurzbz.'">'.$sem->studiensemester_kurzbz.'</option>';
			}
		
echo '
		</select>
		<button type="submit">'.$p->t('global/speichern').'</button>
		<span id="success_message_save" class="alert alert-success" style="display:none;">'. $p->t('global/erfolgreichgespeichert') . '</span>
		<span id="success_message_delete" class="alert alert-success" style="display:none;">'. $p->t('global/erfolgreichgelöscht') . '</span>
	</form>

	<table class="tablesorter" id="faktorTable">
		<thead>
			<tr>
				<th>'.$p->t('lv/faktor').'</th>
				<th>'.$p->t('global/von').'</th>
				<th>'.$p->t('global/bis').'</th>
				<th>'.$p->t('global/bearbeiten').'</th>
				<th>'.$p->t('global/loeschen').'</th>
			</tr>
		</thead>
		<tbody>
';

if(count($faktor->lv_faktoren) > 0)
{
	foreach($faktor->lv_faktoren as $lv_faktor)
	{
		echo "<tr data-id=". $lv_faktor->lehrveranstaltung_faktor_id .">
				<td class='faktor'>".$lv_faktor->faktor."</td>
				<td class='von'>".$lv_faktor->studiensemester_kurzbz_von."</td>
				<td class='bis'>".$lv_faktor->studiensemester_kurzbz_bis."</td>
				<td><button class='edit'>".$p->t('global/bearbeiten')."</button></td>
				<td><button class='delete'>".$p->t('global/loeschen')."</button></td>
			</tr>"
		;
	}
}

		'</tbody>
	</table>
';

?>


