<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilKarenz extends Vertragsbestandteil
{
	protected $karenztyp_kurzbz;
	protected $tatsaechlicher_geburtstermin;
	protected $geplanter_geburtstermin;
	
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
		isset($data->geplanter_geburtstermin) && $this->setGeplanterGeburtstermin($data->geplanter_geburtstermin);
		isset($data->tatsaechlicher_geburtstermin) && $this->setGeburtstermin($data->tatsaechlicher_geburtstermin);		
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
	 * Get the value of tatsaechlicher_geburtstermin
	 */
	public function getTatsaechlicherGeburtstermin()
	{
		return $this->tatsaechlicher_geburtstermin;
	}

	/**
	 * Set the value of tatsaechlicher_geburtstermin
	 */
	public function setTatsaechlicherGeburtstermin($tatsaechlicher_geburtstermin): self
	{
		$this->tatsaechlicher_geburtstermin = $tatsaechlicher_geburtstermin;

		return $this;
	}

	/**
	 * Get the value of geplanter_geburtstermin
	 */
	public function getGeplanterGeburtstermin()
	{
		return $this->geplanter_geburtstermin;
	}

	/**
	 * Set the value of geplanter_geburtstermin
	 */
	public function setGeplanterGeburtstermin($geplanter_geburtstermin): self
	{
		$this->geplanter_geburtstermin = $geplanter_geburtstermin;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'karenztyp_kurzbz' => $this->getKarenztypKurzbz(),
			'tatsaechlicher_geburtstermin' => $this->getTatsaechlicherGeburtstermin(),
			'geplanter_geburtstermin' => $this->getGeplanterGeburtstermin()
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
		tatsaechlicher_geburtstermin: {$this->getTatsaechlicherGeburtstermin()}
		geplanter_geburtstermin: {$this->getGeplanterGeburtstermin()}

EOTXT;
		return parent::__toString() . $txt;
	}

	public function validate()
	{
		return parent::validate();
	}
}
