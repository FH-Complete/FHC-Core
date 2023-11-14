<?php
namespace vertragsbestandteil;

use vertragsbestandteil\AbstractBestandteil;

/**
 * Description of Vertragsbestandteil
 *
 * @author bambi
 */
abstract class Vertragsbestandteil extends AbstractBestandteil implements \JsonSerializable
{
	protected $vertragsbestandteil_id;
	protected $dienstverhaeltnis_id;
	protected $von;
	protected $bis;
	protected $vertragsbestandteiltyp_kurzbz;
	protected $insertamum;
	protected $insertvon;
	protected $updateamum;
	protected $updatevon;
	
	protected $gehaltsbestandteile;

	public function __construct()
	{
		parent::__construct();
		$this->gehaltsbestandteile = array();
	}
	
	public function hydrateByStdClass($data, $fromdb=false)
	{
		$this->fromdb = $fromdb;
		isset($data->vertragsbestandteil_id) && $this->setVertragsbestandteil_id($data->vertragsbestandteil_id);
		isset($data->dienstverhaeltnis_id) && $this->setDienstverhaeltnis_id($data->dienstverhaeltnis_id);
		isset($data->von) && $this->setVon($data->von);
		isset($data->bis) && $this->setBis($data->bis);
		isset($data->vertragsbestandteiltyp_kurzbz) && $this->setVertragsbestandteiltyp_kurzbz($data->vertragsbestandteiltyp_kurzbz);
		isset($data->insertamum) && $this->setInsertamum($data->insertamum);
		isset($data->insertvon) && $this->setInsertvon($data->insertvon);
		isset($data->updateamum) && $this->setUpdateamum($data->updateamum);
		isset($data->updatevon) && $this->setUpdatevon($data->updatevon);
		$this->fromdb = false;
	}
	
	public function addGehaltsbestandteil(Gehaltsbestandteil $gehaltsbestandteil)
	{
		$gehaltsbestandteil->setDienstverhaeltnis_id($this->getDienstverhaeltnis_id());
		$gehaltsbestandteil->setVertragsbestandteil_id($this->getVertragsbestandteil_id());
		$this->gehaltsbestandteile[] = $gehaltsbestandteil;
		return $this;
	}
	
	public function getGehaltsbestandteile() 
	{
		return $this->gehaltsbestandteile;
	}
	
	public function getVertragsbestandteil_id()
	{
		return $this->vertragsbestandteil_id;
	}

	public function getDienstverhaeltnis_id()
	{
		return $this->dienstverhaeltnis_id;
	}

	public function getVon()
	{
		return $this->von;
	}

	public function getBis()
	{
		return $this->bis;
	}

	public function getVertragsbestandteiltyp_kurzbz()
	{
		return $this->vertragsbestandteiltyp_kurzbz;
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

	public function setGehaltsbestandteile($gehaltsbestandteile)
	{
		$this->gehaltsbestandteile = $gehaltsbestandteile;
		return $this;
	}
	
	public function setVertragsbestandteil_id($vertragsbestandteil_id)
	{
		$this->markDirty('vertragsbestandteil_id', $this->vertragsbestandteil_id, $vertragsbestandteil_id);
		$this->vertragsbestandteil_id = $vertragsbestandteil_id;
		foreach ($this->gehaltsbestandteile as $gehaltsbestandteil)
		{
			$gehaltsbestandteil->setVertragsbestandteil_id($vertragsbestandteil_id);
		}
		return $this;
	}

	public function setDienstverhaeltnis_id($dienstverhaeltnis_id)
	{
		$this->markDirty('dienstverhaeltnis_id', $this->dienstverhaeltnis_id, $dienstverhaeltnis_id);
		$this->dienstverhaeltnis_id = $dienstverhaeltnis_id;
		foreach ($this->gehaltsbestandteile as $gehaltsbestandteil)
		{
			$gehaltsbestandteil->setDienstverhaeltnis_id($dienstverhaeltnis_id);
		}
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

	public function setVertragsbestandteiltyp_kurzbz($vertragsbestandteiltyp_kurzbz)
	{
		$this->markDirty('vertragsbestandteiltyp_kurzbz', $this->vertragsbestandteiltyp_kurzbz, $vertragsbestandteiltyp_kurzbz);
		$this->vertragsbestandteiltyp_kurzbz = $vertragsbestandteiltyp_kurzbz;
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
	
	public function baseToStdClass() {
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'dienstverhaeltnis_id' => $this->getDienstverhaeltnis_id(),
			'von' => $this->getVon(),
			'bis' => $this->getBis(),
			'vertragsbestandteiltyp_kurzbz' => $this->getVertragsbestandteiltyp_kurzbz(),
			'insertamum' => $this->getInsertamum(),
			'insertvon' => $this->getInsertvon(),
			'updateamum' => $this->getUpdateamum(),
			'updatevon' => $this->getUpdatevon(),
		);
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
		return (object) $tmp;
	}

	public function jsonSerialize()
    {
		$vars = get_object_vars($this);
		unset($vars['CI']);
		
		// TODO cleanup workaroung for vb freitext where db column is anmerkung and formfield is freitext
		if( isset($vars['anmerkung']) ) {
			$vars['freitext'] = $vars['anmerkung'];
		}
		
        return $vars;
    }
	
	public function __toString() 
	{
		return <<<EOTXT
		vertragsbestandteil_id: {$this->getVertragsbestandteil_id()}
		dienstverhaeltnis_id: {$this->getDienstverhaeltnis_id()}
		von: {$this->getVon()}
		bis: {$this->getBis()}
		vertragsbestandteiltyp_kurzbz: {$this->getVertragsbestandteiltyp_kurzbz()}
		insertamum: {$this->getInsertamum()}
		insertvon: {$this->getInsertvon()}
		updateamum: {$this->getUpdateamum()}
		updatevon: {$this->getUpdatevon()}

EOTXT;
		
	}
	
	public function beforePersist() {
		// can be overridden in childs
	}
	
	public function afterDelete() {
		// can be overridden in childs
	}
	
	public function validate() {
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
		
		if( count($this->validationerrors) > 0 ) {
			$this->isvalid = false;
		} else {
			$this->isvalid = true;
		}
		
		return $this->isvalid;
	}
	
	public abstract function toStdClass();
}
