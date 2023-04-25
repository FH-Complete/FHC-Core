<?php
require_once __DIR__ . '/IValidation.php';

namespace vertragsbestandteil;

const TYPE_ECHT = 'echterdv';
const TYPE_STUDENTISCHE_HILFSKRAFT = 'studentischehilfskr';
const TYPE_FREI = 'freierdv';
const TYPE_EXTERN = 'externerlehrender';
const TYPE_GAST = 'gastlektor';
const TYPE_ECHT_FREI = 'echterfreier';
const TYPE_WERKVERTRAG = 'werkvertrag';
const TYPE_UEBERLASSUNG = 'ueberlassungsvertrag';

class Dienstverhaeltnis implements IValidation {
    /** @var integer */
    protected $dienstverhaeltnis_id;
    /** @var integer */
    protected $unternehmen;  
    /** @var string */
    protected $vertragsart_kurzbz;
    protected $gueltig_ab;
    protected $gueltig_bis;
	
	protected $isvalid;
	protected $validationerrors;

	public function __construct()
	{
		$this->isvalid = false;
		$this->validationerrors = array();
	}
	
    public function toStdClass(): \stdClass
	{
		$tmp = array(
			'dienstverhaeltnis_id' => $this->getDienstverhaeltnisId(),
			'vertragsart_kurzbz' => $this->getVertragsartKurzbz(),
            'unternehmen' => $this->getUnternehmen(),
			'gueltig_ab' => $this->getGueltigAb(),
            'gueltig_bis' => $this->getGueltigBis(),
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}


    public function __toString()
	{
		$txt = <<<EOTXT
		dienstverhaeltnis_id: {$this->getDienstverhaeltnisId()}
		vertragsart_kurzbz: {$this->getVertragsartKurzbz()}
		gueltig_ab: {$this->getGueltigAb()}
        gueltig_bis: {$this->getGueltigBis()}

EOTXT;
		return $txt;
	}


    /**
     * Get the value of dienstverhaeltnis_id
     */
    public function getDienstverhaeltnisId()
    {
        return $this->dienstverhaeltnis_id;
    }

    /**
     * Set the value of dienstverhaeltnis_id
     */
    public function setDienstverhaeltnisId($dienstverhaeltnis_id): self
    {
        $this->dienstverhaeltnis_id = $dienstverhaeltnis_id;

        return $this;
    }

    /**
     * Get the value of unternehmen
     */
    public function getUnternehmen()
    {
        return $this->unternehmen;
    }

    /**
     * Set the value of unternehmen
     */
    public function setUnternehmen($unternehmen): self
    {
        $this->unternehmen = $unternehmen;

        return $this;
    }

    /**
     * Get the value of vertragsart_kurzbz
     */
    public function getVertragsartKurzbz()
    {
        return $this->vertragsart_kurzbz;
    }

    /**
     * Set the value of vertragsart_kurzbz
     */
    public function setVertragsartKurzbz($vertragsart_kurzbz): self
    {
        $this->vertragsart_kurzbz = $vertragsart_kurzbz;

        return $this;
    }

    /**
     * Get the value of gueltig_ab
     */
    public function getGueltigAb()
    {
        return $this->gueltig_ab;
    }

    /**
     * Set the value of gueltig_ab
     */
    public function setGueltigAb($gueltig_ab): self
    {
        $this->gueltig_ab = $gueltig_ab;

        return $this;
    }

    /**
     * Get the value of gueltig_bis
     */
    public function getGueltigBis()
    {
        return $this->gueltig_bis;
    }

    /**
     * Set the value of gueltig_bis
     */
    public function setGueltigBis($gueltig_bis): self
    {
        $this->gueltig_bis = $gueltig_bis;

        return $this;
    }
	
	public function isValid()
	{
		return $this->isvalid;
	}

	public function getValidationErrors()
	{
		return $this->validationerrors;
	}
	
	public function validate() {
		//do Validation here
		
		// return status after Validation
		if( count($this->validationerrors) > 0 ) {
			$this->isvalid = false;
		} else {
			$this->isvalid = true;
		}
		
		return $this->isvalid;
	}
}