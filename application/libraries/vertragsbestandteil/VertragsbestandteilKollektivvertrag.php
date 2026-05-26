<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilKollektivvertrag extends Vertragsbestandteil
{
	protected $kv_jahre;
	protected $verwendungsgruppe_kurzbz;
	protected $kommentar;


	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_KOLLEKTIVVERTRAG);
	}

	public function getKv_jahre()
	{
		return $this->kv_jahre;
	}

	public function setKv_jahre($kv_jahre): self
	{
		$this->markDirty('kv_jahre', $this->kv_jahre, $kv_jahre);
		$this->kv_jahre = $kv_jahre;
		return $this;
	}

	public function getVerwendungsgruppe_kurzbz()
	{
		return $this->verwendungsgruppe_kurzbz;
	}

	public function setVerwendungsgruppe_kurzbz($verwendungsgruppe_kurzbz): self
	{
		$this->markDirty('verwendungsgruppe_kurzbz', $this->verwendungsgruppe_kurzbz, $verwendungsgruppe_kurzbz);
		$this->verwendungsgruppe_kurzbz = $verwendungsgruppe_kurzbz;
		return $this;
	}

	public function getKommentar()
	{
		return $this->kommentar;
	}

	public function setKommentar($kommentar): self
	{
		$this->markDirty('kommentar', $this->kommentar, $kommentar);
		$this->kommentar = $kommentar;
		return $this;
	}

	
	
	
	public function hydrateByStdClass($data, $fromdb=false)
	{
		parent::hydrateByStdClass($data, $fromdb);
		$this->fromdb = $fromdb;
		isset($data->verwendungsgruppe_kurzbz) && $this->setVerwendungsgruppe_kurzbz($data->verwendungsgruppe_kurzbz);
		isset($data->kv_jahre) && $this->setKv_jahre($data->kv_jahre);
		isset($data->kommentar) && $this->setKommentar($data->kommentar);
		$this->fromdb = false;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'kv_jahre' => $this->getKv_jahre(),
			'verwendungsgruppe_kurzbz' => $this->getVerwendungsgruppe_kurzbz(),
			'kommentar' => $this->getKommentar(),
		);
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		verwendungsgruppe_kurzbz: {$this->getVerwendungsgruppe_kurzbz()}

EOTXT;
		return parent::__toString() . $txt;
	}

	/* public function validate()
	{
		if( !(filter_var($this->tage, FILTER_VALIDATE_INT, 
				array(
					'options' => array(
						'min_range' => 1,
						'max_range' => 50
					)
				)
			)) ) {
			$this->validationerrors[] = 'Urlaubsanspruch muss eine Tagesanzahl im Bereich 1 bis 50 sein.';
		}
		
		return parent::validate();
	} */
}
