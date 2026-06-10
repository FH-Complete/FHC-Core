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
	
	public function hydrateByStdClass($data, $fromdb=false)
	{
		parent::hydrateByStdClass($data, $fromdb);
		$this->fromdb = $fromdb;
		isset($data->tage) && $this->setTage($data->tage);
		$this->fromdb = false;
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
		$this->markDirty('tage', $this->tage, $tage);
		$this->tage = $tage;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'tage' => $this->getTage(),
		);
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		tage: {$this->getTage()}

EOTXT;
		return parent::__toString() . $txt;
	}

	public function validate()
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
	}
}
