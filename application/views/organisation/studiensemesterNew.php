<?php
$this->load->view('templates/header', array('title' => 'StudiensemesterNew', 'jqueryCurrent' => true, 'datepicker' => true, 'datepickerclass' => 'dateinput'));
?>
<body>

<div class="row">
	<div class="row">
		<div class="span4">
			<h2>Neues Studiensemester anlegen</h2>
			<form method="post" action="<?php echo site_url("organisation/studiensemester/insStudiensemester"); ?>" id="newSemesterForm">
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

	/**
	 * prefills all date-associated input fields depending on given Semesterkurzbezeichnung
	 * fires when value in input field Semesterkurzbezeichnung is changed
	 * @param semesterkurzbez
	 */
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
			studienjahr = (parseInt(jahr) - 1) + "/" + (parseInt(jahr.substr(2, 4)));
			start = "01.02." + jahr;
			ende = "01.08." + jahr;
		}
		var bezeichnung = wsssbezeichnung + " " + jahrbez;
		$('input[name=sembz]').val(bezeichnung);
		$('input[name=semstart]').val(start);
		$('input[name=semende]').val(ende);
		$('select[name=studienjahrkurzbz]').val(studienjahr);
	}

	/**
	 * prevents submitting the form data if data entered incorrectly
	 * additional check before php check for user-friendliness (no php die)
	 * outputs errormessages in case of wrong inputs
	 */
	$('#newSemesterForm').submit(function (event) {
		var semesterkurzbez = $('input[name=semkurzbz]').val();
		var startdatum = $('input[name=semstart]').val();
		var enddatum = $('input[name=semende]').val();
		var errormessage = "";
		var error = false;
		if (!checkSemesterkurzbez(semesterkurzbez)) {
			errormessage = "Semesterkurzbezeichnung muss mit WS oder SS beginnen und mit einer Jahreszahl enden, z.B. SS2017";
			error = true;
		} else if (!checkDate(startdatum)) {
			errormessage = "Startdatum falsch eingegeben. Richtiges Format: dd.mm.yyyy, z.B. 01.01.2017";
			error = true;
		} else if (!checkDate(enddatum)) {
			errormessage = "Enddatum falsch eingegeben. Richtiges Format: dd.mm.yyyy, z.B. 01.01.2017";
			error = true;
		}
		if (error) {
			event.preventDefault();
			$('#errormessage').text(errormessage);
		}
	});

	/**
	 * checks correct Semesterkurzbezeichnung format with regex
	 * @param semesterkurzbez
	 * @returns {boolean} whether the Semesterkurzbezeichnung has correct format
	 */
	function checkSemesterkurzbez(semesterkurzbez) {
		var pattern = /^(WS|SS)\d{4}$/;
		return pattern.test(semesterkurzbez);
	}

	/**
	 * checks date for right (german) format
	 * @param date
	 * @returns {boolean} whether the Semesterkurzbezeichnung has correct format
	 */
	function checkDate(date) {
		var pattern = /^\d{2}.\d{2}.\d{4}$/;
		return pattern.test(date);
	}
</script>
</body>
</html>
