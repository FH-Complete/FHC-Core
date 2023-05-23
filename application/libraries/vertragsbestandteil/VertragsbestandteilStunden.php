<?php

namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;



/**
 * Description of VertragsbestandteilStunden
 *
 * @author bambi
 */
class VertragsbestandteilStunden extends Vertragsbestandteil
{
	protected $wochenstunden;
	protected $teilzeittyp_kurzbz;
	
	public function __construct()
	{
		parent::__construct();
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_STUNDEN);
	}
	
	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->wochenstunden) && $this->setWochenstunden($data->wochenstunden);
		isset($data->teilzeittyp_kurzbz) && $this->setTeilzeittyp_kurzbz($data->teilzeittyp_kurzbz);
	}
	
	public function getWochenstunden()
	{
		return $this->wochenstunden;
	}

	public function getTeilzeittyp_kurzbz()
	{
		return $this->teilzeittyp_kurzbz;
	}

	public function setWochenstunden($wochenstunden)
	{
		$this->wochenstunden = $wochenstunden;
		return $this;
	}

	public function setTeilzeittyp_kurzbz($teilzeittyp_kurzbz)
	{
		$this->teilzeittyp_kurzbz = $teilzeittyp_kurzbz;
		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'wochenstunden' => $this->getWochenstunden(),
			'teilzeittyp_kurzbz' => $this->getTeilzeittyp_kurzbz()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}
	
	public function __toString() 
	{
		$txt = <<<EOTXT
		wochenstunden: {$this->getWochenstunden()}
		teilzeittyp_kurzbz: {$this->getTeilzeittyp_kurzbz()}

EOTXT;
		return parent::__toString() . $txt;
	}
	
	public function validate()
	{
		if( !(filter_var($this->wochenstunden, FILTER_VALIDATE_FLOAT, 
				array(
					'options' => array(
						'min_range' => 0,
						'max_range' => 100
					)
				)
			)) ) {
			$this->validationerrors[] = 'Stunden muss eine Kommazahl im Bereich 0 bis 100 sein.';
		}
		
		return parent::validate();
	}
}
