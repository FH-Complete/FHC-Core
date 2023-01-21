<?php
namespace vertragsbestandteil;

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

/**
 * Salary always depends on employment (Dienstverhältnis) and optionally on part of contract (Vetragsbestandteil)
 */
class VertragsbestandteilGehalt extends Vertragsbestandteil
{
	protected $gehaltsbestandteil_id;
	protected $gehalt_von;
	protected $gehalt_bis;
	protected $anmerkung;
	protected $grundbetrag;
	protected $betrag_valorisiert;
	protected $valorisieren;
	protected $gehalt_dienstverhaeltnis_id;
	protected $gehaltstyp_kurzbz;
	protected $valorisierungssperre;

	public function __construct()
	{
		$this->setVertragsbestandteiltyp_kurzbz(
			VertragsbestandteilFactory::VERTRAGSBESTANDTEIL_GEHALT);
	}

	public function hydrateByStdClass($data)
	{
		parent::hydrateByStdClass($data);
		isset($data->gehalt_von) && $this->setGehaltVon($data->gehalt_von);
		isset($data->gehalt_bis) && $this->setGehaltBis($data->gehalt_bis);
		isset($data->anmerkung) && $this->setAnmerkung($data->anmerkung);
		isset($data->gehalt_dienstverhaeltnis_id) && $this->setGehaltDienstverhaeltnisID($data->gehalt_dienstverhaeltnis_id);
		isset($data->gehaltstyp_kurzbz) && $this->setGehaltstypKurzbz($data->gehaltstyp_kurzbz);
		isset($data->valorisierungssperre) && $this->setValorisierungssperre($data->valorisierungssperre);
	}

	public function getGehaltVon()
	{
		return $this->gehalt_von;
	}

	public function getGehaltBis()
	{
		return $this->gehalt_bis;
	}

	public function setGehaltVon($von)
	{
		$this->gehalt_von = $von;
		return $this;
	}

	public function setGehaltBis($bis)
	{
		$this->gehalt_bis = $bis;
		return $this;
	}

	/**
	 * Get the value of gehaltsbestandteil_id
	 */
	public function getGehaltsbestandteilId()
	{
		return $this->gehaltsbestandteil_id;
	}

	/**
	 * Set the value of gehaltsbestandteil_id
	 */
	public function setGehaltsbestandteilId($gehaltsbestandteil_id): self
	{
		$this->gehaltsbestandteil_id = $gehaltsbestandteil_id;

		return $this;
	}

	/**
	 * Get the value of anmerkung
	 */
	public function getAnmerkung()
	{
		return $this->anmerkung;
	}

	/**
	 * Set the value of anmerkung
	 */
	public function setAnmerkung($anmerkung): self
	{
		$this->anmerkung = $anmerkung;

		return $this;
	}

	/**
	 * Get the value of grundbetrag
	 */
	public function getGrundbetrag()
	{
		return $this->grundbetrag;
	}

	/**
	 * Set the value of grundbetrag
	 */
	public function setGrundbetrag($grundbetrag): self
	{
		$this->grundbetrag = $grundbetrag;

		return $this;
	}

	/**
	 * Get the value of betrag_valorisiert
	 */
	public function getBetragValorisiert()
	{
		return $this->betrag_valorisiert;
	}

	/**
	 * Set the value of betrag_valorisiert
	 */
	public function setBetragValorisiert($betrag_valorisiert): self
	{
		$this->betrag_valorisiert = $betrag_valorisiert;

		return $this;
	}

	/**
	 * Get the value of valorisieren
	 */
	public function getValorisieren()
	{
		return $this->valorisieren;
	}

	/**
	 * Set the value of valorisieren
	 */
	public function setValorisieren($valorisieren): self
	{
		$this->valorisieren = $valorisieren;

		return $this;
	}

	/**
	 * Get the value of dienstverhaeltnis_id
	 */
	public function getGehaltDienstverhaeltnisID()
	{
		return $this->gehalt_dienstverhaeltnis_id;
	}

	/**
	 * Set the value of dienstverhaeltnis_id
	 */
	public function setGehaltDienstverhaeltnisID($dienstverhaeltnis_id): self
	{
		$this->gehalt_dienstverhaeltnis_id = $dienstverhaeltnis_id;

		return $this;
	}

	/**
	 * Get the value of gehaltstyp_kurzbz
	 */
	public function getGehaltstypKurzbz()
	{
		return $this->gehaltstyp_kurzbz;
	}

	/**
	 * Set the value of gehaltstyp_kurzbz
	 */
	public function setGehaltstypKurzbz($gehaltstyp_kurzbz): self
	{
		$this->gehaltstyp_kurzbz = $gehaltstyp_kurzbz;

		return $this;
	}

	/**
	 * Get the value of valorisierungssperre
	 */
	public function getValorisierungssperre()
	{
		return $this->valorisierungssperre;
	}

	/**
	 * Set the value of valorisierungssperre
	 */
	public function setValorisierungssperre($valorisierungssperre): self
	{
		$this->valorisierungssperre = $valorisierungssperre;

		return $this;
	}




	public function toStdClass(): \stdClass
	{
		$tmp = array(			
			'von' => $this->getVon(),
			'bis' => $this->getBis(),
			'gehalt_von' => $this->getGehaltVon(),
			'gehalt_bis' => $this->getGehaltBis(),
			'gehalt_dienstverhaeltnis_id' => $this->getGehaltDienstverhaeltnisID(),
			'grundbetrag' => $this->getGrundbetrag(),
			'betrag_valorisiert' => $this->getBetragValorisiert(),
			'valorisieren' => $this->getValorisieren(),
			'gehaltstyp_kurzbz' => $this->getGehaltstypKurzbz(),
			'valorisierungssperre' => $this->getValorisierungssperre(),
			'anmerkung' => $this->getAnmerkung()
		);

		$tmp = array_filter($tmp, function($v) {
			return !is_null($v);
		});

		return (object) $tmp;
	}

	public function __toString()
	{
		$txt = <<<EOTXT
		von: {$this->getVon()}
		bis: {$this->getBis()}
		grundbetrag: {$this->getGrundbetrag()}
		valorisieren: {$this->getValorisieren()}

EOTXT;
		return parent::__toString() . $txt;
	}

	
}
