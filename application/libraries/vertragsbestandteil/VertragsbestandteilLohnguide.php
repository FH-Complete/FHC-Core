<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilLohnguide extends Vertragsbestandteil
{
	protected $stellenbezeichnung;
	protected $vordienstzeit;
	protected $fachrichtung_kurzbz;
	protected $modellstelle_kurzbz;
	protected $kommentar_person;
	protected $kommentar_modellstelle;


	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_LOHNGUIDE);
	}

	public function getStellenbezeichnung()
	{
		return $this->stellenbezeichnung;
	}

	public function setStellenbezeichnung($stellenbezeichnung): self
	{
		$this->markDirty('stellenbezeichnung', $this->stellenbezeichnung, $stellenbezeichnung);
		$this->stellenbezeichnung = $stellenbezeichnung;
		return $this;
	}

	public function getVordienstzeit()
	{
		return $this->vordienstzeit;
	}

	public function setVordienstzeit($vordienstzeit): self
	{
		$this->markDirty('vordienstzeit', $this->vordienstzeit, $vordienstzeit);
		$this->vordienstzeit = $vordienstzeit;
		return $this;
	}

	public function getFachrichtung_kurzbz()
	{
		return $this->fachrichtung_kurzbz;
	}

	public function setFachrichtung_kurzbz($fachrichtung_kurzbz): self
	{
		$this->markDirty('fachrichtung_kurzbz', $this->fachrichtung_kurzbz, $fachrichtung_kurzbz);
		$this->fachrichtung_kurzbz = $fachrichtung_kurzbz;
		return $this;
	}

	public function getModellstelle_kurzbz()
	{
		return $this->modellstelle_kurzbz;
	}

	public function setModellstelle_kurzbz($modellstelle_kurzbz): self
	{
		$this->markDirty('modellstelle_kurzbz', $this->modellstelle_kurzbz, $modellstelle_kurzbz);
		$this->modellstelle_kurzbz = $modellstelle_kurzbz;
		return $this;
	}

	public function getKommentar_person()
	{
		return $this->kommentar_person;
	}

	public function setKommentar_person($kommentar_person): self
	{
		$this->markDirty('kommentar_person', $this->kommentar_person, $kommentar_person);
		$this->kommentar_person = $kommentar_person;
		return $this;
	}

	public function getKommentar_modellstelle()
	{
		return $this->kommentar_modellstelle;
	}

	public function setKommentar_modellstelle($kommentar_modellstelle): self
	{
		$this->markDirty('kommentar_modellstelle', $this->kommentar_modellstelle, $kommentar_modellstelle);
		$this->kommentar_modellstelle = $kommentar_modellstelle;
		return $this;
	}

	
	
	
	public function hydrateByStdClass($data, $fromdb=false)
	{
		parent::hydrateByStdClass($data, $fromdb);
		$this->fromdb = $fromdb;
		isset($data->fachrichtung_kurzbz) && $this->setFachrichtung_kurzbz($data->fachrichtung_kurzbz);
		isset($data->stellenbezeichnung) && $this->setStellenbezeichnung($data->stellenbezeichnung);
		isset($data->vordienstzeit) && $this->setVordienstzeit($data->vordienstzeit);
		isset($data->modellstelle_kurzbz) && $this->setModellstelle_kurzbz($data->modellstelle_kurzbz);
		isset($data->kommentar_person) && $this->setKommentar_person($data->kommentar_person);
		isset($data->kommentar_modellstelle) && $this->setKommentar_modellstelle($data->kommentar_modellstelle);
		$this->fromdb = false;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'stellenbezeichnung' => $this->getStellenbezeichnung(),
			'vordienstzeit' => $this->getVordienstzeit(),
			'fachrichtung_kurzbz' => $this->getFachrichtung_kurzbz(),
			'modellstelle_kurzbz' => $this->getModellstelle_kurzbz(),
			'kommentar_person' => $this->getKommentar_person(),
			'kommentar_modellstelle' => $this->getKommentar_modellstelle(),
		);
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		modellstelle_kurzbz: {$this->getModellstelle_kurzbz()}

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
