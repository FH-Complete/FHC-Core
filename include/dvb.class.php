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

class dvb
{
	const DVB_URL_WEBSERVICE_OAUTH = 'https://stubei-q.portal.at/dvb/oauth/token';
	const DVB_URL_WEBSERVICE_SVNR = 'https://stubei-q.portal.at/rws/0.1/simpleStudentBySozialVersicherungsnummer.xml';
	const DVB_URL_WEBSERVICE_ERSATZKZ = 'https://stubei-q.portal.at/rws/0.1/simpleStudentByErsatzKennzeichen.xml';
	const DVB_URL_WEBSERVICE_RESERVIERUNG = 'https://stubei-q.portal.at/dvb/matrikelnummern/1.0/reservierung.xml';
	const DVB_URL_WEBSERVICE_MELDUNG = 'https://stubei-q.portal.at/dvb/matrikelnummern/1.0/meldung.xml';

	public $authentication;
	private $username;
	private $password;
	private $debug;
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
			$this->errormsg = 'Request Failed with HTTP Code:'.$curl_info['http_code'].' and Response:'.$json_response;
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
			$this->authenticate();
		}

		$this->debug('getMatirkelnrBySVNR');

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

		$this->debug('Response '.$curl_info['http_code']);
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
			$domnodes_matrikelnummer = $dom->getElementsByTagNameNS($namespace, 'matrikelNummer');
			foreach ($domnodes_matrikelnummer as $row)
			{
				// Found
				return $row->textContent;
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
			$this->authenticate();
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
		$this->debug('Response: '.$curl_info['http_code']);

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
			$domnodes_matrikelnummer = $dom->getElementsByTagNameNS($namespace, 'matrikelNummer');
			foreach ($domnodes_matrikelnummer as $row)
			{
				// Found
				return $row->textContent;
			}
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
			$this->authenticate();
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

		$this->debug('Response: '.$curl_info['http_code']);

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
			$this->authenticate();
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

		$this->debug('Response: '.$curl_info['http_code']);

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
			$this->authenticate();
		}

		$data = '<?xml version="1.0" encoding="UTF-8"?>
		<matrikelnummernmeldung>
			<uuid>'.$uuid.'</uuid>
			<personmeldung>
				<be>'.$bildungseinrichtung.'</be>
				<gebdat>'.$person->geburtsdatum.'</gebdat>
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
}
