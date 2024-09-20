<?php
namespace vertragsbestandteil;

require_once __DIR__ . '/IValidation.php';
require_once __DIR__ . '/AbstractBestandteil.php';

use vertragsbestandteil\AbstractBestandteil;
use vertragsbestandteil\IValidation;

const TYPE_ECHT = 'echterdv';
const TYPE_STUDENTISCHE_HILFSKRAFT = 'studentischehilfskr';
const TYPE_FREI = 'freierdv';
const TYPE_EXTERN = 'externerlehrender';
const TYPE_GAST = 'gastlektor';
const TYPE_ECHT_FREI = 'echterfreier';
const TYPE_WERKVERTRAG = 'werkvertrag';
const TYPE_UEBERLASSUNG = 'ueberlassungsvertrag';

class Dienstverhaeltnis extends AbstractBestandteil {
    protected $dienstverhaeltnis_id;
	protected $mitarbeiter_uid;
    protected $vertragsart_kurzbz;
    protected $oe_kurzbz;
	protected $checkoverlap;
    protected $von;
    protected $bis;
	protected $insertamum;
	protected $insertvon;
	protected $updateamum;
	protected $updatevon;

	protected $dvendegrund_kurzbz;
	protected $dvendegrund_anmerkung;

	public function __construct()
	{
		parent::__construct();
		$this->checkoverlap = true;
	}
	
	public function hydrateByStdClass($data, $fromdb=false)
	{		
		$this->fromdb = $fromdb;
		isset($data->dienstverhaeltnis_id) && $this->setDienstverhaeltnis_id($data->dienstverhaeltnis_id);
		isset($data->mitarbeiter_uid) && $this->setMitarbeiter_uid($data->mitarbeiter_uid);
		isset($data->vertragsart_kurzbz) && $this->setVertragsart_kurzbz($data->vertragsart_kurzbz);
		isset($data->checkoverlap) && $this->setCheckoverlap($data->checkoverlap);
		isset($data->oe_kurzbz) && $this->setOe_kurzbz($data->oe_kurzbz);		
		isset($data->von) && $this->setVon($data->von);
		isset($data->bis) && $this->setBis($data->bis);
		isset($data->insertamum) && $this->setInsertamum($data->insertamum);
		isset($data->insertvon) && $this->setInsertvon($data->insertvon);
		isset($data->updateamum) && $this->setUpdateamum($data->updateamum);
		isset($data->updatevon) && $this->setUpdatevon($data->updatevon);
		isset($data->dvendegrund_kurzbz) && $this->setDvendegrund_kurzbz($data->dvendegrund_kurzbz);
		isset($data->dvendegrund_anmerkung) && $this->setDvendegrund_anmerkung($data->dvendegrund_anmerkung);
		$this->fromdb = false;
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
			'updatevon' => $this->getUpdatevon(), 
			'dvendegrund_kurzbz' => $this->getDvendegrund_kurzbz(), 
			'dvendegrund_anmerkung' => $this->getDvendegrund_anmerkung()
		);
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
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

	public function getDvendegrund_kurzbz()
	{
	    return $this->dvendegrund_kurzbz;
	}

	public function getDvendegrund_anmerkung()
	{
	    return $this->dvendegrund_anmerkung;
	}

	public function setDienstverhaeltnis_id($dienstverhaeltnis_id)
	{
		$this->markDirty('dienstverhaeltnis_id', $this->dienstverhaeltnis_id, $dienstverhaeltnis_id);
		$this->dienstverhaeltnis_id = $dienstverhaeltnis_id;
		return $this;
	}

	public function setMitarbeiter_uid($mitarbeiter_uid)
	{
		$this->markDirty('mitarbeiter_uid', $this->mitarbeiter_uid, $mitarbeiter_uid);
		$this->mitarbeiter_uid = $mitarbeiter_uid;
		return $this;
	}

	public function setVertragsart_kurzbz($vertragsart_kurzbz)
	{
		$this->markDirty('vertragsart_kurzbz', $this->vertragsart_kurzbz, $vertragsart_kurzbz);
		$this->vertragsart_kurzbz = $vertragsart_kurzbz;
		return $this;
	}

	public function setCheckoverlap(bool $checkoverlap)
	{
		$this->checkoverlap = $checkoverlap;
	}
	
	public function setOe_kurzbz($oe_kurzbz)
	{
		$this->markDirty('oe_kurzbz', $this->oe_kurzbz, $oe_kurzbz);
		$this->oe_kurzbz = $oe_kurzbz;
		return $this;
	}

	public function setVon($von)
	{
		$this->markDirty('von', $this->von, $von);
		$this->von = $von;
		return $this;
	}

	public function setBis($bis)
	{
		$this->markDirty('bis', $this->bis, $bis);
		$this->bis = $bis;
		return $this;
	}

	public function setInsertamum($insertamum)
	{
		$this->markDirty('insertamum', $this->insertamum, $insertamum);
		$this->insertamum = $insertamum;
		return $this;
	}

	public function setInsertvon($insertvon)
	{
		$this->markDirty('insertvon', $this->insertvon, $insertvon);
		$this->insertvon = $insertvon;
		return $this;
	}

	public function setUpdateamum($updateamum)
	{
		$this->markDirty('updateamum', $this->updateamum, $updateamum);
		$this->updateamum = $updateamum;
		return $this;
	}

	public function setUpdatevon($updatevon)
	{
		$this->markDirty('updatevon', $this->updatevon, $updatevon);
		$this->updatevon = $updatevon;
		return $this;
	}
	
	public function setDvendegrund_kurzbz($dvendegrund_kurzbz)
	{
	    $this->markDirty('dvendegrund_kurzbz', $this->dvendegrund_kurzbz, $dvendegrund_kurzbz);
	    $this->dvendegrund_kurzbz = $dvendegrund_kurzbz;
	    return $this;
	}

	public function setDvendegrund_anmerkung($dvendegrund_anmerkung)
	{
	    $this->markDirty('dvendegrund_anmerkung', $this->dvendegrund_anmerkung, $dvendegrund_anmerkung);
	    $this->dvendegrund_anmerkung = $dvendegrund_anmerkung;
	    return $this;
	}

	public function validate() {		
		//do Validation here
		$ci = get_instance();
		$ci->load->library('vertragsbestandteil/VertragsbestandteilLib', 
            null, 'VertragsbestandteilLib');
		
		if( empty($this->mitarbeiter_uid) ) {
			$this->validationerrors[] = 'Mitarbeiter_UID fehlt.';
		}

		if( empty($this->oe_kurzbz) ) {
			$this->validationerrors[] = 'Unternehmen fehlt.';
		}

		if( empty($this->vertragsart_kurzbz) ) {
			$this->validationerrors[] = 'Vertragsart fehlt.';
		}
		
		$von = \DateTimeImmutable::createFromFormat('Y-m-d', $this->von);
		$bis = \DateTimeImmutable::createFromFormat('Y-m-d', $this->bis);
		
		if( false === $von ) {
			$this->validationerrors[] = 'Beginn muss ein gültiges Datum sein.';
		}
		
		if( $this->bis !== null && $bis === false ) {
			$this->validationerrors[] = 'Ende muss ein gültiges Datum oder leer sein.';
		}
		
		if( $this-> bis !== null && $von && $bis && $von > $bis ) {
			$this->validationerrors[] = 'Das Beginndatum muss vor dem Endedatum liegen.';
		}
		
		if( $this->checkoverlap && !(in_array($this->vertragsart_kurzbz, array('werkvertrag', 'studentischehilfskr')) )
			&& $ci->VertragsbestandteilLib->isOverlappingExistingDV($this) ) 
		{
			$this->validationerrors[] = 'Es existiert bereits ein überlappendes Dienstverhältnis';
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