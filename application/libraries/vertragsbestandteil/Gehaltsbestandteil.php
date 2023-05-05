<?php
namespace vertragsbestandteil;

/**
 * Salary always depends on employment (Dienstverhältnis) and optionally on part of contract (Vetragsbestandteil)
 */
class Gehaltsbestandteil implements IValidation
{
	protected $gehaltsbestandteil_id;
	protected $dienstverhaeltnis_id;
	protected $vertragsbestandteil_id;
	protected $gehaltstyp_kurzbz;
	protected $von;
    protected $bis;
	protected $anmerkung;
	protected $grundbetrag;
	protected $betrag_valorisiert;
	protected $valorisierungssperre;
	protected $valorisierung;
	protected $auszahlungen;
	
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
		isset($data->gehaltsbestandteil_id) && $this->setGehaltsbestandteil_id($data->gehaltsbestandteil_id);
		isset($data->dienstverhaeltnis_id) && $this->setDienstverhaeltnis_id($data->dienstverhaeltnis_id);
		isset($data->vertragsbestandteil_id) && $this->setVertragsbestandteil_id($data->vertragsbestandteil_id);
		isset($data->gehaltstyp_kurzbz) && $this->setGehaltstyp_kurzbz($data->gehaltstyp_kurzbz);
		isset($data->von) && $this->setVon($data->von);
		isset($data->bis) && $this->setBis($data->bis);
		isset($data->anmerkung) && $this->setAnmerkung($data->anmerkung);
		isset($data->grundbetrag) && $this->setGrundbetrag($data->grundbetrag);
		isset($data->betrag_valorisiert) && $this->setBetrag_valorisiert($data->betrag_valorisiert);
		isset($data->valorisierungssperre) && $this->setValorisierungssperre($data->valorisierungssperre);
		isset($data->valorisierung) && $this->setValorisierung($data->valorisierung);
		isset($data->auszahlungen) && $this->setAuszahlungen($data->auszahlungen);
		
		isset($data->insertamum) && $this->setInsertamum($data->insertamum);
		isset($data->insertvon) && $this->setInsertvon($data->insertvon);
		isset($data->updateamum) && $this->setUpdateamum($data->updateamum);
		isset($data->updatevon) && $this->setUpdatevon($data->updatevon);
	}
	
	public function getGehaltsbestandteil_id()
	{
		return $this->gehaltsbestandteil_id;
	}

	public function getDienstverhaeltnis_id()
	{
		return $this->dienstverhaeltnis_id;
	}

	public function getVertragsbestandteil_id()
	{
		return $this->vertragsbestandteil_id;
	}

	public function getGehaltstyp_kurzbz()
	{
		return $this->gehaltstyp_kurzbz;
	}

	public function getVon()
	{
		return $this->von;
	}

	public function getBis()
	{
		return $this->bis;
	}

	public function getAnmerkung()
	{
		return $this->anmerkung;
	}

	public function getGrundbetrag()
	{
		return $this->grundbetrag;
	}

	public function getBetrag_valorisiert()
	{
		return $this->betrag_valorisiert;
	}

	public function getValorisierungssperre()
	{
		return $this->valorisierungssperre;
	}

	public function getValorisierung()
	{
		return $this->valorisierung;
	}

	public function getAuszahlungen()
	{
		return $this->auszahlungen;
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

	public function setGehaltsbestandteil_id($gehaltsbestandteil_id)
	{
		$this->gehaltsbestandteil_id = $gehaltsbestandteil_id;
		return $this;
	}

	public function setDienstverhaeltnis_id($dienstverhaeltnis_id)
	{
		$this->dienstverhaeltnis_id = $dienstverhaeltnis_id;
		return $this;
	}

	public function setVertragsbestandteil_id($vertragsbestandteil_id)
	{
		$this->vertragsbestandteil_id = $vertragsbestandteil_id;
		return $this;
	}

	public function setGehaltstyp_kurzbz($gehaltstyp_kurzbz)
	{
		$this->gehaltstyp_kurzbz = $gehaltstyp_kurzbz;
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

	public function setAnmerkung($anmerkung)
	{
		$this->anmerkung = $anmerkung;
		return $this;
	}

	public function setGrundbetrag($grundbetrag)
	{
		$this->grundbetrag = $grundbetrag;
		return $this;
	}

	public function setBetrag_valorisiert($betrag_valorisiert)
	{
		$this->betrag_valorisiert = $betrag_valorisiert;
		return $this;
	}

	public function setValorisierungssperre($valorisierungssperre)
	{
		$this->valorisierungssperre = $valorisierungssperre;
		return $this;
	}

	public function setValorisierung($valorisierung)
	{
		$this->valorisierung = $valorisierung;
		return $this;
	}

	public function setAuszahlungen($auszahlungen)
	{
		$this->auszahlungen = $auszahlungen;
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
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'gehaltsbestandteil_id' => $this->getGehaltsbestandteil_id(),
			'dienstverhaeltnis_id' => $this->getDienstverhaeltnis_id(),
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'gehaltstyp_kurzbz' => $this->getGehaltstyp_kurzbz(),
			'von' => $this->getVon(),
			'bis' => $this->getBis(),
			'anmerkung' => $this->getAnmerkung(),
			'grundbetrag' => $this->getGrundbetrag(),
			'betrag_valorisiert' => $this->getBetrag_valorisiert(),
			'valorisierungssperre' => $this->getValorisierungssperre(),
			'valorisierung' => $this->getValorisierung(),
			'auszahlungen' => $this->getAuszahlungen(),
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
		gehaltsbestandteil_id: {$this->getGehaltsbestandteil_id()}
		dienstverhaeltnis_id: {$this->getDienstverhaeltnis_id()}
		vertragsbestandteil_id: {$this->getVertragsbestandteil_id()}
		gehaltstyp_kurzbz: {$this->getGehaltstyp_kurzbz()}
		von: {$this->getVon()}
		bis: {$this->getBis()}
		anmerkung: {$this->getAnmerkung()}
		grundbetrag: {$this->getGrundbetrag()}
		betrag_valorisiert: {$this->getBetrag_valorisiert()}
		valorisierungssperre: {$this->getValorisierungssperre()}
		valorisierung: {$this->getValorisierung()}
		auszahlungen: {$this->getAuszahlungen()}
		insertamum: {$this->getInsertamum()}
		insertvon: {$this->getInsertvon()}
		updateamum: {$this->getUpdateamum()}
		updatevon: {$this->getUpdatevon()}

EOTXT;
		return $txt;
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
		if( empty($this->gehaltstyp_kurzbz) ) 
		{
			$this->validationerrors[] = "Ein Gehaltstyp muss ausgewählt sein.";
		}
		
		if( empty($this->grundbetrag) )
		{
			$this->validationerrors[] = "Betrag fehlt.";
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
		
		// return status after Validation
		if( count($this->validationerrors) > 0 ) {
			$this->isvalid = false;
		} else {
			$this->isvalid = true;
		}
		
		return $this->isvalid;
	}
}
