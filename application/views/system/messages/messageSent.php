<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'MessageSent',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'sbadmintemplate' => true,
		'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/messageSent.css')
	)
);
?>
<body>
<div id="wrapper">
	<div id="page-wrapper">
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-6">
					<h3 class="page-header text-right">Thank you for getting in touch!</h3>
				</div>
				<div class="col-xs-6">
					<h3 class="page-header">Danke für die Kontaktaufnahme!</h3>
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
							<div class="row">
								<div class="col-xs-6 text-right">
									<span class="rwd-line">
									Thank you for your message.
									</span>
									<span class="rwd-line">
									We will get back to you shortly.
									</span>
								</div>
								<div class="col-xs-6">
									<span class="rwd-line">
										Herzlichen Dank für Ihre Nachricht.
									</span>
									<span class="rwd-line">
										Wir werden uns schnellstmöglich um Ihr Anliegen kümmern.
									</span>
								</div>
							</div>
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
									<div class="row">
										<div class="col-xs-6 text-right">
											Your InfoCenter@FHTW Team
										</div>
										<div class="col-xs-6">
											Ihr InfoCenter@FHTW Team											
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
<!--						<div class="panel-footer">
							<div class="row">
								<div class="col-xs-12">
									<button class="btn btn-default" onclick="javascript:window.close(); return false;">
										<i class="fa fa-times"></i>&nbsp;Close | Schlie&szlig;en
									</button>
								</div>
								<div class="col-xs-6 text-right">
									<button class="btn btn-default"><i class="glyphicon glyphicon-new-window"></i>&nbsp;My Application | Meine Bewerbung</button>
								</div>
							</div>
						</div>-->
					</div>
				</div>
			</div>
		</div>
</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
