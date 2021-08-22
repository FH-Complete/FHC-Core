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
require_once(dirname(__FILE__).'/errorhandler.class.php');

class dvb extends basis_db
{
	const DVB_URL_WEBSERVICE_OAUTH = DVB_PORTAL.'/dvb/oauth/token';
	const DVB_URL_WEBSERVICE_SVNR = DVB_PORTAL.'/rws/0.2/simpleStudentBySozialVersicherungsnummer.xml';
	const DVB_URL_WEBSERVICE_ERSATZKZ = DVB_PORTAL.'/rws/0.2/simpleStudentByErsatzKennzeichen.xml';
	const DVB_URL_WEBSERVICE_NACHNAME = DVB_PORTAL.'/rws/0.2/simpleStudentByNachname.xml';
	const DVB_URL_WEBSERVICE_NAME = DVB_PORTAL.'/rws/0.2/simpleStudentByName.xml';
	const DVB_URL_WEBSERVICE_MATRIKELNUMMER = DVB_PORTAL.'/rws/0.2/simpleStudentByMatrikelnummer.xml';
	const DVB_URL_WEBSERVICE_RESERVIERUNG = DVB_PORTAL.'/rws/0.5/matrikelreservierung.xml';
	const DVB_URL_WEBSERVICE_MELDUNG = DVB_PORTAL.'/rws/0.5/matrikelmeldung.xml';
	const DVB_URL_WEBSERVICE_BPK = DVB_PORTAL.'/rws/0.5/pruefebpk.xml';

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
	 * @param boolean $softrun Wird dieser Parameter gesetzt, werden nur bestehende Daten abgerufen,
	 * es werden keine neuen Vergabemeldungen gemacht
	 * @return boolean true wenn Erfolgreich, false im Fehlerfall
	 */
	public function assignMatrikelnummer($person_id, $softrun = false)
	{
		$person = new person();
		if (!$person->load($person_id))
		{
			return ErrorHandler::error($person->errormsg);
		}

		$matrikelnummer = false;
		$bpk = false;

		if ($person->svnr != '')
		{
			$data = $this->getMatrikelnrBySVNR($person->svnr);

			if (ErrorHandler::isSuccess($data))
			{
				if (ErrorHandler::hasData($data))
				{
					$matrikelnummer = $data->retval->matrikelnummer;
					$bpk = $data->retval->bpk;
				}
			}
			else
			{
				return ErrorHandler::error();
			}
		}
		elseif ($person->ersatzkennzeichen != '')
		{
			$data = $this->getMatrikelnrByErsatzkennzeichen($person->ersatzkennzeichen);
			if (ErrorHandler::isSuccess($data))
			{
				if (ErrorHandler::hasData($data))
				{
					$matrikelnummer = $data->retval->matrikelnummer;
					$bpk = $data->retval->bpk;
				}
			}
			else
			{
				return ErrorHandler::error();
			}
		}
		else
		{
			$errormsg = 'Person braucht SVNR oder Ersatzkennzeichen';
			return ErrorHandler::error($errormsg);
		}

		// Wenn nicht gefunden, wird zusaetzlich noch eine Namenssuche gestartet
		if ($matrikelnummer == false || $matrikelnummer == '')
		{
			$this->debug('Keine Matrikelnummer gefunden -> Suche per Nachname');
			$nachnameresult = $this->existsByNachname($person_id);
			if (ErrorHandler::isSuccess($nachnameresult))
			{
				if (ErrorHandler::hasData($nachnameresult)
					&& isset($nachnameresult->retval->matrikelnummer)
					&& $nachnameresult->retval->matrikelnummer != '')
				{
					$this->debug('Nachnamensuche erfolgreich');
					$matrikelnummer = $nachnameresult->retval->matrikelnummer;
					if (isset($nachnameresult->retval->bpk))
						$bpk = $nachnameresult->retval->bpk;
				}
				else
				{
					$errormsg = 'Namenssuche ergab nicht eindeutige Treffer -> manuelle Pruefung ist erforderlich';
					return ErrorHandler::error($errormsg);
				}
			}
		}

		if ($matrikelnummer !== false && $matrikelnummer != '')
		{
			// Matrikelnummer wurde gefunden
			// Bei Person speichern
			$person->matr_nr = $matrikelnummer;

			// Wenn ein bPK gefunden wurde dieses auch speichern
			if ($bpk != '')
			{
				$person->bpk = $bpk;
			}

			if ($person->save())
			{
				return ErrorHandler::success();
			}
		}
		else
		{
			if ($softrun == true)
			{
				$errormsg = 'Nicht gefunden Softrun enabled keine Meldung';
				return ErrorHandler::error($errormsg);
			}

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
				AND tbl_prestudentstatus.status_kurzbz in('Student','Incoming')
				AND tbl_prestudent.bismelden
				AND tbl_prestudent.studiengang_kz<10000
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
					$errormsg = 'Fehler beim Ermitteln des Studienjahrs für diese Person';
					return ErrorHandler::error($errormsg);
				}
			}
			else
			{
				$this->logRequest($person, 'assignNewMatrikelnummer', false);
				$errormsg = 'Fehler beim Ermitteln des Studienjahrs für diese Person';
				return ErrorHandler::error($errormsg);
			}

			$studienjahr = substr($studiensemester_kurzbz, 2);
			$art = substr($studiensemester_kurzbz, 0, 2);
			if ($art == 'SS')
				$studienjahr = $studienjahr - 1;

			// Erstaustattung im Jahr 2018. Alle davor bekommen 18er Nummern
			if ($studienjahr < 2018)
				$studienjahr = 2018;

			// Neue Matrikelnummer aus Kontingent anfordern
			$data = $this->getKontingent(DVB_BILDUNGSEINRICHTUNG_CODE, $studienjahr);

			if (ErrorHandler::isSuccess($data) && ErrorHandler::hasdata($data))
			{
				$kontingent = $data->retval->kontingent;

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
					else if ($person->ersatzkennzeichen != '')
						$person_meldung->svnr = $person->ersatzkennzeichen;

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
					$data = $this->setMatrikelnummer(DVB_BILDUNGSEINRICHTUNG_CODE, $person_meldung);
					if (ErrorHandler::isSuccess($data))
					{
						// Matrikelnummer bei Person speichern
						$person->matr_nr = $data->retval->matrikelnummer;

						// Wenn ein BPK bei der Meldung ermittelt wurde, dann dieses auch speichern
						if (ErrorHandler::hasData($data) && isset($data->retval->bpk) && $data->retval->bpk != '')
						{
							$person->bpk = $data->retval->bpk;
						}
						if ($person->save())
						{
							return ErrorHandler::success();
						}
					}
					else
					{
						$this->logRequest($person, 'assignNewMatrikelnummer', false, $person_meldung);
						$errormsg = 'Vergabe fehlgeschlagen';
						return ErrorHandler::error($errormsg);
					}
				}
				else
				{
					$this->logRequest($person, 'assignNewMatrikelnummer', false, $studienjahr);
					$errormsg = 'Failed to get Kontingent';
					return ErrorHandler::error($errormsg);
				}
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

			return ErrorHandler::success();
		}
		else
		{
			$this->errormsg = 'Authentication failed with HTTP Code:'.$curl_info['http_code'];
			$this->errormsg .= ' and Response:'.$json_response;
			return ErrorHandler::error();
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
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$this->debug('getMatrikelnrBySVNR');

		$uuid = $this->getUUID();
		$curl = curl_init();

		$url = self::DVB_URL_WEBSERVICE_SVNR;
		$url .= '?sozialVersicherungsNummer='.curl_escape($curl, $svnr);
		$url .= '&uuid='.curl_escape($curl, $uuid);

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
					<uni:personenkennzeichen>sdfaASDAFasdfads+asasdffd=</uni:personenkennzeichen>
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
			$matrikelnr = false;
			$bpk = false;

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
						// MatrikelNr Found
						$matrikelnr = $row->textContent;
						break;
					}
					$domnodes_bpk = $row_student->getElementsByTagNameNS($namespace, 'personenkennzeichen');
					foreach ($domnodes_bpk as $row)
					{
						// BPK Found
						$bpk = $row->textContent;
						break;
					}
				}
			}

			if ($matrikelnr !== false)
			{
				$retval = new stdClass();
				$retval->matrikelnummer = $matrikelnr;
				$retval->bpk = $bpk;

				return ErrorHandler::success($retval);
			}
			else
			{
				$this->errormsg = '';
				return ErrorHandler::success();
			}
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
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
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$this->debug('getMatrikelnrByErsatzkennzeichen');
		$curl = curl_init();
		$uuid = $this->getUUID();
		$url = self::DVB_URL_WEBSERVICE_ERSATZKZ;
		$url .= '?ersatzKennzeichen='.curl_escape($curl, $ersatzkennzeichen);
		$url .= '&uuid='.curl_escape($curl, $uuid);

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

			$matrikelnr = '';
			$bpk = '';

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
						$matrikelnr = $row->textContent;
					}

					$domnodes_bpk = $row_student->getElementsByTagNameNS($namespace, 'personenkennzeichen');
					foreach ($domnodes_bpk as $row)
					{
						// BPK Found
						$bpk = $row->textContent;
						break;
					}
				}
			}

			if ($matrikelnr != '')
			{
				$retval = new stdClass();
				$retval->matrikelnummer = $matrikelnr;
				$retval->bpk = $bpk;
				return ErrorHandler::success($retval);
			}
			else
			{
				$this->errormsg = '';
				return ErrorHandler::success();
			}
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$xml_response;
			return ErrorHandler::error($errormsg);
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
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
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
			$retval = new stdClass();
			$retval->reservations = $reservations;
			return ErrorHandler::success($retval);
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
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
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$data = '<?xml version="1.0" encoding="UTF-8"?>
		<matrikelnummernanfrage xmlns="http://www.brz.gv.at/datenverbund-unis">
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

			$retval = new stdClass();
			$retval->kontingent = $kontingent;
			return ErrorHandler::success($retval);
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
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
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}
		$gebdat = str_replace("-", "", $person->geburtsdatum);

		$data = '<?xml version="1.0" encoding="UTF-8"?>
		<matrikelnummernmeldung xmlns="http://www.brz.gv.at/datenverbund-unis">
			<uuid>'.$uuid.'</uuid>
			<personmeldung xmlns="http://www.brz.gv.at/datenverbund-unis">
				<be>'.$bildungseinrichtung.'</be>
				<gebdat>'.$gebdat.'</gebdat>
				<geschlecht>'.$person->geschlecht.'</geschlecht>
				<matrikelnummer>'.$person->matrikelnummer.'</matrikelnummer>';
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
				<fehler>
					<fehlernummer>ED10065</fehlernummer>
					<kategorie>E</kategorie>
					<datenfeld>bPK</datenfeld>
					<fehlertext>fehlt oder anders als im Datenverbund ermittelt(Yl329U/jt7fjoo5p+z4lH37ZKrg=)</fehlertext>
					<massnahme>
					Zurückgemeldete bPK in den lokalen Datenbestand übernehmen. Fallsim Fehlertext keine bPK enthalten ist,
					müssen die Personendaten geprüft und ggf. ergänzt werden (Abgleich von Name/Geburtsdatum/Adresse mit dem zentralen
					Melderegister)
					</massnahme>
					<feldinhalt>Yl329keinEchtesbPK4lH37ZKrg=</feldinhalt>
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

			$fehleranzahl = $domnodes_fehlerliste->item(0)->getAttribute('fehleranzahl');
			if ($fehleranzahl === '0')
			{
				// Keine Fehler -> Meldung erfolgreich
				$retval = new stdClass();
				$retval->matrikelnummer = $person->matrikelnummer;
				return ErrorHandler::success($retval);
			}
			else
			{
				$this->errormsg = 'Es gab '.$fehleranzahl.' Fehler:';
				$domnodes_fehler = $dom->getElementsByTagName('fehler');
				foreach ($domnodes_fehler as $row)
				{
					$fehlernummer = $row->getElementsByTagName('fehlernummer');

					/**
					 * Bei Fehlernummer ED10065 wurde die Matrikelnummer korrekt gesetzt.
					 * Das BPK wurde vom Datenverbund versucht zu ermitteln und wird in der Fehlermeldung
					 * zurückgeliefert. Dieses sollte dann gespeichert werden.
					 * Es muss eine erneute Vergabemeldung mit korrigierten Daten vorgenommen werden um die Daten im
					 * DVB zu aktualisieren
					 * Dies gilt nur, wenn ED10065 alleine geliefert wird und keine sonstigen Fehler auftreten
					 */
					if ($fehlernummer->length == 1 && $fehlernummer->item(0)->textContent == 'ED10065')
					{
						$this->debug('ED10065 Response');
						$domnodes_feldinhalt = $row->getElementsByTagName('feldinhalt');
						if ($domnodes_feldinhalt->length > 0 && $domnodes_feldinhalt->item(0)->textContent!='')
						{
							$bpk = $domnodes_feldinhalt->item(0)->textContent;
							$retval = new stdClass();
							$retval->matrikelnummer = $person->matrikelnummer;
							if ($bpk != 'keine bPK gefunden')
								$retval->bpk = $bpk;

							$this->errormsg .= 'ED10065 Response';
							$this->errormsg .= 'Eine Personendatenprüfung ist erforderlich';
							$this->errormsg .= 'Danach muss eine erneute Vergabemeldung mit dieser Matrikelnummer erfolgen.';
							$this->debug('BPK:'.$bpk);
							$this->debug('MatrNr:'.$person->matrikelnummer);

							return ErrorHandler::success($retval);
						}
					}
					else
					{
						$datenfeld = $row->getElementsByTagName('datenfeld');
						$fehlertext = $row->getElementsByTagName('fehlertext');
						$this->errormsg .= ' Datenfeld:'.$datenfeld->item(0)->textContent;
						$this->errormsg .= ' Fehlertext:'.$fehlertext->item(0)->textContent;
					}
				}
				return ErrorHandler::error();
			}
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
		}
	}

	public function setMatrikelnummerErnp($bildungseinrichtung, $person, $reisepass)
	{
		$this->debug('ernpMeldung');
		$uuid = $this->getUUID();

		if ($this->tokenIsExpired())
		{
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$data = '<?xml version="1.0" encoding="UTF-8"?>';
		$data .= '<matrikelnummernmeldung xmlns="http://www.brz.gv.at/datenverbund-unis">
					<uuid>'.$uuid.'</uuid>';

		$data .= $this->getPersonmeldungXml($bildungseinrichtung, $person);

		$data .= '
			<ernpmeldung xmlns="http://www.brz.gv.at/datenverbund-unis">
			<ausgabedatum>'.$reisepass->ausgabedatum.'</ausgabedatum>
			<ausstellBehoerde>'.$reisepass->ausstellBehoerde.'</ausstellBehoerde>
			<ausstellland>'.$reisepass->ausstellland.'</ausstellland>
			<dokumentnr>'.$reisepass->dokumentnr.'</dokumentnr>
			<dokumenttyp>'.$reisepass->dokumenttyp.'</dokumenttyp>
			</ernpmeldung>		
		';
		$data .= '</matrikelnummernmeldung>';

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

		if ($curl_info['http_code'] == '200')
		{
			$dom = new DOMDocument();
			$dom->loadXML($response);
			$domnodes_fehlerliste = $dom->getElementsByTagName('fehlerliste');

			$fehleranzahl = $domnodes_fehlerliste->item(0)->getAttribute('fehleranzahl');
			if ($fehleranzahl === '0')
			{
				// Keine Fehler -> Meldung erfolgreich
				$retval = new stdClass();
				$retval->matrikelnummer = $person->matrikelnummer;
				return ErrorHandler::success($retval);
			}
			else
			{
				$this->errormsg = 'Es gab '.$fehleranzahl.' Fehler:';
				$domnodes_fehler = $dom->getElementsByTagName('fehler');
				foreach ($domnodes_fehler as $row)
				{
					$fehlernummer = $row->getElementsByTagName('fehlernummer');

					/**
					 * Bei Fehlernummer ED10065 wurde die Matrikelnummer korrekt gesetzt.
					 * Das BPK wurde vom Datenverbund versucht zu ermitteln und wird in der Fehlermeldung
					 * zurückgeliefert. Dieses sollte dann gespeichert werden.
					 * Es muss eine erneute Vergabemeldung mit korrigierten Daten vorgenommen werden um die Daten im
					 * DVB zu aktualisieren
					 * Dies gilt nur, wenn ED10065 alleine geliefert wird und keine sonstigen Fehler auftreten
					 */
					if ($fehlernummer->length == 1 && $fehlernummer->item(0)->textContent == 'ED10065')
					{
						$this->debug('ED10065 Response');
						$domnodes_feldinhalt = $row->getElementsByTagName('feldinhalt');
						if ($domnodes_feldinhalt->length > 0 && $domnodes_feldinhalt->item(0)->textContent!='')
						{
							$bpk = $domnodes_feldinhalt->item(0)->textContent;
							$retval = new stdClass();
							$retval->matrikelnummer = $person->matrikelnummer;
							if ($bpk != 'keine bPK gefunden')
								$retval->bpk = $bpk;

							$this->errormsg .= 'ED10065 Response';
							$this->errormsg .= 'Eine Personendatenprüfung ist erforderlich';
							$this->errormsg .= 'Danach muss eine erneute Vergabemeldung mit dieser Matrikelnummer erfolgen.';
							$this->debug('BPK:'.$bpk);
							$this->debug('MatrNr:'.$person->matrikelnummer);

							return ErrorHandler::success($retval);
						}
					}
					else
					{
						$datenfeld = $row->getElementsByTagName('datenfeld');
						$fehlertext = $row->getElementsByTagName('fehlertext');
						$this->errormsg .= ' Datenfeld:'.$datenfeld->item(0)->textContent;
						$this->errormsg .= ' Fehlertext:'.$fehlertext->item(0)->textContent;
					}
				}
				return ErrorHandler::error();
			}
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
		}
	}

	/**
	 * Get BPK from Person
	 * @param string $person_id ID of the Person.
	 * @return BPK or false on error.
	 */
	public function getBPK($person_id)
	{
		$person = new person();
		if ($person->load($person_id))
		{
			if ($person->bpk != '')
			{
				// BPK exisitert bereits
				$retval = new stdClass();
				$retval->bpk = $person->bpk;
				return ErrorHandler::success($retval);
			}

			if ($person->gebdatum == '')
			{
				$errormsg = 'Geburtsdatum ist nicht gesetzt';
				return ErrorHandler::error($errormsg);
			}

			if ($person->vorname == '')
			{
				$errormsg = 'Vorname ist nicht gesetzt';
				return ErrorHandler::error($errormsg);
			}

			if ($person->nachname == '')
			{
				$errormsg = 'Nachname ist nicht gesetzt';
				return ErrorHandler::error($errormsg);
			}

			$geburtsdatum = str_replace("-", "", $person->gebdatum);
			$vorname = $person->vorname;
			$nachname = $person->nachname;
			$geschlecht = mb_strtoupper($person->geschlecht);

			$adresse = new adresse();
			$adresse->loadZustellAdresse($person_id);

			/**
			 * Wenn die Person beim Ersten mal nicht eindeutig gefunden wird,
			 * dann wird nochmal versucht mit Postleitzahl und ggf ein drittes
			 * mal mit der Strasse der Person
			 */

			$try = 1;
			$plz = null;
			$strasse = null;

			while ($try <= 3)
			{
				if ($try == 2)
				{
					$plz = $adresse->plz;
				}
				elseif ($try == 3)
				{
					$plz = $adresse->plz;
					$strasse = $adresse->strasse;
				}

				// Versuchen BPK zu ermitteln
				$data = $this->pruefeBPK($geburtsdatum, $vorname, $nachname, $geschlecht, $plz, $strasse);

				if (ErrorHandler::isSuccess($data))
				{
					// gefunden
					return ErrorHandler::success($data->retval);
				}
				elseif (!ErrorHandler::hasData($data))
				{
					// nicht gefunden
					return ErrorHandler::error();
				}
				else
				{
					// mehrere gefunden
					if (isset($data->retval->multiple) && $data->retval->multiple === true)
					{
						// ggf nochmal versuchen mit weiteren Parametern
					}
					else
					{
						return ErrorHandler::error();
					}
				}
				$try++;
			}

			// nicht eindeutig auffindbar
			return ErrorHandler::error();
		}
		else
		{
			$this->errormsg = $person->errormsg;
			return ErrorHandler::error();
		}
	}

	/**
	 * Get BPK from Person
	 * @param string $geburtsdatum Geburtsdatum der Person im format YYYYMMDD
	 * @param $vorname Vorname der Person.
	 * @param $nachname Nachname der Person.
	 * @param $geschlecht Geschlecht der Person M | W
	 * @param $plz Postleitzahl der Person (optional).
	 * @param $strasse Strasse der Person (optional).
	 * @return BPK or false on error.
	 */
	public function pruefeBPK($geburtsdatum, $vorname, $nachname, $geschlecht, $plz = null, $strasse = null)
	{
		if ($this->tokenIsExpired())
		{
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$this->debug('getBPK');

		$curl = curl_init();

		$uuid = $this->getUUID();

		$url = self::DVB_URL_WEBSERVICE_BPK;
		$url .= '?geburtsdatum='.curl_escape($curl, $geburtsdatum);
		$url .= '&vorname='.curl_escape($curl, $vorname);
		$url .= '&nachname='.curl_escape($curl, $nachname);
		$url .= '&geschlecht='.curl_escape($curl, $geschlecht);
		$url .= '&uuid='.curl_escape($curl, $uuid);

		if (!is_null($plz))
			$url .= '&plz='.curl_escape($curl, $plz);

		if (!is_null($strasse))
			$url .= '&strasse='.curl_escape($curl, $strasse);

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
			 <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
			 <simpleBpkResponse xmlns="http://www.brz.gv.at/datenverbund-unis">
			 	<bpk>12345ABCDEFGHXXXXXXX=</bpk>
				<personInfo>
				<person>
					<vorname>Hans</vorname>
					<nachname>Huber</nachname>
					<geschlecht>M</geschlecht>
					<gebdat>1990-01-01</gebdat>
				</person>
				<adresse>
					<staat></staat>
					<plz>1100</plz>
					<ort></ort>
					<strasse></strasse>
				</adresse>
				</personInfo>
			</simpleBpkResponse>

			Example Error:
			<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
			<simpleBpkResponse xmlns="http://www.brz.gv.at/datenverbund-unis">
			<fehlerAntwort>
				<fehlerliste>
					<fehler>
						<fehlernummer>ED10065</fehlernummer>
						<kategorie>E</kategorie>
						<datenfeld>bPK</datenfeld>
						<fehlertext>fehlt oder anders als im Datenverbund ermittelt (keine bPK gefunden)</fehlertext>
						<massnahme>
							Zurückgemeldete bPK in den lokalen Datenbestand übernehmen.
							Falls im Fehlertext keine bPK enthalten ist, müssen die Personendaten geprüft und
							ggf. ergänzt werden (Abgleich von Name/Geburtsdatum/Adresse
							mit dem zentralen Melderegister)
						</massnahme>
						<feldinhalt>keine bPK gefunden</feldinhalt>
					</fehler>
				</fehlerliste>
			</fehlerAntwort>
			</simpleBpkResponse>
			*/

			$dom = new DOMDocument();
			$dom->loadXML($response);
			$namespace = 'http://www.brz.gv.at/datenverbund-unis';
			$domnodes_fehlernummer = $dom->getElementsByTagNameNS($namespace, 'fehlernummer');
			if ($domnodes_fehlernummer->length > 0)
			{
				$fehlercode = $domnodes_fehlernummer->item(0)->textContent;
				if ($fehlercode == 'ZD00001')
				{
					// Zu viele Requests pro Minute
					$this->debug('Zu viele Requests pro Minute -> Pause');
					sleep(30);
				}
			}

			$domnodes_bpk = $dom->getElementsByTagNameNS($namespace, 'bpk');
			if ($domnodes_bpk->length > 0)
			{
				$retval = new stdClass();
				$retval->bpk = $domnodes_bpk->item(0)->textContent;
				return ErrorHandler::success($retval);
			}
			else
			{
				$retval = new stdClass();
				$domnodes_personen = $dom->getElementsByTagNameNS($namespace, 'personInfo');
				if ($domnodes_personen->length > 1)
				{
					$retval = new stdClass();
					$retval->multiple = true;
					return ErrorHandler::error(null, $retval);
				}
			}
			return ErrorHandler::error();
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
		}
	}

	/**
	 * Prueft ob eine Person aufgrund Nachname und Geburtsdatum gefunden wird
	 * @param $person_id PersonID der gesuchten Person.
	 * @return Success wenn gefunden, error wenn nicht gefunden. Hat die Person 100% Uebereinstimmung der Daten
	 * dann wird auch MatrNr und BPK als Retrun geliefert.
	 */
	public function existsByNachname($person_id)
	{
		$person = new person();
		if ($person->load($person_id))
		{
			$result = $this->getMatrikelnrByNachname($person->nachname, $person->gebdatum);

			if (ErrorHandler::isSuccess($result) && ErrorHandler::hasData($result)
				&& isset($result->retval->data)
				&& is_array($result->retval->data)
				&& count($result->retval->data)>0)
			{
				foreach($result->retval->data as $row)
				{
					if (isset($row->vorname) && isset($row->nachname))
					{
						$this->debug('Eintrag gefunden -> Pruefe Eindeutigkeit');
						// Vorpruefung des Datenverbund
						if (mb_substr(mb_strtolower($row->vorname),0,5) == mb_substr(mb_strtolower($person->vorname),0,5)
						&& mb_substr(mb_strtolower($row->nachname),0,10) == mb_substr(mb_strtolower($person->nachname),0,10))
						{
							// Bei 100% eindeutiger Uebereinstimmung werden die Daten zurueckgeliefert
							if (mb_strtolower($row->geschlecht) == mb_strtolower($person->geschlecht)
								&& $row->staatsangehoerigkeit == $person->staatsbuergerschaft
								&& mb_strtolower($row->nachname) == mb_strtolower($person->nachname)
								&& (
									mb_strtolower($row->vorname) == mb_strtolower($person->vorname)
									||
									mb_strtolower($row->vorname) == mb_strtolower($person->vorname.' '.$person->vornamen)
									)
								&& $row->matrikelnummer != ''
								&& count($result->retval->data) == 1
								)
							{
								$this->debug('Uebereinstimmung gefunden');
								$retval = new stdClass();
								if (isset($row->bpk) && $row->bpk!='')
									$retval->bpk = $row->bpk;
								$retval->matrikelnummer = $row->matrikelnummer;
								return ErrorHandler::success($retval);
							}
							else
							{
								$this->debug('keine 100% Eindeutigkeit gegeben:'.print_r($result->retval->data,true));
								// Uebereinstimmung gefunden aber nicht 100% eindeutig
								return ErrorHandler::success();
							}
						}
						else
						{
							$this->debug('keine 100% Eindeutigkeit beim Namen gegeben:'.print_r($result->retval->data,true));
							// Uebereinstimmung gefunden aber nicht 100% eindeutig
							return ErrorHandler::success();
						}
					}
				}
				$this->debug('Keine Uebereinstimmung per Namenssuche');
				return ErrorHandler::error();
			}
			else
			{
				return ErrorHandler::error();
			}
		}
	}

	/**
	 * Get Matrikelnummer by Surname
	 * @param string $nachname Surname of Person.
	 * @param string $geburtsdatum Date of Birth
	 * @return Matrikelnummer or false on error.
	 */
	public function getMatrikelnrByNachname($nachname, $geburtsdatum)
	{
		if ($this->tokenIsExpired())
		{
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$this->debug('getMatrikelnrByNachname');

		$curl = curl_init();

		$geburtsdatum = str_replace("-", "", $geburtsdatum);

		$uuid = $this->getUUID();

		$url = self::DVB_URL_WEBSERVICE_NACHNAME;
		$url .= '?nachName='.curl_escape($curl, $nachname);
		$url .= '&geburtsDatum='.curl_escape($curl, $geburtsdatum);
		$url .= '&uuid='.curl_escape($curl, $uuid);

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
					<uni:personenkennzeichen>sdfaASDAFasdfads+asasdffd=</uni:personenkennzeichen>
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

			$retval = new stdClass();
			$retval->data = array();

			foreach ($domnodes_student as $row_student)
			{
				// Wenn nicht gesperrt und fix vergeben
				$ingesamtpool = $row_student->getAttribute('inGesamtPool');
				$gesperrt = $row_student->getAttribute('gesperrt');

				if ($ingesamtpool == 'true' && $gesperrt == 'false')
				{
					$data = new stdClass();

					$domnodes_matrikelnummer = $row_student->getElementsByTagNameNS($namespace, 'matrikelNummer');
					foreach ($domnodes_matrikelnummer as $row)
					{
						// MatrikelNr Found
						$data->matrikelnummer = $row->textContent;
						break;
					}
					$domnodes_bpk = $row_student->getElementsByTagNameNS($namespace, 'personenkennzeichen');
					foreach ($domnodes_bpk as $row)
					{
						// BPK Found
						$data->bpk = $row->textContent;
						break;
					}
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'vorName');
					if ($domnodes->length>0)
						$data->vorname = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'nachName');
					if ($domnodes->length>0)
						$data->nachname = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'geschlecht');
					if ($domnodes->length>0)
						$data->geschlecht = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'staatsAngehoerigkeit');
					if ($domnodes->length > 0)
						$data->staatsangehoerigkeit = $domnodes->item(0)->textContent;

					$retval->data[] = $data;
				}

			}

			return ErrorHandler::success($retval);
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
		}
	}

	/**
	 * Get Matrikelnummer by Name
	 * @param string $nachname Surname of Person.
	 * @param string $vorname Firstname of Person.
	 * @param string $geburtsdatum Date of Birth
	 * @return Matrikelnummer or false on error.
	 */
	public function getMatrikelnrByName($nachname, $vorname, $geburtsdatum)
	{
		if ($this->tokenIsExpired())
		{
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$this->debug('getMatrikelnrByName');

		$curl = curl_init();

		$geburtsdatum = str_replace("-", "", $geburtsdatum);
		$uuid = $this->getUUID();

		$url = self::DVB_URL_WEBSERVICE_NAME;
		$url .= '?nachName='.curl_escape($curl, $nachname);
		$url .= '&vorName='.curl_escape($curl, $vorname);
		$url .= '&geburtsDatum='.curl_escape($curl, $geburtsdatum);
		$url .= '&uuid='.curl_escape($curl, $uuid);

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
					<uni:personenkennzeichen>sdfaASDAFasdfads+asasdffd=</uni:personenkennzeichen>
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

			$retval = new stdClass();
			$retval->data = array();

			foreach ($domnodes_student as $row_student)
			{
				// Wenn nicht gesperrt und fix vergeben
				$ingesamtpool = $row_student->getAttribute('inGesamtPool');
				$gesperrt = $row_student->getAttribute('gesperrt');

				if ($ingesamtpool == 'true' && $gesperrt == 'false')
				{
					$data = new stdClass();

					$domnodes_matrikelnummer = $row_student->getElementsByTagNameNS($namespace, 'matrikelNummer');
					foreach ($domnodes_matrikelnummer as $row)
					{
						// MatrikelNr Found
						$data->matrikelnummer = $row->textContent;
						break;
					}
					$domnodes_bpk = $row_student->getElementsByTagNameNS($namespace, 'personenkennzeichen');
					foreach ($domnodes_bpk as $row)
					{
						// BPK Found
						$data->bpk = $row->textContent;
						break;
					}
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'vorName');
					if ($domnodes->length>0)
						$data->vorname = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'nachName');
					if ($domnodes->length>0)
						$data->nachname = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'geschlecht');
					if ($domnodes->length>0)
						$data->geschlecht = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'staatsAngehoerigkeit');
					if ($domnodes->length > 0)
						$data->staatsangehoerigkeit = $domnodes->item(0)->textContent;

					$retval->data[] = $data;
				}

			}

			return ErrorHandler::success($retval);
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
		}
	}

	/**
	 * Get Persondata by Matrikelnummer
	 * @param string $matrikelnr Matrikelnummer of Person.
	 * @return Matrikelnummer or false on error.
	 */
	public function getDataByMatrikelnr($matrikelnr)
	{
		if ($this->tokenIsExpired())
		{
			$result = $this->authenticate();
			if (ErrorHandler::isError($result))
				return ErrorHandler::error();
		}

		$this->debug('getDataByMatrikelnr');

		$curl = curl_init();

		$uuid = $this->getUUID();

		$url = self::DVB_URL_WEBSERVICE_MATRIKELNUMMER;
		$url .= '?matrikelNummer='.curl_escape($curl, $matrikelnr);
		$url .= '&uuid='.curl_escape($curl, $uuid);

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
					<uni:personenkennzeichen>sdfaASDAFasdfads+asasdffd=</uni:personenkennzeichen>
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

			$retval = new stdClass();
			$retval->data = array();

			foreach ($domnodes_student as $row_student)
			{
				// Wenn nicht gesperrt und fix vergeben
				$ingesamtpool = $row_student->getAttribute('inGesamtPool');
				$gesperrt = $row_student->getAttribute('gesperrt');

				if ($ingesamtpool == 'true' && $gesperrt == 'false')
				{
					$data = new stdClass();

					$domnodes_matrikelnummer = $row_student->getElementsByTagNameNS($namespace, 'matrikelNummer');
					foreach ($domnodes_matrikelnummer as $row)
					{
						// MatrikelNr Found
						$data->matrikelnummer = $row->textContent;
						break;
					}
					$domnodes_bpk = $row_student->getElementsByTagNameNS($namespace, 'personenkennzeichen');
					foreach ($domnodes_bpk as $row)
					{
						// BPK Found
						$data->bpk = $row->textContent;
						break;
					}
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'vorName');
					if ($domnodes->length>0)
						$data->vorname = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'nachName');
					if ($domnodes->length>0)
						$data->nachname = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'geschlecht');
					if ($domnodes->length>0)
						$data->geschlecht = $domnodes->item(0)->textContent;
					$domnodes = $row_student->getElementsByTagNameNS($namespace, 'staatsAngehoerigkeit');
					if ($domnodes->length > 0)
						$data->staatsangehoerigkeit = $domnodes->item(0)->textContent;

					$retval->data[] = $data;
				}

			}

			return ErrorHandler::success($retval);
		}
		else
		{
			$errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$response;
			return ErrorHandler::error($errormsg);
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
			$this->debug_output .= "\n".date('Y-m-d H:i:s').': '.$msg;
	}

	private function getPersonmeldungXml($bildungseinrichtung, $person)
	{
		$gebdat = str_replace("-", "", $person->geburtsdatum);

		$data = '<personmeldung xmlns="http://www.brz.gv.at/datenverbund-unis">
				<be>'.$bildungseinrichtung.'</be>
				<gebdat>'.$gebdat.'</gebdat>
				<geschlecht>'.$person->geschlecht.'</geschlecht>
				<matrikelnummer>'.$person->matrikelnummer.'</matrikelnummer>';
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
			</personmeldung>';

		return $data;
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
