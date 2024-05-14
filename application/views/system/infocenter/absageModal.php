<div class="modal fade absageModalForAll"
	 tabindex="-1"
	 role="dialog"
	 aria-labelledby="absageModalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button"
						class="close"
						data-dismiss="modal"
						aria-hidden="true">
					&times;
				</button>
				<h4 class="modal-title"
					id="absageModalLabel"><?php echo  $this->p->t('infocenter', 'absageBestaetigen') ?></h4>
			</div>
			<div class="modal-body">
				<?php echo  $this->p->t('infocenter', 'absageBestaetigenTxt') ?>
			</div>
			<div class="modal-footer">
				<button type="button"
						class="btn btn-default"
						data-dismiss="modal">
					<?php echo  $this->p->t('ui', 'abbrechen') ?>
				</button>
				<button class="btn btn-primary saveAbsage" id="saveAbsageForAll">
					<?php echo  $this->p->t('infocenter', 'interessentAbweisen') ?>
				</button>
			</div>
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>