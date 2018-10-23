<?php
/* Copyright (C) 2018 fhcomplete.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>,
 */
/**
 * Datenverbund Services
 * Anbindung für Datenverbund des Bundesrechenzetrums zur
 * Abfrage und Vergabe von Matrikelnummern
 */
require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/person.class.php');
require_once(dirname(__FILE__).'/student.class.php');
require_once(dirname(__FILE__).'/studiensemester.class.php');
require_once(dirname(__FILE__).'/adresse.class.php');
require_once(dirname(__FILE__).'/webservicelog.class.php');
require_once(dirname(__FILE__).'/prestudent.class.php');

class dvb extends basis_db
{
	const DVB_URL_WEBSERVICE_OAUTH = DVB_PORTAL.'/dvb/oauth/token';
	const DVB_URL_WEBSERVICE_SVNR = DVB_PORTAL.'/rws/0.2/simpleStudentBySozialVersicherungsnummer.xml';
	const DVB_URL_WEBSERVICE_ERSATZKZ = DVB_PORTAL.'/rws/0.2/simpleStudentByErsatzKennzeichen.xml';
	const DVB_URL_WEBSERVICE_RESERVIERUNG = DVB_PORTAL.'/dvb/matrikelnummern/1.0/reservierung.xml';
	const DVB_URL_WEBSERVICE_MELDUNG = DVB_PORTAL.'/dvb/matrikelnummern/1.0/meldung.xml';

	public $authentication;
	private $username;
	private $password;
	protected $debug;
	public $debug_output = '';

	/**
	 * Constructor
	 * @param string $username Username fuer OAuth2 Login.
	 * @param string $password Passwort fuer OAuth2 Login.
	 * @param bool $debug Enables/Disables Debugging.
	 */
	public function __construct($username, $password, $debug = false)
	{
		$this->username = $username;
		$this->password = $password;
		$this->debug = $debug;
	}

	/**
	 * Versucht die Matrikelnummer für eine Person zu ermitteln.
	 * Wenn die Person noch keine Matrikelnummer besitzt, wird eine neue Matrikelnummer
	 * angefordert und der Person zugeordnet
	 * @param int $person_id ID der Person.
	 * @return boolean true wenn Erfolgreich, false im Fehlerfall
	 */
	public function assignMatrikelnummer($person_id)
	{
		$person = new person();
		if (!$person->load($person_id))
		{
			$this->errormsg = $person->errormsg;
			return false;
		}

		if ($person->svnr != '')
		{
			$matrikelnummer = $this->getMatrikelnrBySVNR($person->svnr);

			if ($matrikelnummer === false && $this->errormsg != '')
			{
				$this->logRequest($person, 'getMatrikelnrBySVNR', false);
				return false;
			}
		}
		elseif ($person->ersatzkennzeichen != '')
		{
			$matrikelnummer = $this->getMatrikelnrByErsatzkennzeichen($person->ersatzkennzeichen);

			if ($matrikelnummer === false && $this->errormsg != '')
			{
				$this->logRequest($person, 'getMatrikelnrByErsatzkennzeichen', false);
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Person braucht SVNR oder Ersatzkennzeichen';
			return false;
		}

		if ($matrikelnummer !== false && $matrikelnummer != '')
		{
			// Matrikelnummer wurde gefunden
			// Bei Person speichern
			$person->matr_nr = $matrikelnummer;
			if ($person->save())
			{
				$this->logRequest($person, 'assignExistingMatrikelnummer', true, $matrikelnummer);
				return true;
			}
		}
		else
		{
			// Es wurde noch keine Matrikelnummer zu dieser Person zugeordnet
			// Es wird eine neue Matrikelnummer aus dem Kontingent angefordert
			// und an die Person vergeben

			// Studienjahr ermitteln
			$qry = "
			SELECT
				studiensemester_kurzbz, prestudent_id
			FROM
				public.tbl_student
				JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
				JOIN public.tbl_benutzer ON(tbl_student.student_uid = tbl_benutzer.uid)
			WHERE
				tbl_prestudent.person_id=".$this->db_add_param($person->person_id)."
				AND tbl_benutzer.aktiv
				AND tbl_prestudentstatus.status_kurzbz='Student'
				AND tbl_prestudent.bismelden
			ORDER BY tbl_prestudentstatus.datum desc LIMIT 1
			";

			$prestudent_id = '';
			$studiensemester_kurzbz = '';

			if ($result = $this->db_query($qry))
			{
				if ($row = $this->db_fetch_object($result))
				{
					$studiensemester_kurzbz = $row->studiensemester_kurzbz;
					$prestudent_id = $row->prestudent_id;
				}
				else
				{
					$this->logRequest($person, 'assignNewMatrikelnummer', false);
					$this->errormsg = 'Fehler beim Ermitteln des Studienjahrs für diese Person';
					return false;
				}
			}
			else
			{
				$this->logRequest($person, 'assignNewMatrikelnummer', false);
				$this->errormsg = 'Fehler beim Ermitteln des Studienjahrs für diese Person';
				return false;
			}

			$studienjahr = substr($studiensemester_kurzbz, 4);
			$art = substr($studiensemester_kurzbz, 0, 2);
			if ($art == 'SS')
				$studienjahr = $studienjahr - 1;

			// Erstaustattung im Jahr 2018. Alle davor bekommen 18er Nummern
			if ($studienjahr < 2018)
				$studienjahr = 2018;

			// Neue Matrikelnummer aus Kontingent anfordern
			$kontingent = $this->getKontingent(DVB_BILDUNGSEINRICHTUNG_CODE, $studienjahr);

			if ($kontingent !== false && isset($kontingent[0]))
			{
				$person_meldung = new stdClass();
				$person_meldung->matrikelnummer = $kontingent[0];
				$person_meldung->vorname = $person->vorname;
				$person_meldung->nachname = $person->nachname;
				$person_meldung->geburtsdatum = $person->gebdatum;
				$person_meldung->geschlecht = mb_strtoupper($person->geschlecht);
				$person_meldung->staat = $person->staatsbuergerschaft;
				if ($person->svnr != '')
					$person_meldung->svnr = $person->svnr;

				// PLZ der Meldeadresse laden
				$adresse = new adresse();
				if ($adresse->loadZustellAdresse($person->person_id))
					$person_meldung->plz = $adresse->plz;

				// ZGV Datum laden falls vorhanden
				$prestudent = new prestudent();
				if ($prestudent->load($prestudent_id) && $prestudent->zgvdatum != '')
				{
					$datum_obj = new datum();
					$person_meldung->matura = $datum_obj->formatDatum($prestudent->zgvdatum, 'Ymd');
				}

				// Meldung der Vergabe der Matrikelnummer
				if ($this->setMatrikelnummer(DVB_BILDUNGSEINRICHTUNG_CODE, $person_meldung))
				{
					// Matrikelnummer bei Person speichern
					$person->matr_nr = $matrikelnummer;
					if ($person->save())
					{
						$this->logRequest($person, 'assignNewMatrikelnummer', true, $matrikelnummer);
						return true;
					}
				}
				else
				{
					$this->logRequest($person, 'assignNewgMatrikelnummer', false, $person_meldung);
					$this->errormsg .= 'Vergabe fehlgeschlagen';
					return false;
				}
			}
			else
			{
				$this->logRequest($person, 'assignNewgMatrikelnummer', false, $studienjahr);
				$this->errormsg .= 'Failed to get Kontingent';
				return false;
			}
		}
	}

	/**
	 * Performs a OAuth2 Authentication and returns the OAuth Bearer Token
	 * @return boolean true wenn erfolgreich, false im Fehlerfall
	 */
	public function authenticate()
	{
		$this->debug('Request new OAuth Token');

		$curl = curl_init();
		$url = self::DVB_URL_WEBSERVICE_OAUTH;
		$url .= '?grant_type=client_credentials';

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$headers = array(
			'Accept: application/json',
			'Content-Type: application/x-www-form-urlencoded',
			'Authorization: Basic '.base64_encode($this->username.":".$this->password),
			'User-Agent: FHComplete',
			'Connection: Keep-Alive',
			'Expect:',
			'Content-Length: 0'
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$this->debug('Sending Request to '.$url);
		$json_response = curl_exec($curl);
		$curl_info = curl_getinfo($curl);
		curl_close($curl);

		$this->debug('Response: '.$curl_info['http_code']);

		if ($curl_info['http_code'] == '200')
		{
			/* Example Response:
				{
				"access_token": "d9c60404-1530-4b05-bb8e-0a0b0f321726",
				"token_type": "bearer",
				"expires_in": 41087,
				"scope": "read write ROLE_bildungseinrichtung
				ROLE_bildungseinrichtung_A"
				}
			*/

			$this->authentication = json_decode($json_response);

			// Calculate Expire Date
			$ttl = new DateTime();
			$ttl->add(new DateInterval('PT'.$this->authentication->expires_in.'S'));
			$this->authentication->DateTimeExpires = $ttl;

			$this->debug('Access_token:'.$this->authentication->access_token);
			$this->debug('Scope:'.$this->authentication->scope);

			return true;
		}
		else
		{
			$this->errormsg = 'Authentication failed with HTTP Code:'.$curl_info['http_code'];
			$this->errormsg .= ' and Response:'.$json_response;
			return false;
		}
	}

	/**
	 * Checks if the Token is Expired
	 * @return boolean true if expired, false if valid.
	 */
	private function tokenIsExpired()
	{
		if (!isset($this->authentication))
			return true;

		$dtnow = new DateTime();
		if ($this->authentication->DateTimeExpires < $dtnow)
		{
			return true;
		}
		else
			return false;
	}

	/**
	 * Get Matrikelnummer by Social Security Number
	 * @param string $svnr Social Security Number.
	 * @return Matrikelnummer or false on error.
	 */
	public function getMatrikelnrBySVNR($svnr)
	{
		if ($this->tokenIsExpired())
		{
			if (!$this->authenticate())
				return false;
		}

		$this->debug('getMatrikelnrBySVNR');

		$curl = curl_init();

		$url = self::DVB_URL_WEBSERVICE_SVNR;
		$url .= '?sozialVersicherungsNummer='.curl_escape($curl, $svnr);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$headers = array(
			'Accept: application/json',
			'Authorization: Bearer '.$this->authentication->access_token,
			'User-Agent: FHComplete',
			'Connection: Keep-Alive',
			'Expect:',
			'Content-Length: 0'
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$this->debug('Sending Request to '.$url);

		$response = curl_exec($curl);
		$curl_info = curl_getinfo($curl);
		curl_close($curl);

		$this->debug('ResponseCode: '.$curl_info['http_code']);
		$this->debug('ResponseData: '.print_r($response, true));

		if ($curl_info['http_code'] == '200')
		{
			/* Example Response:
			<uni:simpleStudentResponse xmlns:uni="http://www.brz.gv.at/datenverbund-unis">
				<uni:student inStudienBeitragsPool="false" inGesamtPool="true" gesperrt="false">
					<uni:matrikelNummer>12345678</uni:matrikelNummer>
					<uni:vorName>Max</uni:vorName>
					<uni:nachName>Mustermann</uni:nachName>
					<uni:geschlecht>M</uni:geschlecht>
					<uni:geburtsDatum>1999-02-19</uni:geburtsDatum>
					<uni:staatsAngehoerigkeit>A</uni:staatsAngehoerigkeit>
				</uni:student>
			</uni:simpleStudentResponse>
			*/
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$namespace = 'http://www.brz.gv.at/datenverbund-unis';
			$domnodes_student = $dom->getElementsByTagNameNS($namespace, 'student');
			foreach ($domnodes_student as $row_student)
			{
				// Wenn nicht gesperrt und fix vergeben
				$ingesamtpool = $row_student->getAttribute('inGesamtPool');
				$gesperrt = $row_student->getAttribute('gesperrt');

				if ($ingesamtpool == 'true' && $gesperrt == 'false')
				{
					$domnodes_matrikelnummer = $row_student->getElementsByTagNameNS($namespace, 'matrikelNummer');
					foreach ($domnodes_matrikelnummer as $row)
					{
						// Found
						return $row->textContent;
					}
				}
			}

			$this->errormsg = '';
			return false;
		}
		else
		{
			$this->errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return false;
		}
	}

	/**
	 * Get Matrikelnummer by Ersatzkennzeichen
	 * @param string $ersatzkennzeichen Ersatzkennzeichen to search for.
	 * @return Matrikelnummer or false
	 */
	public function getMatrikelnrByErsatzkennzeichen($ersatzkennzeichen)
	{
		if ($this->tokenIsExpired())
		{
			if (!$this->authenticate())
				return false;
		}

		$this->debug('getMatrikelnrByErsatzkennzeichen');
		$curl = curl_init();

		$url = self::DVB_URL_WEBSERVICE_ERSATZKZ;
		$url .= '?ersatzKennzeichen='.curl_escape($curl, $ersatzkennzeichen);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$headers = array(
			'Accept: application/json',
			'Authorization: Bearer '.$this->authentication->access_token,
			'User-Agent: FHComplete',
			'Connection: Keep-Alive',
			'Expect:',
			'Content-Length: 0'
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$this->debug('Sending Request to '.$url);
		$xml_response = curl_exec($curl);
		$curl_info = curl_getinfo($curl);
		curl_close($curl);
		$this->debug('ResponseCode: '.$curl_info['http_code']);
		$this->debug('ResponseData: '.print_r($xml_response, true));

		if ($curl_info['http_code'] == '200')
		{
			/* Example Response Success
			<uni:simpleStudentResponse xmlns:uni="http://www.brz.gv.at/datenverbund-unis">
				<uni:student inStudienBeitragsPool="true" inGesamtPool="false">
					<uni:uniKennzeichen>A</uni:uniKennzeichen>
					<uni:matrikelNummer>12345678</uni:matrikelNummer>
					<uni:semesterKennzeichen>2017S</uni:semesterKennzeichen>
					<uni:ersatzKennzeichen>ABCD201093</uni:ersatzKennzeichen>
					<uni:akadGradPre>Bc.</uni:akadGradPre>
					<uni:vorName>Max</uni:vorName>
					<uni:nachName>Mustermann</uni:nachName>
					<uni:geschlecht>W</uni:geschlecht>
					<uni:geburtsDatum>1993-06-26</uni:geburtsDatum>
					<uni:staatsAngehoerigkeit>TCH</uni:staatsAngehoerigkeit>
					<uni:wohnAdresse>
						<uni:staat>A</uni:staat>
						<uni:plz>1030</uni:plz>
						<uni:ort>Wien</uni:ort>
						<uni:strasse>Obere Bahngasse 20/12</uni:strasse>
					</uni:wohnAdresse>
					<uni:zustellAdresse>
						<uni:staat>A</uni:staat>
						<uni:plz>1030</uni:plz>
						<uni:ort>Wien</uni:ort>
						<uni:strasse>Obere Bahngasse 20/12</uni:strasse>
					</uni:zustellAdresse>
				</uni:student>
				<uni:student inStudienBeitragsPool="false" inGesamtPool="true" gesperrt="false">
					<uni:matrikelNummer>12345678</uni:matrikelNummer>
					<uni:vorName>Max</uni:vorName>
					<uni:nachName>Mustermann</uni:nachName>
					<uni:geschlecht>W</uni:geschlecht>
					<uni:geburtsDatum>1993-06-26</uni:geburtsDatum>
					<uni:staatsAngehoerigkeit>TCH</uni:staatsAngehoerigkeit>
				</uni:student>
			</uni:simpleStudentResponse>
			*/
			/* 200 - No Entry found
			<uni:simpleStudentResponse xmlns:uni="http://www.brz.gv.at/datenverbund-unis"/>
			*/
			/* 401 Error Code Token Expired
			{
				"error": "invalid_token",
				"error_description": "Access token expired: 64a58ef3-1a70-46e9-b44f-35cc5051ae8e"
			}
			*/
			/* 400 Bad Request
			<FehlerAntwort xmlns="http://www.brz.gv.at/datenverbund-unis">
				<uuid>318e1bc5-279d-43c4-af47-5e6df2ff5279</uuid>
				<fehlerliste fehleranzahl="1">
					<fehler>
						<fehlernummer>ZD00001</fehlernummer>
						<kategorie>Z</kategorie>
						<datenfeld/>
						<fehlertext>Der Server konnte die Anfrage nicht vearbeiten.</fehlertext>
						<massnahme>Required String parameter 'ersatzKennzeichen' is not present</massnahme>
					</fehler>
				</fehlerliste>
			</FehlerAntwort>
			*/

			$dom = new DOMDocument();
			$dom->loadXML($xml_response);
			$namespace = 'http://www.brz.gv.at/datenverbund-unis';
			$domnodes_student = $dom->getElementsByTagNameNS($namespace, 'student');
			foreach ($domnodes_student as $row_student)
			{
				// Wenn nicht gesperrt und fix vergeben
				$ingesamtpool = $row_student->getAttribute('inGesamtPool');
				$gesperrt = $row_student->getAttribute('gesperrt');

				if ($ingesamtpool == 'true' && $gesperrt == 'false')
				{
					$domnodes_matrikelnummer = $row_student->getElementsByTagNameNS($namespace, 'matrikelNummer');
					foreach ($domnodes_matrikelnummer as $row)
					{
						// Found
						return $row->textContent;
					}
				}
			}

			$this->errormsg = '';
			return false;
		}
		else
		{
			$this->errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$xml_response;
			return false;
		}
	}

	/**
	 * List of already Reserved Matrikelnummern
	 * @param string $bildungseinrichtung Shortname of Institution.
	 * @param string $studienjahr Year of Reservation.
	 * @return array with reserved Matrikelnr. or false on failure.
	 */
	public function getReservations($bildungseinrichtung, $studienjahr)
	{
		$this->debug('getReservations');
		$uuid = $this->getUUID();

		if ($this->tokenIsExpired())
		{
			if (!$this->authenticate())
				return false;
		}

		$curl = curl_init();
		$url = self::DVB_URL_WEBSERVICE_RESERVIERUNG;
		$url .= '?uuid='.curl_escape($curl, $uuid);
		$url .= '&be='.curl_escape($curl, $bildungseinrichtung);
		$url .= '&sj='.curl_escape($curl, $studienjahr);

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$headers = array(
			'Accept: application/xml',
			'Authorization: Bearer '.$this->authentication->access_token,
			'User-Agent: FHComplete',
			'Connection: Keep-Alive',
			'Expect:',
			'Content-Length: 0'
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$this->debug('Request URL:'.$url);
		$response = curl_exec($curl);
		$curl_info = curl_getinfo($curl);
		curl_close($curl);

		$this->debug('ResponseCode: '.$curl_info['http_code']);
		$this->debug('ResponseData: '.print_r($response, true));

		/* 200 ok
		<?xml version="1.0" encoding="UTF-8"?>
		<matrikelnummernantwort>
			<uuid>793d44fa-5646-42b1-b0cb-f2f121b2f14f</uuid>
			<fehlerliste fehleranzahl="0"/>
			<matrikelnummernliste>
				<matrikelnummer>12345678</matrikelnummer>
				<matrikelnummer>23456789</matrikelnummer>
			</matrikelnummernliste>
		</matrikelnummernantwort>
		*/
		if ($curl_info['http_code'] == '200')
		{
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$domnodes_matrikelnummer = $dom->getElementsByTagName('matrikelnummer');
			$reservations = array();
			foreach ($domnodes_matrikelnummer as $row)
			{
				$reservations[] = $row->textContent;
			}
			return $reservations;
		}
		else
		{
			$this->errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return false;
		}
	}

	/**
	 * Request a new Matrikelnummer
	 * @param string $bildungseinrichtung Shortname of Institution.
	 * @param string $studienjahr Year of issuing.
	 * @param int $anzahl Number of Requested Numbers.
	 * @return array list of matrikelnr or false on failure.
	 */
	public function getKontingent($bildungseinrichtung, $studienjahr, $anzahl = 1)
	{
		$this->debug('getKontingent');
		$uuid = $this->getUUID();

		if ($this->tokenIsExpired())
		{
			if (!$this->authenticate())
				return false;
		}

		$data = '<?xml version="1.0" encoding="UTF-8"?>
		<matrikelnummernanfrage>
			<uuid>'.$uuid.'</uuid>
			<kontingentblock>
				<anzahl>'.$anzahl.'</anzahl>
				<be>'.$bildungseinrichtung.'</be>
				<sj>'.$studienjahr.'</sj>
			</kontingentblock>
		</matrikelnummernanfrage>
		';

		$curl = curl_init();
		$url = self::DVB_URL_WEBSERVICE_RESERVIERUNG;

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$headers = array(
			'Accept: application/xml',
			'Content-Type: application/xml',
			'Authorization: Bearer '.$this->authentication->access_token,
			'User-Agent: FHComplete',
			'Connection: Keep-Alive',
			'Expect:'
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$this->debug('Request URL:'.$url);
		$this->debug('Request Data:'.$data);
		$response = curl_exec($curl);
		$curl_info = curl_getinfo($curl);
		curl_close($curl);

		$this->debug('ResponseCode: '.$curl_info['http_code']);
		$this->debug('ResponseData: '.print_r($response, true));

		if ($curl_info['http_code'] == '200')
		{
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$domnodes_matrikelnummer = $dom->getElementsByTagName('matrikelnummer');
			$kontingent = array();
			foreach ($domnodes_matrikelnummer as $row)
			{
				$kontingent[] = $row->textContent;
			}
			return $kontingent;
		}
		else
		{
			$this->errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return false;
		}
	}

	/**
	 * Meldet die Vergabe der Matrikelnummer
	 * @param string $bildungseinrichtung Kennzeichen der Bildungseinrichtung.
	 * @param object $person Objekt mit den Personendaten.
	 * @return booelan true wenn erfolgreich
	 */
	public function setMatrikelnummer($bildungseinrichtung, $person)
	{
		$this->debug('setMatrikelnummer');
		$uuid = $this->getUUID();

		if ($this->tokenIsExpired())
		{
			if (!$this->authenticate())
				return false;
		}
		$gebdat = str_replace("-", "", "$person->geburtsdatum");

		$data = '<?xml version="1.0" encoding="UTF-8"?>
		<matrikelnummernmeldung>
			<uuid>'.$uuid.'</uuid>
			<personmeldung>
				<be>'.$bildungseinrichtung.'</be>
				<gebdat>'.$gebdat.'</gebdat>
				<geschlecht>'.$person->geschlecht.'</geschlecht>
				<matrikel>'.$person->matrikelnummer.'</matrikel>';
		if (isset($person->matura) && $person->matura != '')
			$data .= '<matura>'.$person->matura.'</matura>';
		else
			$data .= '<matura>00000000</matura>';

		$data .= '<nachname>'.$person->nachname.'</nachname>';

		if (isset($person->plz) && $person->plz != '')
			$data .= '<plz>'.$person->plz.'</plz>';

		$data .= '<staat>'.$person->staat.'</staat>';

		if (isset($person->svnr) && $person->svnr != '')
			$data .= '<svnr>'.$person->svnr.'</svnr>';

		$data .= '<vorname>'.$person->vorname.'</vorname>';

		if (isset($person->writeonerror) && $person->writeonerror === true)
			$data .= '<writeOnError>J</writeOnError>';

		$data .= '
			</personmeldung>
		</matrikelnummernmeldung>
		';

		$curl = curl_init();
		$url = self::DVB_URL_WEBSERVICE_MELDUNG;

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

		$headers = array(
			'Accept: application/xml',
			'Content-Type: application/xml',
			'Authorization: Bearer '.$this->authentication->access_token,
			'User-Agent: FHComplete',
			'Connection: Keep-Alive',
			'Expect:'
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$this->debug('Request URL:'.$url);
		$this->debug('Request Data:'.$data);
		$response = curl_exec($curl);
		$curl_info = curl_getinfo($curl);
		curl_close($curl);

		$this->debug('Response: '.$curl_info['http_code']);

		$this->debug('Response: '.print_r($response, true));
		/* 200 Fehlermeldung
		<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
		<matrikelnummernmeldungantwort>
			<uuid>b76e84a9-c0bd-494c-97cb-c4ab9cd452c5</uuid>
			<fehlerliste fehleranzahl="3">
				<fehler>
					<fehlernummer>ZD01471</fehlernummer>
					<kategorie>90</kategorie>
					<datenfeld>UNI-Kennzeichen</datenfeld>
					<fehlertext>UNI-Kennzeichen fehlt oder ungültig (FHTEST)</fehlertext>
					<massnahme>BRZ</massnahme>
					<feldinhalt>FHTEST</feldinhalt>
				</fehler>
				<fehler>
					<fehlernummer>AG21333</fehlernummer>
					<kategorie>65</kategorie>
					<datenfeld>Datum allg.Univ.reife</datenfeld>
					<fehlertext>kein gültiges Datum oder Format</fehlertext>
					<massnahme>Korrektur Datum allg. Univ.reife oder 000000 angeben, falls nicht anwendbar</massnahme>
					<feldinhalt>leer</feldinhalt>
				</fehler>
				<fehler>
					<fehlernummer>ZD10073</fehlernummer>
					<kategorie>90</kategorie>
					<datenfeld>Matrikelnummer</datenfeld>
					<fehlertext>aus ungültigem Kontingent</fehlertext>
					<massnahme>Korrektur der Matrikelnummer</massnahme>
					<feldinhalt>12345678</feldinhalt>
				</fehler>
			</fehlerliste>
		</matrikelnummernmeldungantwort>
		*/
		/* 200 ok
		<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
		<matrikelnummernmeldungantwort>
			<uuid>8b239582-6bc5-4193-ac79-2dcf9ec96439</uuid>
			<fehlerliste fehleranzahl="0"/>
		</matrikelnummernmeldungantwort>
		*/
		if ($curl_info['http_code'] == '200')
		{
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$domnodes_fehlerliste = $dom->getElementsByTagName('fehlerliste');

			$fehleranzahl = $domnodes_fehlerliste[0]->getAttribute('fehleranzahl');
			if ($fehleranzahl === '0')
			{
				// Keine Fehler -> Meldung erfolgreich
				return true;
			}
			else
			{
				$this->errormsg = 'Es gab '.$fehleranzahl.' Fehler:';
				$domnodes_fehler = $dom->getElementsByTagName('fehler');
				foreach ($domnodes_fehler as $row)
				{
					$datenfeld = $row->getElementsByTagName('datenfeld');
					$fehlertext = $row->getElementsByTagName('fehlertext');
					$this->errormsg .= ' Datenfeld:'.$datenfeld[0]->textContent;
					$this->errormsg .= ' Fehlertext:'.$fehlertext[0]->textContent;
				}
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return false;
		}
	}

	/**
	 * Generiert eine eindeutige UUID
	 * @return uuid
	 */
	private function getUUID()
	{
		$data = openssl_random_pseudo_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

	 	return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	/**
	 * Erstellt eine Debug Message
	 * @param string $msg Message to log.
	 * @return void
	 */
	private function debug($msg)
	{
		if ($this->debug)
			$this->debug_output .= "\n<br>".date('Y-m-d H:i:s').': '.htmlentities($msg);
	}

	/**
	 * Erstellt einen Logeintrag
	 * @param object $person Personen objekt.
	 * @param string $typ Art des Requests.
	 * @param bool $result True wen Erfolgreich, false wenn Fehlerhaft.
	 * @param object $data Zusatzdaten die Übermittelt wurden und geloggt werden sollen.
	 * @return void
	 */
	public function logRequest($person, $typ, $result, $data = null)
	{
		$webservicelog = new webservicelog();

		$webservicelog->webservicetyp_kurzbz = 'dvb';
		$webservicelog->request_id = $person->person_id;
		$webservicelog->beschreibung = $typ;
		$webservicelog->request_data = ($result?'SUCCESS':'FAILED').' '.$this->errormsg;
		if (!is_null($data))
			$webservicelog->request_data .= ' '.print_r($data, true);

		$webservicelog->save();
	}
}
