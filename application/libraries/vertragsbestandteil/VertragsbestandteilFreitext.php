<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilFreitext extends Vertragsbestandteil
{
	protected $anmerkung;
	protected $kuendigungrelevant;
	protected $freitexttyp_kurzbz;
	
	public function __construct()
	{
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_FREITEXT);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->anmerkung) && $this->setAnmerkung($data->anmerkung);
		isset($data->kuendigungrelevant) && $this->setKuendigungrelevant($data->kuendigungrelevant);
		isset($data->freitexttyp_kurzbz) && $this->setFreitexttypKurzbz($data->freitexttyp_kurzbz);
	}
		
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'anmerkung' => $this->getAnmerkung(),
			'kuendigungrelevant' => $this->getKuendigungrelevant(),
			'freitexttyp_kurzbz' => $this->getFreitexttypKurzbz()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		anmerkung: {$this->getAnmerkung()}
		kuendigungrelevant: {$this->getKuendigungrelevant()}
		freitexttyp_kurzbz: {$this->getFreitexttypKurzbz()}

EOTXT;
		return parent::__toString() . $txt;
	}

	/**
	 * Get the value of anmerkung
	 */
	public function getAnmerkung()
	{
		return $this->anmerkung;
	}

	/**
	 * Set the value of anmerkung
	 */
	public function setAnmerkung($anmerkung): self
	{
		$this->anmerkung = $anmerkung;

		return $this;
	}

	/**
	 * Get the value of kuendigungrelevant
	 */
	public function getKuendigungrelevant()
	{
		return $this->kuendigungrelevant;
	}

	/**
	 * Set the value of kuendigungrelevant
	 */
	public function setKuendigungrelevant($kuendigungrelevant): self
	{
		$this->kuendigungrelevant = $kuendigungrelevant;

		return $this;
	}

	/**
	 * Get the value of freitexttyp_kurzbz
	 */
	public function getFreitexttypKurzbz()
	{
		return $this->freitexttyp_kurzbz;
	}

	/**
	 * Set the value of freitexttyp_kurzbz
	 */
	public function setFreitexttypKurzbz($freitexttyp_kurzbz): self
	{
		$this->freitexttyp_kurzbz = $freitexttyp_kurzbz;

		return $this;
	}
}
