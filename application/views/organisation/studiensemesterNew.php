<?php
$this->load->view('templates/header', array('title' => 'StudiensemesterNew', 'jqueryComposer' => true, 'datepicker' => true, 'datepickerclass' => 'dateinput'));
?>
<body>

<div class="row">
	<div class="row">
		<div class="span4">
			<h2>Neues Studiensemester anlegen</h2>
			<form method="post"
				  action="<?php echo APP_ROOT."index.ci.php/organisation/studiensemester/insStudiensemester"; ?>"
				  id="newSemesterForm">
				<table>
					<tr>
						<td colspan="2">
							Kurzbezeichnung:<br/><br/>
							<input type="text" name="semkurzbz" value=""/><br/>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							&nbsp;
						</td>
					</tr>
					<?php include('studiensemesterForm.php'); ?>
			</form>
		</div>
	</div>
</div>
<script>
	$(document).ready(function () {
		$('input[name=semkurzbz]').on(
			'input',
			function () {
				var semesterkurzbez = $('input[name=semkurzbz]').val();
				prefillYearFields(semesterkurzbez);
			}
		);
	});

	function prefillYearFields(semesterkurzbez) {
		if (!checkSemesterkurzbez(semesterkurzbez))return;
		var semester = semesterkurzbez.substr(0, 2);
		var jahr = semesterkurzbez.substr(2, 6);
		var wsssbezeichnung, jahrbez, studienjahr, start, ende = "";
		if (semester == 'WS') {
			wsssbezeichnung = "Wintersemester";
			jahrbez = jahr + "/" + (parseInt(jahr) + 1);
			studienjahr = jahr + "/" + (parseInt(jahr.substr(2, 4)) + 1);
			start = "01.09." + jahr;
			ende = "01.02." + (parseInt(jahr) + 1);
		} else {
			wsssbezeichnung = "Sommersemester";
			jahrbez = jahr;
			studienjahr = jahr + "/" + (parseInt(jahr.substr(2, 4)) - 1);
			start = "01.02." + jahr;
			ende = "01.08." + jahr;
		}
		var bezeichnung = wsssbezeichnung + " " + jahrbez;
		$('input[name=sembz]').val(bezeichnung);
		$('input[name=semstart]').val(start);
		$('input[name=semende]').val(ende);
		$('input[name=studienjahrkurzbz]').val(studienjahr);
	}

	$('#newSemesterForm').submit(function (event) {
		var semesterkurzbez = $('input[name=semkurzbz]').val();
		if (checkSemesterkurzbez(semesterkurzbez))return;
		$('#errormessage').text("Semesterkurzbezeichnung muss mit WS oder SS beginnen und mit einer Jahreszahl enden, z.B. SS2017");
		event.preventDefault();
	});

	function checkSemesterkurzbez(semesterkurzbez) {
		var pattern = /^(WS|SS)\d{4}$/;
		return pattern.test(semesterkurzbez);
	}
</script>
</body>
</html>
