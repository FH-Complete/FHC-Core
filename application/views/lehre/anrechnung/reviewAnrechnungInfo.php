<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <div class="panel panel-info">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne"
                   aria-expanded="true" aria-controls="collapseOne">
                    <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
	                <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoFristenTitle'); ?>
                </a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
	            <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoFristenBody'); ?>
            </div>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading" role="tab" id="headingZero">
            <h4 class="panel-title">
                <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseZero"
                   aria-expanded="true" aria-controls="collapseZero">
                    <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
	                <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoAntragVoraussetungenTitle'); ?>
                </a>
            </h4>
        </div>
        <div id="collapseZero" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingZero">
            <div class="panel-body">
	            <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoAntragVoraussetungenBody'); ?>
            </div>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading" role="tab" id="headingTwo">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo"
                   aria-expanded="false" aria-controls="collapseTwo">
                    <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
	                <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoNachweisdokumenteTitle'); ?>
                </a>
            </h4>
        </div>
        <div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
            <div class="panel-body">
	            <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoNachweisdokumenteBody'); ?>
            </div>
        </div>
    </div>
    <div class="panel panel-info">
        <div class="panel-heading" role="tab" id="headingThree">
            <h4 class="panel-title">
                <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree"
                   aria-expanded="false" aria-controls="collapseThree">
                    <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
	                <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoHerkunftKenntnisseTitle'); ?>
                </a>
            </h4>
        </div>
        <div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
            <div class="panel-body">
	            <?php echo $this->p->t('anrechnung', 'reviewAnrechnungInfoHerkunftKenntnisseBody'); ?>
            </div>
        </div>
    </div>
</div>