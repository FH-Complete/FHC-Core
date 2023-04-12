<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilZeitaufzeichnung extends Vertragsbestandteil
{
	protected $zeitaufzeichnung;
	protected $azgrelevant;
	protected $homeoffice;
	
	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_ZEITAUFZEICHNUNG);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->zeitaufzeichnung) && $this->setZeitaufzeichnung($data->zeitaufzeichnung);
		isset($data->azgrelevant) && $this->setAzgrelevant($data->azgrelevant);
		isset($data->homeoffice) && $this->setHomeoffice($data->homeoffice);
	}
	
	/**
	 * Get the value of zeitaufzeichnung
	 */
	public function getZeitaufzeichnung()
	{
		return $this->zeitaufzeichnung;
	}

	/**
	 * Set the value of zeitaufzeichnung
	 */
	public function setZeitaufzeichnung($zeitaufzeichnung): self
	{
		$this->zeitaufzeichnung = $zeitaufzeichnung;

		return $this;
	}

	/**
	 * Get the value of azgrelevant
	 */
	public function getAzgrelevant()
	{
		return $this->azgrelevant;
	}

	/**
	 * Set the value of azgrelevant
	 */
	public function setAzgrelevant($azgrelevant): self
	{
		$this->azgrelevant = $azgrelevant;

		return $this;
	}

	/**
	 * Get the value of homeoffice
	 */
	public function getHomeoffice()
	{
		return $this->homeoffice;
	}

	/**
	 * Set the value of homeoffice
	 */
	public function setHomeoffice($homeoffice): self
	{
		$this->homeoffice = $homeoffice;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'zeitaufzeichnung' => $this->getZeitaufzeichnung(),
			'azgrelevant' => $this->getAzgrelevant(),
			'homeoffice' => $this->getHomeoffice()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		zeitaufzeichnung: {$this->getZeitaufzeichnung()}
		azgrelevant: {$this->getAzgrelevant()}
		homeoffice: {$this->getHomeoffice()}

EOTXT;
		return parent::__toString() . $txt;
	}

	
}
