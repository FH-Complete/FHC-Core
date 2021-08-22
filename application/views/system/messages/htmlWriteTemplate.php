<?php
	$this->load->view(
		'templates/FHC-Header',
		array(
			'title' => 'Write a new message or reply using templates',
			'jquery' => true,
			'jqueryui' => true,
			'bootstrap' => true,
			'ajaxlib' => true,
			'fontawesome' => true,
			'tinymce' => true,
			'sbadmintemplate' => true,
			'dialoglib' => true,
			'widgets' => true,
			'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/message.css'),
			'customJSs' => array('public/js/bootstrapper.js', 'public/js/messaging/messageWrite.js')
		)
	);
?>
<body>
	<div id="wrapper">
		<div id="page-wrapper">
			<div class="container-fluid">
				<div class="row">
					<div class="col-lg-12">
						<h3 class="page-header">

							<?php echo ucfirst($this->p->t('ui', 'nachrichtSenden')); ?>

						</h3>
					</div>
				</div>
				<form id="sendForm" method="post" action="<?php echo site_url('/system/messages/Messages/sendImplicitTemplate'); ?>">
					<div class="row">
						<div class="form-group">
							<div class="col-lg-1 msgfieldcol-left">
								<label>

									<?php echo ucfirst($this->p->t('global', 'empfaenger')); ?>:

								</label>
							</div>
							<div class="col-lg-11 msgfieldcol-right">
								<?php echo $recipientsList; ?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-lg-1 msgfield msgfieldcol-left">
								<label>

									<?php echo ucfirst($this->p->t('global', 'betreff')); ?>:

								</label>
							</div>
							&nbsp;
							<div class="col-lg-7">
								<input id="subject" class="form-control" type="text" value="<?php echo $subject; ?>" name="subject">
							</div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-9">
							<label>

								<?php echo ucfirst($this->p->t('global', 'nachricht')); ?>:

							</label>
							<textarea id="bodyTextArea" name="body">

								<?php echo $body; ?>

							</textarea>
						</div>
						<div class="col-lg-3">
							<div class="form-group">
								<label>

									<?php echo ucfirst($this->p->t('ui', 'felder')); ?>:

								</label>

								<?php
                                    $size = count($variables) > 19 ? 19 : count($variables);
									echo $this->widgetlib->widget(
										'MultipleDropdown_widget',
										array('elements' => success($variables)),
										array(
											'name' => 'variables[]',
											'id' => 'variables',
											'size' => $size,
											'multiple' => true
										)
									);
								?>
							</div>
                            <br>
                            <div class="form-group">
                                <label>
									
									<?php echo ucfirst($this->p->t('ui', 'meineFelder')); ?>:

                                </label>
								
								<?php
								$size = count($user_fields) > 5 ? 5 : count($user_fields);
								echo $this->widgetlib->widget(
									'MultipleDropdown_widget',
									array('elements' => success($user_fields)),
									array(
										'name' => 'user_fields[]',
										'id' => 'user_fields',
										'size' => $size,
										'multiple' => true
									)
								);
								?>
                            </div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-xs-3">

							<?php
								echo $this->widgetlib->widget(
									'Vorlage_widget',
									array('oe_kurzbz' => $organisationUnits, 'isAdmin' => $senderIsAdmin),
									array('name' => 'vorlage', 'id' => 'vorlageDnD')
								);
							?>

						</div>
						<div class="col-xs-6">
							<button id="sendButton" class="btn btn-default pull-right" type="button">

								<?php echo $this->p->t('ui', 'senden'); ?>

							</button>
						</div>
					</div>
                    <br>
						<hr>
						<div class="row">
							<div class="col-lg-12">
								<label>

									<?php echo ucfirst($this->p->t('global', 'vorschau')); ?>:

								</label>
							</div>
						</div>
						<div class="well" id="templatePreviewDiv">
							<div class="row">
								<div class="col-sm-12" style="display: inline">
									<div class="form-group form-inline">
										<div class="input-group">
										<?php
											echo $this->widgetlib->widget(
												'Dropdown_widget',
												array('elements' => success($recipientsArray), 'emptyElement' => ucfirst($this->p->t('global', 'empfaenger')).'...'),
												array(
													'name' => 'recipients[]',
													'id' => 'recipients'
												)
											);
										?>
											<span class="input-group-btn">
												<a class="btn btn-default" href="#templatePreviewDiv" id="refresh">
													<?php echo ucfirst($this->p->t('ui', 'refresh')); ?>
												</a>
											</span>
										</div>
									</div>
								</div>
							</div>
							<br>
							<textarea id="tinymcePreview"></textarea>
						</div>

					<?php echo $recipients_ids; ?>
					<?php echo $relationmessage_id; ?>
					<?php echo $type; ?>

				</form>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
