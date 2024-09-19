<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Read message - Lies die Nachricht',
			'jquery3' => true,
			'bootstrap3' => true,
			'fontawesome4' => true,
			'sbadmintemplate3' => true,
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/message.css')
		)
	);
?>
	<body>
		<div id="wrapper">
			<div id="page-wrapper">
				<div class="container-fluid">
					<div class="row">
						<div class="col-xs-12">
							<h3 class="page-header text-center">

								<?php echo ucfirst($this->p->t('ui', 'newMessage')); ?>:

							</h3>
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
												<b>

													<?php echo ucfirst($this->p->t('ui', 'from')); ?>:

												</b>
											</td>
											<td>

												<?php echo $sender->vorname.' '.$sender->nachname; ?>

											</td>
										</tr>
										<tr>
											<td width="80px">
												<b>

													<?php echo ucfirst($this->p->t('global', 'betreff')); ?>:

												</b>
											</td>
											<td>

												<?php echo $message->subject; ?>

											</td>
										</tr>
										<tr>
											<td width="80px">
												<b>

													<?php echo ucfirst($this->p->t('global', 'nachricht')); ?>:

												</b>
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

											<?php if (!isEmptyString($hrefReply)): ?>

												<button class="btn btn-default" id="replybutton" onclick="location.href='<?php echo $hrefReply; ?>';">
													<i class="fa fa-reply"></i>&nbsp;<?php echo ucfirst($this->p->t('global', 'reply')); ?>
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
