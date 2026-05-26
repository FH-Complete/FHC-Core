<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Library containing definitions of all core plausichecks.
 */
class PlausicheckDefinitionLib
{
	// set fehler for core plausichecks
	// structure: fehler_kurzbz => class (library) name for resolving
	private $_fehlerKurzbz = array(
		'AbbrecherAktiv',
		'AbschlussstatusFehlt',
		'AbschlusspruefungOderAbsolventFehlt',
		'AktSemesterNull',
		'AktiverStudentOhneStatus',
		'AusbildungssemPrestudentUngleichAusbildungssemStatus',
		'DatumAbschlusspruefungFehlt',
		'DatumSponsionFehlt',
		'DatumStudiensemesterFalscheReihenfolge',
		'FalscheAnzahlAbschlusspruefungen',
		'FalscheAnzahlHeimatadressen',
		'FalscheAnzahlZustelladressen',
		'FalscheStatusabfolgeVorStudentstatus',
		'GbDatumWeitZurueck',
		'InaktiverStudentAktiverStatus',
		'IncomingHeimatNationOesterreich',
		'IncomingOhneIoDatensatz',
		'IncomingOrGsFoerderrelevant',
		'InskriptionVorLetzerBismeldung',
		'NationNichtOesterreichAberGemeinde',
		'OrgformBewerberUngleichOrgformStudent',
		'OrgformStgUngleichOrgformPrestudent',
		'PrestudentMischformOhneOrgform',
		'StartsemesterUngleichPersonenkennzeichen',
		'StgPrestudentUngleichStgStudienplan',
		'StgPrestudentUngleichStgStudent',
		'StudentstatusNachDiplomand',
		'StudentstatusNachAbbrecher',
		'DualesStudiumOhneMarkierung'
		//'StudienplanUngueltig'
		//'BewerberNichtZumRtAngetreten'
	);

	/**
	 * Gets all fehler_kurzbz for fehler which need to be checked.
	 */
	public function getFehlerKurzbz()
	{
		return $this->_fehlerKurzbz;
	}
}
