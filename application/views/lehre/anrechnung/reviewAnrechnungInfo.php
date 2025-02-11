<div class="accordion" id="requestAnrechnungImportant">
    <!--    Beantragung: Fristen panel -->
	<?php if (isset($this->config->item('display_infobox')['fristen']) && $this->config->item('display_infobox')['fristen'] === true): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <div class="bg-info-subtle accordion-button" type="button" data-bs-toggle="collapse"
                     data-bs-target="#Beantragung" aria-expanded="true" aria-controls="Beantragung">
                    <div class="d-flex">

                        <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
						<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoFristenTitle'); ?>

                    </div>
                </div>
            </h2>
            <div id="Beantragung" class="accordion-collapse collapse show" data-bs-parent="#requestAnrechnungImportant">
                <div class="accordion-body">
					<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoFristenBody'); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>
    <!--    Referenzbeispiele ECTS Berechnung panel -->
	<?php if (isset($this->config->item('display_infobox')['referenzbeispiele_ects']) && $this->config->item('display_infobox')['referenzbeispiele_ects'] === true): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse"
                     data-bs-target="#Referenzbeispiele" aria-expanded="false" aria-controls="Referenzbeispiele">
                    <div class="d-flex">
                        <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
						<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoEctsBerechnungTitle'); ?>
                    </div>
                </div>
            </h2>
            <div id="Referenzbeispiele" class="accordion-collapse collapse"
                 data-bs-parent="#requestAnrechnungImportant">
                <div class="accordion-body">
					<?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoEctsBerechnungBody'); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>
    <!--    Antrag: Voraussetzungen panel -->
	<?php if (isset($this->config->item('display_infobox')['voraussetzungen']) && $this->config->item('display_infobox')['voraussetzungen'] === true): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <div class="d-flex">
                    <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse"
                         data-bs-target="#Antrag" aria-expanded="false" aria-controls="Antrag">
                        <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
						<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoAntragVoraussetungenTitle'); ?>
                    </div>
                </div>
            </h2>
            <div id="Antrag" class="accordion-collapse collapse" data-bs-parent="#requestAnrechnungImportant">
                <div class="accordion-body">
					<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoAntragVoraussetungenBody'); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>
    <!--    Nachweisdokumente: Voraussetzung panel -->
	<?php if (isset($this->config->item('display_infobox')['nachweisdokumente']) && $this->config->item('display_infobox')['nachweisdokumente'] === true): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse"
                     data-bs-target="#Nachweisdokumente" aria-expanded="false" aria-controls="Nachweisdokumente">
                    <div class="d-flex">
                        <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
						<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoNachweisdokumenteTitle'); ?>
                    </div>
                </div>
            </h2>
            <div id="Nachweisdokumente" class="accordion-collapse collapse"
                 data-bs-parent="#requestAnrechnungImportant">
                <div class="accordion-body">
					<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoNachweisdokumenteBody'); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>
    <!--    Herkunft der Kenntnisse: Angaben panel -->
	<?php if (isset($this->config->item('display_infobox')['herkunft_kenntnisse']) && $this->config->item('display_infobox')['herkunft_kenntnisse'] === true): ?>
        <div class="accordion-item">
            <h2 class="accordion-header">
                <div class="bg-info-subtle accordion-button collapsed" type="button" data-bs-toggle="collapse"
                     data-bs-target="#HerkunftKenntnisse" aria-expanded="false" aria-controls="HerkunftKenntnisse">
                    <div class="d-flex">
                        <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
						<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoHerkunftKenntnisseTitle'); ?>
                    </div>
                </div>
            </h2>
            <div id="HerkunftKenntnisse" class="accordion-collapse collapse"
                 data-bs-parent="#requestAnrechnungImportant">
                <div class="accordion-body">
					<?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoHerkunftKenntnisseBody'); ?>
                </div>
            </div>
        </div>
	<?php endif; ?>
</div>
