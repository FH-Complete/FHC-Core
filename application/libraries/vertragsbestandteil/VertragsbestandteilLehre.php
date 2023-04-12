<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilLehre extends Vertragsbestandteil
{
	protected $inkludierte_lehre;
	
	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_LEHRE);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->inkludierte_lehre) && $this->setInkludierteLehre($data->inkludierte_lehre);
	}
	
	/**
	 * Get the value of inkludierte_lehre
	 */
	public function getInkludierteLehre()
	{
		return $this->inkludierte_lehre;
	}

	/**
	 * Set the value of inkludierte_lehre
	 */
	public function setInkludierteLehre($inkludierte_lehre): self
	{
		$this->inkludierte_lehre = $inkludierte_lehre;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'inkludierte_lehre' => $this->getInkludierteLehre(),
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		inkludierte_lehre: {$this->getInkludierteLehre()}

EOTXT;
		return parent::__toString() . $txt;
	}

	
}
