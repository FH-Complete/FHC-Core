<?php
namespace vertragsbestandteil;

require_once __DIR__ . '/IValidation.php';

const TYPE_ECHT = 'echterdv';
const TYPE_STUDENTISCHE_HILFSKRAFT = 'studentischehilfskr';
const TYPE_FREI = 'freierdv';
const TYPE_EXTERN = 'externerlehrender';
const TYPE_GAST = 'gastlektor';
const TYPE_ECHT_FREI = 'echterfreier';
const TYPE_WERKVERTRAG = 'werkvertrag';
const TYPE_UEBERLASSUNG = 'ueberlassungsvertrag';

class Dienstverhaeltnis implements IValidation {
    protected $dienstverhaeltnis_id;
	protected $mitarbeiter_uid;
    protected $vertragsart_kurzbz;
    protected $oe_kurzbz;      
    protected $von;
    protected $bis;
	protected $insertamum;
	protected $insertvon;
	protected $updateamum;
	protected $updatevon;
	
	protected $isvalid;
	protected $validationerrors;

	public function __construct()
	{
		$this->isvalid = false;
		$this->validationerrors = array();
	}
	
	public function hydrateByStdClass($data)
	{		
		isset($data->dienstverhaeltnis_id) && $this->setDienstverhaeltnis_id($data->dienstverhaeltnis_id);
		isset($data->mitarbeiter_uid) && $this->setMitarbeiter_uid($data->mitarbeiter_uid);
		isset($data->vertragsart_kurzbz) && $this->setVertragsart_kurzbz($data->vertragsart_kurzbz);
		isset($data->oe_kurzbz) && $this->setOe_kurzbz($data->oe_kurzbz);		
		isset($data->von) && $this->setVon($data->von);
		isset($data->bis) && $this->setBis($data->bis);
		isset($data->insertamum) && $this->setInsertamum($data->insertamum);
		isset($data->insertvon) && $this->setInsertvon($data->insertvon);
		isset($data->updateamum) && $this->setUpdateamum($data->updateamum);
		isset($data->updatevon) && $this->setUpdatevon($data->updatevon);
	}
	
    public function toStdClass(): \stdClass
	{
		$tmp = array(
			'dienstverhaeltnis_id' => $this->getDienstverhaeltnis_id(),
			'mitarbeiter_uid' => $this->getMitarbeiter_uid(),
			'vertragsart_kurzbz' => $this->getVertragsart_kurzbz(),
            'oe_kurzbz' => $this->getOe_kurzbz(),
			'von' => $this->getVon(),
            'bis' => $this->getBis(),
			'insertamum' => $this->getInsertamum(),
			'insertvon' => $this->getInsertvon(),
			'updateamum' => $this->getUpdateamum(),
			'updatevon' => $this->getUpdatevon()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}


    public function __toString()
	{
		$txt = <<<EOTXT
		dienstverhaeltnis_id: {$this->getDienstverhaeltnis_id()}
		mitarbeiter_uid: {$this->getMitarbeiter_uid()}
		vertragsart_kurzbz: {$this->getVertragsart_kurzbz()}
		oe_kurzbz: {$this->getOe_kurzbz()}
		von: {$this->getVon()}
        bis: {$this->getBis()}

EOTXT;
		return $txt;
	}

	public function getDienstverhaeltnis_id()
	{
		return $this->dienstverhaeltnis_id;
	}

	public function getMitarbeiter_uid()
	{
		return $this->mitarbeiter_uid;
	}

	public function getVertragsart_kurzbz()
	{
		return $this->vertragsart_kurzbz;
	}

	public function getOe_kurzbz()
	{
		return $this->oe_kurzbz;
	}

	public function getVon()
	{
		return $this->von;
	}

	public function getBis()
	{
		return $this->bis;
	}

	public function getInsertamum()
	{
		return $this->insertamum;
	}

	public function getInsertvon()
	{
		return $this->insertvon;
	}

	public function getUpdateamum()
	{
		return $this->updateamum;
	}

	public function getUpdatevon()
	{
		return $this->updatevon;
	}

	public function setDienstverhaeltnis_id($dienstverhaeltnis_id)
	{
		$this->dienstverhaeltnis_id = $dienstverhaeltnis_id;
		return $this;
	}

	public function setMitarbeiter_uid($mitarbeiter_uid)
	{
		$this->mitarbeiter_uid = $mitarbeiter_uid;
		return $this;
	}

	public function setVertragsart_kurzbz($vertragsart_kurzbz)
	{
		$this->vertragsart_kurzbz = $vertragsart_kurzbz;
		return $this;
	}

	public function setOe_kurzbz($oe_kurzbz)
	{
		$this->oe_kurzbz = $oe_kurzbz;
		return $this;
	}

	public function setVon($von)
	{
		$this->von = $von;
		return $this;
	}

	public function setBis($bis)
	{
		$this->bis = $bis;
		return $this;
	}

	public function setInsertamum($insertamum)
	{
		$this->insertamum = $insertamum;
		return $this;
	}

	public function setInsertvon($insertvon)
	{
		$this->insertvon = $insertvon;
		return $this;
	}

	public function setUpdateamum($updateamum)
	{
		$this->updateamum = $updateamum;
		return $this;
	}

	public function setUpdatevon($updatevon)
	{
		$this->updatevon = $updatevon;
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
		
		if( empty($this->mitarbeiter_uid) ) {
			$this->validationerrors[] = 'Mitarbeiter_UID fehlt.';
		}

		if( empty($this->oe_kurzbz) ) {
			$this->validationerrors[] = 'Unternehmen fehlt.';
		}

		if( empty($this->vertragsart_kurzbz) ) {
			$this->validationerrors[] = 'Vertragsart fehlt.';
		}
		
		// return status after Validation
		if( count($this->validationerrors) > 0 ) {
			$this->isvalid = false;
		} else {
			$this->isvalid = true;
		}
		
		return $this->isvalid;
	}
}