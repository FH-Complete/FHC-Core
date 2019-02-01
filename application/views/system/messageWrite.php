<?php
$this->load->view(
	'templates/FHC-Header',
	array(
		'title' => 'Write a message',
		'jquery' => true,
		'bootstrap' => true,
		'fontawesome' => true,
		'tinymce' => true,
		'sbadmintemplate' => true,
		'customCSSs' => array('public/css/sbadmin2/admintemplate_contentonly.css', 'public/css/messaging/messageWrite.css'),
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
						<h3 class="page-header"><?php echo ucfirst($this->p->t('ui', 'nachrichtSenden')); ?></h3>
					</div>
				</div>
				<form id="sendForm" method="post" action="<?php echo site_url('/system/Messages/send'); ?>">
					<div class="row">
						<div class="form-group">
							<div class="col-lg-1 msgfieldcol-left">
								<label><?php echo ucfirst($this->p->t('global', 'empfaenger')).':'; ?></label>
							</div>
							<div class="col-lg-11 msgfieldcol-right">
								<?php
									for ($i = 0; $i < count($recipients); $i++)
									{
										$receiver = $recipients[$i];
										// Every 10 recipients a new line
										if ($i > 1 && $i % 10 == 0)
										{
											echo '<br>';
										}
										echo $receiver->Vorname." ".$receiver->Nachname."; ";
									}
								?>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="form-group">
							<div class="col-lg-1 msgfield msgfieldcol-left">
								<label><?php echo ucfirst($this->p->t('global', 'betreff')).':'; ?></label>
							</div>&nbsp;
							<?php
								$subject = '';
								if (isset($message))
								{
									$subject = 'Re: '.$message->subject;
								}
							?>
							<div class="col-lg-7">
								<input id="subject" class="form-control" type="text" value="<?php echo $subject; ?>" name="subject">
							</div>
						</div>
					</div>
					<br>
					<div class="row">
						<div class="col-lg-10">
							<label><?php echo ucfirst($this->p->t('global', 'nachricht')).':'; ?></label>
							<?php
								$body = '';
								if (isset($message))
								{
									$body = $message->body;
								}
							?>
							<textarea id="bodyTextArea" name="body"><?php echo $body; ?></textarea>
						</div>
						<?php
							if (isset($variables))
							{
						?>
								<div class="col-lg-2">
									<div class="form-group">
										<label><?php echo ucfirst($this->p->t('ui', 'felder')).':'; ?></label>
										<select id="variables" class="form-control" size="14" multiple="multiple">
											<?php
												foreach ($variables as $key => $val)
												{
											?>
													<option value="<?php echo $key; ?>"><?php echo $val; ?></option>
											<?php
												}
											?>
										</select>
									</div>
								</div>
						<?php
							}
						?>
					</div>
					<br>
					<div class="row">
						<div class="col-xs-3">
							<?php
								echo $this->widgetlib->widget(
									'Vorlage_widget',
									array('oe_kurzbz' => $oe_kurzbz, 'isAdmin' => $isAdmin),
									array('name' => 'vorlage', 'id' => 'vorlageDnD')
								);
							?>
						</div>
						<div class="col-lg-7 col-xs-9 text-right">
							<button id="sendButton" class="btn btn-default" type="button"><?php echo  $this->p->t('ui', 'senden'); ?></button>
						</div>
					</div>
					<?php
						if (isset($recipients) && count($recipients) > 0)
						{
					?>
							<hr>
							<div class="row">
								<div class="col-lg-12">
									<label><?php echo ucfirst($this->p->t('global', 'vorschau')).':'; ?></label>
								</div>
							</div>
							<div class="well">
								<div class="row">
									<div class="col-lg-5">
										<div class="form-grop form-inline">
											<label><?php echo ucfirst($this->p->t('global', 'empfaenger')).': '; ?></label>
											<select id="recipients">
												<?php
													if (count($recipients) > 1) echo '<option value="-1">Select...</option>';

													foreach ($recipients as $receiver)
													{
												?>
														<option value="<?php echo $receiver->person_id; ?>">
															<?php echo $receiver->Vorname." ".$receiver->Nachname; ?>
														</option>
												<?php
													}
												?>
											</select>
											&nbsp;
											<strong><a href="#" id="refresh">Refresh</a></strong>
										</div>
									</div>
									<div class="col-lg-2">

									</div>
								</div>
								<br>
								<textarea id="tinymcePreview"></textarea>
							</div>
					<?php
						}
					?>

					<?php
						foreach ($recipients as $receiver)
						{
							echo '<input type="hidden" name="persons[]" value="'.$receiver->person_id.'">'."\n";
						}
					?>

					<?php
						if (isset($message))
						{
					?>
							<input type="hidden" name="relationmessage_id" value="<?php echo $message->message_id; ?>">
					<?php
						}
					?>

				</form>
			</div>
		</div>
	</div>
</body>

<?php $this->load->view("templates/FHC-Footer"); ?>
