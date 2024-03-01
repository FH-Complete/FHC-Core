

    <div class="accordion" id="accordionExample">
       <!--    Beantragung: Fristen panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
        <div  class="bg-info-subtle accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
        <div class="d-flex">
        
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
				<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoFristenTitle'); ?>
        
        </div>
        </div>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoFristenBody'); ?>
      </div>
    </div>
  </div>
  <!--    Referenzbeispiele ECTS Berechnung panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
        <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
        <div class="d-flex">
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
					<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoEctsBerechnungTitle'); ?>
        </div>
        </div>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoEctsBerechnungBody'); ?>
      </div>
    </div>
  </div>
  <!--    Nachweisdokumente: Voraussetzung panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
    <div class="d-flex">
        <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
					<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoNachweisdokumenteTitle'); ?>
        </div>
        </div>
    </h2>
    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoNachweisdokumenteBody'); ?>
      </div>
    </div>
  </div>
    <!--    Herkunft der Kenntnisse: Angaben panel -->
  <div class="accordion-item">
    <h2 class="accordion-header">
        <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
        <div class="d-flex">
        <i class="me-2 fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
					<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoHerkunftKenntnisseTitle'); ?>
        </div>
        </div>
    </h2>
    <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
      <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoHerkunftKenntnisseBody'); ?>
      </div>
    </div>
  </div>

</div>