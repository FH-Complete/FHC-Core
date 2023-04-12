<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

class VertragsbestandteilKuendigungsfrist extends Vertragsbestandteil
{
	protected $arbeitgeber_frist;
	protected $arbeitnehmer_frist;
	
	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_KUENDIGUNGSFRIST);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->arbeitgeber_frist) && $this->setArbeitgeberFrist($data->arbeitgeber_frist);
		isset($data->arbeitnehmer_frist) && $this->setArbeitnehmerFrist($data->arbeitnehmer_frist);
	}
	
	/**
	 * Get the value of arbeitgeber_frist
	 */
	public function getArbeitgeberFrist()
	{
		return $this->arbeitgeber_frist;
	}

	/**
	 * Set the value of arbeitgeber_frist
	 */
	public function setArbeitgeberFrist($arbeitgeber_frist): self
	{
		$this->arbeitgeber_frist = $arbeitgeber_frist;

		return $this;
	}

	/**
	 * Get the value of arbeitnehmer_frist
	 */
	public function getArbeitnehmerFrist()
	{
		return $this->arbeitnehmer_frist;
	}

	/**
	 * Set the value of arbeitnehmer_frist
	 */
	public function setArbeitnehmerFrist($arbeitnehmer_frist): self
	{
		$this->arbeitnehmer_frist = $arbeitnehmer_frist;

		return $this;
	}	

	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'arbeitgeber_frist' => $this->getArbeitgeberFrist(),
			'arbeitnehmer_frist' => $this->getArbeitnehmerFrist()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		arbeitgeber_frist: {$this->getArbeitgeberFrist()}
		arbeitnehmer_frist: {$this->getArbeitnehmerFrist()}

EOTXT;
		return parent::__toString() . $txt;
	}

	
}
