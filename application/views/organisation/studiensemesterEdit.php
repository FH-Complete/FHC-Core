<?php
$sem = $semester[0];
$this->load->view('templates/header', array('title' => 'StudiensemesterEdit', 'datepicker' => true, 'datepickerclass' => 'dateinput'));
?>
<body>
<div class="row">
	<div class="row">
		<div class="span4">
			<h2>Studiensemester bearbeiten: <?php echo $sem->studiensemester_kurzbz; ?></h2>
			<form method="post"
				  action="<?php echo site_url("organisation/studiensemester/updateStudiensemester") ?>">
				<table>
					<?php include('studiensemesterForm.php'); ?>
					<input type="hidden" name="semkurzbz" value="<?php echo $sem->studiensemester_kurzbz; ?>"/>
			</form>
		</div>
	</div>
</div>
</body>
</html>
