<form method="post" action="#" id="notizform">
	<input type="hidden" name="hiddenNotizId" value="">
	<div class="form-group">
		<div class="text-center">
			<label>Notiz hinzuf&uuml;gen</label>
		</div>
		<div>
			<div class="form-group">
				<label>Titel: </label>
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
			<button type="reset" class="btn btn-default" style="display: none">Abbrechen</button>
			<button type="submit" class="btn btn-default">Speichern</button>
		</div>
	</div>
</form>