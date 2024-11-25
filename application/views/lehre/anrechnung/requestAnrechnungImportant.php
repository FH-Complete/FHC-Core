

    <div class="accordion" id="requestAnrechnungImportant">
       <!--    Beantragung: Fristen panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
        <div  class="bg-info-subtle accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#Beantragung" aria-expanded="true" aria-controls="Beantragung">
        <div class="d-flex">
        
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
				<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoFristenTitle'); ?>
        
        </div>
        </div>
    </h2>
    <div id="Beantragung" class="accordion-collapse collapse show" data-bs-parent="#requestAnrechnungImportant">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoFristenBody'); ?>
      </div>
    </div>
  </div>
  <!--    Referenzbeispiele ECTS Berechnung panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
        <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Referenzbeispiele" aria-expanded="false" aria-controls="Referenzbeispiele">
        <div class="d-flex">
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
					<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoEctsBerechnungTitle'); ?>
        </div>
        </div>
    </h2>
    <div id="Referenzbeispiele" class="accordion-collapse collapse" data-bs-parent="#requestAnrechnungImportant">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoEctsBerechnungBody'); ?>
      </div>
    </div>
  </div>
  <!--    Nachweisdokumente: Voraussetzung panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
    <div class="d-flex">
        <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#Nachweisdokumente" aria-expanded="false" aria-controls="Nachweisdokumente">
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
					<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoNachweisdokumenteTitle'); ?>
        </div>
        </div>
    </h2>
    <div id="Nachweisdokumente" class="accordion-collapse collapse" data-bs-parent="#requestAnrechnungImportant">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoNachweisdokumenteBody'); ?>
      </div>
    </div>
  </div>
    <!--    Herkunft der Kenntnisse: Angaben panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
        <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#HerkunftKenntnisse" aria-expanded="false" aria-controls="HerkunftKenntnisse">
        <div class="d-flex">
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
					<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoHerkunftKenntnisseTitle'); ?>
        </div>
        </div>
    </h2>
    <div id="HerkunftKenntnisse" class="accordion-collapse collapse" data-bs-parent="#requestAnrechnungImportant">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoHerkunftKenntnisseBody'); ?>
      </div>
    </div>
  </div>

</div>