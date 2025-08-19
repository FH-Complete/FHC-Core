<?php
require_once('../../config/vilesci.config.inc.php');
require_once('../../include/studiengang.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');
require_once('../../include/fachbereich.class.php');
require_once('../../include/lvinfo.class.php');
require_once('../../include/lehrveranstaltung.class.php');
require_once('../../include/organisationsform.class.php');
require_once('../../include/organisationseinheit.class.php');

if (!$db = new basis_db())
	die('Es konnte keine Verbindung zum Server aufgebaut werden.');


$getKompatibleLVs = filter_input(INPUT_POST, 'getKompatibleLVs', FILTER_VALIDATE_BOOLEAN);

if ($getKompatibleLVs)
{
	if (isset($_POST['lv_id']))
	{
		$lv = new lehrveranstaltung();
		$lv->load($_POST['lv_id']);

		$kompatibleLvs = $lv->loadLVkompatibel($_POST['lv_id']);
		if (is_array($kompatibleLvs) && count($kompatibleLvs) > 0)
		{
			$result = array();
			foreach($kompatibleLvs as $lvId)
			{
				$lv->load($lvId);
				$studiengang = new studiengang();
				$studiengang->load($lv->studiengang_kz);
				$oe = new organisationseinheit();
				$oe->load($lv->oe_kurzbz);


				$result[] = array(
					"lehrveranstaltung_id" => $lv->lehrveranstaltung_id,
					"kurzbz" => $lv->kurzbz,
					"bezeichnung" => $lv->bezeichnung,
					"ects" => $lv->ects,
					"studiengang_kuerzel" => $studiengang->kuerzel,
					"oe_bezeichnung" => $oe->bezeichnung
				);
			}
			echo json_encode(["result" => $result]);
			exit();
		}
		echo json_encode(["result" => array()]);
		exit();
	}
}

if (isset($_REQUEST['autocomplete']) && ($_REQUEST['autocomplete'] === 'From' || $_REQUEST['autocomplete'] === 'To'))
{
	$search = trim((isset($_REQUEST['term']) ? $_REQUEST['term'] : ''));
	if (is_null($search) || $search == '')
	{
		exit();
	}

	$qry = "SELECT lehre.tbl_lehrveranstaltung.*
		FROM lehre.tbl_lehrveranstaltung
		WHERE
			lower(bezeichnung) like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
			lehrveranstaltung_id::text like '%" . $db->db_escape(mb_strtolower($search)) . "%' OR
			studiengang_kz::text like '%" . $db->db_escape(mb_strtolower($search)) . "%'
		ORDER BY lehrveranstaltung_id DESC
		LIMIT 10
	";

	if ($result = $db->db_query($qry))
	{
		$result_obj = array();
		while ($row = $db->db_fetch_object($result))
		{
			$item['lehrveranstaltung_id'] = html_entity_decode($row->lehrveranstaltung_id);
			$item['bezeichnung'] = html_entity_decode($row->bezeichnung);
			$item['oe_kurzbz'] = html_entity_decode($row->oe_kurzbz);
			$result_obj[] = $item;
		}
		echo json_encode($result_obj);
	}
	exit();
}
?><!DOCTYPE html>
<html>
<head>
	<title>Lehrveranstaltung Verwaltung</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<link rel="stylesheet" href="../../skin/jquery.css" type="text/css"/>
	<script type="text/javascript" src="../../vendor/jquery/jquery1/jquery-1.12.4.min.js"></script>
	<script type="text/javascript" src="../../vendor/christianbach/tablesorter/jquery.tablesorter.min.js"></script>
	<script type="text/javascript" src="../../vendor/components/jqueryui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="../../include/js/jquery.ui.datepicker.translation.js"></script>
	<link rel="stylesheet" href="../../skin/tablesort.css" type="text/css"/>
	<style>
		.container {
			display: flex;
			gap: 20px;
			width: 100%;
		}
		.box {
			flex: 1;
			padding: 20px;
			border: 1px solid #333;
			text-align: center;
			width: 50%;
		}
		.lvDropdown {
			width: 100%;
		}
		.missing-in-to td {
			background-color: #fff3cd !important;
		}
		.autocomplete {
			width: 100%;
			box-sizing: border-box;
		}
		.hidden {
			visibility: hidden;
		}
	</style>
	<script>
		$(document).ready(function () {

			initBox("From");
			initBox("To");

			searchDropdownCleaner("From")
			searchDropdownCleaner("To")

			$('#kompatibleLVsUbernehmen').on("click", function() {

				let lvidFrom = $('#lvDropdownFrom').val();
				let lvidTo = $('#lvDropdownTo').val();
				let uebernahmenCheckboxen = $("input[name='lvUebernehmenCheckbox']:checked");
				let checkboxenUebernahmeCount = uebernahmenCheckboxen.length;

				if (!lvidFrom || !lvidTo)
				{
					alert("Bitte in beiden Dropdowns eine LV auswählen!");
					return;
				}

				if (checkboxenUebernahmeCount === 0)
				{
					alert("Bitte Kompatible LV´s auswählen die übernommen werden sollen!");
					return;
				}

				let done = 0;
				uebernahmenCheckboxen.each(function()
				{
					saveKompatibleLv(lvidTo, this.value, function() {
						done++;
						if (done === checkboxenUebernahmeCount)
						{
							$('#lvDropdownTo').trigger('change');
						}
					});
				});
			})
		});

		function searchDropdownCleaner(side)
		{
			let search = $("#autocomplete" + side);
			let studiengang = $("#stgDropdown" + side);
			let oe = $("#oeDropdown" + side);
			let semester = $("#semDropdown" + side);
			let lvDropdown = $("#lvDropdown" + side);

			let dropdowns = [studiengang, oe, semester, lvDropdown];
			search.on("input", function() {

				if (search.val().trim().length > 0)
				{
					dropdowns.forEach(element => {
						element.closest("tr").addClass("hidden");
					});
				}
				else
				{
					dropdowns.forEach(element => {
						element.closest("tr").removeClass("hidden");
					});
				}
			})
		}
		function initBox(side)
		{
			loadSemester(side);

			$('#stgDropdown' + side).on("change", function() {
				loadSemester(side)
			})

			$('#oeDropdown' + side).on("change", function() {
				loadLehrveranstaltungen(side)
			})

			$('#semDropdown' + side).on("change", function() {
				loadLehrveranstaltungen(side)
			})

			$('#lvDropdown' + side).on("change", function() {
				loadKompatibleLvs(side)
			})

			$('#kompatibleLVs' + side).tablesorter({
				widgets: ["zebra"]
			});

			$("#autocomplete" + side).autocomplete({
				source: "lehrveranstaltung_kompatibel_vergleichen.php?autocomplete=" + side,
				minLength: 3,
				response: function(event, ui) {
					for (let i in ui.content) {
						if (ui.content.hasOwnProperty(i))
						{
							let option = ui.content[i];
							option.value = option.bezeichnung + " (" + option.lehrveranstaltung_id + "/" + option.oe_kurzbz + ")";
							option.label = option.bezeichnung + " (" + option.lehrveranstaltung_id + "/" + option.oe_kurzbz + ")";
						}
					}
				},
				select: function(event, ui) {
					callLoadKompatibleLvs(ui.item.lehrveranstaltung_id, side)
					$("#lvBezeichnung" + side).html(ui.item.bezeichnung);
				}
			});


		}

		function loadSemester(side)
		{
			var studiengang_kz = $("#stgDropdown" + side).val();
			$.ajax(
				{
					dataType: "json",
					url: "../../soap/studienplan.json.php",
					data: {
						"method": "getSemesterFromStudiengang",
						"studiengang_kz": studiengang_kz
					}
				}).success(function(data)
			{
				let html = "";


				let result =  Array.isArray(data?.result) ? data.result : [];

				if (result.length > 0)
				{
					result.forEach(function(option)
					{
						html+="<option value='"+ option +"'>Semester "+ option +"</option>";
					});
					$("#semDropdown" + side).html(html);
				}


				loadLehrveranstaltungen(side);


			});
		}


		function loadKompatibleLvs(side)
		{
			let lv_id = $("#lvDropdown" + side).val();

			if (lv_id == "null")
				$("#lvBezeichnung" + side).html("")
			else
			{
				let bezeichnung = $("#lvDropdown" + side + " option:selected").text();
				$("#lvBezeichnung" + side).html(bezeichnung);
				callLoadKompatibleLvs(lv_id, side)
			}
		}

		function callLoadKompatibleLvs(lv_id, side)
		{
			$.ajax({
				url: "lehrveranstaltung_kompatibel_vergleichen.php",
				data: {
					'getKompatibleLVs': true,
					'lv_id' : lv_id
				},
				type: "POST",
				dataType: "json",
				success: function(data)
				{
					var html = "";
					data.result.forEach(function(option)
					{
						html += "<tr data-lv-id='" + option.lehrveranstaltung_id + "'>" +
							"<td>"+ option.lehrveranstaltung_id +"</td>" +
							"<td>"+ option.kurzbz +"</td>" +
							"<td>"+ option.bezeichnung +"</td>" +
							"<td>"+ option.ects +"</td>" +
							"<td>"+ option.studiengang_kuerzel +"</td>" +
							"<td>"+ option.oe_bezeichnung +"</td>" +
							(side === "From" ? "<td><input type='checkbox' name='lvUebernehmenCheckbox' value='" + option.lehrveranstaltung_id + "'></td>" : "") +
							"<td>" +
							"<a href='#' onclick=\"deleteKompatibleLv('" + lv_id + "', '" + option.lehrveranstaltung_id + "', '" + side + "')\">" +
							"<img height='20' src='../../skin/images/false.png' alt='Delete'></a>" +
							"</td>" +
							"</tr>";

					});
					$("#kompatibleLVs" + side + " tbody").html(html);

					markDifferences();
				},
				error: function(jqXHR, textStatus, errorThrown)
				{

				}
			});
		}

		function markDifferences() {
			const fromVal = $("#lvDropdownFrom").val();
			const toVal   = $("#lvDropdownTo").val();

			if (!fromVal || fromVal === "null" || !toVal || toVal === "null")
			{
				$("#kompatibleLVsFrom tbody tr").removeClass("missing-in-to");
				return;
			}

			$("#kompatibleLVsFrom tbody tr").removeClass("missing-in-to");

			$("#kompatibleLVsFrom tbody tr").each(function ()
			{
				const id = $(this).data("lv-id");
				const existsInTo = $("#kompatibleLVsTo tbody tr[data-lv-id='" + id + "']").length > 0;

				if (!existsInTo)
				{
					$(this).addClass("missing-in-to")
				}
			});
		}



		function loadLehrveranstaltungen(side)
		{
			let studiengang_kz = $("#stgDropdown" + side).val();
			let semester = $("#semDropdown" + side).val();
			let oe_kurzbz = $("#oeDropdown" + side).val();

			if(oe_kurzbz === "null")
			{
				$.ajax(
					{
						dataType: "json",
						url: "../../soap/fhcomplete.php",
						type: "POST",
						data: {
							"typ": "json",
							"class": "lehrveranstaltung",
							"method": "load_lva",
							"parameter_0": studiengang_kz,
							"parameter_1": semester,
							"parameter_2": "null",
							"parameter_3": "null",
							"parameter_4": "true"
						}
					}).success(function(data)
				{
					let html = "";
					let result =  Array.isArray(data?.result) ? data.result : [];
					if (result.length > 0)
					{
						result.forEach(function(option)
						{
							if (option.lehrveranstaltung_id !== null)
								html+="<option value='"+ option.lehrveranstaltung_id +"'>"+ option.bezeichnung + " (" + option.lehrveranstaltung_id + "/" + option.oe_kurzbz + ") </option>";
						});
						$("#lvDropdown" + side).html(html);

					}
					loadKompatibleLvs(side);

				});
			}
			else
			{
				$.ajax(
					{
						dataType: "json",
						url: "../../soap/fhcomplete.php",
						type: "POST",
						data: {
							"typ": "json",
							"class": "lehrveranstaltung",
							"method": "load_lva_oe",
							"parameter_0": oe_kurzbz,
							"parameter_1": true,
							"parameter_2": "null",
							"parameter_3": "bezeichnung"
						}
					}).success(function(data)
				{
					let html = "";
					let result =  Array.isArray(data?.result) ? data.result : [];
					if (result.length > 0)
					{
						result.forEach(function(option)
						{
							if (option.lehrveranstaltung_id !== null)
								html+="<option value='"+ option.lehrveranstaltung_id +"'>"+ option.bezeichnung +"</option>";
						});
						$("#lvDropdown" + side).html(html);
					}
					loadKompatibleLvs(side);


				});
			}
		}

		function loadOrganisationseinheiten(side)
		{
			$.ajax(
				{
					dataType: "json",
					url: "../../soap/fhcomplete.php",
					type: "POST",
					data: {
						"typ": "json",
						"class": "lehrveranstaltung",
						"method": "load_lva",
						"parameter_0": studiengang_kz,
						"parameter_1": semester,
						"parameter_2": "null",
						"parameter_3": "null",
						"parameter_4": "true"
					}
				}).success(function(data)
			{
				var html = "";
				data.result.forEach(function(option)
				{
					html+="<option value='"+ option.lehrveranstaltung_id +"'>"+ option.bezeichnung +"</option>";
				});
				$("#lvDropdown" +side).html(html);
			});
		}

		function saveKompatibleLv(lehrveranstaltung_id, kompatible_id, onComplete)
		{
			$.ajax(
				{
					dataType: "json",
					url: "../../soap/lehrveranstaltung.json.php",
					type: "POST",
					data: {
						"typ": "json",
						"class": "lehrveranstaltung",
						"method": "saveKompatibleLehrveranstaltung",
						"lehrveranstaltung_id":lehrveranstaltung_id,
						"lehrveranstaltung_id_kompatibel": kompatible_id
					}
				}).success(function(data)
			{
				if(data.error === "true")
				{
					alert(data.errormsg);
				}

				if (onComplete)
					onComplete();

			}).error(function(data)
			{
				alert(data.responseText);
				if (onComplete)
					onComplete();
			});
		}

		function deleteKompatibleLv(lehrveranstaltung_id, lehrveranstaltung_id_kompatibel, side)
		{
			$.ajax(
				{
					dataType: "json",
					url: "../../soap/lehrveranstaltung.json.php",
					type: "POST",
					data: {
						"typ": "json",
						"class": "lehrveranstaltung",
						"method": "deleteKompatibleLehrveranstaltung",
						"lehrveranstaltung_id":lehrveranstaltung_id,
						"lehrveranstaltung_id_kompatibel":lehrveranstaltung_id_kompatibel
					}
				}).success(function(data)
			{
				if(data.error === "true")
				{
					alert(data.errormsg);
				}

				if ($('#lvDropdownFrom').val() === $('#lvDropdownTo').val())
				{
					$('#lvDropdownFrom').trigger('change');
					$('#lvDropdownTo').trigger('change');
				}
				else
					$('#lvDropdown' + side).trigger('change');
			}).error(function(data)
			{
				alert(data.responseText);
			});
		}
	</script>
</head>
<body>

<?php
$user = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);

if(!$rechte->isBerechtigt('lehre/lehrveranstaltung', 'suid'))
	die('Sie haben keine Berechtigung fuer diese Seite');

$studiengang = new studiengang();
$studiengang->getAll("kurzbzlang");

$organisationseinheit = new organisationseinheit();
$organisationseinheit->getAll(true, true);

echo "<div class='container'>
		<div class='box'>
			<div style='padding-top: 1em;'>
					<table>
					<tr>
						<td><b>Suche: </b></td>
						<td colspan='3'>
							<input type='text' class='autocomplete' id='autocompleteFrom' placeholder='Suche...'/>
						</td>
					</tr>
					<tr>
						<td><b>Studiengang: </b></td>
						<td>
							<select id='stgDropdownFrom' style='margin-right: 1em;'>";
							foreach($studiengang->result as $stg)
							{
								echo "<option value=".$stg->studiengang_kz.">".$stg->kuerzel." - ".$stg->kurzbzlang."</option>";
							}
							echo "</select>
						</td>
						<td><b>OE:</b></td>";
				echo "<td>
						<select id='oeDropdownFrom' style='margin-right: 1em;'>
							<option value='null'>-- Keine --</option>";
							foreach($organisationseinheit->result as $oe)
							{
								echo "<option value=".$oe->oe_kurzbz.">".$oe->organisationseinheittyp_kurzbz." ".$oe->bezeichnung."</option>";
							}
				echo "</select></td>
				</tr>
				<tr>
					<td><b>Semester: </b></td>
					<td>
						<select id='semDropdownFrom' style='margin-right: 1em;'>
						
						</select>
					</td>
				</tr>
				<tr>
					<td><b>Lehrveranstaltungen: </b></td>
					<td colspan='3'>
						<select class='lvDropdown' id='lvDropdownFrom'></select>
					</td>
				</tr>
				
			</table>
	</div>
	Kompatible Lehrveranstaltungen - <span id='lvBezeichnungFrom'></span>
	
	<table style='width: auto;' class='tablesorter' id='kompatibleLVsFrom'>
			<thead>
				<tr>
					<th>ID</th>
					<th>Kurzbezeichnung</th>
					<th>Bezeichnung</th>
					<th>ECTS</th>
					<th>Studiengang</th>
					<th>Organisationseiheit</th>
					<th>Übernehmen?</th>
					<th>Löschen?</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
	</table>
	<input type='button' id='kompatibleLVsUbernehmen' value='Übernehmen'/>
	

</div>
<div class='box'>
			<div style='padding-top: 1em;'>
		
					<table>
					<tr>
						<td><b>Suche: </b></td>
						<td colspan='3'>
							<input type='text' class='autocomplete' id='autocompleteTo' placeholder='Suche...'/>
						</td>
					</tr>
					<tr>
						<td><b>Studiengang: </b></td>
						<td>
							<select id='stgDropdownTo' style='margin-right: 1em;'>";
								foreach($studiengang->result as $stg)
								{
									echo "<option value=".$stg->studiengang_kz.">".$stg->kuerzel." - ".$stg->kurzbzlang."</option>";
								}
							echo "</select>
						</td>
						<td><b>OE:</b></td>";
						echo "<td>
						<select id='oeDropdownTo' style='margin-right: 1em;'>
							<option value='null'>-- Keine --</option>";
							foreach($organisationseinheit->result as $oe)
							{
								echo "<option value=".$oe->oe_kurzbz.">".$oe->organisationseinheittyp_kurzbz." ".$oe->bezeichnung."</option>";
							}
						echo "</select></td>
				</tr>
				<tr>
					<td><b>Semester: </b></td>
					<td>
						<select id='semDropdownTo' style='margin-right: 1em;'>
						</select>
					</td>
				</tr>
				<tr>
					<td><b>Lehrveranstaltungen: </b></td>
					<td colspan='3'>
						<select class='lvDropdown' id='lvDropdownTo'></select>
					</td>
				</tr>
				
			</table>
	</div>
	Kompatible Lehrveranstaltungen - <span id='lvBezeichnungTo'></span>
	<table style='width: auto;' class='tablesorter' id='kompatibleLVsTo'>
		<thead>
			<tr>
				<th>ID</th>
				<th>Kurzbezeichnung</th>
				<th>Bezeichnung</th>
				<th>ECTS</th>
				<th>Studiengang</th>
				<th>Organisationseiheit</th>
				<th>Löschen?</th>
			</tr>
		</thead>
		<tbody></tbody>
		
	</table>
</div>
";


echo "</body>
	</html>";

?>
