<?php
$this->load->view('templates/header', array('title' => 'StudienjahrNew', 'jqueryCurrent' => true));
?>
<body>

<div class="row">
	<div class="row">
		<div class="span4">
			<h2>Neues Studienjahr anlegen</h2>
			<form method="post"
				  action="<?php echo site_url("organisation/studienjahr/insStudienjahr"); ?>"
				  id="newStudienjahrForm">
				<table>
					<tr>
						<td colspan="2">
							Kurzbezeichnung:<br/><br/>
							<input type="text" name="studienjahrkurzbz" value="<?php echo $studienjahrkurzbz ?>"/><br/>
						</td>
					</tr>
					<?php include('studienjahrForm.php'); ?>
			</form>
		</div>
	</div>
</div>
<script>
	/**
	 * prevents submitting the form data if data entered incorrectly
	 * additional check before php check for user-friendliness (no php die)
	 * outputs errormessages in case of wrong inputs
	 */
	$('#newStudienjahrForm').submit(function (event) {
		var studienjahrkurzbez = $('input[name=studienjahrkurzbz]').val();
		if (checkStudienjahrkurzbez(studienjahrkurzbez))return;
		$('#errormessage').text("Studienjahrbezeichnung muss folgende Form haben: Jahreszahl/letzeZweiZahlenDesNächstenJahres, z.B. 2017/18");
		event.preventDefault();
	});

	/**
	 * checks correct Studienjahrkurzbezeichnung format with regex
	 * first check is whether the form ist right: e.g. 2017/18
	 * second check is whether first year is second year - 1
	 * @param studienjahrkurzbez
	 * @returns {boolean} whether the Studienjahrkurzbezeichnung has correct format
	 */
	function checkStudienjahrkurzbez(studienjahrkurzbez) {
		var firstyear = parseInt(studienjahrkurzbez.substr(2, 2));
		var secondyear = parseInt(studienjahrkurzbez.substr(5, 2));
		var pattern = /^\d{4}\/\d{2}$/;
		return pattern.test(studienjahrkurzbez) && secondyear - firstyear === 1;
	}
</script>
</body>
</html>
