<?php

require_once(dirname(__FILE__).'/basis_db.class.php');
require_once(dirname(__FILE__).'/prestudent.class.php');
require_once(dirname(__FILE__).'/person.class.php');
require_once(dirname(__FILE__).'/reihungstest.class.php');

require_once(dirname(__FILE__).'/../vendor/autoload.php');
use Firebase\JWT\JWT;

class externeUeberwachung extends basis_db
{
	public function __construct()
	{
		parent::__construct();
	}

	public function getStatusByPrestudent($prestudent_id)
	{
		$session_id = $this->getSessionByPrestudent($prestudent_id);
		return $this->getSessionStatus($session_id);
	}
	public function start($prestudent_id, $reihungstest_id)
	{
		$session_id = $this->getSessionByPrestudent($prestudent_id);

		if (!$session_id)
		{
			$session_id = $this->createSession($prestudent_id);
		}
		else
		{
			$status = $this->getSessionStatus($session_id);

			if (in_array($status, array('late_to_start', 'finished')))
			{
				$session_id = $this->createSession($prestudent_id);
			}
		}

		$payload = $this->getPayload($session_id, $prestudent_id, $reihungstest_id);
		return $this->getStartUrl($payload);
	}


	private function createSession($prestudent_id)
	{
		if (is_null($prestudent_id))
		{
			$this->errormsg = 'Falsche Parameterübergabe';
			return false;
		}

		$uuid = $this->genereateUUID();

		$qry = "INSERT INTO testtool.tbl_externe_ueberwachung (prestudent_id, session_id, insertvon)
				VALUES (".
			$this->db_add_param($prestudent_id).",".
			$this->db_add_param($uuid).",".
			$this->db_add_param(get_uid()).")";

		if($this->db_query($qry))
		{
			return $uuid;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Antwort';
			return false;
		}
	}
	public function getSessionByPrestudent($prestudent_id)
	{
		if (is_null($prestudent_id))
		{
			$this->errormsg = 'Falsche Parameterübergabe';
			return false;
		}

		$qry = "SELECT session_id
				FROM testtool.tbl_externe_ueberwachung
				ORDER BY insertamum DESC
				LIMIT 1";

		if($result = $this->db_query($qry))
		{
			if ($row = $this->db_fetch_object($result))
			{
				return $row->session_id;
			}
			else
			{
				$this->errormsg = 'Daten konnten nicht geladen werden';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Abfrage';
			return false;
		}
	}

	public function getSessionStatus($session_id)
	{
		$payload = $this->getSessionPayload($session_id);
		$jwt = $this->createToken($payload);

		$url = $this->getSessionUrl($session_id);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Authorization: JWT {$jwt}",
			"Content-Type: application/json",
		]);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$data = json_decode($response, true);
		return isset($data['status']) ? $data['status'] : false;
	}

	private function getSessionPayload($session_id)
	{
		return [
			"session_id" => $session_id,
			"iat" => time(),
			"exp" => time() + 120,
		];
	}

	private function getPayload($session_id, $prestudent_id, $reihungstest_id)
	{
		$prestudent = new prestudent($prestudent_id);
		$person = new Person($prestudent->person_id);

		$reihungstest = new Reihungstest($reihungstest_id);

		$datetime = new DateTime();
		$today = $datetime->format('Y-m-d');

		$payload = [
			"userId"=> $prestudent_id,
			"lastName"=> $person->nachname,
			"firstName"=> $person->vorname,
			"language"=> $person->sprache,
			"accountName"=> "technikum_wien",
			"accountId"=> "technikum_wien",
			"examId" => !is_null(trim($reihungstest->anmerkung)) ? $reihungstest->anmerkung : ($today . " RT Test"),
			"examName" => !is_null(trim($reihungstest->anmerkung)) ? $reihungstest->anmerkung : ($today . " RT Test"),
			"allowMultipleDisplays" => true,
			"allowMakingRoomScanSecondCamera" => false,
			"duration"=> 120,
			"schedule"=> false,
			"trial"=> true,
			"proctoring"=> "offline",
			"identification"=> "skip",
			"startDate"=> "2018-03-27T00:00:00Z", //TODO anpassen
			"endDate"=> "2027-03-30T12:55:00Z", // TODO anpassen
			"sessionId"=> $session_id,
			"sessionUrl"=> "https://demo.dev.technikum-wien.at/cis/testtool/index.php"
		];
		return $payload;
	}

	private function getSessionUrl($session_id)
	{
		return EXTERNE_UEBERWACHUNG_PROTOCOL_URL . "/api/v2/integration/simple/". EXTERNE_UEBERWACHUNG_INTEGRATION_NAME . "/sessions/". urlencode($session_id) ."/status/";
	}

	private function getStartUrl($payload)
	{
		$token = $this->createToken($payload);
		$query = http_build_query(['token' => $token]);

		return EXTERNE_UEBERWACHUNG_PROTOCOL_URL . '/integration/simple/'. EXTERNE_UEBERWACHUNG_INTEGRATION_NAME .'/start?' . $query;
	}

	private function createToken($payload)
	{
		return JWT::encode($payload, EXTERNE_UEBERWACHUNG_SECRET_KEY, 'HS256');
	}

	private function genereateUUID()
	{
		$data = openssl_random_pseudo_bytes(16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}

?>



