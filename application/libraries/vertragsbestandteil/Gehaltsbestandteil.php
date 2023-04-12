<?php
namespace vertragsbestandteil;

use vertragsbestandteil\VertragsbestandteilFactory;

/**
 * Salary always depends on employment (Dienstverhältnis) and optionally on part of contract (Vetragsbestandteil)
 */
class Gehaltsbestandteil 
{
	protected $gehaltsbestandteil_id;
    protected $gueltig_ab;
    protected $gueltig_bis;
	protected $anmerkung;
	protected $grundbetrag;
	protected $betrag_valorisiert;
	protected $valorisieren;
	protected $gehalt_dienstverhaeltnis_id;
	protected $gehaltstyp_kurzbz;
	protected $valorisierungssperre;

	public function __construct()
	{	
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
			'gueltig_ab' => $this->getGueltigAb(),
			'gueltig_bis' => $this->getGueltigBis(),
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

	

    /**
     * Get the value of gueltig_ab
     */
    public function getGueltigAb()
    {
        return $this->gueltig_ab;
    }

    /**
     * Set the value of gueltig_ab
     */
    public function setGueltigAb($gueltig_ab): self
    {
        $this->gueltig_ab = $gueltig_ab;

        return $this;
    }

    /**
     * Get the value of gueltig_bis
     */
    public function getGueltigBis()
    {
        return $this->gueltig_bis;
    }

    /**
     * Set the value of gueltig_bis
     */
    public function setGueltigBis($gueltig_bis): self
    {
        $this->gueltig_bis = $gueltig_bis;

        return $this;
    }
}
