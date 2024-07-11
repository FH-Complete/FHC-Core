<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library containing definitions of all core plausichecks.
 */
class PlausicheckDefinitionLib
{
	// set fehler for core plausichecks
	// structure: fehler_kurzbz => class (library) name for resolving
	private $_fehlerLibMappings = array(
		'AbbrecherAktiv' => 'AbbrecherAktiv',
		'AbschlussstatusFehlt' => 'AbschlussstatusFehlt',
		'AktSemesterNull' => 'AktSemesterNull',
		'AktiverStudentOhneStatus' => 'AktiverStudentOhneStatus',
		'AusbildungssemPrestudentUngleichAusbildungssemStatus' => 'AusbildungssemPrestudentUngleichAusbildungssemStatus',
		'BewerberNichtZumRtAngetreten' => 'BewerberNichtZumRtAngetreten',
		'DatumAbschlusspruefungFehlt' => 'DatumAbschlusspruefungFehlt',
		'DatumSponsionFehlt' => 'DatumSponsionFehlt',
		'DatumStudiensemesterFalscheReihenfolge' => 'DatumStudiensemesterFalscheReihenfolge',
		'FalscheAnzahlAbschlusspruefungen' => 'FalscheAnzahlAbschlusspruefungen',
		'FalscheAnzahlHeimatadressen' => 'FalscheAnzahlHeimatadressen',
		'FalscheAnzahlZustelladressen' => 'FalscheAnzahlZustelladressen',
		'GbDatumWeitZurueck' => 'GbDatumWeitZurueck',
		'InaktiverStudentAktiverStatus' => 'InaktiverStudentAktiverStatus',
		'IncomingHeimatNationOesterreich' => 'IncomingHeimatNationOesterreich',
		'IncomingOhneIoDatensatz' => 'IncomingOhneIoDatensatz',
		'IncomingOrGsFoerderrelevant' => 'IncomingOrGsFoerderrelevant',
		'InskriptionVorLetzerBismeldung' => 'InskriptionVorLetzerBismeldung',
		'NationNichtOesterreichAberGemeinde' => 'NationNichtOesterreichAberGemeinde',
		'OrgformStgUngleichOrgformPrestudent' => 'OrgformStgUngleichOrgformPrestudent',
		'PrestudentMischformOhneOrgform' => 'PrestudentMischformOhneOrgform',
		'StgPrestudentUngleichStgStudienplan' => 'StgPrestudentUngleichStgStudienplan',
		'StgPrestudentUngleichStgStudent' => 'StgPrestudentUngleichStgStudent',
		'StudentstatusNachAbbrecher' => 'StudentstatusNachAbbrecher',
		'DualesStudiumOhneMarkierung' => 'DualesStudiumOhneMarkierung'
		//'StudienplanUngueltig' => 'StudienplanUngueltig'
	);

	/**
	 * Gets all fehler_kurzbz-library mappings for fehler which need to be checked.
	 */
	public function getFehlerLibMappings()
	{
		return $this->_fehlerLibMappings;
	}

	/**
	 * Gets all fehler_kurzbz for fehler which need to be checked.
	 */
	public function getFehlerKurzbz()
	{
		return array_keys($this->_fehlerLibMappings);
	}
}
