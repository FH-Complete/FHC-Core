	<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'MessageSent',
			'jquery' => true,
			'bootstrap' => true,
			'fontawesome' => true,
			'sbadmintemplate' => true,
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/messageReply.css')
		)
	);
	?>
	<body>
		<div id="wrapper">
			<div id="page-wrapper">
				<div class="container-fluid">
					<div class="row">
						<div class="col-xs-12">
							<h3 class="page-header text-center">You have a new message</h3>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<div class="panel panel-success">
								<div class="panel-heading text-center">
									<?php echo $message->subject; ?>
								</div>
								<div class="panel-body">
									<table class="table table-condensed table-bordered" id="msgtable" align="center">
										<tr>
											<td width="80px">
												<b>From:</b>
											</td>
											<td>
												<?php echo $sender->vorname.' '.$sender->nachname; ?>
											</td>
										</tr>
										<tr>
											<td width="80px">
												<b>Subject:</b>
											</td>
											<td>
												<?php echo $message->subject; ?>
											</td>
										</tr>
										<tr>
											<td width="80px">
												<b>Message:</b>
											</td>
											<td>
												<?php echo $message->body; ?>
											</td>
										</tr>
									</table>
								</div>
								<div class="panel-footer">
									<div class="row">
										<div class="col-xs-12 text-center">
											<?php if ($isEmployee === false && $href != ''): ?>
												<button class="btn btn-default" id="replybutton" onclick="location.href='<?php echo $href; ?>';">
													<i class="fa fa-reply"></i>&nbsp;Reply
												</button>
											<?php endif; ?>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</body>
	<?php $this->load->view("templates/FHC-Footer"); ?>