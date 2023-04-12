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
	protected $karenz;
	
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
		isset($data->karenz) && $this->setKarenz($data->karenz);
	}
	
	public function getWochenstunden()
	{
		return $this->wochenstunden;
	}

	public function getKarenz()
	{
		return $this->karenz;
	}

	public function setWochenstunden($wochenstunden)
	{
		$this->wochenstunden = $wochenstunden;
		return $this;
	}

	public function setKarenz($karenz)
	{
		$this->karenz = $karenz;
		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'wochenstunden' => $this->getWochenstunden(),
			'karenz' => $this->getKarenz()
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
		karenz: {$this->getKarenz()}

EOTXT;
		return parent::__toString() . $txt;
	}
}
