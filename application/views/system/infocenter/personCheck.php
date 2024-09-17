
<div class="row alert-info" id="unruly" style="display: none;">
	<h3 class="header col-lg-12">
		<?php echo $this->p->t('infocenter', 'unrulyPersonFound') . ':'; ?>
	</h3>
	<div class="text-left col-lg-12" id="unrulylist">
		<?php
		if($unruly) {
			foreach ($unruly as $unruled)
			{
				echo '<a>Person ID: ' . $unruled->person_id . '<a/><br />';
			}
		}
		?>
	</div>
</div>

<div class="row alert-warning" id="duplicate" style="display: none;">

	<h3 class="header col-lg-12">
		<?php echo $this->p->t('global', 'bewerberVorhanden') . ':'; ?>
	</h3>
	<div class="text-left col-lg-12" id="duplicatelist">
		<?php
		if($duplicate) {
			foreach ($duplicate as $dupe)
			{
				echo '<a>Person ID: ' . $dupe->person_id . '<a/><br />';
			}
		}
		?>
	</div>

</div>