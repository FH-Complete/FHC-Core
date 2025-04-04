<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Message sent successfully - Nachricht erfolgreich versandt!',
			'jquery3' => true,
			'bootstrap3' => true,
			'fontawesome4' => true,
			'sbadmintemplate3' => true,
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/message.css')
		)
	);
?>

	<div id="wrapper">
		<div id="page-wrapper">
			<div class="container-fluid">

				<div class="row">

					<div class="col-xs-6">
						<h3 class="page-header text-right"></h3>
					</div>

					<div class="col-xs-6">
						<h3 class="page-header"></h3>
					</div>

				</div>

				<div class="row">
					<div class="col-lg-12">

						<div class="panel panel-success">
							<div class="panel-heading">
								<div class="row">
									<div class="col-xs-6 text-right">
										Message sent successfully!
									</div>
									<div class="col-xs-6">
										Nachricht erfolgreich versandt!
									</div>
								</div>
							</div>

							<div class="panel-body">

								<br>

								<div class="row">
									<div class="col-xs-6 text-right" style="border-right: 1px">
										You can safely close this window.
									</div>
									<div class="col-xs-6">
										Sie können dieses Fenster schließen.
									</div>
								</div>

								<br>

							</div>
						</div>

						<div class="row">
							<div class="col-xs-12 text-center">
								<p class="signatureblock">
									Fachhochschule Technikum Wien | University of Applied Sciences Technikum Wien
									<br>Hoechstaedtplatz 6, 1200 Wien, AUSTRIA
									<br><a class="signatureblocklink" href="https://www.technikum-wien.at">www.technikum-wien.at</a>
								</p>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>

<?php $this->load->view("templates/FHC-Footer"); ?>
