<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

/**
 * Description of VertragsbestandteilFunktion
 *
 * @author bambi
 */
class VertragsbestandteilFunktion extends Vertragsbestandteil
{
	protected $benutzerfunktion_id;
	protected $anmerkung;
	protected $kuendigungsrelevant;

	public function __construct()
	{
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_FUNKTION);
	}
	
	public function toStdClass()
	{
		$tmp = array(
			'vertragsbestandteil_id' => $this->getVertragsbestandteil_id(),
			'benutzerfunktion_id' => $this->getBenutzerfunktion_id(),
			'anmerkung' => $this->getAnmerkung(),
			'kuendigungsrelevant' => $this->getKuendigungsrelevant()
		);
		
		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});
		
		return (object) $tmp;
	}

	public function __toString()
	{
		$txt = <<<EOTXT
		benutzerfunktion_id: {$this->getBenutzerfunktion_id()}
		anmerkung: {$this->getAnmerkung()}
		kuendigungsrelevant: {$this->getKuendigungsrelevant()}

EOTXT;
		return parent::__toString() . $txt;
	}

	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->benutzerfunktion_id) && $this->setBenutzerfunktion_id($data->benutzerfunktion_id);
		isset($data->anmerkung) && $this->setAnmerkung($data->anmerkung);
		isset($data->kuendigungsrelevant) && $this->setKuendigungsrelevant($data->kuendigungsrelevant);
	}

	public function getBenutzerfunktion_id()
	{
		return $this->benutzerfunktion_id;
	}

	public function getAnmerkung()
	{
		return $this->anmerkung;
	}

	public function getKuendigungsrelevant()
	{
		return $this->kuendigungsrelevant;
	}
	
	public function setBenutzerfunktion_id($benutzerfunktion_id)
	{
		$this->benutzerfunktion_id = $benutzerfunktion_id;
		return $this;
	}
	public function setAnmerkung($anmerkung)
	{
		$this->anmerkung = $anmerkung;
		return $this;
	}

	public function setKuendigungsrelevant($kuendigungsrelevant)
	{
		$this->kuendigungsrelevant = $kuendigungsrelevant;
		return $this;
	}
}
