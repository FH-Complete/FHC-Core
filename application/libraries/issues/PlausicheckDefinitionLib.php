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
		'AbschlusspruefungOderAbsolventFehlt' => 'AbschlusspruefungOderAbsolventFehlt',
		'AktSemesterNull' => 'AktSemesterNull',
		'AktiverStudentOhneStatus' => 'AktiverStudentOhneStatus',
		'AusbildungssemPrestudentUngleichAusbildungssemStatus' => 'AusbildungssemPrestudentUngleichAusbildungssemStatus',
		'BeginndatumVorBismeldung' => 'BeginndatumVorBismeldung',
		'BewerberNichtZumRtAngetreten' => 'BewerberNichtZumRtAngetreten',
		'DatumAbschlusspruefungFehlt' => 'DatumAbschlusspruefungFehlt',
		'DatumSponsionFehlt' => 'DatumSponsionFehlt',
		'DatumStudiensemesterFalscheReihenfolge' => 'DatumStudiensemesterFalscheReihenfolge',
		'DualesStudiumOhneMarkierung' => 'DualesStudiumOhneMarkierung',
		'FalscheAnzahlAbschlusspruefungen' => 'FalscheAnzahlAbschlusspruefungen',
		'FalscheAnzahlHeimatadressen' => 'FalscheAnzahlHeimatadressen',
		'FalscheAnzahlZustelladressen' => 'FalscheAnzahlZustelladressen',
		'FalscheStatusabfolgeVorStudentstatus' => 'FalscheStatusabfolgeVorStudentstatus',
		'GbDatumWeitZurueck' => 'GbDatumWeitZurueck',
		'InaktiverStudentAktiverStatus' => 'InaktiverStudentAktiverStatus',
		'IncomingHeimatNationOesterreich' => 'IncomingHeimatNationOesterreich',
		'IncomingOhneIoDatensatz' => 'IncomingOhneIoDatensatz',
		'IncomingOrGsFoerderrelevant' => 'IncomingOrGsFoerderrelevant',
		'InskriptionVorLetzerBismeldung' => 'InskriptionVorLetzerBismeldung',
		'NationNichtOesterreichAberGemeinde' => 'NationNichtOesterreichAberGemeinde',
		'OrgformBewerberUngleichOrgformStudent' => 'OrgformBewerberUngleichOrgformStudent',
		'OrgformStgUngleichOrgformPrestudent' => 'OrgformStgUngleichOrgformPrestudent',
		'PrestudentMischformOhneOrgform' => 'PrestudentMischformOhneOrgform',
		'StartsemesterUngleichPersonenkennzeichen' => 'StartsemesterUngleichPersonenkennzeichen',
		'StgPrestudentUngleichStgStudienplan' => 'StgPrestudentUngleichStgStudienplan',
		'StgPrestudentUngleichStgStudent' => 'StgPrestudentUngleichStgStudent',
		'StudentstatusNachDiplomand' => 'StudentstatusNachDiplomand',
		'StudentstatusNachAbbrecher' => 'StudentstatusNachAbbrecher'
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
