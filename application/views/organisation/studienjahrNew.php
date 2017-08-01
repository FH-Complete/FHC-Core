<?php
$this->load->view('templates/header', array('title' => 'StudienjahrNew', 'jqueryComposer' => true));
?>
<body>

<div class="row">
	<div class="row">
		<div class="span4">
			<h2>Neues Studienjahr anlegen</h2>
			<form method="post"
				  action="<?php echo APP_ROOT."index.ci.php/organisation/studienjahr/insStudienjahr"; ?>" id="newStudienjahrForm">
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
	$('#newStudienjahrForm').submit(function (event) {
		var studienjahrkurzbez = $('input[name=studienjahrkurzbz]').val();
		if (checkStudienjahrkurzbez(studienjahrkurzbez))return;
		$('#errormessage').text("Studienjahrbezeichnung muss folgende Form haben: Jahreszahl/letzeZweiZahlenDesNächstenJahres, z.B. 2017/18");
		event.preventDefault();
	});

	function checkStudienjahrkurzbez(semesterkurzbez) {
		var firstyear = parseInt(semesterkurzbez.substr(2,2));
		var secondyear = parseInt(semesterkurzbez.substr(5,2));
		var pattern = /^\d{4}\/\d{2}$/;
		return pattern.test(semesterkurzbez) && secondyear - firstyear === 1;
	}
</script>
</body>
</html>
