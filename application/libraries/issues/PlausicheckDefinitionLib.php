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
		'AktSemesterNull',
		'AktiverStudentOhneStatus',
		'AusbildungssemPrestudentUngleichAusbildungssemStatus',
		'DatumAbschlusspruefungFehlt',
		'DatumSponsionFehlt',
		'DatumStudiensemesterFalscheReihenfolge',
		'FalscheAnzahlAbschlusspruefungen',
		'FalscheAnzahlHeimatadressen',
		'FalscheAnzahlZustelladressen',
		'GbDatumWeitZurueck',
		'InaktiverStudentAktiverStatus',
		'IncomingHeimatNationOesterreich',
		'IncomingOhneIoDatensatz',
		'IncomingOrGsFoerderrelevant',
		'InskriptionVorLetzerBismeldung',
		'NationNichtOesterreichAberGemeinde',
		'OrgformStgUngleichOrgformPrestudent',
		'PrestudentMischformOhneOrgform',
		'StgPrestudentUngleichStgStudienplan',
		'StgPrestudentUngleichStgStudent',
		'StudentstatusNachAbbrecher',
		'DualesStudiumOhneMarkierung' => 'DualesStudiumOhneMarkierung'
		//'StudienplanUngueltig',
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
