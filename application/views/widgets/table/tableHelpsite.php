<!--CollapseHTML 'Help'-->
<div class="row">
	<div class="col-lg-12 collapse" id="tabulatorHelp-<?php echo $tableUniqueId; ?>">
		<div class="mb-2 " 
			 style="<?php 
				if(isset($bootstrapVersion) && $bootstrapVersion==5)
				{ 
					echo "border: 1px solid #ccc;
      					  border-radius: 4px;
      					  padding: 15px;
      					  background-color: #f5f5f5;";
				} ?>"
			>
   
			<h4><?php echo ucfirst($this->p->t('ui', 'tabelleneinstellungen')); ?></h4>
			<div class=" <?php echo (isset($bootstrapVersion) && $bootstrapVersion==5)?"card card-body":"panel panel-body" ?>">
				<b><?php echo $this->p->t('table', 'spaltenEinAusblenden'); ?></b>
				<p>
                    <ul>
                        <li><?php echo $this->p->t('table', 'spaltenEinAusblendenMitKlickOeffnen'); ?></li>
                        <li><?php echo $this->p->t('table', 'spaltenEinAusblendenAufEinstellungenKlicken'); ?></li>
                        <li><?php echo $this->p->t('table', 'spaltenEinAusblendenMitKlickAktivieren'); ?></li>
                        <li><?php echo $this->p->t('table', 'spaltenEinAusblendenMitKlickSchliessen'); ?></li>
                    </ul>
				</p>
                <br>
				<b><?php echo $this->p->t('table', 'spaltenbreiteVeraendern'); ?></b>
				<p><?php echo $this->p->t('table', 'spaltenbreiteVeraendernText'); ?></p>
				<div class="alert alert-info">
                    <strong>INFO: </strong>
					<?php echo $this->p->t('table', 'spaltenbreiteVeraendernInfotext'); ?>
				</div>
			</div>
			<br> <!--end panel-body-->
			
			<h4><?php echo $this->p->t('table', 'zeilenAuswaehlen'); ?></h4>
			<div class="<?php echo (isset($bootstrapVersion) && $bootstrapVersion==5)?"card card-body":"panel panel-body" ?>">
				<ul class="mb-0">
					<li class="mb-1"><?php echo $this->p->t('table', 'zeilenAuswaehlenEinzeln'); ?></li>
					<li class="mb-1"><?php echo $this->p->t('table', 'zeilenAuswaehlenBereich'); ?></li>
					<li class="mb-1"><?php echo $this->p->t('table', 'zeilenAuswaehlenAlle'); ?></li>
				</ul>
			</div>
			<br> <!--end panel-body-->
   
		</div><!--end well-->
	</div><!--end col collapse-->
</div><!--end row-->