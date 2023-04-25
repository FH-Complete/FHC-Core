<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilBefristung extends Vertragsbestandteil
{
	protected $befristet;
	protected $befristet_bis;
	
	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_BEFRISTUNG);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->befristet) && $this->setBefristet($data->befristet);
		isset($data->befristet_bis) && $this->setBefristetBis($data->befristet_bis);
	}
	

	/**
	 * Get the value of befristet
	 */
	public function getBefristet()
	{
		return $this->befristet;
	}

	/**
	 * Set the value of befristet
	 */
	public function setBefristet($befristet): self
	{
		$this->befristet = $befristet;

		return $this;
	}

	/**
	 * Get the value of befristet_bis
	 */
	public function getBefristetBis()
	{
		return $this->befristet_bis;
	}

	/**
	 * Set the value of befristet_bis
	 */
	public function setBefristetBis($befristet_bis): self
	{
		$this->befristet_bis = $befristet_bis;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'befristet' => $this->getBefristet(),
			'befristet_bis' => $this->getBefristetBis()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		befristet: {$this->getBefristet()}
		befristet_bis: {$this->getBefristetBis()}

EOTXT;
		return parent::__toString() . $txt;
	}

	public function validate()
	{
		return parent::validate();
	}
}
