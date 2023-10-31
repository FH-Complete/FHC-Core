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
	
	public function hydrateByStdClass($data, $fromdb=false)
	{
		parent::hydrateByStdClass($data, $fromdb);
		$this->fromdb = $fromdb;
		isset($data->arbeitgeber_frist) && $this->setArbeitgeberFrist($data->arbeitgeber_frist);
		isset($data->arbeitnehmer_frist) && $this->setArbeitnehmerFrist($data->arbeitnehmer_frist);
		$this->fromdb = false;
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
		$this->markDirty('arbeitgeber_frist', $this->arbeitgeber_frist, $arbeitgeber_frist);
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
		$this->markDirty('arbeitnehmer_frist', $this->arbeitnehmer_frist, $arbeitnehmer_frist);
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
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
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

	public function validate()
	{
		if( !(filter_var($this->arbeitgeber_frist, FILTER_VALIDATE_INT, 
				array(
					'options' => array(
						'min_range' => 0,
						'max_range' => 52
					)
				)
			)) ) {
			$this->validationerrors[] = 'Arbeitgeberfrist muss eine Wochenanzahl im Bereich 1 bis 52 sein.';
		}
		
		if( !(filter_var($this->arbeitnehmer_frist, FILTER_VALIDATE_INT, 
				array(
					'options' => array(
						'min_range' => 1,
						'max_range' => 52
					)
				)
			)) ) {
			$this->validationerrors[] = 'Arbeitnehmerfrist muss eine Wochenanzahl im Bereich 1 bis 52 sein.';
		}
		
		return parent::validate();
	}
}
