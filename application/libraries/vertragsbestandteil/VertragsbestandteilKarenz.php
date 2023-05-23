<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilKarenz extends Vertragsbestandteil
{
	protected $karenztyp_kurzbz;
	protected $geburtstermin;
	protected $geburtstermin_geplant;
	
	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_KARENZ);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->karenztyp_kurzbz) && $this->setKarenztypKurzbz($data->karenztyp_kurzbz);
		isset($data->geburtstermin_geplant) && $this->setGeburtsterminGeplant($data->geburtstermin_geplant);
		isset($data->geburtstermin) && $this->setGeburtstermin($data->geburtstermin);		
	}	
	
	/**
	 * Get the value of karenztyp_kurzbz
	 */
	public function getKarenztypKurzbz()
	{
		return $this->karenztyp_kurzbz;
	}

	/**
	 * Set the value of karenztyp_kurzbz
	 */
	public function setKarenztypKurzbz($karenztyp_kurzbz): self
	{
		$this->karenztyp_kurzbz = $karenztyp_kurzbz;

		return $this;
	}

	/**
	 * Get the value of geburtstermin
	 */
	public function getGeburtstermin()
	{
		return $this->geburtstermin;
	}

	/**
	 * Set the value of geburtstermin
	 */
	public function setGeburtstermin($geburtstermin): self
	{
		$this->geburtstermin = $geburtstermin;

		return $this;
	}

	/**
	 * Get the value of geburtstermin_geplant
	 */
	public function getGeburtsterminGeplant()
	{
		return $this->geburtstermin_geplant;
	}

	/**
	 * Set the value of geburtstermin_geplant
	 */
	public function setGeburtsterminGeplant($geburtstermin_geplant): self
	{
		$this->geburtstermin_geplant = $geburtstermin_geplant;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'karenztyp_kurzbz' => $this->getKarenztypKurzbz(),
			'geburtstermin' => $this->getGeburtstermin(),
			'geburtstermin_geplant' => $this->getGeburtsterminGeplant()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		karenztyp_kurzbz: {$this->getKarenztypKurzbz()}
		geburtstermin: {$this->getGeburtstermin()}
		geburtstermin_geplant: {$this->getGeburtsterminGeplant()}

EOTXT;
		return parent::__toString() . $txt;
	}

	public function validate()
	{
		return parent::validate();
	}
}
