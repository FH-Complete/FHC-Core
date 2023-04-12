<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilUrlaubsanspruch extends Vertragsbestandteil
{
	protected $tage;
	
	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_URLAUBSANSPRUCH);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->tage) && $this->setTage($data->tage);
	}
	
	/**
	 * Get the value of tage
	 */
	public function getTage()
	{
		return $this->tage;
	}

	/**
	 * Set the value of tage
	 */
	public function setTage($tage): self
	{
		$this->tage = $tage;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'tage' => $this->getTage(),
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		tage: {$this->getTage()}

EOTXT;
		return parent::__toString() . $txt;
	}

	
}
