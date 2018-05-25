<form method="post" action="#" id="notizform">
	<input type="hidden" name="hiddenNotizId" value="">
	<div class="form-group">
		<div class="text-center">
			<label><?php echo  $this->p->t('infocenter', 'notizHinzufuegen') ?></label>
		</div>
		<div>
			<div class="form-group">
				<label><?php echo  ucfirst($this->p->t('global', 'titel')) . ': ' ?></label>
				<div class="input-group">
					<input id="inputNotizTitel" type="text" class="form-control" name="notiztitel"/>
					<div class="input-group-addon" onclick="document.getElementById('inputNotizTitel').value='Anmerkung zur Bewerbung'">
						<span class="glyphicon glyphicon-text-background"></span>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label>Text: </label>
			<textarea name="notiz" class="form-control" rows="10" cols="32"></textarea>
		</div>
		<div class="text-right">
			<!--abbrechen-button only shown when notice is clicked to be changed-->
			<span class="text-danger" id="notizmsg"></span>
			<button type="reset" class="btn btn-default" style="display: none"><?php echo  $this->p->t('ui', 'abbrechen') ?></button>
			<button type="submit" class="btn btn-default"><?php echo  $this->p->t('ui', 'speichern') ?></button>
		</div>
	</div>
</form>
