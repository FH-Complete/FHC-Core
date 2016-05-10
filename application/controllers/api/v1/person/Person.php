<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Person extends APIv1_Controller
{
	/**
	 * Person API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('person/person_model', 'PersonModel');
		// Load set the uid of the model to let to check the permissions
		$this->PersonModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getPerson()
	{
		$personID = $this->get('person_id');
		$code = $this->get('code');
		$email = $this->get('email');
		
		if(isset($code) || isset($email) || isset($personID))
		{
			if(isset($code) && isset($email))
			{
				$result = $this->PersonModel->addJoin('public.tbl_kontakt', 'person_id');
				if($result->error == EXIT_SUCCESS)
				{
					$result = $this->PersonModel->loadWhere(array('zugangscode' => $code, 'kontakt' => $email));
				}
			}
			elseif(isset($code))
			{
				$result = $this->PersonModel->loadWhere(array('zugangscode' => $code));
			}
			else
			{
				$result = $this->PersonModel->load($personID);
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postPerson()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['person_id']))
			{
				$result = $this->PersonModel->update($this->post()['person_id'], $this->post());
			}
			else
			{
				$result = $this->PersonModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	/**
	 * @return void
	 */
	public function getCheckBewerbung()
	{
		$email = $this->get('email');
		$studiensemester_kurzbz = $this->get('studiensemester_kurzbz');
		
		if(isset($email))
		{
			$result = $this->PersonModel->checkBewerbung($email, $studiensemester_kurzbz);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($person = NULL)
	{
		if(!isset($person))
		{
			return false;
		}
		
		$person['nachname'] = trim($person['nachname']);
		$person['vorname'] = trim($person['vorname']);
		$person['vornamen'] = trim($person['vornamen']);
		$person['anrede'] = trim($person['anrede']);
		$person['titelpost'] = trim($person['titelpost']);
		$person['titelpre'] = trim($person['titelpre']);

		if(mb_strlen($person['sprache']) > 16)
		{
			//$this->errormsg = 'Sprache darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['anrede']) > 16)
		{
			//$this->errormsg = 'Anrede darf nicht laenger als 16 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['titelpost']) > 32)
		{
			//$this->errormsg = 'Titelpost darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['titelpre']) > 64)
		{
			//$this->errormsg = 'Titelpre darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['nachname']) > 64)
		{
			//$this->errormsg = 'Nachname darf nicht laenger als 64 Zeichen sein';
			return false;
		}
		if($person['nachname'] == '' || is_null($person['nachname']))
		{
			//$this->errormsg = 'Nachname muss eingegeben werden';
			return false;
		}

		if(mb_strlen($person['vorname']) > 32)
		{
			//$this->errormsg = 'Vorname darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['vornamen']) > 128)
		{
			//$this->errormsg = 'Vornamen darf nicht laenger als 128 Zeichen sein';
			return false;
		}
		//ToDo Gebdatum pruefen -> laut bis muss student aelter als 10 Jahre sein
		/* if (strlen($person['gebdatum) == 0 || is_null($person['gebdatum))
		  {
		  //$this->errormsg = "Geburtsdatum muss eingegeben werden\n";
		  return false;
		  } */
		if(mb_strlen($person['gebort']) > 128)
		{
			//$this->errormsg = 'Geburtsort darf nicht laenger als 128 Zeichen sein';
			return false;
		}

		if(mb_strlen($person['homepage']) > 256)
		{
			//$this->errormsg = 'Homepage darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['svnr']) > 16)
		{
			//$this->errormsg = 'SVNR darf nicht laenger als 16 Zeichen sein';
			return false;
		}

		if(mb_strlen($person['matr_nr']) > 32)
		{
			//$this->errormsg = 'Matrikelnummer darf nicht laenger als 32 Zeichen sein';
			return false;
		}

		if($person['svnr'] != '' && mb_strlen($person['svnr']) != 16 && mb_strlen($person['svnr']) != 10)
		{
			//$this->errormsg = 'SVNR muss 10 oder 16 Zeichen lang sein';
			return false;
		}

		if($person['svnr'] != '' && mb_strlen($person['svnr']) == 10)
		{
			//SVNR mit Pruefziffer pruefen
			//Die 4. Stelle in der SVNR ist die Pruefziffer
			//(Summe von (gewichtung[i]*svnr[i])) modulo 11 ergibt diese Pruefziffer
			//Falls nicht, ist die SVNR ungueltig
			$gewichtung = array(3, 7, 9, 0, 5, 8, 4, 2, 1, 6);
			$erg = 0;
			//Quersumme bilden
			for($i = 0; $i < 10; $i++)
			{
				$erg += $gewichtung[$i] * $person['svnr']{$i};
			}

			if($person['svnr']{3} != ($erg % 11)) //Vergleichen der Pruefziffer mit Quersumme Modulo 11
			{
				//$this->errormsg = 'SVNR ist ungueltig';
				return false;
			}
		}

		if($person['svnr'] != '')
		{
			//Pruefen ob bereits ein Eintrag mit dieser SVNR vorhanden ist
			$qry = "SELECT person_id FROM public.tbl_person WHERE svnr=" . $person['svnr'];
			if(db_query($qry))
			{
				if($row = db_fetch_object())
				{
					if($row->person_id != $person['person_id'])
					{
						//$this->errormsg = 'Es existiert bereits eine Person mit dieser SVNR! Daten wurden NICHT gepeichert.';
						return false;
					}
				}
			}
		}

		if(mb_strlen($person['ersatzkennzeichen']) > 10)
		{
			//$this->errormsg = 'Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['familienstand']) > 1)
		{
			//$this->errormsg = 'Familienstand ist ungueltig';
			return false;
		}
		if($person['anzahlkinder'] != '' && !is_numeric($person['anzahlkinder']))
		{
			//$this->errormsg = 'Anzahl der Kinder ist ungueltig';
			return false;
		}
		if($person['aktiv'] != "t" && $person['aktiv'] != "f")
		{
			//$this->errormsg = 'Aktiv ist ungueltig';
			return false;
		}
		if(!isset($person['person_id']) && mb_strlen($person['insertvon']) > 32)
		{
			//$this->errormsg = 'Insertvon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['updatevon']) > 32)
		{
			//$this->errormsg = 'Updatevon darf nicht laenger als 32 Zeichen sein';
			return false;
		}
		/*if($person['ext_id'] != '' && !is_numeric($person['ext_id']))
		{
			//$this->errormsg = 'Ext_ID ist keine gueltige Zahl';
			return false;
		}*/
		if(mb_strlen($person['geschlecht']) > 1)
		{
			//$this->errormsg = 'Geschlecht darf nicht laenger als 1 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['geburtsnation']) > 3)
		{
			//$this->errormsg = 'Geburtsnation darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if(mb_strlen($person['staatsbuergerschaft']) > 3)
		{
			//$this->errormsg = 'Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein';
			return false;
		}
		if($person['geschlecht'] != 'm' && $person['geschlecht'] != 'w' && $person['geschlecht'] != 'u')
		{
			//$this->errormsg = 'Geschlecht muss w, m oder u sein!';
			return false;
		}

		//Pruefen ob das Geburtsdatum mit der SVNR uebereinstimmt.
		if($person['svnr'] != '' && $person['gebdatum'] != '')
		{
			if(mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $person['gebdatum'], $regs))
			{
				//$day = sprintf('%02s',$regs[1]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[3],2,2);
			}
			elseif(mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $person['gebdatum'], $regs))
			{
				//$day = sprintf('%02s',$regs[3]);
				//$month = sprintf('%02s',$regs[2]);
				//$year = mb_substr($regs[1],2,2);
			}
			else
			{
				//$this->errormsg = 'Format des Geburtsdatums ist ungueltig';
				return false;
			}

			/* das muss nicht immer so sein
			  $day_svnr = mb_substr($person['svnr, 4, 2);
			  $month_svnr = mb_substr($person['svnr, 6, 2);
			  $year_svnr = mb_substr($person['svnr, 8, 2);

			  if ($day_svnr!=$day || $month_svnr!=$month || $year_svnr!=$year)
			  {
			  //$this->errormsg = 'SVNR und Geburtsdatum passen nicht zusammen';
			  return false;
			  }
			 */
		}

		return true;
	}
}