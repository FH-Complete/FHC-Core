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
	
	public function hydrateByStdClass($data, $fromdb=false)
	{
		parent::hydrateByStdClass($data, $fromdb);
		$this->fromdb = $fromdb;
		isset($data->karenztyp_kurzbz) && $this->setKarenztypKurzbz($data->karenztyp_kurzbz);
		isset($data->geplanter_geburtstermin) && $this->setGeplanterGeburtstermin($data->geplanter_geburtstermin);
		isset($data->tatsaechlicher_geburtstermin) && $this->setTatsaechlicherGeburtstermin($data->tatsaechlicher_geburtstermin);
		$this->fromdb = false;
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
		$this->markDirty('karenztyp_kurzbz', $this->karenztyp_kurzbz, $karenztyp_kurzbz);
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
		$this->markDirty('tatsaechlicher_geburtstermin', $this->tatsaechlicher_geburtstermin, $tatsaechlicher_geburtstermin);
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
		$this->markDirty('geplanter_geburtstermin', $this->geplanter_geburtstermin, $geplanter_geburtstermin);
		$this->geplanter_geburtstermin = $geplanter_geburtstermin;

		return $this;
	}
	
	public function toStdClass(): \stdClass
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'karenztyp_kurzbz' => $this->getKarenztypKurzbz(),
			'tatsaechlicher_geburtstermin' => $this->getTatsaechlicherGeburtstermin(),
			'geplanter_geburtstermin' => $this->getGeplanterGeburtstermin()
		);
		
		$tmp = array_filter($tmp, function($k) {
			return in_array($k, $this->modifiedcolumns);
		},  ARRAY_FILTER_USE_KEY);
		
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
		if( empty($this->karenztyp_kurzbz) ) {
			$this->validationerrors[] = 'Ein Karenztyp muss ausgewählt sein.';
		}
		
		if( $this->karenztyp_kurzbz === 'elternkarenz' ) {			
			$geplant = \DateTimeImmutable::createFromFormat('Y-m-d', $this->geplanter_geburtstermin);
			$tatsaechlich = \DateTimeImmutable::createFromFormat('Y-m-d', $this->tatsaechlicher_geburtstermin);

			if( false === $geplant ) {
				$this->validationerrors[] = 'Bei Elternkarenz muss der geplanter Geburtstermin ein gültiges Datum sein.';
			}
			
			if( !empty($this->tatsaechlicher_geburtstermin) && $tatsaechlich === false ) {
				$this->validationerrors[] = 'Bei Elternkarenz muss der tatsaechliche Geburtstermin leer oder ein gültiges Datum sein.';
			}
		}		
		
		$bis = \DateTimeImmutable::createFromFormat('Y-m-d', $this->bis);
		
		if( false === $bis ) {
			$this->validationerrors[] = 'Bei einer Karenz muss ein gültiges Ende-Datum angegeben werden.';
		}
		
		return parent::validate();
	}
}
